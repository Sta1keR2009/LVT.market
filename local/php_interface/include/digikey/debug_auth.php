<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/digikey/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/digikey/DigiKeyAPI.php');

echo '<h1>Тест авторизации Digi-Key с новыми credentials</h1>';

echo '<h2>Проверка конфигурации:</h2>';
echo '<pre>';
echo 'Client ID: ' . substr(DIGIKEY_CLIENT_ID, 0, 10) . '...' . "\n";
echo 'Client Secret: ' . (DIGIKEY_CLIENT_SECRET ? 'SET' : 'NOT SET') . "\n";
echo 'Redirect URI: ' . DIGIKEY_REDIRECT_URI . "\n";
echo 'Proxy: ' . PROXY_SERVER . "\n";
echo '</pre>';

try {
    $digikey = new DigiKeyAPI();
    
    echo '<h2>Генерация URL авторизации:</h2>';
    $authUrl = $digikey->getAuthorizationUrl();
    
    echo '<p><strong>Полный URL:</strong></p>';
    echo '<div style="background: #f5f5f5; padding: 10px; border: 1px solid #ddd; word-wrap: break-word;">';
    echo htmlspecialchars($authUrl);
    echo '</div>';
    
    // Разбираем URL для проверки
    $urlParts = parse_url($authUrl);
    if (isset($urlParts['query'])) {
        parse_str($urlParts['query'], $params);
        echo '<p><strong>Параметры в URL:</strong></p>';
        echo '<pre>';
        print_r($params);
        echo '</pre>';
    }
    
    echo '<h2>Действия:</h2>';
    echo '<ol>';
    echo '<li><a href="' . $authUrl . '" target="_blank" style="font-size: 18px; color: blue;">🔗 1. Нажмите здесь для авторизации в Digi-Key</a></li>';
    echo '<li>После авторизации вы будете перенаправлены на callback страницу</li>';
    echo '<li>Токены автоматически сохранятся</li>';
    echo '<li><a href="/local/php_interface/include/digikey/test_api.php">4. Протестируйте API запросы</a></li>';
    echo '</ol>';
    
} catch (Exception $e) {
    echo '<div style="color: red; padding: 10px; border: 1px solid red;">';
    echo '<strong>Ошибка:</strong> ' . $e->getMessage();
    echo '</div>';
}