<?php
// В начале файла удаляем все лимиты (по вашему требованию)
// ini_set и set_time_limit удалены

// БЕЗОПАСНОСТЬ: Разрешить выполнение только из командной строки (CLI)
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    die('Доступ запрещен. Скрипт доступен только из командной строки (CLI).');
}

if (empty($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['DOCUMENT_ROOT'] = '/var/www/www-root/data/www/lvtgroup.ru';
}

$prologPath = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
if (!file_exists($prologPath)) {
    die("Файл prolog_before.php не найден по пути: $prologPath\n");
}
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Loader;
use Bitrix\Iblock\IblockSectionTable;
use Bitrix\Catalog\PriceTable;

if (!CModule::IncludeModule('iblock')) {
    die('Не загружён модуль iblock');
}
if (!CModule::IncludeModule('catalog')) {
    die('Не загружён модуль catalog');
}
require_once __DIR__ . '/promelec_pricebreaks_helper.php';

// Статистика импорта
$statsFile = __DIR__ . '/import_stats.json';
$stats = [
    'start_time' => date('Y-m-d H:i:s'),
    'total_received_from_api' => 0,
    'total_processed' => 0,
    'total_created' => 0,
    'total_updated' => 0,
    'total_errors' => 0,
    'memory_peak' => 0,
    'end_time' => null,
    'batches_processed' => 0,
    'last_item_id' => null
];

function saveStats($stats) {
    global $statsFile;
    $stats['memory_peak'] = round(memory_get_peak_usage(true) / 1024 / 1024, 2); // MB
    file_put_contents($statsFile, json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Лог статистики
$statLogFile = __DIR__ . '/import_statistics.log';
function logStat($message) {
    global $statLogFile;
    $memory = round(memory_get_usage(true) / 1024 / 1024, 2);
    file_put_contents($statLogFile, date('Y-m-d H:i:s') . " [{$memory}MB] - " . $message . PHP_EOL, FILE_APPEND | LOCK_EX);
}

$priceTypeId = 1;
$iblock_id = 11;
$logFile = __DIR__ . '/product_import.log';
$lastProcessedIdFile = __DIR__ . '/last_processed_item_id.txt';

// Тестовый режим: обрабатывать только товар по коду (item_id или подстрока названия/артикула)
$testCode = null;
foreach (array_slice($_SERVER['argv'] ?? [], 1) as $arg) {
    if (strpos($arg, '--test=') === 0) {
        $testCode = trim(substr($arg, 7));
        if ($testCode !== '' && is_numeric($testCode)) {
            $testCode = (int) $testCode;
        }
        break;
    }
}

// Маппинг срок доставки (дней) → ID склада Битрикс (загружаем и мержим с конфигом)
$PROMELEC_DELIVERY_TO_STORE = [
    0  => 9,  // "на складе"
    2  => 10,
    5  => 1,
    7  => 2,
    12 => 44,
    23 => 3,
    26 => 4,
    37 => 5,
    91 => 8,
];
$PROMELEC_DELIVERY_TO_PRICE_TYPE = [
    0  => 9,
    2  => 10,
    5  => 2,
    7  => 3,
    12 => 44,
    23 => 4,
    26 => 5,
    37 => 6,
    91 => 7,
];
$docRoot = $_SERVER['DOCUMENT_ROOT'] ?: dirname(__DIR__);
$deliveryMappingConfig = $docRoot . '/config/promelec_delivery_mapping.php';
$deliveryMappingFile = $docRoot . '/api/data/promelec_delivery_mapping.php';
$extra = ['delivery_to_store' => [], 'delivery_to_price_type' => []];
foreach ([$deliveryMappingConfig, $deliveryMappingFile] as $f) {
    if (file_exists($f)) {
        $c = include $f;
        if (is_array($c)) {
            $extra['delivery_to_store'] = ($extra['delivery_to_store'] ?? []) + ($c['delivery_to_store'] ?? []);
            $extra['delivery_to_price_type'] = ($extra['delivery_to_price_type'] ?? []) + ($c['delivery_to_price_type'] ?? []);
        }
    }
}
$PROMELEC_DELIVERY_TO_STORE = $PROMELEC_DELIVERY_TO_STORE + $extra['delivery_to_store'];
$PROMELEC_DELIVERY_TO_PRICE_TYPE = $PROMELEC_DELIVERY_TO_PRICE_TYPE + $extra['delivery_to_price_type'];
// Сроки доставки > 60 дней без явного маппинга → склад 8, тип цены 7 (PROMELEC7)
for ($d = 61; $d <= 365; $d++) {
    if (!isset($PROMELEC_DELIVERY_TO_STORE[$d])) {
        $PROMELEC_DELIVERY_TO_STORE[$d] = 8;
        $PROMELEC_DELIVERY_TO_PRICE_TYPE[$d] = 7;
    }
}

function logMessage($message) {
    global $logFile;
    $memory = round(memory_get_usage(true) / 1024 / 1024, 2);
    file_put_contents($logFile, date('Y-m-d H:i:s') . " [{$memory}MB] - " . $message . PHP_EOL, FILE_APPEND | LOCK_EX);
}

// ИСПРАВЛЕННАЯ ФУНКЦИЯ: правильная пагинация согласно документации API
function fetchApiData($lastItemId = null, $inStock = false) {
    global $login, $password_md5, $api_url;
    
    logStat("API запрос: lastItemId={$lastItemId}, inStock={$inStock}");
    
    $request_data = [
        'login' => $login,
        'password' => $password_md5,
        'method' => 'items_data_get',
        'customer_id' => 148949,
    ];

    // Добавляем item_id если это не первый запрос
    if ($lastItemId !== null) {
        $request_data['item_id'] = $lastItemId;
    }
    
    // Параметр для получения только товаров в наличии
    if ($inStock) {
        $request_data['in_stock'] = 'true';
    }

    $request_json = json_encode($request_data, JSON_UNESCAPED_UNICODE);
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $api_url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $request_json,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 600,
        CURLOPT_CONNECTTIMEOUT => 120,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($http_code !== 200 || !$response) {
        logMessage("❌ Ошибка API: HTTP $http_code, ошибка: $error");
        logStat("❌ Ошибка API: HTTP $http_code");
        return false;
    }
    
    $data = json_decode($response, true, 512, JSON_UNESCAPED_UNICODE);
    if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
        logMessage("❌ Ошибка разбора JSON: " . json_last_error_msg() . " Ответ: " . substr($response, 0, 500));
        return false;
    }
    
    logStat("✅ Получено от API: " . count($data) . " товаров");
    return $data;
}

// Глобальные кэши
$enumCache = [];
$sectionCache = [];
$brandCache = [];

// Кэш для enum значений с оптимизацией
function ensureEnumValueExists($propertyId, $value) {
    global $enumCache;
    
    if (empty($value)) {
        return false;
    }
    
    $cacheKey = $propertyId . '|' . $value;
    if (isset($enumCache[$cacheKey])) {
        return $enumCache[$cacheKey];
    }

    // Пакетный поиск для оптимизации
    static $batchCache = [];
    if (!isset($batchCache[$propertyId])) {
        $batchCache[$propertyId] = [];
    }
    $batchCache[$propertyId][$value] = true;
    
    // Если накопилось много значений, ищем все сразу
    if (count($batchCache[$propertyId]) >= 50) {
        $valuesToSearch = array_keys($batchCache[$propertyId]);
        $dbRes = CIBlockPropertyEnum::GetList(
            [],
            ['PROPERTY_ID' => $propertyId, 'VALUE' => $valuesToSearch]
        );
        while ($enum = $dbRes->Fetch()) {
            $enumCache[$propertyId . '|' . $enum['VALUE']] = $enum['ID'];
        }
        
        // Создаем недостающие значения
        foreach ($valuesToSearch as $val) {
            if (!isset($enumCache[$propertyId . '|' . $val])) {
                $enumFields = [
                    'PROPERTY_ID' => $propertyId,
                    'VALUE'       => $val,
                    'XML_ID'      => md5($val),
                ];
                
                $enumValueId = CIBlockPropertyEnum::Add($enumFields);
                if ($enumValueId) {
                    $enumCache[$propertyId . '|' . $val] = $enumValueId;
                }
            }
        }
        
        $batchCache[$propertyId] = [];
    }
    
    // Если значение уже в кэше, возвращаем
    if (isset($enumCache[$cacheKey])) {
        return $enumCache[$cacheKey];
    }
    
    // Ищем отдельное значение если не нашли в пакете
    $dbRes = CIBlockPropertyEnum::GetList([], ['PROPERTY_ID' => $propertyId, 'VALUE' => $value]);
    if ($enum = $dbRes->Fetch()) {
        $enumCache[$cacheKey] = $enum['ID'];
        return $enum['ID'];
    }

    // Создаем новое значение
    $enumFields = [
        'PROPERTY_ID' => $propertyId,
        'VALUE'       => $value,
        'XML_ID'      => md5($value),
    ];
    
    $enumValueId = CIBlockPropertyEnum::Add($enumFields);
    if ($enumValueId) {
        $enumCache[$cacheKey] = $enumValueId;
        return $enumValueId;
    } else {
        return false;
    }
}

// Оптимизированная функция для работы с разделами
function getOrCreateSection($sectionName, $iblockId, $parentId = false) {
    global $sectionCache;
    
    $parentId = (int)$parentId;
    if (empty($sectionName)) {
        $sectionName = 'Без категории';
    }
    
    $cacheKey = $iblockId . '|' . $sectionName . '|' . $parentId;
    
    if (isset($sectionCache[$cacheKey])) {
        return $sectionCache[$cacheKey];
    }

    // Пакетный поиск разделов
    static $sectionBatch = [];
    if (!isset($sectionBatch[$iblockId])) {
        $sectionBatch[$iblockId] = [];
    }
    
    $sectionBatch[$iblockId][$cacheKey] = ['NAME' => $sectionName, 'PARENT' => $parentId];
    
    // Если накопилось много разделов, ищем все сразу
    if (count($sectionBatch[$iblockId]) >= 100) {
        $names = array_column($sectionBatch[$iblockId], 'NAME');
        $filter = [
            'IBLOCK_ID'         => $iblockId,
            'NAME'              => $names,
            'GLOBAL_ACTIVE'     => 'Y',
            'CHECK_PERMISSIONS' => 'N',
        ];
        
        $dbRes = CIBlockSection::GetList(
            ['ID' => 'ASC'],
            $filter,
            false,
            ['ID', 'NAME', 'IBLOCK_SECTION_ID']
        );
        
        $foundSections = [];
        while ($section = $dbRes->Fetch()) {
            $foundSections[$section['NAME'] . '|' . $section['IBLOCK_SECTION_ID']] = $section['ID'];
        }
        
        // Создаем недостающие разделы
        foreach ($sectionBatch[$iblockId] as $key => $sectionData) {
            $searchKey = $sectionData['NAME'] . '|' . $sectionData['PARENT'];
            if (isset($foundSections[$searchKey])) {
                $sectionCache[$key] = $foundSections[$searchKey];
            } else {
                $bs = new CIBlockSection();
                $fields = [
                    'IBLOCK_ID'          => $iblockId,
                    'NAME'               => $sectionData['NAME'],
                    'ACTIVE'             => 'Y',
                    'CODE'               => CUtil::translit($sectionData['NAME'], 'ru', [
                        'max_len' => 100,
                        'replace_space' => '_',
                        'replace_other' => '_',
                        'delete_repeat_replace' => true,
                    ]),
                    'IBLOCK_SECTION_ID'  => $sectionData['PARENT'] > 0 ? $sectionData['PARENT'] : false,
                ];
                
                $newId = $bs->Add($fields);
                if ($newId) {
                    $sectionCache[$key] = $newId;
                } else {
                    $sectionCache[$key] = false;
                }
            }
        }
        
        $sectionBatch[$iblockId] = [];
    }
    
    // Если значение уже в кэше, возвращаем
    if (isset($sectionCache[$cacheKey])) {
        return $sectionCache[$cacheKey];
    }
    
    // Ищем раздел отдельно если не нашли в пакете
    $filter = [
        'IBLOCK_ID'         => $iblockId,
        'NAME'              => $sectionName,
        'GLOBAL_ACTIVE'     => 'Y',
        'CHECK_PERMISSIONS' => 'N',
    ];

    if ($parentId > 0) {
        $filter['IBLOCK_SECTION_ID'] = $parentId;
    } else {
        $filter['DEPTH_LEVEL'] = 1;
    }

    $dbRes = CIBlockSection::GetList(
        ['ID' => 'ASC'],
        $filter,
        false,
        ['ID']
    );

    if ($section = $dbRes->Fetch()) {
        $sectionCache[$cacheKey] = $section['ID'];
        return $section['ID'];
    }

    // Создаем новый раздел
    $bs = new CIBlockSection();
    $fields = [
        'IBLOCK_ID'          => $iblockId,
        'NAME'               => $sectionName,
        'ACTIVE'             => 'Y',
        'CODE'               => CUtil::translit($sectionName, 'ru', [
            'max_len' => 100,
            'replace_space' => '_',
            'replace_other' => '_',
            'delete_repeat_replace' => true,
        ]),
        'IBLOCK_SECTION_ID'  => $parentId > 0 ? $parentId : false,
    ];

    $newId = $bs->Add($fields);

    if (!$newId) {
        logMessage('❌ Ошибка создания раздела "' . $sectionName . '" (IBLOCK_ID=' . $iblockId . ', PARENT=' . $parentId . ')');
        $sectionCache[$cacheKey] = false;
        return false;
    }

    $sectionCache[$cacheKey] = $newId;
    return $newId;
}

// Функция для получения/создания бренда (СВОЙСТВО 66)
function getOrCreateBrand($brandName) {
    global $brandCache;
    
    if (empty($brandName)) {
        return '';
    }
    
    if (isset($brandCache[$brandName])) {
        return $brandCache[$brandName];
    }

    // Ищем существующий бренд
    $dbRes = CIBlockElement::GetList(
        [],
        [
            'IBLOCK_ID' => 6,
            'NAME' => $brandName,
            'ACTIVE' => 'Y'
        ],
        false,
        ['nTopCount' => 1],
        ['ID', 'NAME']
    );
    
    if ($brandElement = $dbRes->Fetch()) {
        $brandCache[$brandName] = $brandElement['ID'];
        return $brandElement['ID'];
    }
    
    // Создаем новый бренд
    $el = new CIBlockElement();
    $brandFields = [
        'IBLOCK_ID' => 6,
        'NAME' => $brandName,
        'ACTIVE' => 'Y',
        'CODE' => CUtil::translit($brandName, "ru", [
            "replace_space" => "-",
            "replace_other" => "-",
            "change_case" => "L"
        ])
    ];
    
    $newBrandId = $el->Add($brandFields);
    if ($newBrandId) {
        $brandCache[$brandName] = $newBrandId;
        logMessage("🎉 Создан новый бренд: '{$brandName}' (ID: {$newBrandId})");
        return $newBrandId;
    } else {
        logMessage("❌ Ошибка создания бренда '{$brandName}': " . $el->LAST_ERROR);
        $brandCache[$brandName] = '';
        return '';
    }
}

// Функция для обновления документации:
// - сохраняет «сырые» ссылки поставщика в свойство 1219 (как и раньше);
// - при наличии datasheet загружает файл, использует реестр hash => FILE_ID из api/duplicates/specification_registry.php
//   и записывает FILE_ID в свойство INSTRUCTIONS (ID 500) без создания дублей файлов.
function updateDocumentationLinks($elementId, $product, $iblockId) {
    if (!$elementId) {
        return 0;
    }
    
    $documentLinks = [];
    
    // Обрабатываем datasheet (если есть)
    if (!empty($product['datasheet'])) {
        $documentLinks[] = $product['datasheet'];
    }
    
    // Обновляем множественное свойство 1219 "Документы (ссылки)" (поведение без изменений)
    if (empty($documentLinks)) {
        CIBlockElement::SetPropertyValuesEx($elementId, $iblockId, [1219 => false]);
    } else {
        $propertyValues = [];
        foreach ($documentLinks as $index => $value) {
            if (!empty($value)) {
                $propertyValues['n' . ($index + 1)] = [
                    'VALUE' => $value,
                    'DESCRIPTION' => 'Техническая документация'
                ];
            }
        }
        CIBlockElement::SetPropertyValuesEx($elementId, $iblockId, [1219 => $propertyValues]);
    }
    
    // Дополнительно: работаем с файловым свойством INSTRUCTIONS (ID 500) через реестр hash => FILE_ID
    if (empty($product['datasheet'])) {
        return count($documentLinks);
    }
    
    $url = $product['datasheet'];
    $url = trim($url);
    if ($url === '') {
        return count($documentLinks);
    }
    
    // Путь к реестру из скрипта дедупликации
    $registryFile = $_SERVER['DOCUMENT_ROOT'] . '/api/duplicates/specification_registry.php';
    $registry = [];
    if (file_exists($registryFile)) {
        $data = include $registryFile;
        if (is_array($data)) {
            $registry = $data;
        }
    }
    
    // Скачиваем файл во временный
    $tmpDir = $_SERVER['DOCUMENT_ROOT'] . '/upload/tmp';
    if (!is_dir($tmpDir)) {
        @mkdir($tmpDir, 0775, true);
    }
    $tmpFile = $tmpDir . '/datasheet_' . md5($url . microtime(true)) . '.bin';
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);
    $content = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200 || !$content) {
        // Не удалось скачать файл — оставляем только ссылку в 1219
        return count($documentLinks);
    }
    
    file_put_contents($tmpFile, $content);
    
    // Вычисляем хэш содержимого
    $hash = md5_file($tmpFile);
    if (!$hash) {
        @unlink($tmpFile);
        return count($documentLinks);
    }
    
    $fileId = null;
    
    // Если файл уже есть в реестре — просто используем существующий FILE_ID
    if (isset($registry[$hash]) && (int)$registry[$hash] > 0) {
        $fileId = (int)$registry[$hash];
    } else {
        // Сохраняем новый файл в upload/specification/
        $ext = 'bin';
        $parsed = parse_url($url, PHP_URL_PATH);
        if ($parsed) {
            $basename = basename($parsed);
            $pos = strrpos($basename, '.');
            if ($pos !== false) {
                $extCandidate = strtolower(substr($basename, $pos + 1));
                if ($extCandidate !== '') {
                    $ext = $extCandidate;
                }
            }
        }
        
        $specDir = $_SERVER['DOCUMENT_ROOT'] . '/upload/specification';
        if (!is_dir($specDir)) {
            @mkdir($specDir, 0775, true);
        }
        
        $targetName = $hash . '.' . $ext;
        $targetPath = $specDir . '/' . $targetName;
        @rename($tmpFile, $targetPath);
        
        if (is_file($targetPath)) {
            $fileArray = [
                'name'     => $targetName,
                'size'     => filesize($targetPath),
                'tmp_name' => $targetPath,
                'type'     => mime_content_type($targetPath) ?: 'application/octet-stream',
                'MODULE_ID'=> 'iblock',
            ];
            $newFileId = CFile::SaveFile($fileArray, 'specification');
            if ($newFileId) {
                $fileId = (int)$newFileId;
                $registry[$hash] = $fileId;
                // Сохраняем обновлённый реестр
                $export = var_export($registry, true);
                $php = "<?php\nreturn " . $export . ";\n";
                file_put_contents($registryFile, $php);
            }
        } else {
            @unlink($tmpFile);
        }
    }
    
    if ($fileId) {
        // Добавляем FILE_ID в INSTRUCTIONS (ID 500), избегая дублей
        $currentValues = [];
        $dbProp = CIBlockElement::GetProperty($iblockId, $elementId, [], ['ID' => 500]);
        while ($prop = $dbProp->Fetch()) {
            if (!empty($prop['VALUE'])) {
                $currentValues[(int)$prop['VALUE']] = true;
            }
        }
        $currentValues[$fileId] = true;
        $newIds = array_keys($currentValues);
        sort($newIds, SORT_NUMERIC);
        
        $propertyValues = [];
        foreach ($newIds as $idx => $fid) {
            $propertyValues[500][$idx] = $fid;
        }
        CIBlockElement::SetPropertyValuesEx($elementId, $iblockId, $propertyValues);
    }
    
    return count($documentLinks);
}

