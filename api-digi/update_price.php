<?php
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

if (!CModule::IncludeModule('iblock') || !CModule::IncludeModule('catalog')) {
    die('Не загружены необходимые модули');
}

$priceTypeId = 1;
$iblock_id = 11; 
$logFile = __DIR__ . '/price_update.log'; 


function logMessage($message) {
    global $logFile;
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - " . $message . PHP_EOL, FILE_APPEND | LOCK_EX);
}


$login = 'lvtgroup2';
$password = '30316';
$api_url = 'https://aaa.na4u.ru/rpc/';
$password_md5 = strtoupper(md5($password));


function fetchApiData($itemId = null, $changedTime = null) {
    global $login, $password_md5, $api_url, $customer_id;
    $request_data = [
        'login' => $login,
        'password' => $password_md5,
        'method' => 'items_data_get',
        'customer_id' => $customer_id
    ];
    
    if ($itemId !== null) {
        $request_data['item_id'] = $itemId;
    }
    
    if ($changedTime !== null) {
        $request_data['changed_time'] = $changedTime;
    }
    
    $request_json = json_encode($request_data, JSON_UNESCAPED_UNICODE);
    logMessage("Отправка запроса: " . $request_json); // Для отладки
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request_json);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    logMessage("Ответ API: HTTP $http_code, Error: $error, Response length: " . strlen($response)); // Для отладки
    
    if ($http_code !== 200 || !$response) {
        logMessage("Ошибка API: HTTP $http_code, ошибка: $error");
        return false;
    }
    
    $data = json_decode($response, true, 512, JSON_UNESCAPED_UNICODE);
    if (json_last_error() !== JSON_ERROR_NONE) {
        logMessage("Ошибка разбора JSON: " . json_last_error_msg() . ", данные: " . substr($response, 0, 200));
        return false;
    }
    
    if (!is_array($data)) {
        logMessage("Неверный формат данных из API: " . gettype($data));
        return false;
    }
    
    logMessage("Получено товаров: " . count($data));
    return $data;
}


function findProductByItemId($itemId) {
    global $iblock_id;
    

    if (!is_numeric($itemId)) {
        logMessage("Неверный формат item_id: " . gettype($itemId) . " - $itemId");
        return false;
    }
    
    $filter = [
        'IBLOCK_ID' => $iblock_id,
        'PROPERTY_501' => $itemId, 
        'CHECK_PERMISSIONS' => 'N'
    ];
    
    $select = ['ID', 'NAME', 'TIMESTAMP_X'];
    $res = CIBlockElement::GetList([], $filter, false, ['nTopCount' => 1], $select);
    
    if ($arElement = $res->Fetch()) {
        return $arElement;
    }
    
    return false;
}


function getCurrentPrices($productId, $priceTypeId) {
    $currentPrices = [];
    $dbPrice = CPrice::GetList([], ['PRODUCT_ID' => $productId, 'CATALOG_GROUP_ID' => $priceTypeId]);
    while ($arPrice = $dbPrice->Fetch()) {
        $currentPrices[] = [
            'QUANTITY_FROM' => $arPrice['QUANTITY_FROM'],
            'QUANTITY_TO' => $arPrice['QUANTITY_TO'],
            'PRICE' => $arPrice['PRICE']
        ];
    }
    return $currentPrices;
}


function formatPricesForLog($prices) {
    $formatted = [];
    foreach ($prices as $price) {
        $from = $price['QUANTITY_FROM'] ?: '1';
        $to = $price['QUANTITY_TO'] ?: '∞';
        $formatted[] = "{$from}-{$to}: {$price['PRICE']} руб.";
    }
    return implode('; ', $formatted);
}


