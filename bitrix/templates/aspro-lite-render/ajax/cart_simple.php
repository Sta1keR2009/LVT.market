<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if ($_POST['action'] == 'add_to_cart_simple') {
    
    CModule::IncludeModule('sale');
    CModule::IncludeModule('catalog');
    
    $productId = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    $storeName = $_POST['store_name'] ?? '';
    $storeId = intval($_POST['store_id'] ?? 0);
    $priceType = $_POST['price_type'] ?? '';
    
    // Проверяем CSRF
    if (!check_bitrix_sessid()) {
        echo json_encode(['status' => 'error', 'message' => 'Ошибка безопасности']);
        die();
    }
    
    // Формируем свойства
    $arProps = [];
    
    if ($storeName) {
        $arProps[] = [
            'NAME' => 'Склад',
            'CODE' => 'STORE',
            'VALUE' => $storeName
        ];
    }
    
    if ($storeId) {
        $arProps[] = [
            'NAME' => 'ID склада',
            'CODE' => 'STORE_ID',
            'VALUE' => $storeId
        ];
    }
    
    if ($priceType) {
        $arProps[] = [
            'NAME' => 'Тип цены',
            'CODE' => 'PRICE_TYPE',
            'VALUE' => $priceType
        ];
    }
    
    // Пробуем добавить в корзину
    $result = Add2BasketByProductID($productId, $quantity, array('PROPS' => $arProps));
    
    if ($result) {
        echo json_encode(['status' => 'success', 'message' => 'Товар добавлен']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Ошибка добавления']);
    }
    
    die();
}

echo json_encode(['status' => 'error', 'message' => 'Неизвестное действие']);
?>