/**
 * Обеспечивает наличие маппинга для срока доставки $days.
 * Сначала проверяет маппинг, затем ищет существующий склад "Склад N дн." в Битрикс; если найден — использует его и дополняет маппинг. Иначе создаёт склад и тип цен.
 */
function ensureDeliveryMapping($days, &$storeMap, &$priceMap, $configPath) {
    $days = (int) $days;
    if (isset($storeMap[$days]) && isset($priceMap[$days])) {
        return;
    }
    if ($days < 0) {
        return;
    }
    $existingStoreId = promelec_find_store_by_delivery_days($days);
    if ($existingStoreId) {
        $priceTypeName = 'PROMELEC' . $days;
        $priceTypeId = promelec_find_price_type_by_name($priceTypeName);
        if (!$priceTypeId) {
            $priceTypeId = CCatalogGroup::Add([
                'NAME' => $priceTypeName,
                'BASE' => 'N',
                'SORT' => 100,
            ]);
        }
        if ($priceTypeId) {
            promelec_save_delivery_mapping($configPath, $days, $existingStoreId, $priceTypeId);
            $storeMap[$days] = $existingStoreId;
            $priceMap[$days] = $priceTypeId;
        }
        return;
    }
    $storeTitle = 'Склад ' . $days . ' дн.';
    $ufCode = $GLOBALS['promelec_store_delivery_uf_code'] ?? '';
    $storeFields = [
        'TITLE'   => $storeTitle,
        'ACTIVE'  => 'Y',
        'ADDRESS' => 'По заказу',
    ];
    if ($ufCode !== '') {
        $storeFields[$ufCode] = (string) $days;
    }
    $newStoreId = CCatalogStore::Add($storeFields);
    if (!$newStoreId) {
        return;
    }
    $priceTypeName = 'PROMELEC' . $days;
    $newPriceTypeId = CCatalogGroup::Add([
        'NAME' => $priceTypeName,
        'BASE' => 'N',
        'SORT' => 100,
    ]);
    if (!$newPriceTypeId) {
        return;
    }
    promelec_save_delivery_mapping($configPath, $days, $newStoreId, $newPriceTypeId);
    $storeMap[$days] = $newStoreId;
    $priceMap[$days] = $newPriceTypeId;
}

