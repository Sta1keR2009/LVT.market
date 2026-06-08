<?php
/**
 * Проверка загрузки тестовых данных в склады и цены Битрикс.
 * Данные берутся из тестового API (api_url_test), затем для выбранного товара
 * обновляются остатки по складам (по срокам доставки) и цены по типам цен.
 *
 * Для каждого склада (срока доставки) создаются свои диапазоны количества (От/До)
 * и цены из API: берутся pricebreaks соответствующего vendor (delivery → тип цены).
 * Используется нормализация pricebreaks (фильтр, сортировка по quant, без дублей).
 *
 * Использование:
 *   Веб:  /api/test/load_to_bitrix.php?item_id=203075
 *   CLI:  php load_to_bitrix.php 203075
 */
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SERVER['DOCUMENT_ROOT']) || !$_SERVER['DOCUMENT_ROOT']) {
    $_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../..');
}

$docRoot = $_SERVER['DOCUMENT_ROOT'];
$prologPath = $docRoot . '/bitrix/modules/main/include/prolog_before.php';
if (!file_exists($prologPath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Bitrix prolog не найден', 'path' => $prologPath], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return;
}
require_once $prologPath;

if (!CModule::IncludeModule('iblock') || !CModule::IncludeModule('catalog')) {
    http_response_code(500);
    echo json_encode(['error' => 'Модули iblock или catalog не загружены'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return;
}

$configFile = $docRoot . '/config/api_promelec.php';
$deliveryMappingConfig = $docRoot . '/config/promelec_delivery_mapping.php';
// Путь к записываемому маппингу — относительно скрипта, чтобы не зависеть от DOCUMENT_ROOT (нет дублей складов при разных доменах/веб vs CLI)
$deliveryMappingFile = dirname(__DIR__) . '/data/promelec_delivery_mapping.php';
if (!file_exists($configFile)) {
    http_response_code(500);
    echo json_encode(['error' => 'Конфиг api_promelec.php не найден'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return;
}

$config = require $configFile;
$GLOBALS['promelec_store_delivery_uf_code'] = $config['store_delivery_uf_code'] ?? '';
require_once __DIR__ . '/../promelec_pricebreaks_helper.php';
$login = $config['login'] ?? '';
$password = $config['password'] ?? '';
$customerId = (int)($config['customer_id'] ?? 0);
$apiUrl = $config['api_url_test'] ?? $config['api_url'] ?? '';
$passwordMd5 = strtoupper(md5($password));

$iblock_id = 11;
$priceTypeId = 1;

$PROMELEC_DELIVERY_TO_STORE = [0 => 9, 2 => 10, 5 => 1, 7 => 2, 12 => 44, 23 => 3, 26 => 4, 37 => 5, 91 => 8];
$PROMELEC_DELIVERY_TO_PRICE_TYPE = [0 => 9, 2 => 10, 5 => 2, 7 => 3, 12 => 44, 23 => 4, 26 => 5, 37 => 6, 91 => 7];
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

// Код товара
$itemId = null;
if (php_sapi_name() === 'cli') {
    if (isset($_SERVER['argv'][1])) {
        $itemId = is_numeric($_SERVER['argv'][1]) ? (int)$_SERVER['argv'][1] : $_SERVER['argv'][1];
    }
    foreach (array_slice($_SERVER['argv'] ?? [], 1) as $arg) {
        if (strpos($arg, '--item_id=') === 0) { $itemId = (int)substr($arg, 10); break; }
        if (strpos($arg, '--code=') === 0) { $v = trim(substr($arg, 7)); $itemId = is_numeric($v) ? (int)$v : $v; break; }
    }
} else {
    if (isset($_GET['item_id']) && $_GET['item_id'] !== '') {
        $itemId = is_numeric($_GET['item_id']) ? (int)$_GET['item_id'] : trim($_GET['item_id']);
    } elseif (isset($_GET['code']) && $_GET['code'] !== '') {
        $itemId = is_numeric($_GET['code']) ? (int)$_GET['code'] : trim($_GET['code']);
    }
}

if ($itemId === null || $itemId === '') {
    echo json_encode([
        'error' => 'Укажите item_id или code товара',
        'usage_web' => '/api/test/load_to_bitrix.php?item_id=203075',
        'usage_cli' => 'php load_to_bitrix.php 203075',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return;
}

$apiItemId = is_int($itemId) ? $itemId : (int)$itemId;

// 1) Получить данные из тестового API
$requestData = [
    'login' => $login,
    'password' => $passwordMd5,
    'customer_id' => $customerId,
    'method' => 'item_data_get',
    'item_id' => $apiItemId,
];
$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($requestData, JSON_UNESCAPED_UNICODE),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_SSL_VERIFYPEER => false,
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || !$response) {
    echo json_encode([
        'error' => 'Ошибка тестового API',
        'http_code' => $httpCode,
        'api_url' => $apiUrl,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return;
}

$product = json_decode($response, true, 512, JSON_UNESCAPED_UNICODE);
if (!$product || !isset($product['item_id'])) {
    echo json_encode([
        'error' => 'API не вернул объект товара',
        'response_preview' => mb_substr($response, 0, 300),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return;
}

// 2) Найти товар в Битрикс по свойству 501 (item_id)
$res = CIBlockElement::GetList(
    [],
    ['IBLOCK_ID' => $iblock_id, 'PROPERTY_501' => $apiItemId],
    false,
    ['nTopCount' => 1],
    ['ID', 'NAME', 'PROPERTY_501']
);
$element = $res->Fetch();
if (!$element) {
    echo json_encode([
        'error' => 'Товар не найден в Битрикс',
        'item_id' => $apiItemId,
        'hint' => 'Сначала импортируйте товар полным импортом (load.php) или убедитесь, что свойство 501 = item_id.',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return;
}

$productId = (int)$element['ID'];
$productName = $element['NAME'];

// 3) Маппинг: при отсутствии — ищем склад "Склад N дн." в Битрикс; если найден — используем и дополняем маппинг, иначе создаём.
function ensureDeliveryMappingLocal($days, &$storeMap, &$priceMap, $configPath) {
    $days = (int)$days;
    if (isset($storeMap[$days]) && isset($priceMap[$days])) return;
    if ($days < 0) return;
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
        $storeFields[$ufCode] = (string)$days;
    }
    $newStoreId = CCatalogStore::Add($storeFields);
    if (!$newStoreId) return;
    $priceTypeName = 'PROMELEC' . $days;
    $newPriceTypeId = CCatalogGroup::Add([
        'NAME' => $priceTypeName,
        'BASE' => 'N',
        'SORT' => 100,
    ]);
    if (!$newPriceTypeId) return;
    promelec_save_delivery_mapping($configPath, $days, $newStoreId, $newPriceTypeId);
    $storeMap[$days] = $newStoreId;
    $priceMap[$days] = $newPriceTypeId;
}

function updateStoreStockLocal($productId, $storeId, $amount) {
    $storeRes = CCatalogStoreProduct::GetList([], ['PRODUCT_ID' => $productId, 'STORE_ID' => $storeId], false, false, ['ID']);
    $existing = $storeRes->Fetch();
    $fields = ['PRODUCT_ID' => $productId, 'STORE_ID' => $storeId, 'AMOUNT' => $amount];
    if ($existing) {
        CCatalogStoreProduct::Update($existing['ID'], $fields);
    } else {
        CCatalogStoreProduct::Add($fields);
    }
}

function addPricesForType($productId, $priceBreaks, $priceTypeId) {
    if (!is_array($priceBreaks) || empty($priceBreaks)) return 0;
    $priceBreaks = promelec_normalize_pricebreaks($priceBreaks);
    if (empty($priceBreaks)) return 0;
    $dbPrice = CPrice::GetList([], ['PRODUCT_ID' => $productId, 'CATALOG_GROUP_ID' => $priceTypeId]);
    while ($ar = $dbPrice->Fetch()) CPrice::Delete($ar['ID']);
    $added = 0;
    foreach ($priceBreaks as $key => $pb) {
        $qtyFrom = (int)$pb['quant'];
        $qtyTo = isset($priceBreaks[$key + 1]) ? (int)$priceBreaks[$key + 1]['quant'] - 1 : false;
        $priceVal = (float)$pb['price'];
        CPrice::Add([
            'PRODUCT_ID' => $productId,
            'CATALOG_GROUP_ID' => $priceTypeId,
            'PRICE' => $priceVal,
            'CURRENCY' => 'RUB',
            'QUANTITY_FROM' => $qtyFrom,
            'QUANTITY_TO' => $qtyTo,
        ]);
        $added++;
    }
    return $added;
}

// 4) Остатки: обнулить по всем складам → внести новые по маппингу → сумма в доступное количество
promelec_zero_product_stock($productId);
$storeQuantities = [];
$totalQuantity = 0;
$totalFromAllVendors = 0;
$vendors = isset($product['vendors']) && is_array($product['vendors']) ? $product['vendors'] : [];
foreach ($vendors as $vendor) {
    $qty = (int)($vendor['quant'] ?? 0);
    if ($qty > 0) {
        $totalFromAllVendors += $qty;
    }
    $days = (int)($vendor['delivery'] ?? 0);
    if ($qty <= 0) continue;
    ensureDeliveryMappingLocal($days, $PROMELEC_DELIVERY_TO_STORE, $PROMELEC_DELIVERY_TO_PRICE_TYPE, $deliveryMappingFile);
    if (!isset($PROMELEC_DELIVERY_TO_STORE[$days])) continue;
    $storeId = $PROMELEC_DELIVERY_TO_STORE[$days];
    if (!isset($storeQuantities[$storeId])) $storeQuantities[$storeId] = 0;
    $storeQuantities[$storeId] += $qty;
    $totalQuantity += $qty;
}
foreach ($storeQuantities as $storeId => $amount) {
    updateStoreStockLocal($productId, $storeId, $amount);
}
$catalogProduct = new CCatalogProduct();
$quantityForCatalog = $totalFromAllVendors > 0 ? $totalFromAllVendors : $totalQuantity;
$catalogFields = ['ID' => $productId, 'QUANTITY' => $quantityForCatalog, 'QUANTITY_TRACE' => 'Y', 'CAN_BUY_ZERO' => 'N'];
if ($catalogProduct->GetByID($productId)) {
    $catalogProduct->Update($productId, $catalogFields);
} else {
    $catalogProduct->Add($catalogFields);
}

// 5) Цены: без расширенных — одна цена в базу, без диапазонов; с расширенными — по маппингу складов с диапазонами
$productPricebreaks = isset($product['pricebreaks']) && is_array($product['pricebreaks']) ? $product['pricebreaks'] : [];
$pricesByDelivery = [];
$singlePriceMode = false;

if (promelec_has_single_price_only($product)) {
    // Нет расширенных цен: записываем одну цену в базовый тип, без расширенного режима по складам
    $single = promelec_get_single_pricebreak($product);
    if ($single !== null) {
        $singlePriceMode = true;
        $dbPrice = CPrice::GetList([], ['PRODUCT_ID' => $productId, 'CATALOG_GROUP_ID' => $priceTypeId]);
        while ($ar = $dbPrice->Fetch()) CPrice::Delete($ar['ID']);
        CPrice::Add([
            'PRODUCT_ID' => $productId,
            'CATALOG_GROUP_ID' => $priceTypeId,
            'PRICE' => (float)$single['price'],
            'CURRENCY' => 'RUB',
            'QUANTITY_FROM' => max(1, (int)$single['quant']),
            'QUANTITY_TO' => false,
        ]);
        $pricesByDelivery[] = [
            'mode' => 'base_price',
            'price_type_id' => $priceTypeId,
            'comment' => 'Розничная цена (одна цена, без расширенного режима)',
            'price' => (float)$single['price'],
            'quantity_from' => max(1, (int)$single['quant']),
        ];
    }
} else {
    // Есть расширенные цены с диапазонами — записываем по маппингу (тип цены по delivery)
    $deliveriesProcessed = [];
    foreach ($vendors as $vendor) {
        $days = (int)($vendor['delivery'] ?? 0);
        ensureDeliveryMappingLocal($days, $PROMELEC_DELIVERY_TO_STORE, $PROMELEC_DELIVERY_TO_PRICE_TYPE, $deliveryMappingFile);
        if (!isset($PROMELEC_DELIVERY_TO_PRICE_TYPE[$days]) || isset($deliveriesProcessed[$days])) continue;
        $deliveriesProcessed[$days] = true;
        $ptId = $PROMELEC_DELIVERY_TO_PRICE_TYPE[$days];
        $priceBreaks = (isset($vendor['pricebreaks']) && is_array($vendor['pricebreaks']) && !empty($vendor['pricebreaks']))
            ? $vendor['pricebreaks'] : $productPricebreaks;
        $normalized = promelec_normalize_pricebreaks($priceBreaks);
        $added = addPricesForType($productId, $priceBreaks, $ptId);
        $ranges = [];
        foreach ($normalized as $i => $pb) {
            $from = (int)$pb['quant'];
            $to = isset($normalized[$i + 1]) ? (int)$normalized[$i + 1]['quant'] - 1 : null;
            $ranges[] = ['from' => $from, 'to' => $to, 'price' => (float)$pb['price']];
        }
        $storeId = isset($PROMELEC_DELIVERY_TO_STORE[$days]) ? $PROMELEC_DELIVERY_TO_STORE[$days] : null;
        $pricesByDelivery[] = [
            'delivery_days' => $days,
            'store_id' => $storeId,
            'price_type_id' => $ptId,
            'prices_added' => $added,
            'ranges' => $ranges,
        ];
    }
}

$storesDetail = [];
foreach ($storeQuantities as $storeId => $amount) {
    $storesDetail[] = ['store_id' => $storeId, 'amount' => $amount];
}

echo json_encode([
    'success' => true,
    'message' => 'Тестовые данные загружены в склады и цены',
    'item_id' => $apiItemId,
    'bitrix_element_id' => $productId,
    'product_name' => $productName,
    'api_source' => 'test (api_url_test)',
    'single_price_mode' => $singlePriceMode,
    'stores_updated' => $storesDetail,
    'total_quantity' => $quantityForCatalog,
    'total_from_all_vendors' => $totalFromAllVendors,
    'prices_by_delivery' => $pricesByDelivery,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
