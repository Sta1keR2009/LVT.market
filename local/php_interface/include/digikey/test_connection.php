<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/digikey/DigiKeyAPI.php');

echo '<h1>Тест подключения Digi-Key API</h1>';

try {
    $digikey = new DigiKeyAPI();
    
    echo '<h2>1. Генерация URL авторизации</h2>';
    $authUrl = $digikey->getAuthorizationUrl();
    echo '<p><strong>URL авторизации:</strong> <a href="' . $authUrl . '" target="_blank">' . $authUrl . '</a></p>';
    
    echo '<h2>2. Проверка токенов</h2>';
    $tokenFile = $_SERVER['DOCUMENT_ROOT'] . '/local/tokens/digikey_tokens.json';
    if (file_exists($tokenFile)) {
        $tokens = json_decode(file_get_contents($tokenFile), true);
        echo '<p><strong>Токены найдены:</strong></p>';
        echo '<pre>';
        print_r($tokens);
        echo '</pre>';
        
        // Проверяем доступность API
        echo '<h2>3. Тестовый запрос к API</h2>';
        try {
            $result = $digikey->searchProducts('SN74HC595N', 1);
            echo '<p style="color: green;"><strong>✅ API запрос успешен!</strong></p>';
            echo '<pre>';
            print_r($result);
            echo '</pre>';
        } catch (Exception $e) {
            echo '<p style="color: orange;"><strong>⚠️ API запрос не удался, но это нормально если токен не действителен</strong></p>';
            echo '<p>Ошибка: ' . $e->getMessage() . '</p>';
        }
        
    } else {
        echo '<p style="color: orange;"><strong>Токены не найдены. Необходима авторизация.</strong></p>';
        echo '<p><a href="' . $authUrl . '" target="_blank">👉 Нажмите здесь для авторизации в Digi-Key</a></p>';
    }
    
} catch (Exception $e) {
    echo '<div style="color: red; padding: 10px; border: 1px solid red;">';
    echo '<strong>Критическая ошибка:</strong> ' . $e->getMessage();
    echo '</div>';
}