// Оптимизированная функция обновления остатков: обнулить по складам → внести новые → сумма в доступное количество
function updateProductStock($elementId, $product) {
    global $PROMELEC_DELIVERY_TO_STORE, $PROMELEC_DELIVERY_TO_PRICE_TYPE, $deliveryMappingFile;

    if (!$elementId) {
        return;
    }

    promelec_zero_product_stock($elementId);

    $storeQuantities = [];
    $totalQuantity = 0;
    $totalFromAllVendors = 0;

    if (!empty($product['vendors']) && is_array($product['vendors'])) {
        foreach ($product['vendors'] as $vendor) {
            $qty = (int)($vendor['quant'] ?? 0);
            if ($qty > 0) {
                $totalFromAllVendors += $qty;
            }
            $days = (int)($vendor['delivery'] ?? 0);
            if ($qty <= 0) {
                continue;
            }
            ensureDeliveryMapping($days, $PROMELEC_DELIVERY_TO_STORE, $PROMELEC_DELIVERY_TO_PRICE_TYPE, $deliveryMappingFile);
            if (!isset($PROMELEC_DELIVERY_TO_STORE[$days])) {
                continue;
            }
            $storeId = $PROMELEC_DELIVERY_TO_STORE[$days];
            if (!isset($storeQuantities[$storeId])) {
                $storeQuantities[$storeId] = 0;
            }
            $storeQuantities[$storeId] += $qty;
            $totalQuantity += $qty;
        }
    }

    $quantityForCatalog = $totalFromAllVendors > 0 ? $totalFromAllVendors : $totalQuantity;
    $catalogFields = [
        'ID' => $elementId,
        'QUANTITY' => $quantityForCatalog,
        'QUANTITY_TRACE' => 'Y',
        'CAN_BUY_ZERO' => 'N',
    ];
    $catalogProduct = new CCatalogProduct();
    $dbRes = CCatalogProduct::GetList([], ['ID' => $elementId], false, false, ['ID']);
    if ($dbRes->Fetch()) {
        $catalogProduct->Update($elementId, $catalogFields);
    } else {
        $catalogProduct->Add($catalogFields);
    }

    foreach ($storeQuantities as $storeId => $amount) {
        updateStoreStock($elementId, $storeId, $amount);
    }
}

