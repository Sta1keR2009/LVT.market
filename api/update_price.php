<?php
// Включаем вывод ошибок для отладки (только для CLI)
if (php_sapi_name() === 'cli') {
    ini_set('display_errors', 0); // Отключаем для cron, чтобы избежать segmentation fault
    ini_set('log_errors', 1);
    ini_set('error_log', '/var/www/www-root/data/www/lvtgroup.ru/api/update_price_php_errors.log');
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT); // Игнорируем deprecated warnings
}

// БЕЗОПАСНОСТЬ: Разрешить выполнение только из командной строки (CLI)
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    die('Доступ запрещен. Скрипт доступен только из командной строки (CLI).');
}

if (empty($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['DOCUMENT_ROOT'] = '/var/www/www-root/data/www/lvtgroup.ru';
}
if (empty($_SERVER['SCRIPT_FILENAME'])) {
    $_SERVER['SCRIPT_FILENAME'] = __FILE__;
}
if (empty($_SERVER['SCRIPT_NAME'])) {
    $_SERVER['SCRIPT_NAME'] = '/' . basename(__FILE__);
}
if (empty($_SERVER['REQUEST_METHOD'])) {
    $_SERVER['REQUEST_METHOD'] = 'CLI';
}
if (empty($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'lvtgroup.ru';
}
// Убраны все лимиты по требованию

if (empty($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['DOCUMENT_ROOT'] = '/var/www/www-root/data/www/lvtgroup.ru';
}

$prologPath = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
if (!file_exists($prologPath)) {
    die("Файл prolog_before.php не найден по пути: $prologPath\n");
}

// Перехватываем ошибки перед подключением Битрикс
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    file_put_contents('/var/www/www-root/data/www/lvtgroup.ru/api/update_price_error.log', 
        date('Y-m-d H:i:s') . " [ERROR $errno] $errstr in $errfile:$errline\n", FILE_APPEND);
    return false; // Продолжаем стандартную обработку
});

try {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
} catch (Throwable $e) {
    $errorMsg = date('Y-m-d H:i:s') . " [EXCEPTION] " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
    $errorMsg .= "Stack trace:\n" . $e->getTraceAsString() . "\n";
    file_put_contents('/var/www/www-root/data/www/lvtgroup.ru/api/update_price_error.log', $errorMsg, FILE_APPEND);
    die("КРИТИЧЕСКАЯ ОШИБКА: " . $e->getMessage() . "\n");
}

use Bitrix\Main\Loader;
use Bitrix\Iblock\IblockSectionTable;
use Bitrix\Catalog\PriceTable;

if (!CModule::IncludeModule('iblock') || !CModule::IncludeModule('catalog')) {
    die('Не загружены необходимые модули');
}

require_once __DIR__ . '/promelec_pricebreaks_helper.php';

// Статистика обновления
$statsFile = __DIR__ . '/price_update_stats.json';
$stats = [
    'start_time' => date('Y-m-d H:i:s'),
    'total_received' => 0,
    'total_updated' => 0,
    'total_errors' => 0,
    'memory_peak' => 0,
    'end_time' => null,
    'batches_processed' => 0
];

function saveStats($stats) {
    global $statsFile;
    $stats['memory_peak'] = round(memory_get_peak_usage(true) / 1024 / 1024, 2);
    file_put_contents($statsFile, json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$priceTypeId = 1;
$iblock_id = 11;

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

// Маппинг срок доставки (дней) → ID склада Битрикс
$PROMELEC_DELIVERY_TO_STORE = [
    0  => 9,
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

/** Максимум пакетов за один запуск */
define('UPDATE_PRICE_MAX_BATCHES', 99);

$logFile = __DIR__ . '/price_update.log';
$statLogFile = __DIR__ . '/price_statistics.log';

function logMessage($message) {
    global $logFile;
    $memory = round(memory_get_usage(true) / 1024 / 1024, 2);
    file_put_contents($logFile, date('Y-m-d H:i:s') . " [{$memory}MB] - " . $message . PHP_EOL, FILE_APPEND | LOCK_EX);
}

function logStat($message) {
    global $statLogFile;
    $memory = round(memory_get_usage(true) / 1024 / 1024, 2);
    file_put_contents($statLogFile, date('Y-m-d H:i:s') . " [{$memory}MB] - " . $message . PHP_EOL, FILE_APPEND | LOCK_EX);
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
$customer_id = $config['customer_id'];
$password_md5 = strtoupper(md5($password));
$GLOBALS['promelec_store_delivery_uf_code'] = $config['store_delivery_uf_code'] ?? '';

// ИСПРАВЛЕННАЯ функция получения данных с правильной пагинацией
function fetchApiData($lastItemId = null, $inStock = false) {
    global $login, $password_md5, $api_url, $customer_id;
    
    logStat("API запрос: lastItemId=" . ($lastItemId ?? 'первый запрос') . ", inStock=" . ($inStock ? 'true' : 'false'));
    
    $request_data = [
        'login' => $login,
        'password' => $password_md5,
        'method' => 'items_data_get',
        'customer_id' => $customer_id,
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
        CURLOPT_TIMEOUT => 300,
        CURLOPT_CONNECTTIMEOUT => 60,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($http_code !== 200 || !$response) {
        logMessage("❌ Ошибка API: HTTP $http_code, ошибка: $error");
        return false;
    }
    
    $data = json_decode($response, true, 512, JSON_UNESCAPED_UNICODE);
    if (json_last_error() !== JSON_ERROR_NONE) {
        logMessage("❌ Ошибка разбора JSON: " . json_last_error_msg());
        return false;
    }
    
    if (!is_array($data)) {
        logMessage("❌ Неверный формат данных из API: " . gettype($data));
        return false;
    }
    
    // Проверяем, не вернул ли API ошибку
    if (isset($data['error'])) {
        $errorMsg = $data['error'];
        $errorId = $data['error_id'] ?? 'не указан';
        logMessage("❌ ОШИБКА API: $errorMsg (ID ошибки: $errorId)");
        logStat("❌ ОШИБКА API: $errorMsg");
        
        // Если это лимит вызовов, не считаем это критической ошибкой
        if (stripos($errorMsg, 'максимально') !== false || stripos($errorMsg, 'лимит') !== false) {
            logMessage("⚠️ Превышен лимит вызовов API. Обновление будет пропущено до следующего запуска.");
            return []; // Возвращаем пустой массив, чтобы скрипт завершился корректно
        }
        
        return false;
    }
    
    // Проверяем структуру ответа API
    if (!empty($data)) {
        $firstElement = reset($data);
        
        // Если первый элемент - не массив, значит структура неправильная
        if (!is_array($firstElement)) {
            logMessage("❌ Неверная структура данных API. Первый элемент: " . gettype($firstElement));
            logMessage("🔍 Первые 200 символов: " . substr(json_encode($firstElement, JSON_UNESCAPED_UNICODE), 0, 200));
            return false;
        }
        
        // Проверяем наличие item_id в первом товаре
        if (isset($firstElement['item_id'])) {
            logMessage("✅ Структура данных корректна. item_id первого товара: " . $firstElement['item_id']);
        } else {
            logMessage("⚠️ В первом товаре отсутствует item_id. Ключи: " . json_encode(array_keys($firstElement)));
        }
    }
    
    logStat("✅ Получено товаров: " . count($data));
    
    return $data;
}

// Оптимизированный поиск товаров по API ID
function findProductsByItemIds($itemIds) {
    global $iblock_id;
    
    $products = [];
    
    if (empty($itemIds)) {
        return $products;
    }
    
    // Разбиваем на части чтобы избежать слишком длинных SQL запросов
    $chunks = array_chunk($itemIds, 500);
    
    foreach ($chunks as $chunk) {
        $filter = [
            'IBLOCK_ID' => $iblock_id,
            'PROPERTY_501' => $chunk,
            'CHECK_PERMISSIONS' => 'N'
        ];
        
        $select = ['ID', 'NAME', 'TIMESTAMP_X', 'PROPERTY_501'];
        $res = CIBlockElement::GetList([], $filter, false, false, $select);
        
        while ($arElement = $res->Fetch()) {
            $apiId = $arElement['PROPERTY_501_VALUE'];
            $products[$apiId] = $arElement;
        }
    }
    
    return $products;
}

// Получение текущих цен (оптимизировано)
function getCurrentPricesBatch($productIds, $priceTypeId) {
    $prices = [];
    
    if (empty($productIds)) {
        return $prices;
    }
    
    $chunks = array_chunk($productIds, 500);
    
    foreach ($chunks as $chunk) {
        $dbPrice = CPrice::GetList([], ['PRODUCT_ID' => $chunk, 'CATALOG_GROUP_ID' => $priceTypeId]);
        while ($arPrice = $dbPrice->Fetch()) {
            $productId = $arPrice['PRODUCT_ID'];
            if (!isset($prices[$productId])) {
                $prices[$productId] = [];
            }
            $prices[$productId][] = [
                'ID' => $arPrice['ID'],
                'QUANTITY_FROM' => $arPrice['QUANTITY_FROM'],
                'QUANTITY_TO' => $arPrice['QUANTITY_TO'],
                'PRICE' => $arPrice['PRICE']
            ];
        }
    }
    
    return $prices;
}

// Оптимизированное обновление цен
function updateProductPrices($productId, $priceBreaks, $priceTypeId, $productName, $apiItemId) {
    global $APPLICATION;
    
    if (!is_array($priceBreaks) || empty($priceBreaks)) {
        logMessage("❌ Нет цен для обновления товара ID $productId (API ID: $apiItemId)");
        return false;
    }
    
    $priceBreaks = promelec_normalize_pricebreaks($priceBreaks);
    if (empty($priceBreaks)) {
        logMessage("❌ Нет валидных порогов цен (quant>0, price>0) для товара ID $productId (API ID: $apiItemId)");
        return false;
    }
    
    // Удаляем старые цены
    $dbPrice = CPrice::GetList([], ['PRODUCT_ID' => $productId, 'CATALOG_GROUP_ID' => $priceTypeId]);
    $deletedCount = 0;
    while ($arPrice = $dbPrice->Fetch()) {
        if (CPrice::Delete($arPrice['ID'])) {
            $deletedCount++;
        }
    }
    
    // Добавляем новые цены (только нормализованные пороги)
    $addedCount = 0;
    $newPricesData = [];
    
    foreach ($priceBreaks as $key => $priceBreak) {
        $quantityFrom = (int)$priceBreak['quant'];
        $quantityTo = isset($priceBreaks[$key + 1]) ? (int)$priceBreaks[$key + 1]['quant'] - 1 : false;
        $priceValue = (float)$priceBreak['price'];
        
        $priceFields = [
            'PRODUCT_ID' => $productId,
            'CATALOG_GROUP_ID' => $priceTypeId,
            'PRICE' => $priceValue,
            'CURRENCY' => 'RUB',
            'QUANTITY_FROM' => $quantityFrom,
            'QUANTITY_TO' => $quantityTo,
        ];
        
        $newPricesData[] = $priceFields;
    }
    
    // Добавляем цены пачкой
    foreach ($newPricesData as $priceFields) {
        $priceId = CPrice::Add($priceFields);
        if ($priceId) {
            $addedCount++;
        }
    }
    
    logMessage("💰 Цены обновлены для товара: $productName (Битрикс ID: $productId, API ID: $apiItemId)");
    logMessage("   Удалено старых цен: $deletedCount, Добавлено новых: $addedCount");
    
    return $addedCount > 0;
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

function updateStoreStock($productId, $storeId, $amount) {
    $storeRes = CCatalogStoreProduct::GetList(
        [],
        ['PRODUCT_ID' => $productId, 'STORE_ID' => $storeId],
        false,
        false,
        ['ID']
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

// Обновление остатков: обнулить по складам → внести новые → сумма в доступное количество
function updateProductStock($productId, $vendors, $productName, $apiItemId) {
    global $PROMELEC_DELIVERY_TO_STORE, $PROMELEC_DELIVERY_TO_PRICE_TYPE, $deliveryMappingFile;

    promelec_zero_product_stock($productId);

    $storeQuantities = [];
    $totalQuantity = 0;
    $totalFromAllVendors = 0;

    if (is_array($vendors)) {
        foreach ($vendors as $vendor) {
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
    $arCatalogFields = [
        'ID' => $productId,
        'QUANTITY' => $quantityForCatalog,
        'QUANTITY_TRACE' => 'Y',
        'CAN_BUY_ZERO' => 'N',
    ];
    $catalogProduct = new CCatalogProduct();
    if ($catalogProduct->GetByID($productId)) {
        $catalogProduct->Update($productId, $arCatalogFields);
    } else {
        $catalogProduct->Add($arCatalogFields);
    }

    foreach ($storeQuantities as $storeId => $amount) {
        updateStoreStock($productId, $storeId, $amount);
    }
    logMessage("📦 Остатки обновлены для товара: $productName (ID: $productId)");
}

// Обновление даты изменения
function updateProductTimestamp($productId, $productName) {
    $el = new CIBlockElement();
    $arFields = [
        "TIMESTAMP_X" => ConvertTimeStamp(time(), "FULL")
    ];
    
    $el->Update($productId, $arFields);
    logMessage("🕒 Дата изменения обновлена для товара: $productName (ID: $productId)");
}

// Глобальный кэш для брендов
$brandCache = [];

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

// Обновление бренда (СВОЙСТВО 66)
function updateProductBrand($productId, $brandName, $productName, $apiItemId) {
    global $iblock_id;
    
    if (empty($brandName)) {
        return;
    }
    
    $brandElementId = getOrCreateBrand($brandName);
    
    if ($brandElementId) {
        CIBlockElement::SetPropertyValues($productId, $iblock_id, $brandElementId, 66);
        logMessage("✅ Обновлен бренд для товара ID $productId: $brandName (API ID: $apiItemId)");
    }
}

// Обновление даты окончания резерва (СВОЙСТВО 1229 - EDGE)
function updateProductReserveDate($productId, $edgeDate, $productName, $apiItemId) {
    global $iblock_id;
    $dateEndReservPropertyId = 1229;
    
    if (empty($edgeDate)) {
        return;
    }
    
    CIBlockElement::SetPropertyValues($productId, $iblock_id, $edgeDate, $dateEndReservPropertyId);
    logMessage("✅ Обновлена дата окончания резерва для товара ID $productId: $edgeDate (API ID: $apiItemId)");
}

// Основная функция обработки пакета
function processProductsBatch($products) {
    global $priceTypeId, $stats, $iblock_id;
    
    $batchStartTime = microtime(true);
    $batchSize = count($products);
    $updatedCount = 0;
    $errorCount = 0;
    
    if (!is_array($products) || empty($products)) {
        return 0;
    }
    
    // Собираем все item_id
    $itemIds = [];
    $productsMap = [];
    
    foreach ($products as $product) {
        // Проверяем, что $product - массив
        if (!is_array($product)) {
            logMessage("⚠️ Товар не является массивом: " . gettype($product) . " - " . substr(json_encode($product), 0, 100));
            continue;
        }
        
        // Проверяем разные варианты ключей для item_id
        $itemId = null;
        if (isset($product['item_id'])) {
            $itemId = $product['item_id'];
        } elseif (isset($product['itemId'])) {
            $itemId = $product['itemId'];
        } elseif (isset($product['id'])) {
            $itemId = $product['id'];
        }
        
        if ($itemId !== null) {
            $itemIds[] = $itemId;
            $productsMap[$itemId] = $product;
        } else {
            logMessage("⚠️ Товар без item_id. Ключи: " . json_encode(array_keys($product)));
        }
    }
    
    if (empty($itemIds)) {
        logMessage("❌ КРИТИЧЕСКАЯ ОШИБКА: Нет item_id в данных API! Первый товар: " . json_encode($products[0] ?? []));
    }
    
    // Находим все товары одним запросом
    $existingProducts = findProductsByItemIds($itemIds);
    
    // Отладочная информация
    if (empty($existingProducts)) {
        logMessage("⚠️ Товары не найдены в базе для item_ids: " . implode(', ', $itemIds));
    } else {
        logMessage("✅ Найдено товаров в базе: " . count($existingProducts) . " из " . count($itemIds));
    }
    
    // Собираем ID товаров для обновления цен
    $productIds = [];
    foreach ($existingProducts as $apiId => $product) {
        $productIds[] = $product['ID'];
    }
    
    // Получаем текущие цены пачкой
    $currentPrices = getCurrentPricesBatch($productIds, $priceTypeId);
    
    // Собираем все бренды для пакетного поиска
    $brandsToFind = [];
    foreach ($productsMap as $apiId => $product) {
        if (!empty($product['producer_name'])) {
            $brandsToFind[$product['producer_name']] = true;
        }
    }
    
    // Предзагружаем бренды
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
    
    // Обрабатываем товары
    foreach ($productsMap as $apiId => $product) {
        if (!isset($existingProducts[$apiId])) {
            continue;
        }
        
        $existingProduct = $existingProducts[$apiId];
        $productId = $existingProduct['ID'];
        $productName = $existingProduct['NAME'];
        
        // Проверяем, нужно ли обновлять цены
        $needUpdate = true;
        if (isset($currentPrices[$productId])) {
            // Можно добавить логику сравнения цен для оптимизации
            // если цены не изменились, пропускаем обновление
        }
        
        if ($needUpdate) {
            global $priceTypeId, $PROMELEC_DELIVERY_TO_STORE, $PROMELEC_DELIVERY_TO_PRICE_TYPE, $deliveryMappingFile;
            $productPricebreaks = isset($product['pricebreaks']) && is_array($product['pricebreaks']) ? $product['pricebreaks'] : [];
            $priceUpdated = false;
            // Без расширенных цен — одна цена в базовый тип; с расширенными — по маппингу (склад/тип цены по delivery)
            if (promelec_has_single_price_only($product)) {
                $single = promelec_get_single_pricebreak($product);
                if ($single !== null && updateProductPrices($productId, [[ 'quant' => $single['quant'], 'price' => $single['price'] ]], $priceTypeId, $productName, $apiId)) {
                    $priceUpdated = true;
                }
            } else {
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
                        if (!empty($priceBreaks) && updateProductPrices($productId, $priceBreaks, $ptId, $productName, $apiId)) {
                            $priceUpdated = true;
                        }
                    }
                }
            }
            if ($priceUpdated || (empty($product['vendors']) && !empty($productPricebreaks) && updateProductPrices($productId, $productPricebreaks, $priceTypeId, $productName, $apiId))) {
                $updatedCount++;
                updateProductStock($productId, $product['vendors'] ?? [], $productName, $apiId);
                updateProductTimestamp($productId, $productName);
                if (isset($product['producer_name'])) {
                    updateProductBrand($productId, $product['producer_name'], $productName, $apiId);
                }
                if (isset($product['edge'])) {
                    updateProductReserveDate($productId, $product['edge'], $productName, $apiId);
                }
            } elseif ($needUpdate) {
                $errorCount++;
            }
        }
    }
    
    $batchTime = round(microtime(true) - $batchStartTime, 2);
    logStat("✅ Пакет цен обработан за {$batchTime} сек. Обновлено: {$updatedCount}, Ошибок: {$errorCount}");
    
    // Обновляем статистику
    global $stats;
    $stats['total_received'] += $batchSize;
    $stats['total_updated'] += $updatedCount;
    $stats['total_errors'] += $errorCount;
    $stats['batches_processed']++;
    
    saveStats($stats);
    
    return $updatedCount;
}

// Не обнулять $GLOBALS['APPLICATION'] — иначе CPrice::Delete вызывает fileman, который вызывает method_exists($APPLICATION, ...) и падает с null.

// Основной процесс
try {
logMessage("=== НАЧАЛО ОБНОВЛЕНИЯ ЦЕН И ОСТАТКОВ ===");
logStat("=== СТАРТ ОБНОВЛЕНИЯ ЦЕН ===");

if ($testCode !== null) {
    logMessage("ТЕСТОВЫЙ РЕЖИМ: обрабатывается только товар с кодом: " . (is_int($testCode) ? $testCode : $testCode));
    logStat("ТЕСТОВЫЙ РЕЖИМ: код=" . (is_int($testCode) ? $testCode : $testCode));
}

$lastItemId = null;
$totalReceived = 0;
$totalUpdated = 0;
$batchNumber = 0;

logMessage("🔄 Начинаем получение данных о товарах для обновления цен и остатков");
logStat("Запрос данных о товарах");

do {
    $batchNumber++;
    $batchStartTime = microtime(true);
    
    logMessage("📦 Пакет #{$batchNumber}, lastItemId: " . ($lastItemId ?? 'первый запрос'));
    logStat("🔄 Пакет #{$batchNumber}, lastItemId: " . ($lastItemId ?? 'первый запрос'));
    
    $data = fetchApiData($lastItemId, false); // false - получаем все товары
    
    if ($data === false) {
        logMessage("❌ Ошибка API, остановка");
        break;
    }
    
    if (empty($data)) {
        logMessage("✅ Нет данных для обновления");
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
    
    logMessage("📥 Получено товаров: {$receivedCount}");
    
    // Обрабатываем пакет
    $updatedInBatch = processProductsBatch($data);
    $totalUpdated += $updatedInBatch;

    if ($testCode !== null && $updatedInBatch > 0) {
        logMessage("Тестовый товар обработан, завершение.");
        logStat("Тестовый режим: товар обработан");
        break;
    }
    
    // Получаем последний item_id для следующего запроса
    $lastItem = end($data);
    $lastItemId = $lastItem['item_id'] ?? null;
    
    $batchTime = round(microtime(true) - $batchStartTime, 2);
    $memoryUsage = round(memory_get_usage(true) / 1024 / 1024, 2);
    
    logMessage("✅ Пакет #{$batchNumber} обработан за {$batchTime} сек. Обновлено: {$updatedInBatch}, Память: {$memoryUsage}MB");
    $packetsLeft = max(0, UPDATE_PRICE_MAX_BATCHES - $batchNumber);
    logMessage("   До лимита осталось пакетов: {$packetsLeft}");
    
    // Проверяем последний ли пакет
    if ($receivedCount === 0 || $lastItemId === null) {
        logMessage("🎉 Получен последний пакет");
        break;
    }
    
    // Пауза между пакетами
    sleep(2);
    
    if ($batchNumber >= UPDATE_PRICE_MAX_BATCHES) {
        logMessage("⚠️ Достигнуто максимальное количество пакетов (" . UPDATE_PRICE_MAX_BATCHES . ")");
        break;
    }
    
} while (true);

// Финальная статистика
$stats['end_time'] = date('Y-m-d H:i:s');
saveStats($stats);

$totalTime = round((strtotime($stats['end_time']) - strtotime($stats['start_time'])) / 60, 2);

logMessage("=" . str_repeat("=", 60));
logMessage("🎯 ИТОГИ ОБНОВЛЕНИЯ ЦЕН");
logMessage("=" . str_repeat("=", 60));
logMessage("Всего получено товаров: {$totalReceived}");
logMessage("Всего обновлено товаров: {$totalUpdated}");
logMessage("Ошибок: {$stats['total_errors']}");
logMessage("Обработано пакетов: {$stats['batches_processed']}");
logMessage("Общее время: {$totalTime} минут");
logMessage("Пик памяти: {$stats['memory_peak']} MB");
logMessage("=" . str_repeat("=", 60));
logMessage("--- ОКОНЧАНИЕ ОБНОВЛЕНИЯ ЦЕН ---");

} catch (Throwable $e) {
    $errorMsg = date('Y-m-d H:i:s') . " [КРИТИЧЕСКАЯ ОШИБКА] " . $e->getMessage() . "\n";
    $errorMsg .= "Файл: " . $e->getFile() . ":" . $e->getLine() . "\n";
    $errorMsg .= "Stack trace:\n" . $e->getTraceAsString() . "\n";
    file_put_contents('/var/www/www-root/data/www/lvtgroup.ru/api/update_price_error.log', $errorMsg, FILE_APPEND);
    logMessage("❌ КРИТИЧЕСКАЯ ОШИБКА: " . $e->getMessage());
    echo "ОШИБКА: " . $e->getMessage() . "\n";
    exit(1);
}

logStat("=" . str_repeat("=", 40));
logStat("🎯 ИТОГИ ОБНОВЛЕНИЯ ЦЕН");
logStat("=" . str_repeat("=", 40));
logStat("Получено: {$totalReceived}");
logStat("Обновлено: {$totalUpdated}");
logStat("Ошибок: {$stats['total_errors']}");
logStat("Пакетов: {$stats['batches_processed']}");
logStat("Время: {$totalTime} мин");
logStat("Память: {$stats['memory_peak']} MB");
logStat("=" . str_repeat("=", 40));

?>