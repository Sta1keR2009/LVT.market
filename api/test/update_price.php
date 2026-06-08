<?php
/**
 * Обновление остатков и цен в Битрикс через тестовое API (api_url_test).
 * Логика как у load_to_bitrix.php: обнуление остатков по складам → новые остатки по маппингу → сумма в доступное количество; цены — одна в базу или по типам по delivery.
 *
 * Режимы:
 *   all   — весь каталог (пагинация items_data_get).
 *   item  — один товар по item_id или code.
 *   limit — обновить не более N товаров (параметр count).
 *
 * Использование:
 *   Веб:  ?mode=all  |  ?mode=item&item_id=838637  |  ?mode=limit&count=100
 *   CLI:  php update_price.php all  |  php update_price.php item 838637  |  php update_price.php limit 100
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

// Режим и параметры
$mode = 'all';
$itemParam = null;
$limitCount = null;

if (php_sapi_name() === 'cli') {
    $argv = $_SERVER['argv'] ?? [];
    if (isset($argv[1])) {
        $mode = strtolower(trim($argv[1]));
        if ($mode !== 'all' && $mode !== 'item' && $mode !== 'limit') {
            $itemParam = is_numeric($argv[1]) ? (int)$argv[1] : $argv[1];
            $mode = 'item';
        }
    }
    if ($mode === 'item' && $itemParam === null) {
        foreach (array_slice($argv, 2) as $arg) {
            if (strpos($arg, '--item_id=') === 0) { $itemParam = (int)substr($arg, 10); break; }
            if (strpos($arg, '--code=') === 0) { $v = trim(substr($arg, 7)); $itemParam = is_numeric($v) ? (int)$v : $v; break; }
            if ($itemParam === null && (is_numeric($arg) || $arg !== 'item')) { $itemParam = is_numeric($arg) ? (int)$arg : $arg; }
        }
    }
    if ($mode === 'limit') {
        foreach (array_slice($argv, 2) as $arg) {
            if (strpos($arg, '--count=') === 0) {
                $limitCount = (int) substr($arg, 8);
                break;
            }
            if (is_numeric($arg)) {
                $limitCount = (int) $arg;
                break;
            }
        }
    }
} else {
    $mode = isset($_GET['mode']) ? strtolower(trim($_GET['mode'])) : 'all';
    if ($mode !== 'all' && $mode !== 'item' && $mode !== 'limit') {
        $mode = 'all';
    }
    if ($mode === 'item') {
        if (isset($_GET['item_id']) && $_GET['item_id'] !== '') {
            $itemParam = is_numeric($_GET['item_id']) ? (int)$_GET['item_id'] : trim($_GET['item_id']);
        } elseif (isset($_GET['code']) && $_GET['code'] !== '') {
            $itemParam = is_numeric($_GET['code']) ? (int)$_GET['code'] : trim($_GET['code']);
        }
    }
    if ($mode === 'limit') {
        $limitCount = isset($_GET['count']) ? (int) $_GET['count'] : null;
    }
}

if ($mode === 'item' && ($itemParam === null || $itemParam === '')) {
    echo json_encode([
        'error' => 'В режиме item укажите item_id или code',
        'usage_web' => '/api/test/update_price.php?mode=item&item_id=838637',
        'usage_cli' => 'php update_price.php item 838637',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return;
}

if ($mode === 'limit' && ($limitCount === null || $limitCount < 1)) {
    echo json_encode([
        'error' => 'В режиме limit укажите count (число товаров для обновления, не менее 1)',
        'usage_web' => '/api/test/update_price.php?mode=limit&count=100',
        'usage_cli' => 'php update_price.php limit 100',
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return;
}

/** Вывод прогресса в stderr (только CLI), чтобы stdout оставался валидным JSON */
function progressEcho($msg) {
    if (php_sapi_name() !== 'cli') {
        return;
    }
    $stderr = fopen('php://stderr', 'w');
    if ($stderr) {
        fwrite($stderr, $msg);
        fflush($stderr);
    }
}