// Вспомогательная функция для обновления остатков на складе
function updateStoreStock($productId, $storeId, $amount) {
    $storeRes = CCatalogStoreProduct::GetList(
        [],
        ["PRODUCT_ID" => $productId, "STORE_ID" => $storeId],
        false,
        false,
        ["ID"]
    );
    
    $existingRecord = $storeRes->Fetch();
    $storeFields = [
        'PRODUCT_ID' => $productId,
        'STORE_ID'   => $storeId,
        'AMOUNT'     => $amount,
    ];
    
    if ($existingRecord) {
        CCatalogStoreProduct::Update($existingRecord['ID'], $storeFields);
    } else {
        CCatalogStoreProduct::Add($storeFields);
    }
}

// Основная функция обработки пакета товаров (СФОКУСИРОВАНА НА БРЕНДАХ И EDGE)
function processProductsBatch($products) {
    global $iblock_id, $priceTypeId, $stats;
    
    $batchStartTime = microtime(true);
    $batchSize = count($products);
    $processedCount = 0;
    $createdCount = 0;
    $updatedCount = 0;
    $errorCount = 0;

    if (empty($products)) {
        return;
    }

    // 1. Собираем все item_id для пакетного поиска
    $itemIds = [];
    foreach ($products as $product) {
        if (isset($product['item_id'])) {
            $itemIds[] = $product['item_id'];
        }
    }
    
    // 2. Находим существующие товары одним запросом
    $existingProducts = [];
    if (!empty($itemIds)) {
        $filter = [
            'IBLOCK_ID' => $iblock_id,
            'PROPERTY_501' => $itemIds,
            'CHECK_PERMISSIONS' => 'N'
        ];
        
        $res = CIBlockElement::GetList(
            [],
            $filter,
            false,
            false,
            ['ID', 'NAME', 'XML_ID', 'PROPERTY_501']
        );
        
        while ($element = $res->Fetch()) {
            $apiId = $element['PROPERTY_501_VALUE'];
            $existingProducts[$apiId] = $element;
        }
    }
    
    // 3. Собираем все бренды для пакетного поиска
    $brandsToFind = [];
    foreach ($products as $product) {
        if (!empty($product['producer_name'])) {
            $brandsToFind[$product['producer_name']] = true;
        }
    }
    
    // 4. Предзагружаем бренды
    if (!empty($brandsToFind)) {
        $brandNames = array_keys($brandsToFind);
        $dbRes = CIBlockElement::GetList(
            [],
            [
                'IBLOCK_ID' => 6,
                'NAME' => $brandNames,
                'ACTIVE' => 'Y'
            ],
            false,
            false,
            ['ID', 'NAME']
        );
        
        global $brandCache;
        while ($brand = $dbRes->Fetch()) {
            $brandCache[$brand['NAME']] = $brand['ID'];
        }
    }

    // 5. Обрабатываем товары
    foreach ($products as $index => $product) {
        $currentMemory = memory_get_usage(true) / 1024 / 1024;
        
        // Мониторинг памяти
        if ($currentMemory > 1024) { // Если превышаем 1GB
            logMessage("⚠️ Высокое потребление памяти: {$currentMemory}MB, пауза...");
            sleep(3);
            gc_collect_cycles();
        }
        
        if (!isset($product['item_id'], $product['name'])) {
            $errorCount++;
            continue;
        }

        $item_id       = $product['item_id'];
        $raw_name      = $product['name'];
        $description   = !empty($product['description']) ? $product['description'] : '';
        
        // Проверяем существующий товар
        $elementId = false;
        $isUpdate = false;
        
        if (isset($existingProducts[$item_id])) {
            $elementId = $existingProducts[$item_id]['ID'];
            $isUpdate = true;
            $updatedCount++;
        } else {
            $createdCount++;
        }

        // Обработка бренда (СВОЙСТВО 66)
        $brandName = $product['producer_name'] ?? '';
        $brandElementId = '';
        
        if (!empty($brandName)) {
            $brandElementId = getOrCreateBrand($brandName);
        }

        // Обработка даты окончания резерва (СВОЙСТВО 1229 - EDGE)
        $edgeDate = $product['edge'] ?? '';

        // Создание/обновление элемента
        $el = new CIBlockElement();
        $name = $raw_name;
        if (!empty($description)) {
            $name .= ' - ' . $description;
        }

        try {
            // Обработка категорий
            $category_0 = $product['class0name'] ?? 'Без категории';
            $category_1 = $product['class1name'] ?? 'Без подкатегории';
            $category_2 = $product['class2name'] ?? 'Без подподкатегории';
            
            $sec0_id = getOrCreateSection($category_0, $iblock_id);
            $sec1_id = getOrCreateSection($category_1, $iblock_id, $sec0_id);
            $final_section_id = getOrCreateSection($category_2, $iblock_id, $sec1_id);
            
            if (!$sec0_id || !$sec1_id || !$final_section_id) {
                $errorCount++;
                continue;
            }
        } catch (Exception $e) {
            $errorCount++;
            continue;
        }

        // Подготовка свойств
        $arFields = [
            'IBLOCK_ID'      => $iblock_id,
            'NAME'           => $name,
            'CODE'           => CUtil::translit($raw_name, 'ru', [
                'max_len' => 100,
                'change_case' => 'L',
                'replace_space' => '_',
                'replace_other' => '_',
                'delete_repeat_delimiter' => true
            ]),
            'XML_ID'         => 'API_' . $item_id,
            'ACTIVE'         => 'Y',
            'PREVIEW_TEXT'   => $description,
            'DETAIL_TEXT'    => $description,
            'IBLOCK_SECTION' => $final_section_id,
            'PROPERTY_VALUES' => [
                501 => $item_id, // Внешний ID
                627 => $raw_name, // Название
            ],
        ];

        // Обработка enum свойств
        $propertiesToProcess = [
            494 => 'remark',
            495 => 'year_of_issue',
            496 => 'package',
            497 => 'pack_quant',
            498 => 'packaging',
            499 => 'weight'
        ];
        
        foreach ($propertiesToProcess as $propId => $fieldName) {
            if (!empty($product[$fieldName])) {
                $enumId = ensureEnumValueExists($propId, $product[$fieldName]);
                if ($enumId) {
                    $arFields['PROPERTY_VALUES'][$propId] = ['VALUE' => $enumId];
                }
            }
        }

        // Обработка изображения
        if (!empty($product['photo_url'])) {
            $temp_file = $_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/' . md5($product['photo_url']) . '.jpg';
            if (!file_exists(dirname($temp_file))) {
                mkdir(dirname($temp_file), 0755, true);
            }
            
            // Используем curl для скачивания с таймаутом
            $ch = curl_init($product['photo_url']);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
            ]);
            
            $imageContent = curl_exec($ch);
            if ($imageContent !== false && file_put_contents($temp_file, $imageContent)) {
                $arFields['DETAIL_PICTURE'] = CFile::MakeFileArray($temp_file);
                @unlink($temp_file);
            }
            curl_close($ch);
        }

        if ($isUpdate && $elementId) {
            // Обновляем товар
            if (!$el->Update($elementId, $arFields)) {
                $errorCount++;
                continue;
            }
        } else {
            // Создаем новый товар
            $newElementId = $el->Add($arFields);
            if (!$newElementId) {
                $errorCount++;
                continue;
            }
            $elementId = $newElementId;
        }

        // ОБНОВЛЕНИЕ БРЕНДА (СВОЙСТВО 66)
        if (!empty($brandName) && $brandElementId && $elementId) {
            CIBlockElement::SetPropertyValues($elementId, $iblock_id, $brandElementId, 66);
            logMessage("✅ Обновлен бренд для товара ID {$elementId}: {$brandName}");
        }

        // ОБНОВЛЕНИЕ ДАТЫ ОКОНЧАНИЯ РЕЗЕРВА (СВОЙСТВО 1229 - EDGE)
        if (!empty($edgeDate) && $elementId) {
            CIBlockElement::SetPropertyValues($elementId, $iblock_id, $edgeDate, 1229);
            logMessage("✅ Обновлена дата окончания резерва (edge) для товара ID {$elementId}: {$edgeDate}");
        }

        // Обновление документации (ссылки в свойство 1219)
        if ($elementId) {
            updateDocumentationLinks($elementId, $product, $iblock_id);
        }

        // Цены: без расширенных (одна цена) — только базовая, без диапазонов; с расширенными — по маппингу складов с диапазонами
        if ($elementId) {
            global $priceTypeId, $PROMELEC_DELIVERY_TO_STORE, $PROMELEC_DELIVERY_TO_PRICE_TYPE, $deliveryMappingFile;
            $productPricebreaks = isset($product['pricebreaks']) && is_array($product['pricebreaks']) ? $product['pricebreaks'] : [];

            if (promelec_has_single_price_only($product)) {
                // Нет расширенных цен: одна цена в базовый тип, без включения расширенного режима по складам
                $single = promelec_get_single_pricebreak($product);
                if ($single !== null) {
                    $res_old = CPrice::GetList([], ['PRODUCT_ID' => $elementId, 'CATALOG_GROUP_ID' => $priceTypeId]);
                    while ($ar_old = $res_old->Fetch()) {
                        CPrice::Delete($ar_old['ID']);
                    }
                    CPrice::Add([
                        'PRODUCT_ID' => $elementId,
                        'CATALOG_GROUP_ID' => $priceTypeId,
                        'PRICE' => (float)$single['price'],
                        'CURRENCY' => 'RUB',
                        'QUANTITY_FROM' => max(1, (int)$single['quant']),
                        'QUANTITY_TO' => false,
                    ]);
                }
            } else {
                // Есть расширенные цены с диапазонами количества — записываем по маппингу (склад/тип цены по delivery)
                $deliveriesProcessed = [];
                if (!empty($product['vendors']) && is_array($product['vendors'])) {
                    foreach ($product['vendors'] as $vendor) {
                        $days = (int)($vendor['delivery'] ?? 0);
                        ensureDeliveryMapping($days, $PROMELEC_DELIVERY_TO_STORE, $PROMELEC_DELIVERY_TO_PRICE_TYPE, $deliveryMappingFile);
                        if (!isset($PROMELEC_DELIVERY_TO_PRICE_TYPE[$days]) || isset($deliveriesProcessed[$days])) {
                            continue;
                        }
                        $deliveriesProcessed[$days] = true;
                        $ptId = $PROMELEC_DELIVERY_TO_PRICE_TYPE[$days];
                        $priceBreaks = (isset($vendor['pricebreaks']) && is_array($vendor['pricebreaks']) && !empty($vendor['pricebreaks']))
                            ? $vendor['pricebreaks']
                            : $productPricebreaks;
                        $priceBreaks = promelec_normalize_pricebreaks($priceBreaks);
                        if (empty($priceBreaks)) {
                            continue;
                        }
                        $res_old = CPrice::GetList([], ['PRODUCT_ID' => $elementId, 'CATALOG_GROUP_ID' => $ptId]);
                        while ($ar_old = $res_old->Fetch()) {
                            CPrice::Delete($ar_old['ID']);
                        }
                        foreach ($priceBreaks as $key => $priceBreak) {
                            $quantityFrom = (int)$priceBreak['quant'];
                            $quantityTo = isset($priceBreaks[$key + 1]) ? (int)$priceBreaks[$key + 1]['quant'] - 1 : false;
                            $priceValue = (float)$priceBreak['price'];
                            CPrice::Add([
                                'PRODUCT_ID' => $elementId,
                                'CATALOG_GROUP_ID' => $ptId,
                                'PRICE' => $priceValue,
                                'CURRENCY' => 'RUB',
                                'QUANTITY_FROM' => $quantityFrom,
                                'QUANTITY_TO' => $quantityTo,
                            ]);
                        }
                    }
                }
            }
        }

        // Обновление остатков
        if ($elementId) {
            updateProductStock($elementId, $product);
        }

        $processedCount++;
        
        // Пауза и сборка мусора после каждых 200 товаров
        if ($processedCount % 200 === 0) {
            $currentMemory = round(memory_get_usage(true) / 1024 / 1024, 2);
            logMessage("Обработано {$processedCount}/{$batchSize}, память: {$currentMemory}MB");
            gc_collect_cycles();
            usleep(100000); // 100ms пауза
        }
    }
    
    $batchTime = round(microtime(true) - $batchStartTime, 2);
    logStat("✅ Пакет {$batchSize} товаров обработан за {$batchTime} сек. Создано: {$createdCount}, Обновлено: {$updatedCount}, Ошибок: {$errorCount}");
    
    // Обновляем статистику
    global $stats;
    $stats['total_processed'] += $processedCount;
    $stats['total_created'] += $createdCount;
    $stats['total_updated'] += $updatedCount;
    $stats['total_errors'] += $errorCount;
    $stats['batches_processed']++;
    
    if (!empty($products)) {
        $lastProduct = end($products);
        $stats['last_item_id'] = $lastProduct['item_id'] ?? null;
    }
    
    saveStats($stats);
    
    // Очищаем кэши после каждого пакета
    gc_collect_cycles();
    
    return $processedCount;
}

