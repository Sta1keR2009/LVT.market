<?php
class MouserAPI {
    private $api_key;
    private $endpoint;
    private $proxies;
    private $proxy_auth;
    private $current_proxy_index = 0;
    private $retry_count = 0;
    private $max_retries = 3;
    
    public function __construct($api_key, $endpoint, $proxies, $proxy_auth) {
        $this->api_key = $api_key;
        $this->endpoint = $endpoint;
        $this->proxies = $proxies;
        $this->proxy_auth = $proxy_auth;
    }
    
    private function getProxy() {
        if (empty($this->proxies)) {
            return null;
        }
        
        $proxy = $this->proxies[$this->current_proxy_index];
        $this->current_proxy_index = ($this->current_proxy_index + 1) % count($this->proxies);
        
        // Добавляем авторизацию к прокси
        $parsed = parse_url($proxy);
        $parsed['user'] = $this->proxy_auth['username'];
        $parsed['pass'] = $this->proxy_auth['password'];
        
        return $this->buildProxyUrl($parsed);
    }
    
    private function buildProxyUrl($parts) {
        $scheme   = isset($parts['scheme']) ? $parts['scheme'] . '://' : '';
        $host     = isset($parts['host']) ? $parts['host'] : '';
        $port     = isset($parts['port']) ? ':' . $parts['port'] : '';
        $user     = isset($parts['user']) ? $parts['user'] : '';
        $pass     = isset($parts['pass']) ? ':' . $parts['pass'] : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        
        return "$scheme$user$pass$host$port";
    }
    
    public function search($keyword, $records = 50, $starting_record = 0) {
        $url = $this->endpoint . "/search/keyword?apiKey=" . $this->api_key;
        
        $data = [
            "SearchByKeywordRequest" => [
                "keyword" => $keyword,
                "records" => $records,
                "startingRecord" => $starting_record,
                "searchOptions" => "",
                "searchWithYourSignUpLanguage" => ""
            ]
        ];
        
        return $this->makeRequest($url, $data);
    }
    
    private function makeRequest($url, $data, $retry = 0) {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); // Увеличили таймаут
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        // Настраиваем прокси
        $proxy_url = $this->getProxy();
        if ($proxy_url) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy_url);
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        }
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        // Проверяем ошибки
        if ($error) {
            if ($retry < $this->max_retries) {
                sleep(2); // Пауза перед повторной попыткой
                return $this->makeRequest($url, $data, $retry + 1);
            }
            throw new Exception("CURL Error after {$this->max_retries} retries: " . $error);
        }
        
        if ($http_code != 200) {
            if ($http_code == 429 || $http_code >= 500) { // Rate limit или серверная ошибка
                if ($retry < $this->max_retries) {
                    sleep(5); // Дольше ждем при ошибках сервера
                    return $this->makeRequest($url, $data, $retry + 1);
                }
            }
            throw new Exception("HTTP Error $http_code");
        }
        
        $result = json_decode($response, true);
        
        // Проверяем структуру ответа
        if (!isset($result['SearchResults'])) {
            if ($retry < $this->max_retries) {
                sleep(2);
                return $this->makeRequest($url, $data, $retry + 1);
            }
            throw new Exception("Invalid API response structure");
        }
        
        return $result;
    }
    
    public function testConnection() {
        try {
            $result = $this->search('resistor', 1);
            return !empty($result['SearchResults']['Parts']);
        } catch (Exception $e) {
            return false;
        }
    }
}
?>