/** Запрос к тестовому API: items_data_get (пагинация) */
function fetchTestApiBatch($apiUrl, $login, $passwordMd5, $customerId, $lastItemId = null) {
    $request = [
        'login' => $login,
        'password' => $passwordMd5,
        'method' => 'items_data_get',
        'customer_id' => $customerId,
    ];
    if ($lastItemId !== null) {
        $request['item_id'] = $lastItemId;
    }
    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($request, JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 300,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode !== 200 || !$response) {
        return false;
    }
    $data = json_decode($response, true, 512, JSON_UNESCAPED_UNICODE);
    if (!is_array($data) || (isset($data['error']) && $data['error'])) {
        return isset($data['error']) ? ['error' => $data['error']] : false;
    }
    return $data;
}

/** Один товар по item_id/code: item_data_get */
function fetchTestApiItem($apiUrl, $login, $passwordMd5, $customerId, $itemId) {
    $request = [
        'login' => $login,
        'password' => $passwordMd5,
        'method' => 'item_data_get',
        'customer_id' => $customerId,
        'item_id' => is_numeric($itemId) ? (int)$itemId : $itemId,
    ];
    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($request, JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 60,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode !== 200 || !$response) {
        return null;
    }
    $product = json_decode($response, true, 512, JSON_UNESCAPED_UNICODE);
    return (is_array($product) && isset($product['item_id'])) ? $product : null;
}

function findProductsByItemIds($iblock_id, $itemIds) {
    $products = [];
    if (empty($itemIds)) return $products;
    foreach (array_chunk($itemIds, 500) as $chunk) {
        $res = CIBlockElement::GetList(
            [],
            ['IBLOCK_ID' => $iblock_id, 'PROPERTY_501' => $chunk, 'CHECK_PERMISSIONS' => 'N'],
            false,
            false,
            ['ID', 'NAME', 'PROPERTY_501']
        );
        while ($ar = $res->Fetch()) {
            $apiId = $ar['PROPERTY_501_VALUE'] ?? $ar['PROPERTY_501'];
            $products[$apiId] = $ar;
        }
    }
    return $products;
}

