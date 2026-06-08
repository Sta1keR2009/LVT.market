<?php
// Включение Bitrix ядра
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

// === Основные идентификаторы ===
define('IBLOCK_ID', 39); // ID инфоблока товаров
define('DIGIKEY_STORE_ID', 5); // ID склада Digi-Key
define('MOUSER_STORE_ID', 6);  // ID склада Mouser
define('DIGIKEY_PRICE_TYPE_ID', 3); // Тип цен Битрикс №3 DIGIKEY
define('MOUSER_PRICE_TYPE_ID', 4);  // Тип цен Битрикс №4 MOUSER

// === Конфигурация авторизации — здесь должны быть актуальные токены и ключи ===
$digiKeyToken = 'ВАШ_ТОКЕН_OAUTH2_DIGIKEY';
$mouserApiKey = 'ВАШ_API_KEY_MOUSER';

// Функция получения данных от Digi-Key (по массиву MPN)
function fetchDigiKeyProducts($mpnArray) {
    $url = "https://api.digikey.com/products/v4/search";
    $headers = [
        "Authorization: Bearer YOUR_TOKEN", // Замените на $GLOBALS['digiKeyToken']
        "Content-Type: application/json",
        "Accept: application/json"
    ];
    $data = [ "keywords" => $mpnArray ];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// Функция получения данных от Mouser (по массиву MPN)
function fetchMouserProducts($mpnArray) {
    $url = "https://api.mouser.com/api/v1/search/partnumber";
    $headers = [
        "apiKey: YOUR_KEY", // Замените на $GLOBALS['mouserApiKey']
        "Content-Type: application/json"
    ];
    $data = [
        "SearchByPartRequest" => [
            "mouserPartNumber" => $mpnArray
        ]
    ];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// Основная обработка: получаем массив MPN (например, из CSV или списка)
$mpnArray = ["SN74HC595N", "LM358"]; // пример набора

// Получение товаров с двух источников
$digiProducts = fetchDigiKeyProducts($mpnArray);
$mouserProducts = fetchMouserProducts($mpnArray);

// Обработка каждой позиции (либо Digi-Key, либо Mouser)
foreach ($mpnArray as $mpn) {
    // Поиск существующего товара по PROPERTY_MPN
    $productId = null;
    $res = CIBlockElement::GetList([], [
        "IBLOCK_ID" => IBLOCK_ID,
        "PROPERTY_MPN" => $mpn
    ], false, false, ["ID"]);
    if ($ar = $res->Fetch()) {
        $productId = $ar["ID"];
    } else {
        // Создание нового товара
        $el = new CIBlockElement();
        $arLoadProductArray = [
            "IBLOCK_ID" => IBLOCK_ID,
            "NAME" => $mpn,
            "PROPERTY_VALUES" => ["MPN" => $mpn],
            "ACTIVE" => "Y"
        ];
        $productId = $el->Add($arLoadProductArray);
    }

    // --- Digi-Key: обновить остаток, цену ---
    if (isset($digiProducts['Products'][$mpn])) {
        $data = $digiProducts['Products'][$mpn];
        // Остаток
        $stock = $data['QuantityAvailable'] ?? 0;
        updateStock($productId, DIGIKEY_STORE_ID, $stock);

        // Цена (берём первую прайсовую или по объёму)
        $price = $data['StandardPricing'][0]['Price'] ?? 0;
        updatePrice($productId, $price, DIGIKEY_PRICE_TYPE_ID, 'USD');
    }

    // --- Mouser: обновить остаток, цену ---
    if (isset($mouserProducts['SearchResults']['Parts'][$mpn])) {
        $data = $mouserProducts['SearchResults']['Parts'][$mpn];
        $stock = $data['Availability'] ?? 0;
        updateStock($productId, MOUSER_STORE_ID, $stock);

        $price = $data['PriceBreaks'][0]['Price'] ?? 0;
        updatePrice($productId, $price, MOUSER_PRICE_TYPE_ID, 'USD');
    }
}

// Обновление остатка на конкретном складе Bitrix
function updateStock($productId, $storeId, $amount) {
    $arFields = [
        "PRODUCT_ID" => $productId,
        "STORE_ID" => $storeId,
        "AMOUNT" => $amount
    ];
    $res = CCatalogStoreProduct::GetList([], [
        "PRODUCT_ID" => $productId,
        "STORE_ID" => $storeId
    ]);
    if ($ob = $res->Fetch()) {
        CCatalogStoreProduct::Update($ob["ID"], $arFields);
    } else {
        CCatalogStoreProduct::Add($arFields);
    }
}

// Обновление цены Bitrix по нужному типу цен
function updatePrice($productId, $price, $priceTypeId, $currency = 'USD') {
    $arFields = [
        "PRODUCT_ID" => $productId,
        "CATALOG_GROUP_ID" => $priceTypeId,
        "PRICE" => $price,
        "CURRENCY" => $currency
    ];
    $dbPrice = CPrice::GetList([], [
        "PRODUCT_ID" => $productId,
        "CATALOG_GROUP_ID" => $priceTypeId
    ]);
    if ($arPrice = $dbPrice->Fetch()) {
        CPrice::Update($arPrice["ID"], $arFields);
    } else {
        CPrice::Add($arFields);
    }
}

// Конец скрипта
echo "Импорт завершён";
