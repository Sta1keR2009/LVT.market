<?php
// Класс для работы с прокси через Битрикс
class BitrixProxy {
    private static $proxies = [];
    private static $current = 0;
    
    public static function init($proxies) {
        self::$proxies = $proxies;
    }
    
    public static function getNextProxy() {
        if (empty(self::$proxies)) {
            return null;
        }
        
        $proxy = self::$proxies[self::$current];
        self::$current = (self::$current + 1) % count(self::$proxies);
        
        return $proxy;
    }
    
    public static function makeRequest($url, $options = []) {
        $proxy = self::getNextProxy();
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_PROXY, $proxy);
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        if (isset($options['headers'])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $options['headers']);
        }
        
        if (isset($options['post'])) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $options['post']);
        }
        
        $result = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return ['error' => $error];
        }
        
        return $result;
    }
}
?>