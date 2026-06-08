<?php
// /api/callback.php - УПРОЩЕННАЯ ВЕРСИЯ

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

echo "<h3>🔗 Digi-Key Callback Handler</h3>";

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    echo "<p style='color: green; font-size: 18px;'>✅ УСПЕХ! Получен код авторизации!</p>";
    echo "<p><strong>Код:</strong> " . htmlspecialchars($code) . "</p>";
    
    // Автоматически сохраняем код
    $codeFile = $_SERVER['DOCUMENT_ROOT'] . '/upload/digikey_auth_code.txt';
    file_put_contents($codeFile, $code);
    
    echo "<p>✅ Код сохранен в файл</p>";
    
    // Ссылка для продолжения
    echo "<p><a href='/local/digikey_process_code.php?code=" . urlencode($code) . "' style='font-size: 18px; padding: 10px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>🚀 ПРОДОЛЖИТЬ НАСТРОЙКУ</a></p>";
    
} elseif (isset($_GET['error'])) {
    echo "<p style='color: red;'>❌ Ошибка авторизации: " . htmlspecialchars($_GET['error']) . "</p>";
    echo "<p>Описание: " . htmlspecialchars($_GET['error_description'] ?? 'N/A') . "</p>";
} else {
    echo "<p>Этот endpoint обрабатывает callback от Digi-Key OAuth.</p>";
    echo "<p>Для начала авторизации перейдите: <a href='/local/digikey_auth_simple.php'>/local/digikey_auth_simple.php</a></p>";
}

// Логируем все параметры
echo "<hr><h4>📋 Параметры URL:</h4>";
echo "<pre>";
print_r($_GET);
echo "</pre>";

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php';