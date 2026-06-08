<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

header('Content-Type: application/json; charset=utf-8');

$response = ['status' => 'error', 'message' => 'Неизвестное действие'];

if ($_POST['action'] == 'update_stores_in_cart') {
    
    CModule::IncludeModule('sale');
    CModule::IncludeModule('catalog');
    CModule::IncludeModule('iblock');
    
    $productId = intval($_POST['product_id']);
    $storeQuantities = json_decode($_POST['store_quantities'] ?? '{}', true);
    
    // Проверяем CSRF
    if (!check_bitrix_sessid()) {
        $response = ['status' => 'error', 'message' => 'Ошибка безопасности'];
        echo json_encode($response);
        exit();
    }
    
    if ($productId <= 0) {
        $response = ['status' => 'error', 'message' => 'Неверные параметры'];
        echo json_encode($response);
        exit();
    }
    
    try {
        // Получаем название товара и цену (обязательные поля для CSaleBasket::Add)
        $productName = '';
        $productIblockId = 0;
        $res = CIBlockElement::GetList([], ['ID' => $productId], false, false, ['ID', 'NAME', 'IBLOCK_ID']);
        if ($el = $res->Fetch()) {
            $productName = $el['NAME'];
            $productIblockId = (int)$el['IBLOCK_ID'];
        }
        if (empty($productName)) {
            $productName = 'Товар #' . $productId;
        }
        
        // Получаем корзину текущего пользователя
        $fuserId = CSaleBasket::GetBasketUserID();
        
        // 1. Удаляем все старые записи этого товара из корзины
        $basketItems = CSaleBasket::GetList(
            ['ID' => 'DESC'],
            [
                'FUSER_ID' => $fuserId,
                'LID' => SITE_ID,
                'ORDER_ID' => 'NULL',
                'PRODUCT_ID' => $productId
            ],
            false,
            false,
            ['ID']
        );
        
        while ($item = $basketItems->Fetch()) {
            CSaleBasket::Delete($item['ID']);
        }
        
        $addedQuantities = [];
        $successCount = 0;
        
        // 2. Добавляем новые записи только для складов с количеством > 0
        foreach ($storeQuantities as $storeId => $quantity) {
            $quantity = intval($quantity);
            $storeId = intval($storeId);
            
            if ($quantity <= 0) continue;
            
            // Получаем информацию о складе
            $store = \Bitrix\Catalog\StoreTable::getById($storeId)->fetch();
            if (!$store) continue;
            
            // Проверяем максимальное количество товара
            $maxQuantity = 0;
            $dbRes = CCatalogProduct::GetList(
                array(), 
                array('ID' => $productId), 
                false, 
                false, 
                array('ID', 'QUANTITY', 'QUANTITY_TRACE', 'CAN_BUY_ZERO')
            );
            
            if ($arProductCatalog = $dbRes->Fetch()) {
                if ($arProductCatalog['QUANTITY_TRACE'] == 'Y' && $arProductCatalog['CAN_BUY_ZERO'] == 'N') {
                    $maxQuantity = $arProductCatalog['QUANTITY'];
                }
            }
            
            // Проверяем количество
            if ($maxQuantity > 0 && $quantity > $maxQuantity) {
                $quantity = $maxQuantity;
            }
            
            // Получаем цену для данного количества (расширенные цены от N штук)
            $price = 0;
            $currency = 'RUB';
            $priceData = CCatalogProduct::GetOptimalPrice($productId, $quantity, [], 'N', [], SITE_ID);
            if (!empty($priceData)) {
                if (!empty($priceData['RESULT_PRICE']['DISCOUNT_PRICE'])) {
                    $price = floatval($priceData['RESULT_PRICE']['DISCOUNT_PRICE']);
                    $currency = $priceData['RESULT_PRICE']['CURRENCY'] ?? $currency;
                } elseif (!empty($priceData['RESULT_PRICE']['BASE_PRICE'])) {
                    $price = floatval($priceData['RESULT_PRICE']['BASE_PRICE']);
                    $currency = $priceData['RESULT_PRICE']['CURRENCY'] ?? $currency;
                } elseif (!empty($priceData['PRICE']['PRICE'])) {
                    $price = floatval($priceData['PRICE']['PRICE']);
                    $currency = $priceData['PRICE']['CURRENCY'] ?? $currency;
                }
            }
            if ($price <= 0) {
                // Fallback: берём цену по количеству из PriceTable (расширенные цены)
                $prices = \Bitrix\Catalog\PriceTable::getList([
                    'filter' => [
                        '=PRODUCT_ID' => $productId,
                        '<=QUANTITY_FROM' => $quantity
                    ],
                    'order' => ['QUANTITY_FROM' => 'DESC'],
                    'limit' => 1,
                    'select' => ['PRICE', 'CURRENCY']
                ])->fetch();
                if ($prices) {
                    $price = floatval($prices['PRICE']);
                    $currency = $prices['CURRENCY'] ?? 'RUB';
                }
            }
            if ($price <= 0) {
                // Последняя попытка: любая цена товара
                $prices = \Bitrix\Catalog\PriceTable::getList([
                    'filter' => ['=PRODUCT_ID' => $productId],
                    'order' => ['QUANTITY_FROM' => 'ASC'],
                    'limit' => 1,
                    'select' => ['PRICE', 'CURRENCY']
                ])->fetch();
                if ($prices) {
                    $price = floatval($prices['PRICE']);
                    $currency = $prices['CURRENCY'] ?? 'RUB';
                }
            }
            if (empty($currency)) {
                $currency = 'RUB';
            }
            if ($price <= 0) {
                error_log('cart_store: Could not get price for product ' . $productId . ', quantity ' . $quantity);
                continue;
            }
            
            // Формируем свойства для товара
            $arProps = [];
            if ($storeId > 0) {
                $arProps[] = [
                    'NAME' => 'ID склада', 
                    'CODE' => 'STORE_ID', 
                    'VALUE' => (string)$storeId
                ];
            }
            if (!empty($store['TITLE'])) {
                $arProps[] = [
                    'NAME' => 'Склад', 
                    'CODE' => 'STORE_NAME', 
                    'VALUE' => $store['TITLE']
                ];
            }
            
            // Добавляем через стандартную функцию Битрикс (корректная работа с каталогом и сессией)
            $basketId = Add2BasketByProductID($productId, $quantity, [], $arProps);
            
            if ($basketId) {
                $addedQuantities[$storeId] = $quantity;
                $successCount++;
            } else {
                $ex = $GLOBALS['APPLICATION']->GetException();
                $errMsg = $ex ? $ex->GetString() : 'Не удалось добавить товар в корзину';
                error_log('cart_store: Add2BasketByProductID failed for product ' . $productId . ', store ' . $storeId . ': ' . $errMsg);
            }
        }
        
        $response = [
            'status' => 'success', 
            'message' => $successCount > 0 ? 'Корзина обновлена' : 'Все товары удалены из корзины',
            'updated_quantities' => $addedQuantities
        ];
        
    } catch (Exception $e) {
        $response = ['status' => 'error', 'message' => 'Ошибка сервера: ' . $e->getMessage()];
    }
    
    echo json_encode($response);
    exit();
}

