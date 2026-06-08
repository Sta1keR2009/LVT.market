<?php
// ВРЕМЕННО ВКЛЮЧАЕМ ВЫВОД ОШИБОК
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Digi-Key Auth Test</h1>";

try {
    // Проверяем существование классов
    if (!class_exists('DigiKeyAPI')) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/digikey/DigiKeyAPI.php';
    }
    
    echo "✅ DigiKeyAPI класс загружен<br>";
    
    $digikey = new DigiKeyAPI();
    echo "✅ DigiKeyAPI объект создан<br>";
    
    $authUrl = $digikey->getAuthorizationUrl();
    echo "✅ URL авторизации получен<br>";
    
    echo "<h3>URL авторизации:</h3>";
    echo "<div style='background: #f5f5f5; padding: 10px; border: 1px solid #ddd; word-wrap: break-word;'>";
    echo htmlspecialchars($authUrl);
    echo "</div>";
    
    echo "<br><a href='" . $authUrl . "' style='background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px;'>🔗 Перейти к авторизации Digi-Key</a>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Ошибка:</h3>";
    echo "<p><strong>Сообщение:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Файл:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Строка:</strong> " . $e->getLine() . "</p>";
    
    // Дополнительная диагностика
    echo "<h4>Диагностика:</h4>";
    echo "DigiKeyAPI exists: " . (class_exists('DigiKeyAPI') ? 'YES' : 'NO') . "<br>";
    echo "DigiKeyProvider exists: " . (class_exists('DigiKeyProvider') ? 'YES' : 'NO') . "<br>";
    
    if (!class_exists('DigiKeyProvider')) {
        echo "<p>Пробуем загрузить DigiKeyProvider вручную...</p>";
        require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/digikey/DigiKeyProvider.php';
        echo "DigiKeyProvider loaded manually: " . (class_exists('DigiKeyProvider') ? 'SUCCESS' : 'FAILED') . "<br>";
    }
}