function updateProductPrices($productId, $priceBreaks, $priceTypeId, $productName, $apiItemId) {
    global $APPLICATION;
    

    if (!is_array($priceBreaks)) {
        logMessage("PriceBreaks не является массивом для товара ID $productId (API ID: $apiItemId): " . gettype($priceBreaks));
        return false;
    }
    

    $currentPrices = getCurrentPrices($productId, $priceTypeId);
    $currentPricesLog = formatPricesForLog($currentPrices);
    
    $deletedCount = 0;
    $dbPrice = CPrice::GetList([], ['PRODUCT_ID' => $productId, 'CATALOG_GROUP_ID' => $priceTypeId]);
    while ($arPrice = $dbPrice->Fetch()) {
        CPrice::Delete($arPrice['ID']);
        $deletedCount++;
    }
    

    $newPrices = [];
    $newPricesData = [];
    

    if (!empty($priceBreaks)) {
        foreach ($priceBreaks as $key => $priceBreak) {
            if (!is_array($priceBreak)) {
                logMessage("Неверная структура priceBreak для товара ID $productId (API ID: $apiItemId): " . gettype($priceBreak));
                continue;
            }
            
            $quantityFrom = isset($priceBreak['quant']) ? (int)$priceBreak['quant'] : 0;
            $quantityTo = isset($priceBreaks[$key + 1]) ? (int)($priceBreaks[$key + 1]['quant']) - 1 : false;
            $priceValue = isset($priceBreak['price']) ? (float)$priceBreak['price'] : 0;
            
            if ($quantityFrom <= 0 || $priceValue <= 0) {
                logMessage("Пропущена некорректная цена для товара ID $productId (API ID: $apiItemId): quant=$quantityFrom, price=$priceValue");
                continue;
            }
            
            $priceFields = [
                'PRODUCT_ID' => $productId,
                'CATALOG_GROUP_ID' => $priceTypeId,
                'PRICE' => $priceValue,
                'CURRENCY' => 'RUB',
                'QUANTITY_FROM' => $quantityFrom,
                'QUANTITY_TO' => $quantityTo,
            ];
            
            $newPrices[] = [
                'QUANTITY_FROM' => $quantityFrom,
                'QUANTITY_TO' => $quantityTo,
                'PRICE' => $priceValue
            ];
            $newPricesData[] = $priceFields;
        }
    }
    
    $newPricesLog = formatPricesForLog($newPrices);
    
    $addedCount = 0;
    foreach ($newPricesData as $priceFields) {
        $priceId = CPrice::Add($priceFields);
        if ($priceId) {
            $addedCount++;
        } else {
            $errorMessage = $APPLICATION->GetException() ? $APPLICATION->GetException()->GetString() : 'Неизвестная ошибка';
            logMessage("❌ Ошибка при добавлении цены для товара ID $productId (API ID: $apiItemId): " . $errorMessage);
            return false;
        }
    }
    
    logMessage("💰 Цены обновлены для товара: $productName (Битрикс ID: $productId, API ID: $apiItemId)");
    logMessage("   Старые цены: " . ($currentPricesLog ?: 'нет'));
    logMessage("   Новые цены: " . ($newPricesLog ?: 'нет'));
    logMessage("   Удалено старых цен: $deletedCount, Добавлено новых: $addedCount");
    
    return true;
}


function updateProductStock($productId, $vendors, $productName, $apiItemId) {
    global $APPLICATION;

    $totalQuantity = 0;
    $vendor0Quantity = 0;
    
    if (is_array($vendors)) {
        foreach ($vendors as $vendor) {
            if (isset($vendor['vendor']) && $vendor['vendor'] != 0) {
                $totalQuantity += (int)($vendor['quant'] ?? 0);
            }
            if (isset($vendor['vendor']) && $vendor['vendor'] == 0) {
                $vendor0Quantity += (int)($vendor['quant'] ?? 0);
            }
        }
    }

    logMessage("📦 Обновление остатков для товара: $productName (Битрикс ID: $productId, API ID: $apiItemId)");
    logMessage("   Общий остаток (без vendor 0): $totalQuantity");
    logMessage("   Остаток vendor 0 (для склада 1): $vendor0Quantity");


    $arCatalogFields = [
        "ID" => $productId,
        "QUANTITY" => $totalQuantity,
        "QUANTITY_TRACE" => "Y",
        "CAN_BUY_ZERO" => "N",
    ];

    $catalogProduct = new CCatalogProduct();
    if ($catalogProduct->GetByID($productId)) {
        if (!$catalogProduct->Update($productId, $arCatalogFields)) {
            logMessage("❌ Ошибка обновления общего количества для товара ID $productId: " . $catalogProduct->LAST_ERROR);
        }
    } else {
        if (!$catalogProduct->Add($arCatalogFields)) {
            logMessage("❌ Ошибка добавления общего количества для товара ID $productId: " . $catalogProduct->LAST_ERROR);
        }
    }


    $storeFields1 = [
        'PRODUCT_ID' => $productId,
        'STORE_ID' => 1,
        'AMOUNT' => $vendor0Quantity,
    ];

    $storeRes1 = CCatalogStoreProduct::UpdateFromForm($storeFields1);
    if (!$storeRes1) {
        logMessage("❌ Ошибка обновления остатка на складе ID 1 для товара $productId: " . $APPLICATION->GetException()->GetString());
    }

    $storeFields2 = [
        'PRODUCT_ID' => $productId,
        'STORE_ID' => 2,
        'AMOUNT' => $totalQuantity,
    ];

    $storeRes2 = CCatalogStoreProduct::UpdateFromForm($storeFields2);
    if (!$storeRes2) {
        logMessage("❌ Ошибка обновления остатка на складе ID 2 для товара $productId: " . $APPLICATION->GetException()->GetString());
    }

    logMessage("✅ Остатки обновлены для товара: $productName (ID: $productId)");
}

