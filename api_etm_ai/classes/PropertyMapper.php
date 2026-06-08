<?php
/**
 * Маппинг свойств API ETM → инфоблок.
 * Сопоставление по columns.csv; новые свойства → new_properties.csv.
 */

class ApiEtmPropertyMapper
{
    /** @var array нормализованное имя колонки => код свойства */
    private $columnsMap = [];

    /** @var string */
    private $columnsPath;

    /** @var string */
    private $newPropertiesPath;

    /** @var int */
    private $iblockId;

    /** @var array уже добавленные в new_properties в этой сессии */
    private $addedNew = [];

    public function __construct(int $iblockId, string $columnsPath, string $newPropertiesPath)
    {
        $this->iblockId = $iblockId;
        $this->columnsPath = $columnsPath;
        $this->newPropertiesPath = $newPropertiesPath;
        $this->loadColumnsMap();
    }

    private function loadColumnsMap(): void
    {
        $this->columnsMap = [];
        if (!is_readable($this->columnsPath)) {
            return;
        }
        $lines = file($this->columnsPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $header = true;
        foreach ($lines as $line) {
            $line = trim(preg_replace('/^\xEF\xBB\xBF/', '', $line));
            if ($line === '') {
                continue;
            }
            $row = str_getcsv($line, ';');
            if ($header && isset($row[0]) && (stripos($row[0], 'название') !== false || stripos($row[0], 'name') !== false)) {
                $header = false;
                continue;
            }
            $header = false;
            $name = isset($row[0]) ? trim((string)$row[0]) : '';
            $code = isset($row[1]) ? trim((string)$row[1]) : '';
            if ($name === '' || $code === '') {
                continue;
            }
            $norm = $this->normalizeColumnName($name);
            $this->columnsMap[$norm] = ['name' => $name, 'code' => $code];
        }
    }

    public function normalizeColumnName(string $s): string
    {
        $s = trim($s);
        $s = mb_strtolower($s);
        $s = preg_replace('/\s+/u', ' ', $s);
        return $s;
    }

    /**
     * Транслит для кода свойства (заглавные латинские).
     */
    public function transliterateCode(string $ru): string
    {
        if (!class_exists('CUtil') || !method_exists('CUtil', 'translit')) {
            return $this->simpleTranslit($ru);
        }
        $code = CUtil::translit($ru, 'ru', [
            'max_len' => 50,
            'change_case' => 'U',
            'replace_space' => '_',
            'replace_other' => '_',
            'delete_repeat_replace' => true,
        ]);
        return $code ?: $this->simpleTranslit($ru);
    }

    private function simpleTranslit(string $s): string
    {
        $map = [
            'а' => 'A', 'б' => 'B', 'в' => 'V', 'г' => 'G', 'д' => 'D', 'е' => 'E', 'ё' => 'E',
            'ж' => 'ZH', 'з' => 'Z', 'и' => 'I', 'й' => 'Y', 'к' => 'K', 'л' => 'L', 'м' => 'M',
            'н' => 'N', 'о' => 'O', 'п' => 'P', 'р' => 'R', 'с' => 'S', 'т' => 'T', 'у' => 'U',
            'ф' => 'F', 'х' => 'H', 'ц' => 'TS', 'ч' => 'CH', 'ш' => 'SH', 'щ' => 'SCH',
            'ъ' => '', 'ы' => 'Y', 'ь' => '', 'э' => 'E', 'ю' => 'YU', 'я' => 'YA',
        ];
        $s = mb_strtolower($s);
        $out = '';
        $len = mb_strlen($s);
        for ($i = 0; $i < $len; $i++) {
            $c = mb_substr($s, $i, 1);
            $out .= $map[$c] ?? (preg_match('/[a-z0-9]/i', $c) ? strtoupper($c) : '_');
        }
        $out = preg_replace('/_+/', '_', trim($out, '_'));
        return $out ?: 'PROP';
    }

    /**
     * По имени колонки из API вернуть код свойства.
     * При отсутствии в columns.csv: создать новое свойство, записать в new_properties.csv.
     */
    public function resolvePropertyCode(string $apiColumnName, ?\Closure $log = null): ?string
    {
        $norm = $this->normalizeColumnName($apiColumnName);
        if (isset($this->columnsMap[$norm])) {
            return $this->columnsMap[$norm]['code'];
        }

        // Свойство не найдено в маппинге - создаем новое
        $code = $this->transliterateCode($apiColumnName);
        if ($code === '') {
            $code = 'PROP_' . mb_substr(md5($apiColumnName), 0, 8);
        }
        $this->appendNewProperty($apiColumnName, $code, $log);
        $this->columnsMap[$norm] = ['name' => $apiColumnName, 'code' => $code];
        return $code;
    }
    
    /**
     * Получить имя свойства по коду (для передачи в ensurePropertyExists).
     */
    public function getPropertyNameForCode(string $code, string $apiColumnName): string
    {
        // Сначала проверяем в маппинге
        foreach ($this->columnsMap as $v) {
            if (isset($v['code']) && $v['code'] === $code) {
                return $v['name'];
            }
        }
        // Если не найдено - используем название из API или сам код
        return $apiColumnName ?: $code;
    }

    private function appendNewProperty(string $nameRu, string $code, ?\Closure $log): void
    {
        $key = $nameRu . '|' . $code;
        if (isset($this->addedNew[$key])) {
            return;
        }
        $this->addedNew[$key] = true;
        $line = $nameRu . ' [' . $code . ']';
        $dir = dirname($this->newPropertiesPath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        if (!file_exists($this->newPropertiesPath)) {
            $bom = "\xEF\xBB\xBF";
            $header = "Русское название;Код свойства (транслит)\n";
            @file_put_contents($this->newPropertiesPath, $bom . $header, LOCK_EX);
        }
        @file_put_contents($this->newPropertiesPath, $line . "\n", FILE_APPEND | LOCK_EX);
        if ($log) {
            $log("Новое свойство: $line");
        }
    }

    /**
     * Получить или создать свойство в инфоблоке по коду.
     * Возвращает ID свойства или null.
     * Если свойство создано новое - записывает в txt-файл для дальнейшей обработки.
     */
    public function ensurePropertyExists(string $code, string $nameRu, ?string $apiColumnName = null): ?int
    {
        if (!CModule::IncludeModule('iblock')) {
            return null;
        }
        $res = CIBlockProperty::GetList(
            [],
            ['IBLOCK_ID' => $this->iblockId, 'CODE' => $code]
        );
        $prop = $res->Fetch();
        if ($prop) {
            return (int)$prop['ID'];
        }

        // Свойство не найдено - создаем новое
        $ob = new CIBlockProperty();
        $fields = [
            'IBLOCK_ID' => $this->iblockId,
            'CODE' => $code,
            'NAME' => $nameRu,
            'ACTIVE' => 'Y',
            'SORT' => 500,
            'PROPERTY_TYPE' => 'S',
            'MULTIPLE' => 'N',
            'IS_REQUIRED' => 'N',
            'SEARCHABLE' => 'N',
            'FILTRABLE' => 'Y',
            'ROW_COUNT' => 1,
            'COL_COUNT' => 30,
        ];
        $id = $ob->Add($fields);
        
        // Если свойство успешно создано - записываем в txt-файл для дальнейшей обработки
        if ($id) {
            $this->logNewProperty($code, $nameRu, $apiColumnName);
            return (int)$id;
        }
        
        return null;
    }
    
    /**
     * Записать информацию о новом созданном свойстве в txt-файл для дальнейшей обработки.
     */
    private function logNewProperty(string $code, string $nameRu, ?string $apiColumnName = null): void
    {
        $logPath = defined('API_ETM_NEW_PROPERTIES_LOG') ? API_ETM_NEW_PROPERTIES_LOG : (dirname(__DIR__) . '/logs/new_properties_log.txt');
        $logDir = dirname($logPath);
        
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $apiInfo = $apiColumnName ? " (из API: $apiColumnName)" : '';
        $line = "[$timestamp] [$code] - $nameRu$apiInfo\n";
        
        @file_put_contents($logPath, $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * Маппинг массива [имя_колонки => значение] в [CODE => значение] для PROPERTY_VALUES.
     */
    public function mapRowToProperties(array $row, ?\Closure $log = null): array
    {
        $out = [];
        foreach ($row as $colName => $value) {
            if ($value === '' || $value === null) {
                continue;
            }
            $code = $this->resolvePropertyCode($colName, $log);
            if ($code) {
                $out[$code] = $value;
            }
        }
        return $out;
    }

    public function getColumnsMap(): array
    {
        return $this->columnsMap;
    }

    /** Имя по коду (из columns.csv) или сам код. */
    public function getPropertyNameByCode(string $code): string
    {
        foreach ($this->columnsMap as $v) {
            if (isset($v['code']) && $v['code'] === $code) {
                return $v['name'];
            }
        }
        return $code;
    }
}
