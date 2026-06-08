<?php
/**
 * Cron: обновление остатков ETM-товаров в Bitrix.
 * Запускать дважды в день. Из-за лимита 1 req/сек на remains
 * скрипт обрабатывает не более MAX_PER_RUN товаров за запуск
 * и сохраняет позицию (offset) для следующего запуска.
 *
 * За одну сессию (MAX_PER_RUN=1800): ~30 минут работы.
 * При 2 запусках/день покрывает ~3600 товаров/день.
 * Полный цикл 131 270 товаров ≈ 36 дней — для первичного прохода,
 * потом можно увеличить частоту запусков.
 *
 * Запуск из crontab (дважды в день: 06:00 и 14:00):
 *   0 6,14 * * * php /var/www/www-root/data/www/lvtgroup.ru/api_etm_ai/cron/update_remains.php >> /var/www/www-root/data/www/lvtgroup.ru/api_etm_ai/logs/cron_remains.log 2>&1
 */

set_time_limit(3600);
ini_set('memory_limit', '256M');

define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);

define('MAX_PER_RUN', 1800); // товаров за один запуск (≈30 минут при 1 req/сек)

// Bootstrap
$docRoot = '';
if (php_sapi_name() === 'cli') {
    $tryRoots = [
        '/var/www/www-root/data/www/lvtgroup.ru',
        dirname(dirname(__DIR__)),
    ];
    foreach ($tryRoots as $r) {
        if (is_dir($r . '/bitrix')) { $docRoot = $r; break; }
    }
} else {
    $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/');
}
if ($docRoot === '') { die("Cannot find document root\n"); }

$_SERVER['DOCUMENT_ROOT'] = $docRoot;
require_once $docRoot . '/bitrix/modules/main/include/prolog_before.php';
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/classes/EtmApiClient.php';

if (!CModule::IncludeModule('iblock'))  { die("iblock not loaded\n"); }
if (!CModule::IncludeModule('catalog')) { die("catalog not loaded\n"); }

$logFile = API_ETM_LOGS_DIR . '/cron_remains_' . date('Y-m-d') . '.log';
if (!is_dir(API_ETM_LOGS_DIR)) { @mkdir(API_ETM_LOGS_DIR, 0755, true); }

// Файл состояния для сохранения позиции между запусками
$remainsStateFile = API_ETM_LOGS_DIR . '/remains_offset.json';

function rmLog(string $msg): void {
    global $logFile;
    $line = '[' . date('H:i:s') . '] ' . $msg . "\n";
    echo $line;
    @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

rmLog('=== ETM update_remains START ===');

$iblockId = (int)API_ETM_IBLOCK_ID;
$propCode = API_ETM_PROP_ETM_CODE;
$storeId  = (int)API_ETM_STORE_ID;

// Загружаем все ETM-коды
rmLog('Загружаем ETM-коды из Bitrix IB' . $iblockId . '...');
$etmMap = []; // [etmCode => elementId]
$res = CIBlockElement::GetList(
    [], ['IBLOCK_ID' => $iblockId, '!PROPERTY_' . $propCode => false],
    false, false, ['ID', 'PROPERTY_' . $propCode]
);
while ($row = $res->Fetch()) {
    $val = $row['PROPERTY_' . strtoupper($propCode) . '_VALUE'];
    if ((string)$val !== '') {
        $etmMap[(string)$val] = (int)$row['ID'];
    }
}
$allCodes = array_keys($etmMap);
$total = count($allCodes);
rmLog("Найдено $total товаров с ETM-кодом.");

if ($total === 0) {
    rmLog('Нечего обновлять.');
    exit(0);
}

// Читаем сохранённый offset (позиция в массиве для продолжения)
$savedState = [];
if (file_exists($remainsStateFile)) {
    $savedState = json_decode(file_get_contents($remainsStateFile), true) ?? [];
}
$offset = (int)($savedState['offset'] ?? 0);
if ($offset >= $total) {
    $offset = 0; // Начинаем новый цикл
    rmLog("Новый цикл обновления остатков (предыдущий завершён).");
}
rmLog("Начинаем с позиции $offset из $total (обработаем до " . MAX_PER_RUN . " товаров).");

// Авторизация
$client = new ApiEtmClient(ETM_API_URL, ETM_LOGIN, ETM_PASSWORD);
if (!$client->login()) {
    rmLog('ОШИБКА авторизации ETM API. HTTP=' . $client->lastHttpCode);
    exit(1);
}
rmLog('Авторизация OK.');

$slice   = array_slice($allCodes, $offset, MAX_PER_RUN);
$updated = 0;
$errors  = 0;
$cnt     = count($slice);

foreach ($slice as $i => $etmCode) {
    $elId = $etmMap[$etmCode];
    $data = $client->getGoodsRemains($etmCode, 'etm');

    if ($data === null) {
        $errors++;
        continue;
    }

    // Ищем суммарный остаток: StoreType='all'
    $qty = 0;
    $stores = $data['data']['InfoStores'] ?? ($data['InfoStores'] ?? []);
    foreach ((array)$stores as $store) {
        $type = $store['StoreType'] ?? '';
        if ($type === 'all' || $type === '') {
            $qty = max($qty, (int)($store['StoreQuantRem'] ?? 0));
        }
    }

    // Если нет 'all' — суммируем все склады типа 'reg'
    if ($qty === 0) {
        foreach ((array)$stores as $store) {
            if (($store['StoreType'] ?? '') === 'reg') {
                $qty += (int)($store['StoreQuantRem'] ?? 0);
            }
        }
    }

    // Обновляем количество в каталоге Bitrix
    $prod = CCatalogProduct::GetByID($elId);
    if ($prod) {
        CCatalogProduct::Update($elId, ['QUANTITY' => $qty]);
    } else {
        CCatalogProduct::Add(['ID' => $elId, 'QUANTITY' => $qty]);
    }
    $updated++;

    if (($i + 1) % 300 === 0) {
        rmLog(sprintf('  [%d/%d] обновлено=%d ошибок=%d', $i+1, $cnt, $updated, $errors));
    }
}

// Сохраняем новый offset
$newOffset = $offset + $cnt;
$stateToSave = [
    'offset'     => $newOffset >= $total ? 0 : $newOffset,
    'total'      => $total,
    'last_run'   => date('Y-m-d H:i:s'),
    'last_range' => "$offset - " . ($offset + $cnt - 1),
];
file_put_contents($remainsStateFile, json_encode($stateToSave, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

$cycleComplete = $newOffset >= $total;
rmLog(sprintf(
    "=== ГОТОВО: обновлено=%d, ошибок=%d. Позиция: %d/%d%s ===",
    $updated, $errors, min($newOffset, $total), $total,
    $cycleComplete ? ' (цикл завершён)' : ''
));