// Основной процесс импорта
logMessage("=== НАЧАЛО ИМПОРТА ТОВАРОВ ===");
logStat("=== СТАРТ ИМПОРТА ТОВАРОВ ===");

if ($testCode !== null) {
    logMessage("ТЕСТОВЫЙ РЕЖИМ: обрабатывается только товар с кодом: " . (is_int($testCode) ? $testCode : $testCode));
    logStat("ТЕСТОВЫЙ РЕЖИМ: код=" . (is_int($testCode) ? $testCode : $testCode));
}

// Загрузка конфигурации API из защищенного файла
$configFile = $_SERVER['DOCUMENT_ROOT'] . '/config/api_promelec.php';
if (!file_exists($configFile)) {
    die("Ошибка: Конфигурационный файл не найден: $configFile\n");
}
$config = require $configFile;

$login = $config['login'];
$password = $config['password'];
$api_url = $config['api_url'];
$password_md5 = strtoupper(md5($password));
$GLOBALS['promelec_store_delivery_uf_code'] = $config['store_delivery_uf_code'] ?? '';

logMessage("🔄 Начинаем получение полного справочника товаров");
logStat("Запрос полного справочника товаров");

// Используем правильную пагинацию согласно документации API
$lastItemId = null;
$totalReceived = 0;
$totalProcessed = 0;
$batchNumber = 0;

