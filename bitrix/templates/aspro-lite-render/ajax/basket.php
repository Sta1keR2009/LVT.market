<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if ($_POST['action'] == 'add_to_cart') {
    
    // Включаем необходимые модули
    CModule::IncludeModule('sale');
    CModule::IncludeModule('catalog');
    CModule::IncludeModule('iblock');
    
    $productId = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    $storeName = $_POST['store_name'] ?? '';
    $storeId = intval($_POST['store_id'] ?? 0);
    $priceType = $_POST['price_type'] ?? '';
    
    // Проверяем CSRF-токен
    if (!check_bitrix_sessid()) {
        echo json_encode(['status' => 'error', 'message' => 'Ошибка безопасности']);
        die();
    }
    
    // Проверяем существование товара
    $arProduct = CIBlockElement::GetByID($productId)->Fetch();
    if (!$arProduct) {
        echo json_encode(['status' => 'error', 'message' => 'Товар не найден']);
        die();
    }
    
    // Подготавливаем свойства для корзины
    $arProps = [];
    
    if (!empty($storeName)) {
        $arProps[] = [
            'NAME' => 'Склад',
            'CODE' => 'STORE',
            'VALUE' => $storeName
        ];
    }
    
    if ($storeId > 0) {
        $arProps[] = [
            'NAME' => 'ID склада',
            'CODE' => 'STORE_ID', 
            'VALUE' => $storeId
        ];
    }
    
    if (!empty($priceType)) {
        $arProps[] = [
            'NAME' => 'Тип цены',
            'CODE' => 'PRICE_TYPE',
            'VALUE' => $priceType
        ];
    }
    
    // Добавляем товар в корзину
    $result = Add2BasketByProductID(
        $productId,
        $quantity,
        [
            'LID' => SITE_ID,
            'PROPS' => $arProps
        ]
    );
    
    if ($result) {
        echo json_encode(['status' => 'success', 'message' => 'Товар добавлен в корзину']);
    } else {
        // Получаем последнюю ошибку
        global $APPLICATION;
        $error = $APPLICATION->GetException();
        $errorMessage = $error ? $error->GetString() : 'Неизвестная ошибка';
        
        echo json_encode(['status' => 'error', 'message' => $errorMessage]);
    }
    
    die();
}

echo json_encode(['status' => 'error', 'message' => 'Неизвестное действие']);
?>