elseif ($_POST['action'] == 'get_cart_state') {
    
    CModule::IncludeModule('sale');
    CModule::IncludeModule('catalog');
    
    $productId = intval($_POST['product_id']);
    
    // Проверяем CSRF
    if (!check_bitrix_sessid()) {
        $response = ['status' => 'error', 'message' => 'Ошибка безопасности'];
        echo json_encode($response);
        exit();
    }
    
    try {
        // Получаем корзину текущего пользователя
        $fuserId = CSaleBasket::GetBasketUserID();
        
        $storeQuantities = [];
        
        // Ищем товары этого продукта в корзине
        $basketItems = CSaleBasket::GetList(
            ['ID' => 'DESC'],
            [
                'FUSER_ID' => $fuserId,
                'LID' => SITE_ID,
                'ORDER_ID' => 'NULL',
                'PRODUCT_ID' => $productId
            ],
            false,
            false,
            ['ID', 'QUANTITY', 'PRODUCT_ID', 'NAME']
        );
        
        while ($item = $basketItems->Fetch()) {
            // Получаем свойства товара
            $dbProps = CSaleBasket::GetPropsList(
                ['SORT' => 'ASC'],
                ['BASKET_ID' => $item['ID']],
                false,
                false,
                ['CODE', 'VALUE']
            );
            
            $storeId = null;
            
            while ($prop = $dbProps->Fetch()) {
                if ($prop['CODE'] == 'STORE_ID') $storeId = $prop['VALUE'];
            }
            
            if ($storeId) {
                $storeId = intval($storeId);
                $storeQuantities[$storeId] = floatval($item['QUANTITY']);
            }
        }
        
        $response = [
            'status' => 'success', 
            'store_quantities' => $storeQuantities
        ];
        
    } catch (Exception $e) {
        $response = ['status' => 'error', 'message' => 'Ошибка сервера: ' . $e->getMessage()];
    }
    
    echo json_encode($response);
    exit();
}

