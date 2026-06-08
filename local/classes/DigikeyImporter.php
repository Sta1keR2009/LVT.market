<?php
// /local/classes/DigikeyImporter.php

class DigikeyImporter
{
    private $api;
    private $iblockId;
    
    public function __construct()
    {
        $this->api = new DigikeyAPI();
        $this->iblockId = 40; // Ваш инфоблок
    }
    
    /**
     * Импортирует товары в Битрикс
     */
    public function importProducts($limit = 10)
    {
        // Получаем товары из API
        $searchResult = $this->api->searchProducts('', $limit);
        
        if (empty($searchResult['Products'])) {
            return ['success' => false, 'message' => 'Товары не найдены'];
        }
        
        $imported = 0;
        $errors = [];
        
        foreach ($searchResult['Products'] as $product) {
            try {
                // Получаем детальную информацию о товаре
                $details = $this->api->getProductDetails($product['DigiKeyPartNumber']);
                
                // Создаем товар в Битрикс
                $result = $this->createBitrixProduct($product, $details);
                
                if ($result['success']) {
                    $imported++;
                    echo "<p>✅ Импортирован: " . $product['ManufacturerPartNumber'] . " - " . $product['QuantityAvailable'] . " шт. - $" . $product['UnitPrice'] . "</p>";
                } else {
                    $errorMsg = $product['ManufacturerPartNumber'] . ': ' . $result['message'];
                    $errors[] = $errorMsg;
                    echo "<p style='color: red;'>❌ " . $errorMsg . "</p>";
                }
                
            } catch (Exception $e) {
                $errorMsg = $product['ManufacturerPartNumber'] . ': ' . $e->getMessage();
                $errors[] = $errorMsg;
                echo "<p style='color: red;'>❌ " . $errorMsg . "</p>";
            }
        }
        
        return [
            'success' => true,
            'imported' => $imported,
            'total' => count($searchResult['Products']),
            'errors' => $errors
        ];
    }
    
    /**
     * Создает товар в Битрикс
     */
    private function createBitrixProduct($product, $details)
    {
        CModule::IncludeModule('catalog');
        CModule::IncludeModule('iblock');
        
        // Проверяем, существует ли уже товар
        $existingProduct = CIBlockElement::GetList(
            [],
            [
                'IBLOCK_ID' => $this->iblockId,
                '=XML_ID' => $product['DigiKeyPartNumber']
            ],
            false,
            false,
            ['ID']
        )->Fetch();
        
        if ($existingProduct) {
            return $this->updateBitrixProduct($existingProduct['ID'], $product, $details);
        }
        
        // Создаем новый товар
        $el = new CIBlockElement;
        
        $productName = $product['ManufacturerPartNumber'] . ' - ' . $product['Manufacturer']['Value'];
        if (strlen($productName) > 255) {
            $productName = substr($productName, 0, 252) . '...';
        }
        
        $productData = [
            'IBLOCK_ID' => $this->iblockId,
            'NAME' => $productName,
            'XML_ID' => $product['DigiKeyPartNumber'],
            'CODE' => $this->generateCode($product['ManufacturerPartNumber']),
            'ACTIVE' => 'Y',
            'PREVIEW_TEXT' => $product['ProductDescription'] ?? '',
            'DETAIL_TEXT' => $this->prepareDescription($product, $details),
            'PROPERTY_VALUES' => [
                'MANUFACTURER' => $product['Manufacturer']['Value'],
                'MANUFACTURER_PART_NUMBER' => $product['ManufacturerPartNumber'],
                'DIGIKEY_PART_NUMBER' => $product['DigiKeyPartNumber'],
                'QUANTITY_AVAILABLE' => $product['QuantityAvailable'],
                'UNIT_PRICE' => $product['UnitPrice'],
                'DATASHEET_URL' => $product['PrimaryDatasheet'] ?? '',
                'PRODUCT_DESCRIPTION' => $product['ProductDescription'] ?? '',
                'DIGIKEY_PRODUCT_URL' => $product['ProductUrl'] ?? ''
            ]
        ];
        
        if ($productId = $el->Add($productData)) {
            // Добавляем в торговый каталог и цену
            $this->addToCatalog($productId, $product);
            
            return ['success' => true, 'product_id' => $productId];
        } else {
            return ['success' => false, 'message' => $el->LAST_ERROR];
        }
    }
    
