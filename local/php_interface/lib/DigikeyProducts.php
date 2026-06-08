<?php
class DigikeyProducts {
    private $auth;
    
    public function __construct() {
        $this->auth = new DigikeyAuth();
    }
    
    public function searchProducts($keywords, $limit = 10) {
        $token = $this->auth->getAccessToken();
        
        $url = DIGIKEY_API_URL . '/products/v4/search';
        $queryParams = http_build_query([
            'keywords' => $keywords,
            'recordCount' => $limit,
            'recordStartPosition' => 0,
            'filters' => 'InStock',
            'sort' => 'Relevance'
        ]);
        
        $fullUrl = $url . '?' . $queryParams;
        
        $headers = [
            'Authorization: Bearer ' . $token,
            'X-DIGIKEY-Locale-Site: RU',
            'X-DIGIKEY-Locale-Language: ru',
            'X-DIGIKEY-Locale-Currency: USD',
            'X-DIGIKEY-Customer-Id: 0',
            'User-Agent: lvt.market/1.0',
            'Accept: application/json'
        ];
        
        $response = $this->makeCurlRequest($fullUrl, $headers);
        
        return $response ? json_decode($response, true) : null;
    }
    
    public function getProductDetails($digiKeyPartNumber) {
        $token = $this->auth->getAccessToken();
        
        $url = DIGIKEY_API_URL . "/products/v4/productdetails/{$digiKeyPartNumber}";
        
        $headers = [
            'Authorization: Bearer ' . $token,
            'X-DIGIKEY-Locale-Site: RU',
            'X-DIGIKEY-Locale-Language: ru',
            'X-DIGIKEY-Locale-Currency: USD',
            'X-DIGIKEY-Customer-Id: 0',
            'User-Agent: lvt.market/1.0',
            'Accept: application/json'
        ];
        
        $response = $this->makeCurlRequest($url, $headers);
        
        return $response ? json_decode($response, true) : null;
    }
    
    private function makeCurlRequest($url, $headers) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_PROXY => PROXY_SOCKS5,
            CURLOPT_PROXYTYPE => CURLPROXY_SOCKS5,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        $info = curl_getinfo($ch);
        
        curl_close($ch);
        
        // Логируем запрос
        error_log("Digi-Key API: $url - HTTP: $httpCode, Time: " . ($info['total_time'] ?? 0));
        
        if ($httpCode === 200) {
            return $response;
        } else {
            error_log("Digi-Key API Error: $error (HTTP: $httpCode)");
            return false;
        }
    }
    
    public function parseProductData($apiData) {
        // Определяем структуру ответа (search vs productdetails)
        if (isset($apiData['Products'])) {
            // Это ответ от search
            $product = $apiData['Products'][0] ?? [];
        } else {
            // Это ответ от productdetails
            $product = $apiData;
        }
        
        if (empty($product)) {
            return null;
        }
        
        $data = [
            'DIGIKEY_PN' => $product['DigiKeyPartNumber'] ?? '',
            'MPN' => $product['ManufacturerPartNumber'] ?? '',
            'MANUFACTURER' => $product['Manufacturer']['Value'] ?? '',
            'DESCRIPTION' => $product['ProductDescription'] ?? $product['DetailedDescription'] ?? '',
            'DATASHEET_URL' => $product['PrimaryDatasheet'] ?? '',
            'IMAGE_URL' => $product['PrimaryPhoto'] ?? '',
            'CATEGORY' => $product['Family']['Value'] ?? '',
            'SUBCATEGORY' => $product['Category']['Value'] ?? '',
            
            // Остатки и цены
            'STOCK' => $product['QuantityAvailable'] ?? 0,
            'MIN_ORDER_QTY' => $product['MinimumOrderQuantity'] ?? 1,
            'CURRENCY' => 'USD',
            'LEAD_TIME' => $this->parseLeadTime($product['LeadTime'] ?? ''),
            
            // Цены по объемам
            'PRICE_TIERS' => $this->parsePriceTiers($product['StandardPricing'] ?? []),
            
            // Дополнительные параметры
            'PARAMETERS' => $this->parseParameters($product['Parameters'] ?? []),
            
            // Упаковка
            'PACKAGING' => $product['Packaging']['Value'] ?? '',
            'UNIT_WEIGHT' => $product['UnitWeight'] ?? 0,
        ];
        
        return $data;
    }
    
    private function parseLeadTime($leadTime) {
        if (preg_match('/(\d+)\s*-\s*(\d+)/', $leadTime, $matches)) {
            return $matches[1] . '-' . $matches[2] . ' дней';
        }
        return $leadTime;
    }
    
    private function parsePriceTiers($pricing) {
        $tiers = [];
        if (is_array($pricing)) {
            foreach ($pricing as $tier) {
                $tiers[] = [
                    'QUANTITY' => $tier['BreakQuantity'] ?? 1,
                    'PRICE' => $tier['UnitPrice'] ?? 0,
                    'CURRENCY' => 'USD'
                ];
            }
        }
        return $tiers;
    }
    
    private function parseParameters($parameters) {
        $params = [];
        if (is_array($parameters)) {
            foreach ($parameters as $param) {
                $params[] = [
                    'NAME' => $param['Parameter'] ?? '',
                    'VALUE' => $param['Value'] ?? '',
                    'UNIT' => $param['Unit'] ?? ''
                ];
            }
        }
        return $params;
    }
}