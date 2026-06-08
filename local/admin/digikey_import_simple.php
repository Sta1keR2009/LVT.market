<?php
// /local/admin/digikey_import_simple.php

// Проверяем наличие токенов
$tokenFile = $_SERVER['DOCUMENT_ROOT'] . '/upload/digikey_tokens.json';
if (!file_exists($tokenFile)) {
    echo '<div style="color: red; padding: 10px; background: #fff0f0; border: 1px solid red; margin: 10px 0;">';
    echo '<strong>❌ Ошибка:</strong> Файл с токенами не найден. Сначала выполните авторизацию.';
    echo '<p><a href="/local/admin/digikey_auth.php">➡ Выполнить авторизацию</a></p>';
    echo '</div>';
    echo '</div>';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
    exit;
}

CModule::IncludeModule('catalog');
CModule::IncludeModule('iblock');

$APPLICATION->SetTitle('Импорт товаров из Digi-Key в инфоблок 40');

/**
 * Простой класс для работы с Digi-Key API
 */
class SimpleDigikeyAPI
{
    private $clientId = 'uEodk824rK2UOfxvMynvIXnPlPMmlz9He6eDh4oQANnGUivF';
    private $clientSecret = 'VVGFDAx4VCgOGTDBXgAA7LM0PNSUnustYao96CWqJSbDH1AUh5DFSU3vqtPdqKtr';
    private $proxy = 'socks5://LM8Efc73:7cvVyzHi@193.176.21.211:64541';
    private $tokenFile;

    public function __construct()
    {
        $this->tokenFile = $_SERVER['DOCUMENT_ROOT'] . '/upload/digikey_tokens.json';
    }

    /**
     * Получает access token
     */
    private function getAccessToken()
    {
        if (!file_exists($this->tokenFile)) {
            throw new Exception('Токены не найдены. Сначала выполните авторизацию.');
        }
        
        $tokenData = json_decode(file_get_contents($this->tokenFile), true);
        
        // Проверяем, не истек ли access token
        if (time() >= $tokenData['expires_at']) {
            return $this->refreshToken($tokenData['refresh_token']);
        }
        
        return $tokenData['access_token'];
    }

    /**
     * Обновляет токен
     */
    private function refreshToken($refreshToken)
    {
        $tokenUrl = 'https://api.digikey.com/v1/oauth2/token';
        $postData = http_build_query([
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken
        ]);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $tokenUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_PROXY => $this->proxy,
            CURLOPT_PROXYTYPE => CURLPROXY_SOCKS5,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $newTokenData = json_decode($response, true);
            $newTokenData['expires_at'] = time() + 1800;
            $newTokenData['refresh_expires_at'] = time() + (90 * 24 * 60 * 60);
            
            file_put_contents($this->tokenFile, json_encode($newTokenData));
            return $newTokenData['access_token'];
        } else {
            throw new Exception('Ошибка обновления токена: ' . $response);
        }
    }

    /**
     * Выполняет запрос к API Digi-Key
     */
    private function makeApiRequest($url, $postData = null)
    {
        $accessToken = $this->getAccessToken();
        
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'X-DIGIKEY-Client-Id: ' . $this->clientId,
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_PROXY => $this->proxy,
            CURLOPT_PROXYTYPE => CURLPROXY_SOCKS5,
            CURLOPT_HTTPHEADER => $headers,
        ]);
        
        if ($postData) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('API Error: ' . $response);
        }
        
        return json_decode($response, true);
    }

    /**
     * Ищет товары по ключевым словам
     */
    public function searchProducts($keywords = '', $limit = 10)
    {
        $url = 'https://api.digikey.com/products/v4/search';
        
        $postData = [
            'Keywords' => $keywords ?: 'resistor capacitor ic',
            'RecordCount' => $limit,
            'Sort' => [
                'Option' => 'SortByQuantityAvailable',
                'Direction' => 'Descending'
            ],
            'RequestedQuantity' => 1
        ];
        
        return $this->makeApiRequest($url, $postData);
    }

    /**
     * Получает детальную информацию о продукте
     */
    public function getProductDetails($digiKeyPartNumber)
    {
        $url = 'https://api.digikey.com/products/v4/details/' . urlencode($digiKeyPartNumber);
        $postData = [
            'Includes' => [
                'DetailedDescription',
                'Parameters',
                'ProductPhotos',
                'Specifications'
            ]
        ];
        
        return $this->makeApiRequest($url, $postData);
    }
}