do {
    $batchNumber++;
    $batchStartTime = microtime(true);
    
    logMessage("📦 Пакет #{$batchNumber}, lastItemId: " . ($lastItemId ?? 'первый запрос'));
    logStat("🔄 Пакет #{$batchNumber}, lastItemId: " . ($lastItemId ?? 'первый запрос'));
    
    $data = fetchApiData($lastItemId, false); // false - получаем все товары, не только в наличии
    
    if ($data === false) {
        logMessage("❌ Критическая ошибка API, остановка импорта");
        logStat("❌ КРИТИЧЕСКАЯ ОШИБКА API, ОСТАНОВКА");
        break;
    }
    
    if (empty($data)) {
        logMessage("✅ Нет данных для обработки, импорт завершен");
        logStat("✅ Нет данных для обработки, импорт завершен");
        break;
    }

    if ($testCode !== null) {
        $origData = $data;
        $filtered = [];
        foreach ($data as $p) {
            if (is_int($testCode)) {
                if (isset($p['item_id']) && (int)$p['item_id'] === $testCode) {
                    $filtered[] = $p;
                    break;
                }
            } else {
                $str = (string) $testCode;
                if (isset($p['name']) && stripos($p['name'], $str) !== false) {
                    $filtered[] = $p;
                    break;
                }
                if (isset($p['item_id']) && (string)$p['item_id'] === $str) {
                    $filtered[] = $p;
                    break;
                }
            }
        }
        $data = $filtered;
        if (empty($data)) {
            logMessage("Тестовый товар не найден в пакете #{$batchNumber}, запрашиваем следующий...");
            $lastItem = end($origData);
            $lastItemId = is_array($lastItem) ? ($lastItem['item_id'] ?? null) : null;
            sleep(2);
            if ($batchNumber >= 1000) {
                logMessage("Тестовый товар не найден за 1000 пакетов.");
                break;
            }
            continue;
        }
        logMessage("Найден тестовый товар в пакете #{$batchNumber}, обработка...");
    }
    
    $receivedCount = count($data);
    $totalReceived += $receivedCount;
    
    logMessage("📥 Получено товаров в пакете: {$receivedCount}");
    logStat("📥 Получено товаров: {$receivedCount}");
    
    // Обрабатываем пакет
    $processedInBatch = processProductsBatch($data);
    $totalProcessed += $processedInBatch;
    
    if ($testCode !== null && $processedInBatch > 0) {
        logMessage("Тестовый товар обработан, завершение.");
        logStat("Тестовый режим: товар обработан");
        break;
    }
    
    // Получаем последний item_id для следующего запроса
    $lastItem = end($data);
    $lastItemId = $lastItem['item_id'] ?? null;
    
    $batchTime = round(microtime(true) - $batchStartTime, 2);
    $memoryUsage = round(memory_get_usage(true) / 1024 / 1024, 2);
    
    logMessage("✅ Пакет #{$batchNumber} обработан за {$batchTime} сек. Обработано: {$processedInBatch}/{$receivedCount}, Память: {$memoryUsage}MB");
    logStat("✅ Пакет #{$batchNumber} обработан за {$batchTime} сек. Обработано: {$processedInBatch}/{$receivedCount}");
    
    // Проверяем, был ли это последний пакет
    if ($receivedCount === 0 || $lastItemId === null) {
        logMessage("🎉 Получен последний пакет, импорт завершен");
        logStat("🎉 ПОСЛЕДНИЙ ПАКЕТ ПОЛУЧЕН");
        break;
    }
    
    // Делаем паузу между пакетами (уменьшаем нагрузку на API и БД)
    $pauseTime = 2; // секунды
    logMessage("⏸️ Пауза {$pauseTime} сек. перед следующим пакетом...");
    sleep($pauseTime);
    
    // Ограничиваем максимальное количество пакетов на всякий случай
    if ($batchNumber >= 1000) { // 1000 пакетов максимум
        logMessage("⚠️ Достигнуто максимальное количество пакетов (1000)");
        break;
    }
    
} while (true);