function ensureDeliveryMappingLocal($days, &$storeMap, &$priceMap, $configPath) {
    $days = (int)$days;
    if (isset($storeMap[$days]) && isset($priceMap[$days])) return;
    if ($days < 0) return;
    $existingStoreId = promelec_find_store_by_delivery_days($days);
    if ($existingStoreId) {
        $priceTypeName = 'PROMELEC' . $days;
        $ptId = promelec_find_price_type_by_name($priceTypeName);
        if (!$ptId) {
            $ptId = CCatalogGroup::Add(['NAME' => $priceTypeName, 'BASE' => 'N', 'SORT' => 100]);
        }
        if ($ptId) {
            promelec_save_delivery_mapping($configPath, $days, $existingStoreId, $ptId);
            $storeMap[$days] = $existingStoreId;
            $priceMap[$days] = $ptId;
        }
        return;
    }
    $storeTitle = 'Склад ' . $days . ' дн.';
    $ufCode = $GLOBALS['promelec_store_delivery_uf_code'] ?? '';
    $storeFields = ['TITLE' => $storeTitle, 'ACTIVE' => 'Y', 'ADDRESS' => 'По заказу'];
    if ($ufCode !== '') $storeFields[$ufCode] = (string)$days;
    $newStoreId = CCatalogStore::Add($storeFields);
    if (!$newStoreId) return;
    $priceTypeName = 'PROMELEC' . $days;
    $newPriceTypeId = CCatalogGroup::Add(['NAME' => $priceTypeName, 'BASE' => 'N', 'SORT' => 100]);
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

function addPricesForTypeLocal($productId, $priceBreaks, $priceTypeId) {
    if (!is_array($priceBreaks) || empty($priceBreaks)) return 0;
    $priceBreaks = promelec_normalize_pricebreaks($priceBreaks);
    if (empty($priceBreaks)) return 0;
    $dbPrice = CPrice::GetList([], ['PRODUCT_ID' => $productId, 'CATALOG_GROUP_ID' => $priceTypeId]);
    while ($ar = $dbPrice->Fetch()) CPrice::Delete($ar['ID']);
    $added = 0;
    foreach ($priceBreaks as $key => $pb) {
        $qtyFrom = (int)$pb['quant'];
        $qtyTo = isset($priceBreaks[$key + 1]) ? (int)$priceBreaks[$key + 1]['quant'] - 1 : false;
        CPrice::Add([
            'PRODUCT_ID' => $productId,
            'CATALOG_GROUP_ID' => $priceTypeId,
            'PRICE' => (float)$pb['price'],
            'CURRENCY' => 'RUB',
            'QUANTITY_FROM' => $qtyFrom,
            'QUANTITY_TO' => $qtyTo,
        ]);
        $added++;
    }
    return $added;
}

/**
 * Применить обновление остатков и цен для одного товара (как в load_to_bitrix.php).
 * Возвращает ['ok' => bool, 'product_id' => int, 'message' => string].
 */
function processOneProduct($product, $iblock_id, $priceTypeId, $deliveryMappingFile) {
    global $PROMELEC_DELIVERY_TO_STORE, $PROMELEC_DELIVERY_TO_PRICE_TYPE;

    $apiItemId = isset($product['item_id']) ? (int)$product['item_id'] : 0;
    $res = CIBlockElement::GetList(
        [],
        ['IBLOCK_ID' => $iblock_id, 'PROPERTY_501' => $apiItemId],
        false,
        ['nTopCount' => 1],
        ['ID', 'NAME']
    );
    $element = $res->Fetch();
    if (!$element) {
        return ['ok' => false, 'product_id' => 0, 'message' => 'Товар не найден в Битрикс', 'item_id' => $apiItemId];
    }
    $productId = (int)$element['ID'];
    $productName = $element['NAME'];

    promelec_zero_product_stock($productId);

    $storeQuantities = [];
    $totalFromAllVendors = 0;
    $vendors = isset($product['vendors']) && is_array($product['vendors']) ? $product['vendors'] : [];
    foreach ($vendors as $vendor) {
        $qty = (int)($vendor['quant'] ?? 0);
        if ($qty > 0) $totalFromAllVendors += $qty;
        $days = (int)($vendor['delivery'] ?? 0);
        if ($qty <= 0) continue;
        ensureDeliveryMappingLocal($days, $PROMELEC_DELIVERY_TO_STORE, $PROMELEC_DELIVERY_TO_PRICE_TYPE, $deliveryMappingFile);
        if (!isset($PROMELEC_DELIVERY_TO_STORE[$days])) continue;
        $storeId = $PROMELEC_DELIVERY_TO_STORE[$days];
        if (!isset($storeQuantities[$storeId])) $storeQuantities[$storeId] = 0;
        $storeQuantities[$storeId] += $qty;
    }
    foreach ($storeQuantities as $storeId => $amount) {
        updateStoreStockLocal($productId, $storeId, $amount);
    }
    $quantityForCatalog = $totalFromAllVendors > 0 ? $totalFromAllVendors : array_sum($storeQuantities);
    $catalogProduct = new CCatalogProduct();
    $catalogFields = ['ID' => $productId, 'QUANTITY' => $quantityForCatalog, 'QUANTITY_TRACE' => 'Y', 'CAN_BUY_ZERO' => 'N'];
    if ($catalogProduct->GetByID($productId)) {
        $catalogProduct->Update($productId, $catalogFields);
    } else {
        $catalogProduct->Add($catalogFields);
    }

    $productPricebreaks = isset($product['pricebreaks']) && is_array($product['pricebreaks']) ? $product['pricebreaks'] : [];
    if (promelec_has_single_price_only($product)) {
        $single = promelec_get_single_pricebreak($product);
        if ($single !== null) {
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
        }
    } else {
        $deliveriesProcessed = [];
        foreach ($vendors as $vendor) {
            $days = (int)($vendor['delivery'] ?? 0);
            ensureDeliveryMappingLocal($days, $PROMELEC_DELIVERY_TO_STORE, $PROMELEC_DELIVERY_TO_PRICE_TYPE, $deliveryMappingFile);
            if (!isset($PROMELEC_DELIVERY_TO_PRICE_TYPE[$days]) || isset($deliveriesProcessed[$days])) continue;
            $deliveriesProcessed[$days] = true;
            $ptId = $PROMELEC_DELIVERY_TO_PRICE_TYPE[$days];
            $priceBreaks = (isset($vendor['pricebreaks']) && is_array($vendor['pricebreaks']) && !empty($vendor['pricebreaks']))
                ? $vendor['pricebreaks'] : $productPricebreaks;
            addPricesForTypeLocal($productId, $priceBreaks, $ptId);
        }
    }

    return ['ok' => true, 'product_id' => $productId, 'item_id' => $apiItemId, 'name' => $productName];
}

// ——— Режим item: один товар ———
if ($mode === 'item') {
    $apiItemId = is_numeric($itemParam) ? (int)$itemParam : $itemParam;
    $product = fetchTestApiItem($apiUrl, $login, $passwordMd5, $customerId, $apiItemId);
    if (!$product) {
        echo json_encode([
            'error' => 'Тестовый API не вернул товар',
            'item_id' => $apiItemId,
            'api_source' => 'test',
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return;
    }
    $result = processOneProduct($product, $iblock_id, $priceTypeId, $deliveryMappingFile);
    echo json_encode(array_merge([
        'success' => $result['ok'],
        'mode' => 'item',
        'api_source' => 'test (api_url_test)',
    ], $result), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return;
}

// ——— Режим all или limit: каталог (limit — не более count товаров) ———
$maxToUpdate = ($mode === 'limit') ? $limitCount : null;
$totalReceived = 0;
$totalUpdated = 0;
$totalSkipped = 0;
$lastItemId = null;
$batchNumber = 0;

$limitLabel = $maxToUpdate !== null ? ", лимит: $maxToUpdate" : '';
progressEcho("Обновление остатков и цен (режим: $mode$limitLabel)...\n");

do {
    $batchNumber++;
    $data = fetchTestApiBatch($apiUrl, $login, $passwordMd5, $customerId, $lastItemId);
    if ($data === false) {
        echo json_encode(['error' => 'Ошибка запроса к тестовому API', 'batch' => $batchNumber], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return;
    }
    if (isset($data['error'])) {
        echo json_encode(['error' => 'API: ' . ($data['error'] ?? 'unknown'), 'batch' => $batchNumber], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return;
    }
    if (empty($data)) {
        break;
    }

    $itemIds = [];
    $productsMap = [];
    foreach ($data as $p) {
        if (!is_array($p)) continue;
        $itemId = $p['item_id'] ?? $p['itemId'] ?? $p['id'] ?? null;
        if ($itemId !== null) {
            $itemIds[] = $itemId;
            $productsMap[$itemId] = $p;
        }
    }
    $totalReceived += count($productsMap);

    $existing = findProductsByItemIds($iblock_id, $itemIds);
    foreach ($productsMap as $apiId => $product) {
        if ($maxToUpdate !== null && $totalUpdated >= $maxToUpdate) {
            progressEcho("  Достигнут лимит $maxToUpdate, остановка.\n");
            break 2;
        }
        if (!isset($existing[$apiId])) {
            $totalSkipped++;
            continue;
        }
        $r = processOneProduct($product, $iblock_id, $priceTypeId, $deliveryMappingFile);
        if ($r['ok']) {
            $totalUpdated++;
            if ($maxToUpdate !== null) {
                progressEcho("  [" . $totalUpdated . "/" . $maxToUpdate . "] item_id=$apiId\n");
            }
        }
    }

    $receivedInBatch = count($productsMap);
    $suffix = $maxToUpdate !== null ? " (обновлено $totalUpdated/$maxToUpdate)" : "";
    progressEcho("  Пакет $batchNumber: получено $receivedInBatch, всего обновлено $totalUpdated, пропущено $totalSkipped$suffix\n");

    $lastItem = end($data);
    $lastItemId = is_array($lastItem) ? ($lastItem['item_id'] ?? null) : null;
    if (count($data) === 0 || $lastItemId === null) {
        break;
    }
    if ($batchNumber >= 999) {
        break;
    }
} while (true);

progressEcho("Готово. Обновлено: $totalUpdated, пропущено: $totalSkipped, пакетов: $batchNumber\n");

$result = [
    'success' => true,
    'mode' => $mode,
    'api_source' => 'test (api_url_test)',
    'total_received' => $totalReceived,
    'total_updated' => $totalUpdated,
    'total_skipped' => $totalSkipped,
    'batches' => $batchNumber,
];
if ($mode === 'limit') {
    $result['count_requested'] = $limitCount;
}
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
