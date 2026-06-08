<?php
class DigikeyAuth {
    private $accessToken;
    private $tokenFile;
    
    public function __construct() {
        $this->tokenFile = $_SERVER['DOCUMENT_ROOT'] . '/upload/digikey_token.json';
    }
    
    public function getAccessToken() {
        if ($this->isTokenValid()) {
            return $this->accessToken;
        }
        
        return $this->refreshToken();
    }
    
    private function isTokenValid() {
        if (!file_exists($this->tokenFile)) {
            return false;
        }
        
        $tokenData = json_decode(file_get_contents($this->tokenFile), true);
        
        if (empty($tokenData['access_token']) || time() >= $tokenData['expires_at']) {
            return false;
        }
        
        $this->accessToken = $tokenData['access_token'];
        return true;
    }
    
    private function refreshToken() {
        $url = DIGIKEY_API_URL . '/v1/oauth2/token';
        
        $postData = http_build_query([
            'client_id' => DIGIKEY_CLIENT_ID,
            'client_secret' => DIGIKEY_CLIENT_SECRET,
            'grant_type' => 'client_credentials'
        ]);
        
        $response = $this->makeCurlRequest($url, $postData, 'POST');
        
        if ($response) {
            $tokenData = json_decode($response, true);
            $tokenData['expires_at'] = time() + $tokenData['expires_in'] - 300;
            
            // Создаем директорию если нет
            $dir = dirname($this->tokenFile);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            file_put_contents($this->tokenFile, json_encode($tokenData));
            $this->accessToken = $tokenData['access_token'];
            
            return $this->accessToken;
        }
        
        throw new Exception('Не удалось получить токен');
    }
    
    private function makeCurlRequest($url, $data = null, $method = 'GET') {
        $ch = curl_init();
        
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_PROXY => PROXY_SOCKS5,
            CURLOPT_PROXYTYPE => CURLPROXY_SOCKS5,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => [
                'User-Agent: lvt.market/1.0',
                'Accept: application/json'
            ]
        ];
        
        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $data;
            $options[CURLOPT_HTTPHEADER][] = 'Content-Type: application/x-www-form-urlencoded';
        }
        
        curl_setopt_array($ch, $options);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($httpCode === 200) {
            return $response;
        } else {
            error_log("Digi-Key Auth Error: $error (HTTP: $httpCode)");
            return false;
        }
    }
}