elseif ($_POST['action'] == 'add_to_cart' || $_POST['action'] == 'update_cart_quantity') {
    
    CModule::IncludeModule('sale');
    CModule::IncludeModule('catalog');
    
    $productId = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    $storeName = trim($_POST['store_name'] ?? '');
    $storeId = intval($_POST['store_id'] ?? 0);
    
    // Проверяем CSRF
    if (!check_bitrix_sessid()) {
        $response = ['status' => 'error', 'message' => 'Ошибка безопасности'];
        echo json_encode($response);
        exit();
    }
    
    if ($productId <= 0 || $quantity <= 0) {
        $response = ['status' => 'error', 'message' => 'Неверные параметры'];
        echo json_encode($response);
        exit();
    }
    
    // Проверяем максимальное количество товара
    $maxQuantity = 0;
    $dbRes = CCatalogProduct::GetList(
        array(), 
        array('ID' => $productId), 
        false, 
        false, 
        array('ID', 'QUANTITY', 'QUANTITY_TRACE', 'CAN_BUY_ZERO')
    );
    
    if ($arProductCatalog = $dbRes->Fetch()) {
        if ($arProductCatalog['QUANTITY_TRACE'] == 'Y' && $arProductCatalog['CAN_BUY_ZERO'] == 'N') {
            $maxQuantity = $arProductCatalog['QUANTITY'];
        }
    }
    
    // Проверяем количество
    if ($maxQuantity > 0 && $quantity > $maxQuantity) {
        $response = ['status' => 'error', 'message' => 'Максимальное доступное количество: ' . $maxQuantity . ' шт.'];
        echo json_encode($response);
        exit();
    }
    
    try {
        // Получаем корзину текущего пользователя
        $fuserId = CSaleBasket::GetBasketUserID();
        
        // Ищем товар с таким же ID и складом в корзине
        $basketItems = CSaleBasket::GetList(
            ['ID' => 'DESC'],
            [
                'FUSER_ID' => $fuserId,
                'LID' => SITE_ID,
                'ORDER_ID' => 'NULL',
                'PRODUCT_ID' => $productId
            ],
            false,
            false,
            ['ID', 'QUANTITY', 'PRODUCT_ID', 'NAME']
        );
        
        $existingItemId = null;
        $existingQuantity = 0;
        
        while ($item = $basketItems->Fetch()) {
            // Получаем свойства товара
            $dbProps = CSaleBasket::GetPropsList(
                ['SORT' => 'ASC'],
                ['BASKET_ID' => $item['ID']],
                false,
                false,
                ['CODE', 'VALUE']
            );
            
            $itemStoreId = null;
            $itemStoreName = null;
            
            while ($prop = $dbProps->Fetch()) {
                if ($prop['CODE'] == 'STORE_ID') $itemStoreId = $prop['VALUE'];
                if ($prop['CODE'] == 'STORE_NAME') $itemStoreName = $prop['VALUE'];
            }
            
            // Сравниваем склады
            if ((string)$itemStoreId === (string)$storeId || $itemStoreName === $storeName) {
                $existingItemId = $item['ID'];
                $existingQuantity = $item['QUANTITY'];
                break;
            }
        }
        
        // Формируем свойства для товара - ПРОСТЫЕ КОДЫ
        $arProps = [];
        if ($storeId > 0) {
            $arProps[] = [
                'NAME' => 'ID склада', 
                'CODE' => 'STORE_ID', 
                'VALUE' => (string)$storeId
            ];
        }
        if (!empty($storeName)) {
            $arProps[] = [
                'NAME' => 'Склад', 
                'CODE' => 'STORE_NAME', 
                'VALUE' => $storeName
            ];
        }
        
        if ($_POST['action'] == 'add_to_cart') {
            // Если товар уже есть в корзине - обновляем количество
            if ($existingItemId) {
                $newQuantity = $existingQuantity + $quantity;
                
                // Проверяем максимальное количество при обновлении
                if ($maxQuantity > 0 && $newQuantity > $maxQuantity) {
                    $response = ['status' => 'error', 'message' => 'Максимальное доступное количество: ' . $maxQuantity . ' шт. У вас уже ' . $existingQuantity . ' шт. в корзине.'];
                    echo json_encode($response);
                    exit();
                }
                
                if ($newQuantity > 10000) $newQuantity = 10000;
                
                $updateResult = CSaleBasket::Update($existingItemId, [
                    'QUANTITY' => $newQuantity,
                    'CAN_BUY' => 'Y',
                    'DELAY' => 'N'
                ]);
                
                if ($updateResult) {
                    $response = ['status' => 'success', 'message' => 'Товар добавлен в корзину', 'quantity' => $newQuantity];
                } else {
                    $response = ['status' => 'error', 'message' => 'Не удалось обновить количество'];
                }
            } else {
                // Добавляем новый товар в корзину
                $basketFields = [
                    'PRODUCT_ID' => $productId,
                    'QUANTITY' => $quantity,
                    'LID' => SITE_ID,
                    'PROPS' => $arProps,
                    'MODULE' => 'catalog',
                    'PRODUCT_PROVIDER_CLASS' => 'CCatalogProductProvider',
                    'CAN_BUY' => 'Y',
                    'DELAY' => 'N'
                ];
                
                $basketId = CSaleBasket::Add($basketFields);
                
                if ($basketId) {
                    $response = ['status' => 'success', 'message' => 'Товар добавлен в корзину', 'quantity' => $quantity];
                } else {
                    $response = ['status' => 'error', 'message' => 'Не удалось добавить товар'];
                }
            }
        } 
        elseif ($_POST['action'] == 'update_cart_quantity') {
            // Обновление количества
            if ($existingItemId) {
                // Проверяем максимальное количество при обновлении
                if ($maxQuantity > 0 && $quantity > $maxQuantity) {
                    $response = ['status' => 'error', 'message' => 'Максимальное доступное количество: ' . $maxQuantity . ' шт.'];
                    echo json_encode($response);
                    exit();
                }
                
                if ($quantity > 10000) $quantity = 10000;
                
                $updateResult = CSaleBasket::Update($existingItemId, [
                    'QUANTITY' => $quantity,
                    'CAN_BUY' => 'Y'
                ]);
                
                if ($updateResult) {
                    $response = ['status' => 'success', 'message' => 'Количество обновлено', 'quantity' => $quantity];
                } else {
                    $response = ['status' => 'error', 'message' => 'Не удалось обновить количество'];
                }
            } else {
                $response = ['status' => 'error', 'message' => 'Товар не найден в корзине'];
            }
        }
        
    } catch (Exception $e) {
        $response = ['status' => 'error', 'message' => 'Ошибка сервера: ' . $e->getMessage()];
    }
    
    echo json_encode($response);
    exit();
}

echo json_encode($response);
exit();
?>