    /**
     * Обновляет существующий товар
     */
    private function updateBitrixProduct($productId, $product, $details)
    {
        // Обновляем количество
        $this->updateCatalogQuantity($productId, $product['QuantityAvailable']);
        
        // Обновляем цену
        $this->updateProductPrice($productId, $product['UnitPrice']);
        
        // Обновляем свойства
        CIBlockElement::SetPropertyValuesEx($productId, $this->iblockId, [
            'QUANTITY_AVAILABLE' => $product['QuantityAvailable'],
            'UNIT_PRICE' => $product['UnitPrice']
        ]);
        
        return ['success' => true, 'product_id' => $productId, 'action' => 'updated'];
    }
    
    /**
     * Добавляет товар в торговый каталог
     */
    private function addToCatalog($productId, $product)
    {
        CModule::IncludeModule('catalog');
        
        // Проверяем, есть ли уже запись в каталоге
        $catalogProduct = CCatalogProduct::GetByID($productId);
        
        if (!$catalogProduct) {
            $result = CCatalogProduct::Add([
                'ID' => $productId,
                'QUANTITY' => $product['QuantityAvailable'],
                'AVAILABLE' => $product['QuantityAvailable'] > 0 ? 'Y' : 'N',
                'CAN_BUY_ZERO' => 'Y',
                'NEGATIVE_AMOUNT_TRACE' => 'Y'
            ]);
            
            if ($result) {
                $this->updateProductPrice($productId, $product['UnitPrice']);
            }
        }
    }
    
    /**
     * Обновляет количество в каталоге
     */
    private function updateCatalogQuantity($productId, $quantity)
    {
        CModule::IncludeModule('catalog');
        
        CCatalogProduct::Update($productId, [
            'QUANTITY' => $quantity,
            'AVAILABLE' => $quantity > 0 ? 'Y' : 'N'
        ]);
    }
    
    /**
     * Обновляет цену товара
     */
    private function updateProductPrice($productId, $price)
    {
        CModule::IncludeModule('catalog');
        
        // ID типа цены (обычно 1 - базовая цена)
        $priceTypeId = 1;
        
        // Ищем существующую цену
        $dbPrice = CPrice::GetList([], [
            'PRODUCT_ID' => $productId,
            'CATALOG_GROUP_ID' => $priceTypeId
        ]);
        
        $priceData = [
            'PRODUCT_ID' => $productId,
            'CATALOG_GROUP_ID' => $priceTypeId,
            'PRICE' => $price,
            'CURRENCY' => 'USD'
        ];
        
        if ($existingPrice = $dbPrice->Fetch()) {
            CPrice::Update($existingPrice['ID'], $priceData);
        } else {
            CPrice::Add($priceData);
        }
    }
    
    /**
     * Генерирует символьный код
     */
    private function generateCode($manufacturerPartNumber)
    {
        $code = CUtil::translit($manufacturerPartNumber, 'ru', [
            'replace_space' => '-',
            'replace_other' => '-'
        ]);
        
        // Ограничиваем длину кода
        if (strlen($code) > 50) {
            $code = substr($code, 0, 47) . '-' . rand(100, 999);
        }
        
        return $code;
    }
    
    /**
     * Подготавливает описание товара
     */
    private function prepareDescription($product, $details)
    {
        $description = '';
        
        if (!empty($product['ProductDescription'])) {
            $description .= '<p><strong>Описание:</strong> ' . $product['ProductDescription'] . '</p>';
        }
        
        if (!empty($details['Parameters'])) {
            $description .= '<h3>Характеристики:</h3><ul>';
            foreach ($details['Parameters'] as $param) {
                $description .= '<li><strong>' . $param['Parameter'] . ':</strong> ' . $param['Value'] . '</li>';
            }
            $description .= '</ul>';
        }
        
        if (!empty($product['PrimaryDatasheet'])) {
            $description .= '<p><a href="' . $product['PrimaryDatasheet'] . '" target="_blank">📄 Datasheet</a></p>';
        }
        
        if (!empty($product['ProductUrl'])) {
            $description .= '<p><a href="' . $product['ProductUrl'] . '" target="_blank">🔗 Страница на Digi-Key</a></p>';
        }
        
        return $description;
    }
}