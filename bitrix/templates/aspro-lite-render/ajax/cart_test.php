<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if ($_POST['action'] == 'add') {
    
    // Включаем необходимые модули
    CModule::IncludeModule('sale');
    CModule::IncludeModule('catalog');
    CModule::IncludeModule('iblock');
    
    $productId = intval($_POST['id']);
    $quantity = intval($_POST['quantity']);
    $storeName = $_POST['PROPS']['STORE'] ?? '';
    $storeId = intval($_POST['PROPS']['STORE_ID'] ?? 0);
    $priceType = $_POST['PROPS']['PRICE_TYPE'] ?? '';
    
    // Проверяем существование товара
    $arProduct = CIBlockElement::GetByID($productId)->Fetch();
    if (!$arProduct) {
        echo json_encode(['STATUS' => 'ERROR', 'MESSAGE' => 'Товар не найден']);
        die();
    }
    
    // Подготавливаем свойства для корзины в правильном формате
    $arProps = [];
    
    if (!empty($storeName)) {
        $arProps[] = [
            'NAME' => 'Склад',
            'CODE' => 'STORE',
            'VALUE' => $storeName,
            'SORT' => 100
        ];
    }
    
    if ($storeId > 0) {
        $arProps[] = [
            'NAME' => 'ID склада',
            'CODE' => 'STORE_ID', 
            'VALUE' => $storeId,
            'SORT' => 200
        ];
    }
    
    if (!empty($priceType)) {
        $arProps[] = [
            'NAME' => 'Тип цены',
            'CODE' => 'PRICE_TYPE',
            'VALUE' => $priceType,
            'SORT' => 300
        ];
    }
    
    // Добавляем товар в корзину
    try {
        $result = Add2BasketByProductID(
            $productId,
            $quantity,
            [
                'LID' => SITE_ID,
                'PROPS' => $arProps
            ],
            []
        );
        
        if ($result) {
            echo json_encode(['STATUS' => 'SUCCESS', 'MESSAGE' => 'Товар добавлен в корзину']);
        } else {
            // Получаем ошибку
            global $APPLICATION;
            $exception = $APPLICATION->GetException();
            $errorMessage = $exception ? $exception->GetString() : 'Неизвестная ошибка';
            echo json_encode(['STATUS' => 'ERROR', 'MESSAGE' => $errorMessage]);
        }
    } catch (Exception $e) {
        echo json_encode(['STATUS' => 'ERROR', 'MESSAGE' => $e->getMessage()]);
    }
    
    die();
}

echo json_encode(['STATUS' => 'ERROR', 'MESSAGE' => 'Неизвестное действие']);
?>