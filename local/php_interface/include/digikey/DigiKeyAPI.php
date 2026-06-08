<?php

// Явно подключаем необходимые файлы
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require_once __DIR__ . '/DigiKeyProvider.php';

use League\OAuth2\Client\Token\AccessToken;

class DigiKeyAPI
{
    private $provider;
    private $accessToken;
    private $config;

    public function __construct($isSandbox = false)
    {
        $this->config = [
            'clientId' => 'uEodk824rK2UOfxvMynvIXnPlPMmlz9He6eDh4oQANnGUivF',
            'clientSecret' => 'VVGFDAx4VCgOGTDBXgAA7LM0PNSUnustYao96CWqJSbDH1AUh5DFSU3vqtPdqKtr',
            'redirectUri' => 'https://lvt.market/api/digikey/callback/index.php',
            'isSandbox' => $isSandbox
        ];

        $this->initializeProvider();
        $this->loadAccessToken();
    }


    private function initializeProvider()
    {
        $this->provider = new DigiKeyProvider($this->config, [
            'httpClient' => new \GuzzleHttp\Client([
                'proxy' => [
                    'http'  => 'http://LM8Efc73:7cvVyzHi@193.176.21.211:64541',
                    'https' => 'http://LM8Efc73:7cvVyzHi@193.176.21.211:64541',
                ],
                'verify' => false,
                'timeout' => 30,
            ])
        ]);
    }

    public function getAuthorizationUrl()
    {
        return $this->provider->getAuthorizationUrl([
            'scope' => ['api']
        ]);
    }

    public function getAccessToken($authorizationCode)
    {
        try {
            $this->accessToken = $this->provider->getAccessToken('authorization_code', [
                'code' => $authorizationCode
            ]);

            $this->saveAccessToken();
            return $this->accessToken;

        } catch (\Exception $e) {
            throw new Exception('Failed to get access token: ' . $e->getMessage());
        }
    }

    public function refreshAccessToken()
    {
        if (!$this->accessToken) {
            throw new Exception('No access token available for refresh');
        }

        try {
            $this->accessToken = $this->provider->getAccessToken('refresh_token', [
                'refresh_token' => $this->accessToken->getRefreshToken()
            ]);

            $this->saveAccessToken();
            return $this->accessToken;

        } catch (\Exception $e) {
            throw new Exception('Failed to refresh token: ' . $e->getMessage());
        }
    }

    /**
     * 🔍 ПОИСК 10 РАНДОМНЫХ ТОВАРОВ - ОСНОВНАЯ ФУНКЦИЯ
     */
    public function searchRandomProducts($limit = 10)
    {
        if (!$this->accessToken) {
            throw new Exception('No access token available');
        }

        // Список популярных электронных компонентов для случайного поиска
        $popularComponents = [
            'resistor', 'capacitor', 'transistor', 'diode', 'led', 
            'microcontroller', 'arduino', 'raspberry pi', 'sensor',
            'connector', 'crystal', 'oscillator', 'inductor', 'transformer',
            'relay', 'switch', 'potentiometer', 'voltage regulator',
            'op amp', 'logic gate', 'memory', 'fpga', 'dac', 'adc'
        ];

        $randomKeyword = $popularComponents[array_rand($popularComponents)];
        
        return $this->searchProducts($randomKeyword, $limit);
    }

    /**
     * Поиск товаров по ключевому слову
     */
    public function searchProducts($keywords, $limit = 10)
    {
        if (!$this->accessToken) {
            throw new Exception('No access token available');
        }

        $url = $this->provider->getApiBaseUrl() . '/products/v4/search';

        $params = [
            'keywords' => $keywords,
            'limit' => $limit,
            'includes' => 'DigiKeyPartNumber,ManufacturerPartNumber,Manufacturer,ProductDescription,QuantityAvailable,StandardPricing,LeadTime,DetailedDescription,Parameters,Datasheets'
        ];

        try {
            $request = $this->provider->getAuthenticatedRequest(
                'GET',
                $url . '?' . http_build_query($params),
                $this->accessToken
            );

            $response = $this->provider->getResponse($request);
            return json_decode($response->getBody(), true);

        } catch (\Exception $e) {
            throw new Exception('API request failed: ' . $e->getMessage());
        }
    }

    /**
     * Получение детальной информации о товаре
     */
    public function getProductDetails($digiKeyPartNumber)
    {
        if (!$this->accessToken) {
            throw new Exception('No access token available');
        }

        $url = $this->provider->getApiBaseUrl() . '/products/v4/productdetails/' . urlencode($digiKeyPartNumber);

        $params = [
            'includes' => 'DigiKeyPartNumber,ManufacturerPartNumber,Manufacturer,ProductDescription,QuantityAvailable,StandardPricing,LeadTime,DetailedDescription,Parameters,Datasheets'
        ];

        try {
            $request = $this->provider->getAuthenticatedRequest(
                'GET',
                $url . '?' . http_build_query($params),
                $this->accessToken
            );

            $response = $this->provider->getResponse($request);
            return json_decode($response->getBody(), true);

        } catch (\Exception $e) {
            throw new Exception('API request failed: ' . $e->getMessage());
        }
    }

    private function loadAccessToken()
    {
        $tokenFile = $_SERVER['DOCUMENT_ROOT'] . '/local/tokens/digikey_tokens.json';
        if (file_exists($tokenFile)) {
            $data = json_decode(file_get_contents($tokenFile), true);
            if ($data && isset($data['access_token'])) {
                $this->accessToken = new AccessToken($data);
                
                // Автоматически обновляем токен если он истек
                if ($this->accessToken->hasExpired()) {
                    $this->refreshAccessToken();
                }
            }
        }
    }

    private function saveAccessToken()
    {
        $tokenDir = $_SERVER['DOCUMENT_ROOT'] . '/local/tokens';
        if (!file_exists($tokenDir)) {
            mkdir($tokenDir, 0755, true);
        }

        $tokenFile = $tokenDir . '/digikey_tokens.json';
        $tokenData = [
            'access_token' => $this->accessToken->getToken(),
            'refresh_token' => $this->accessToken->getRefreshToken(),
            'expires' => $this->accessToken->getExpires(),
            'resource_owner_id' => $this->accessToken->getResourceOwnerId(),
        ];

        file_put_contents($tokenFile, json_encode($tokenData));
    }
}