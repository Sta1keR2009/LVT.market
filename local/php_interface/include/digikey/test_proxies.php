<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/proxy_config.php';

echo "<h1>🔧 Тестирование прокси для Digi-Key API</h1>";

$clientId = '0bMI7zGCISadEqcF7aQqe1aqGtLaupK1jUZV60JcXDn7P4VX';
$redirectUri = 'https://lvt.market/api/digikey/callback/index.php';

$proxies = ProxyConfig::getProxies();

foreach ($proxies as $name => $proxyConfig) {
    echo "<h2>🧪 Тестируем: {$proxyConfig['country']} ({$proxyConfig['type']})</h2>";
    
    try {
        $ch = curl_init();
        
        $testUrl = 'https://sso.digikey.com/as/authorization.oauth2?client_id=' . $clientId . '&response_type=code&redirect_uri=' . urlencode($redirectUri) . '&scope=api';
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $testUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_PROXY => $proxyConfig['proxy'],
            CURLOPT_PROXYTYPE => $proxyConfig['type'] === 'SOCKS5' ? CURLPROXY_SOCKS5 : CURLPROXY_HTTP,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        echo "<div style='border: 1px solid #ddd; padding: 15px; margin: 10px 0;'>";
        echo "<p><strong>Прокси:</strong> {$proxyConfig['proxy']}</p>";
        echo "<p><strong>HTTP код:</strong> {$httpCode}</p>";
        
        if ($httpCode === 200 || $httpCode === 302) {
            echo "<p style='color: green;'>✅ УСПЕХ - Прокси работает!</p>";
            echo "<p><a href='{$testUrl}' target='_blank'>🔗 Перейти к авторизации</a></p>";
        } elseif ($httpCode === 429) {
            echo "<p style='color: orange;'>⚠️ Лимит запросов (Too Many Requests)</p>";
        } elseif ($httpCode === 400) {
            echo "<p style='color: red;'>❌ Ошибка client_id</p>";
        } else {
            echo "<p style='color: red;'>❌ Ошибка: HTTP {$httpCode}</p>";
        }
        
        if ($error) {
            echo "<p><strong>Ошибка cURL:</strong> {$error}</p>";
        }
        
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Исключение: " . $e->getMessage() . "</p>";
    }
    
    echo "<hr>";
}