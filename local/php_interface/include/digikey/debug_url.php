<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Debug Digi-Key Authorization URL</h1>";

$clientId = 'uEodk824rK2UOfxvMynvIXnPlPMmlz9He6eDh4oQANnGUivF';
$redirectUri = 'https://lvt.market/api/digikey/callback/index.php';

$params = [
    'client_id' => $clientId,
    'response_type' => 'code',
    'redirect_uri' => $redirectUri,
    'scope' => 'api'
];

echo "<h2>Parameters:</h2>";
echo "<pre>";
print_r($params);
echo "</pre>";

// Формируем URL разными способами
$url1 = 'https://sso.digikey.com/as/authorization.oauth2?' . http_build_query($params);
$url2 = 'https://sso.digikey.com/as/authorization.oauth2?client_id=' . $clientId . '&response_type=code&redirect_uri=' . urlencode($redirectUri) . '&scope=api';

echo "<h2>URL with http_build_query:</h2>";
echo "<textarea style='width: 100%; height: 80px;'>" . $url1 . "</textarea>";
echo "<p><a href='" . $url1 . "' target='_blank'>🔗 Test this URL</a></p>";

echo "<h2>URL with manual encoding:</h2>";
echo "<textarea style='width: 100%; height: 80px;'>" . $url2 . "</textarea>";
echo "<p><a href='" . $url2 . "' target='_blank'>🔗 Test this URL</a></p>";

// Проверяем разбор URL
echo "<h2>URL Analysis:</h2>";
$parsed = parse_url($url1);
parse_str($parsed['query'], $queryParams);
echo "<pre>";
print_r($queryParams);
echo "</pre>";

// Проверяем длину client_id
echo "<h2>Client ID Check:</h2>";
echo "Client ID length: " . strlen($clientId) . " characters<br>";
echo "Client ID value: " . $clientId . "<br>";