// Финализируем статистику
$stats['total_received_from_api'] = $totalReceived;
$stats['total_processed'] = $totalProcessed;
$stats['end_time'] = date('Y-m-d H:i:s');
saveStats($stats);

// Итоговый отчет
$totalTime = round((strtotime($stats['end_time']) - strtotime($stats['start_time'])) / 60, 2);
$memoryPeak = $stats['memory_peak'];

logMessage("=" . str_repeat("=", 70));
logMessage("🎯 ИТОГИ ИМПОРТА");
logMessage("=" . str_repeat("=", 70));
logMessage("Время начала: {$stats['start_time']}");
logMessage("Время окончания: {$stats['end_time']}");
logMessage("Общее время: {$totalTime} минут");
logMessage("Всего получено от API: {$totalReceived} товаров");
logMessage("Всего обработано: {$totalProcessed} товаров");
logMessage("Создано новых: {$stats['total_created']}");
logMessage("Обновлено: {$stats['total_updated']}");
logMessage("Ошибок: {$stats['total_errors']}");
logMessage("Обработано пакетов: {$stats['batches_processed']}");
logMessage("Пиковое использование памяти: {$memoryPeak} MB");
logMessage("=" . str_repeat("=", 70));
logMessage("--- ОКОНЧАНИЕ ИМПОРТА ---");

logStat("=" . str_repeat("=", 50));
logStat("🎯 ИТОГИ ИМПОРТА");
logStat("=" . str_repeat("=", 50));
logStat("Общее время: {$totalTime} минут");
logStat("Всего получено от API: {$totalReceived}");
logStat("Всего обработано: {$totalProcessed}");
logStat("Создано: {$stats['total_created']}");
logStat("Обновлено: {$stats['total_updated']}");
logStat("Ошибок: {$stats['total_errors']}");
logStat("Пакетов: {$stats['batches_processed']}");
logStat("Память (пик): {$memoryPeak} MB");
logStat("=" . str_repeat("=", 50));

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');
?>