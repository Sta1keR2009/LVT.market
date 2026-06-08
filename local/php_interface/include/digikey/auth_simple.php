<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Простой тест Digi-Key Auth</h1>";

try {
    // Базовые параметры
    $clientId = 'uEodk824rK2UOfxvMynvIXnPlPMmlz9He6eDh4oQANnGUivF';
    $redirectUri = 'https://lvt.market/api/digikey/callback/index.php';
    
    $params = [
        'client_id' => $clientId,
        'response_type' => 'code',
        'redirect_uri' => $redirectUri,
        'scope' => 'api'
    ];
    
    $authUrl = 'https://sso.digikey.com/as/authorization.oauth2?' . http_build_query($params);
    
    echo "<h3>✅ URL авторизации сгенерирован успешно!</h3>";
    echo "<p><strong>URL:</strong> " . htmlspecialchars($authUrl) . "</p>";
    echo "<a href='" . $authUrl . "' style='background: blue; color: white; padding: 10px; text-decoration: none;'>🔗 Перейти к авторизации Digi-Key</a>";
    
} catch (Exception $e) {
    echo "<h3>❌ Ошибка:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}