/**
 * Простой импортер для Битрикс
 */
class SimpleDigikeyImporter
{
    private $api;
    private $iblockId = 40;
    
    public function __construct()
    {
        $this->api = new SimpleDigikeyAPI();
    }
    
    /**
     * Импортирует товары в Битрикс
     */
    public function importProducts($limit = 10)
    {
        echo "<h3>🔍 Получаем товары из Digi-Key API...</h3>";
        
        // Получаем товары из API
        $searchResult = $this->api->searchProducts('', $limit);
        
        if (empty($searchResult['Products'])) {
            return ['success' => false, 'message' => 'Товары не найдены'];
        }
        
        echo "<p>Найдено товаров: " . count($searchResult['Products']) . "</p>";
        
        $imported = 0;
        $errors = [];
        
        foreach ($searchResult['Products'] as $index => $product) {
            echo "<h4>📦 Обрабатываем товар " . ($index + 1) . ": " . $product['ManufacturerPartNumber'] . "</h4>";
            
            try {
                // Получаем детальную информацию о товаре
                $details = $this->api->getProductDetails($product['DigiKeyPartNumber']);
                
                // Создаем товар в Битрикс
                $result = $this->createBitrixProduct($product, $details);
                
                if ($result['success']) {
                    $imported++;
                    echo "<p style='color: green;'>✅ Успешно импортирован: " . $product['ManufacturerPartNumber'] . 
                         " - " . $product['QuantityAvailable'] . " шт. - $" . $product['UnitPrice'] . "</p>";
                } else {
                    $errorMsg = $product['ManufacturerPartNumber'] . ': ' . $result['message'];
                    $errors[] = $errorMsg;
                    echo "<p style='color: red;'>❌ Ошибка: " . $errorMsg . "</p>";
                }
                
            } catch (Exception $e) {
                $errorMsg = $product['ManufacturerPartNumber'] . ': ' . $e->getMessage();
                $errors[] = $errorMsg;
                echo "<p style='color: red;'>❌ Ошибка API: " . $errorMsg . "</p>";
            }
            
            echo "<hr>";
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
        
        // Базовая структура товара
        $productData = [
            'IBLOCK_ID' => $this->iblockId,
            'NAME' => $productName,
            'XML_ID' => $product['DigiKeyPartNumber'],
            'CODE' => $this->generateCode($product['ManufacturerPartNumber']),
            'ACTIVE' => 'Y',
            'PREVIEW_TEXT' => $product['ProductDescription'] ?? '',
            'DETAIL_TEXT' => $this->prepareDescription($product, $details)
        ];
        
        // Добавляем свойства если они существуют
        $propertyValues = [];
        
        $propertiesMap = [
            'MANUFACTURER' => $product['Manufacturer']['Value'],
            'MANUFACTURER_PART_NUMBER' => $product['ManufacturerPartNumber'],
            'DIGIKEY_PART_NUMBER' => $product['DigiKeyPartNumber'],
            'QUANTITY_AVAILABLE' => $product['QuantityAvailable'],
            'UNIT_PRICE' => $product['UnitPrice'],
            'DATASHEET_URL' => $product['PrimaryDatasheet'] ?? '',
            'PRODUCT_DESCRIPTION' => $product['ProductDescription'] ?? '',
            'DIGIKEY_PRODUCT_URL' => $product['ProductUrl'] ?? ''
        ];
        
        foreach ($propertiesMap as $code => $value) {
            $prop = CIBlockProperty::GetList([], ['IBLOCK_ID' => $this->iblockId, 'CODE' => $code])->Fetch();
            if ($prop) {
                $propertyValues[$code] = $value;
            }
        }
        
        if (!empty($propertyValues)) {
            $productData['PROPERTY_VALUES'] = $propertyValues;
        }
        
        if ($productId = $el->Add($productData)) {
            // Добавляем в торговый каталог
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
        echo "<p>ℹ️ Товар уже существует, обновляем...</p>";
        
        // Обновляем количество в каталоге
        $this->updateCatalogQuantity($productId, $product['QuantityAvailable']);
        
        // Обновляем цену
        $this->updateProductPrice($productId, $product['UnitPrice']);
        
        return ['success' => true, 'product_id' => $productId, 'action' => 'updated'];
    }
    
    /**
     * Добавляет товар в торговый каталог
     */
    private function addToCatalog($productId, $product)
    {
        // Проверяем, есть ли уже запись в каталоге
        $catalogProduct = CCatalogProduct::GetByID($productId);
        
        if (!$catalogProduct) {
            $result = CCatalogProduct::Add([
                'ID' => $productId,
                'QUANTITY' => $product['QuantityAvailable'],
                'AVAILABLE' => $product['QuantityAvailable'] > 0 ? 'Y' : 'N'
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
        
        $links = [];
        if (!empty($product['PrimaryDatasheet'])) {
            $links[] = '<a href="' . $product['PrimaryDatasheet'] . '" target="_blank">Datasheet</a>';
        }
        if (!empty($product['ProductUrl'])) {
            $links[] = '<a href="' . $product['ProductUrl'] . '" target="_blank">Страница на Digi-Key</a>';
        }
        
        if (!empty($links)) {
            $description .= '<p><strong>Ссылки:</strong> ' . implode(' | ', $links) . '</p>';
        }
        
        return $description;
    }
}

// Основная логика страницы
echo '<div style="padding: 20px;">';
echo '<h1>Импорт товаров из Digi-Key</h1>';
echo '<p><strong>Инфоблок:</strong> 40</p>';

// Проверяем наличие токенов
$tokenFile = $_SERVER['DOCUMENT_ROOT'] . '/upload/digikey_tokens.json';
if (!file_exists($tokenFile)) {
    echo '<div style="color: red; padding: 10px; background: #fff0f0; border: 1px solid red; margin: 10px 0;">';
    echo '<strong>❌ Ошибка:</strong> Файл с токенами не найден. Сначала выполните авторизацию.';
    echo '<p><a href="/local/digikey_process_code.php">➡ Выполнить авторизацию</a></p>';
    echo '</div>';
    echo '</div>';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
    exit;
}

if ($_POST['import'] || $_GET['auto']) {
    try {
        $limit = $_POST['limit'] ?? 5; // Уменьшим лимит для теста
        
        echo '<div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0;">';
        echo '<h3>🚀 Запуск импорта...</h3>';
        
        $importer = new SimpleDigikeyImporter();
        $result = $importer->importProducts($limit);
        
        echo '</div>';
        
        if ($result['success']) {
            echo '<div style="color: green; padding: 10px; background: #f0fff0; border: 1px solid green; margin: 10px 0;">';
            echo '<strong>✅ ИМПОРТ ЗАВЕРШЕН!</strong><br>';
            echo 'Успешно импортировано: ' . $result['imported'] . ' из ' . $result['total'] . ' товаров';
            echo '</div>';
            
            if (!empty($result['errors'])) {
                echo '<div style="color: orange; padding: 10px; background: #fffaf0; border: 1px solid orange; margin-top: 10px;">';
                echo '<strong>⚠️ Ошибки:</strong><br>';
                foreach ($result['errors'] as $error) {
                    echo '• ' . $error . '<br>';
                }
                echo '</div>';
            }
        } else {
            echo '<div style="color: red; padding: 10px; background: #fff0f0; border: 1px solid red;">';
            echo '<strong>❌ ОШИБКА:</strong> ' . $result['message'];
            echo '</div>';
        }
        
    } catch (Exception $e) {
        echo '<div style="color: red; padding: 10px; background: #fff0f0; border: 1px solid red;">';
        echo '<strong>❌ КРИТИЧЕСКАЯ ОШИБКА:</strong> ' . $e->getMessage();
        echo '</div>';
    }
}

echo '<form method="POST" style="margin: 20px 0; padding: 20px; background: #f5f5f5; border-radius: 5px;">';
echo '<label>Количество товаров для импорта: </label>';
echo '<input type="number" name="limit" value="5" min="1" max="20" style="margin: 0 10px;">';
echo '<input type="submit" name="import" value="🚀 Запустить импорт" class="adm-btn-green" style="padding: 10px 20px;">';
echo '</form>';

echo '<p><a href="?auto=1">🔄 Быстрый импорт (5 товаров)</a></p>';

echo '<hr>';
echo '<p><strong>Проверка:</strong></p>';
echo '<ul>';
echo '<li><a href="/local/digikey_process_code.php" target="_blank">Проверить авторизацию</a></li>';
echo '<li><a href="/bitrix/admin/iblock_element_admin.php?IBLOCK_ID=40&type=catalog" target="_blank">Посмотреть товары в инфоблоке 40</a></li>';
echo '</ul>';

echo '</div>';

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';