function updateProductTimestamp($productId, $productName) {
    $el = new CIBlockElement();
    $arFields = [
        "TIMESTAMP_X" => ConvertTimeStamp(time(), "FULL")
    ];
    
    if ($el->Update($productId, $arFields)) {
        logMessage("🕒 Дата изменения обновлена для товара: $productName (ID: $productId)");
        return true;
    } else {
        logMessage("❌ Ошибка обновления даты изменения для товара: $productName (ID: $productId) - " . $el->LAST_ERROR);
        return false;
    }
}

function processProductsBatch($products) {
    global $priceTypeId;
    $updatedCount = 0;
    
    if (!is_array($products)) {
        logMessage("Products не является массивом: " . gettype($products));
        return 0;
    }
    
    foreach ($products as $index => $product) {
        if (!is_array($product)) {
            logMessage("Неверная структура product[$index]: " . gettype($product));
            continue;
        }
        
        $item_id = isset($product['item_id']) ? $product['item_id'] : null;
        $raw_name = isset($product['name']) ? $product['name'] : 'Без названия';
        
        if (empty($item_id)) {
            logMessage("Пропущен товар без item_id: " . json_encode($product));
            continue;
        }
        
        logMessage("Обработка товара: $raw_name (API ID: $item_id)");
        
        $existingProduct = findProductByItemId($item_id);
        
        if ($existingProduct) {
            if (updateProductPrices($existingProduct['ID'], isset($product['pricebreaks']) ? $product['pricebreaks'] : [], $priceTypeId, $raw_name, $item_id)) {
                logMessage("✅ Обновлены цены для товара: {$raw_name} (Битрикс ID: {$existingProduct['ID']}, API ID: $item_id)");
                $updatedCount++;
            } else {
                logMessage("❌ Ошибка обновления цен для товара: {$raw_name} (API ID: $item_id)");
            }
            
            updateProductStock($existingProduct['ID'], $product['vendors'] ?? [], $raw_name, $item_id);
            
            updateProductTimestamp($existingProduct['ID'], $raw_name);
        } else {
            logMessage("ℹ️ Товар не найден в каталоге: {$raw_name} (API ID: $item_id)");
        }
    }
    
    return $updatedCount;
}

logMessage("--- НАЧАЛО ОБНОВЛЕНИЯ ЦЕН, ОСТАТКОВ И ДАТЫ ИЗМЕНЕНИЯ ---");

$changedTime = date('d.m.Y H:i');
logMessage("Запуск обновления. Ищем изменения с: $changedTime");

$lastItemId = null;
$totalUpdated = 0;
$batchCount = 0;

do {
    $batchCount++;
    logMessage("=== Обработка порции #$batchCount ===");
    
    logMessage("Загрузка порции по 100 товаров" . ($lastItemId ? " начиная с item_id: $lastItemId" : " с начала"));
    $data = fetchApiData($lastItemId, $changedTime);
    
    if ($data === false) {
        logMessage("Ошибка при получении данных из API");
        break;
    }
    
    if (empty($data)) {
        logMessage("Получен пустой ответ от API. Завершение.");
        break;
    }
    
    logMessage("Получено " . count($data) . " товаров для обработки");
    
    $batchUpdated = processProductsBatch($data);
    $totalUpdated += $batchUpdated;
    
    logMessage("Обработано товаров в порции: " . count($data) . ", обновлено: $batchUpdated");
    
    $lastItem = end($data);
    if (is_array($lastItem) && isset($lastItem['item_id'])) {
        $lastItemId = $lastItem['item_id'];
        logMessage("Следующая порция начнется с item_id: $lastItemId");
    } else {
        logMessage("Не удалось определить последний item_id. Завершение.");
        break;
    }
    
    if (count($data) < 100) {
        logMessage("Получена последняя порция товаров (количество: " . count($data) . ")");
        break;
    }
    
    if ($batchCount > 100) {
        logMessage("Превышено максимальное количество порций (100). Принудительное завершение.");
        break;
    }
    
} while (true);

logMessage("--- ОКОНЧАНИЕ ОБНОВЛЕНИЯ ЦЕН, ОСТАТКОВ И ДАТЫ ИЗМЕНЕНИЯ ---");
logMessage("Всего обработано порций: $batchCount");
logMessage("Всего обновлено товаров: $totalUpdated");

?>