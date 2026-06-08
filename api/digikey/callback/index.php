<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/digikey/DigiKeyAPI.php';

// Включаем вывод ошибок для отладки
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo '<h1>Digi-Key Authorization Callback</h1>';

if (isset($_GET['code'])) {
    try {
        $digikey = new DigiKeyAPI();
        $accessToken = $digikey->getAccessToken($_GET['code']);
        
        echo '<div style="background: #d4edda; color: #155724; padding: 20px; border-radius: 5px;">';
        echo '<h3>✅ Authorization Successful!</h3>';
        echo '<p><strong>Access Token:</strong> ' . substr($accessToken->getToken(), 0, 20) . '...</p>';
        echo '<p><strong>Refresh Token:</strong> ' . substr($accessToken->getRefreshToken(), 0, 20) . '...</p>';
        echo '<p><strong>Expires:</strong> ' . date('Y-m-d H:i:s', $accessToken->getExpires()) . '</p>';
        echo '</div>';
        
        // Тестируем поиск сразу после авторизации
        echo '<h3>Testing Product Search:</h3>';
        $products = $digikey->searchRandomProducts(5);
        
        echo '<pre>';
        print_r($products);
        echo '</pre>';
        
    } catch (Exception $e) {
        echo '<div style="background: #f8d7da; color: #721c24; padding: 20px; border-radius: 5px;">';
        echo '<h3>❌ Authorization Failed</h3>';
        echo '<p><strong>Error:</strong> ' . $e->getMessage() . '</p>';
        echo '</div>';
    }
} elseif (isset($_GET['error'])) {
    echo '<div style="background: #f8d7da; color: #721c24; padding: 20px; border-radius: 5px;">';
    echo '<h3>❌ Digi-Key Returned Error</h3>';
    echo '<p><strong>Error:</strong> ' . htmlspecialchars($_GET['error']) . '</p>';
    if (isset($_GET['error_description'])) {
        echo '<p><strong>Description:</strong> ' . htmlspecialchars($_GET['error_description']) . '</p>';
    }
    echo '</div>';
} else {
    echo '<p>No authorization code received.</p>';
}