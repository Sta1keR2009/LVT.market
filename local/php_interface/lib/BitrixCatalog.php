<?php
class BitrixCatalog {
    private $iblockId = 40; // ID вашего инфоблока
    
    public function addOrUpdateProduct($productData) {
        if (!CModule::IncludeModule("iblock")) {
            throw new Exception("Модуль iblock не подключен");
        }
        
        // Ищем существующий товар по MPN
        $existingElement = $this->findProductByMPN($productData['MPN']);
        
        $elementFields = [
            "IBLOCK_ID" => $this->iblockId,
            "NAME" => $productData['MANUFACTURER'] . ' ' . $productData['MPN'],
            "CODE" => $this->generateCode($productData['MPN']),
            "ACTIVE" => "Y",
            "PREVIEW_TEXT" => $productData['DESCRIPTION'],
            "DETAIL_TEXT" => $this->generateDetailText($productData),
            "XML_ID" => "DIGIKEY_" . $productData['DIGIKEY_PN'],
            "SORT" => 500
        ];
        
        if ($existingElement) {
            // Обновляем существующий
            $elementId = $existingElement['ID'];
            $result = CIBlockElement::Update($elementId, $elementFields);
            echo "🔄 Обновлен товар ID: $elementId<br>";
        } else {
            // Создаем новый
            $result = CIBlockElement::Add($elementFields);
            if ($result) {
                $elementId = $result;
                echo "✅ Создан товар ID: $elementId<br>";
            } else {
                global $APPLICATION;
                throw new Exception("Ошибка создания товара: " . $APPLICATION->GetException());
            }
        }
        
        if ($elementId) {
            $this->setProductProperties($elementId, $productData);
            $this->updateStock($elementId, $productData['STOCK']);
            return $elementId;
        }
        
        return false;
    }
    
    private function findProductByMPN($mpn) {
        $res = CIBlockElement::GetList(
            [],
            [
                "IBLOCK_ID" => $this->iblockId,
                "=PROPERTY_MPN" => $mpn
            ],
            false,
            false,
            ["ID", "NAME", "XML_ID"]
        );
        
        return $res->Fetch();
    }
    
    private function setProductProperties($elementId, $productData) {
        $properties = [
            "MPN" => $productData['MPN'],
            "MANUFACTURER" => $productData['MANUFACTURER'],
            "DIGIKEY_PN" => $productData['DIGIKEY_PN'],
            "DATASHEET_URL" => $productData['DATASHEET_URL'],
            "CATEGORY" => $productData['CATEGORY'],
            "SUBCATEGORY" => $productData['SUBCATEGORY'],
            "MIN_ORDER_QTY" => $productData['MIN_ORDER_QTY'],
            "LEAD_TIME" => $productData['LEAD_TIME'],
            "PACKAGING" => $productData['PACKAGING'],
        ];
        
        // Сериализуем массивы
        if (!empty($productData['PRICE_TIERS'])) {
            $properties["PRICE_TIERS"] = serialize($productData['PRICE_TIERS']);
        }
        
        if (!empty($productData['PARAMETERS'])) {
            $properties["PARAMETERS"] = serialize($productData['PARAMETERS']);
        }
        
        CIBlockElement::SetPropertyValuesEx($elementId, $this->iblockId, $properties);
    }
    
    private function updateStock($elementId, $stock) {
        if (!CModule::IncludeModule("catalog")) {
            return; // Модуль catalog не обязателен для этого теста
        }
        
        // Обновляем остатки на складе Digi-Key (ID: 4)
        CCatalogProduct::SetProductStores($elementId, [
            [
                "STORE_ID" => 4, // ID склада Digi-Key
                "AMOUNT" => $stock,
                "QUANTITY_RESERVED" => 0
            ]
        ]);
    }
    
    private function generateCode($mpn) {
        return CUtil::translit($mpn, "ru", [
            "replace_space" => "-",
            "replace_other" => "-"
        ]);
    }
    
    private function generateDetailText($productData) {
        $html = "<h3>Характеристики</h3>";
        
        if (!empty($productData['PARAMETERS'])) {
            $html .= "<ul>";
            foreach ($productData['PARAMETERS'] as $param) {
                $value = $param['VALUE'];
                if (!empty($param['UNIT'])) {
                    $value .= " " . $param['UNIT'];
                }
                $html .= "<li><strong>{$param['NAME']}:</strong> {$value}</li>";
            }
            $html .= "</ul>";
        }
        
        if (!empty($productData['DATASHEET_URL'])) {
            $html .= "<p><a href='{$productData['DATASHEET_URL']}' target='_blank'>📄 Скачать даташит</a></p>";
        }
        
        if (!empty($productData['PRICE_TIERS'])) {
            $html .= "<h4>Цены:</h4><ul>";
            foreach ($productData['PRICE_TIERS'] as $tier) {
                $html .= "<li>от {$tier['QUANTITY']} шт. - \${$tier['PRICE']}</li>";
            }
            $html .= "</ul>";
        }
        
        return $html;
    }
}