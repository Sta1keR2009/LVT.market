<?php
// /local/cron/digikey_sync.php

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

class DigiKeySync {
    private $clientId = 'uEodk824rK2UOfxvMynvIXnPlPMmlz9He6eDh4oQANnGUivF';
    private $clientSecret = 'VVGFDAx4VCgOGTDBXgAA7LM0PNSUnustYao96CWqJSbDH1AUh5DFSU3vqtPdqKtr';
    private $apiBase = 'https://api.digikey.com';
    private $proxy = 'socks5://LM8Efc73:7cvVyzHi@193.176.21.211:64541';
    private $tokenFile;
    private $logFile;
    
    public function __construct() {
        $this->tokenFile = $_SERVER['DOCUMENT_ROOT'] . '/upload/digikey_tokens.json';
        $this->logFile = $_SERVER['DOCUMENT_ROOT'] . '/upload/digikey_sync.log';
    }
    
    public function sync() {
        $this->log("🔄 Запуск синхронизации Digi-Key");
        
        try {
            $accessToken = $this->getValidAccessToken();
            $mpnList = $this->getMPNFromCatalog();
            $this->log("Найдено MPN: " . count($mpnList));
            
            $results = $this->syncProducts($accessToken, $mpnList);
            
            $successCount = count(array_filter($results, function($r) { return $r === 'success'; }));
            $this->log("✅ Синхронизация завершена: $successCount/" . count($mpnList));
            
        } catch (Exception $e) {
            $this->log("❌ Ошибка: " . $e->getMessage(), true);
        }
    }
    
    private function getValidAccessToken() {
        if (!file_exists($this->tokenFile)) {
            throw new Exception('Токены не найдены. Запустите: /local/digikey_auth_setup.php');
        }
        
        $tokenData = json_decode(file_get_contents($this->tokenFile), true);
        
        // Проверяем срок действия access token (30 минут)
        if (isset($tokenData['expires_at']) && $tokenData['expires_at'] > time()) {
            $this->log("Используется существующий токен");
            return $tokenData['access_token'];
        }
        
        // Проверяем срок действия refresh token (90 дней)
        if (isset($tokenData['refresh_expires_at']) && $tokenData['refresh_expires_at'] <= time()) {
            throw new Exception('Refresh token истек. Требуется повторная авторизация.');
        }
        
        // Обновляем токен
        if (isset($tokenData['refresh_token'])) {
            $this->log("Токен истек, обновляем...");
            return $this->refreshAccessToken($tokenData['refresh_token']);
        }
        
        throw new Exception('Токен истек и нет refresh token.');
    }
    
    private function refreshAccessToken($refreshToken) {
        $url = $this->apiBase . '/v1/oauth2/token';
        $postData = http_build_query([
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken
        ]);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
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
            $tokenData = json_decode($response, true);
            
            // Сохраняем с правильными сроками из FAQ
            $tokenData['expires_at'] = time() + 1800; // 30 минут
            $tokenData['refresh_expires_at'] = time() + (90 * 24 * 60 * 60); // 90 дней
            
            // Сохраняем старый refresh token если новый не пришел
            if (!isset($tokenData['refresh_token'])) {
                $tokenData['refresh_token'] = $refreshToken;
            }
            
            file_put_contents($this->tokenFile, json_encode($tokenData));
            $this->log("✅ Токен обновлен");
            
            return $tokenData['access_token'];
        }
        
        throw new Exception('Не удалось обновить токен. HTTP: ' . $httpCode);
    }
    
    private function getMPNFromCatalog() {
        $mpnList = [];
        
        $res = \CIBlockElement::GetList(
            [],
            ['IBLOCK_ID' => 40, '!PROPERTY_MPN' => false],
            false,
            false,
            ['ID', 'PROPERTY_MPN']
        );
        
        while ($element = $res->Fetch()) {
            if (!empty($element['PROPERTY_MPN_VALUE'])) {
                $mpnList[] = $element['PROPERTY_MPN_VALUE'];
            }
        }
        
        return array_slice(array_unique($mpnList), 0, 10);
    }
    
    private function syncProducts($accessToken, $mpnList) {
        $results = [];
        
        foreach ($mpnList as $index => $mpn) {
            $this->log("Обработка $mpn (" . ($index + 1) . "/" . count($mpnList) . ")");
            
            try {
                $productData = $this->searchProduct($accessToken, $mpn);
                
                if ($productData) {
                    $this->updateProductInBitrix($mpn, $productData);
                    $results[$mpn] = 'success';
                    $this->log("✅ $mpn - " . ($productData['QuantityAvailable'] ?? 0) . " шт.");
                } else {
                    $results[$mpn] = 'not_found';
                    $this->log("❌ $mpn - не найден");
                }
                
            } catch (Exception $e) {
                $results[$mpn] = 'error';
                $this->log("❌ $mpn - ошибка: " . $e->getMessage());
            }
            
            sleep(1);
        }
        
        return $results;
    }
    
    private function searchProduct($accessToken, $mpn) {
        $url = $this->apiBase . '/products/v4/search';
        $postData = json_encode([
            'Keywords' => $mpn,
            'RecordCount' => 1
        ]);
        
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'X-DIGIKEY-Client-Id: ' . $this->clientId,
            'X-DIGIKEY-Locale-Site: RU',
            'X-DIGIKEY-Locale-Language: ru', 
            'X-DIGIKEY-Locale-Currency: USD',
            'X-DIGIKEY-Locale-ShipToCountry: RU',
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_PROXY => $this->proxy,
            CURLOPT_PROXYTYPE => CURLPROXY_SOCKS5,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => $headers,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return !empty($data['Products']) ? $data['Products'][0] : null;
        }
        
        throw new Exception('HTTP ' . $httpCode);
    }
    
    private function updateProductInBitrix($mpn, $productData) {
        // Заглушка для обновления в Bitrix
        $this->log("Обновление в Bitrix: $mpn - " . ($productData['QuantityAvailable'] ?? 0) . " шт.");
    }
    
    private function log($message, $isError = false) {
        $timestamp = date('Y-m-d H:i:s');
        $type = $isError ? 'ERROR' : 'INFO';
        $logMessage = "[$timestamp] [$type] $message\n";
        
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
        AddMessage2Log($message, 'digikey_sync', $isError);
    }
}

// Запуск
$sync = new DigiKeySync();
$sync->sync();

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';