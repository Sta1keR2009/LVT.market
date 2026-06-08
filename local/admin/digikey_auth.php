<?php
// /local/admin/digikey_auth.php

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin.php';

$APPLICATION->SetTitle('Авторизация Digi-Key');

echo '<div style="padding: 20px;">';
echo '<h1>🔐 Авторизация Digi-Key API</h1>';

// Параметры авторизации
$clientId = 'uEodk824rK2UOfxvMynvIXnPlPMmlz9He6eDh4oQANnGUivF';
$redirectUri = 'https://lvt.market/local/digikey_process_code.php'; // Замените на ваш домен
$scope = 'order.read';

// Формируем URL для авторизации
$authUrl = 'https://api.digikey.com/v1/oauth2/authorize?' . http_build_query([
    'response_type' => 'code',
    'client_id' => $clientId,
    'redirect_uri' => $redirectUri,
    'scope' => $scope
]);

echo '<div style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">';
echo '<h3>Шаг 1: Авторизация в Digi-Key</h3>';
echo '<p>Нажмите кнопку ниже для авторизации в Digi-Key API:</p>';
echo '<a href="' . $authUrl . '" target="_blank" style="display: inline-block; padding: 12px 24px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; font-weight: bold;">🔐 Авторизоваться в Digi-Key</a>';
echo '</div>';

echo '<div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;">';
echo '<h3>📋 Инструкция:</h3>';
echo '<ol>';
echo '<li>Нажмите кнопку "Авторизоваться в Digi-Key" выше</li>';
echo '<li>Войдите в свой аккаунт Digi-Key (если потребуется)</li>';
echo '<li>Разрешите доступ приложению</li>';
echo '<li>Вы будете перенаправлены на страницу с кодом авторизации</li>';
echo '<li>Скопируйте код из URL и вставьте ниже</li>';
echo '</ol>';
echo '</div>';

// Форма для ручного ввода кода
echo '<div style="background: #e7f3ff; padding: 20px; border-radius: 5px; margin: 20px 0;">';
echo '<h3>Шаг 2: Ввод кода авторизации</h3>';
echo '<form method="POST">';
echo '<label style="display: block; margin-bottom: 10px;"><strong>Код авторизации:</strong></label>';
echo '<input type="text" name="auth_code" style="width: 400px; padding: 8px; border: 1px solid #ccc; border-radius: 4px;" placeholder="Введите код из URL после авторизации">';
echo '<br><br>';
echo '<input type="submit" name="process_code" value="🔑 Получить токены" class="adm-btn-green" style="padding: 10px 20px;">';
echo '</form>';
echo '</div>';

// Обработка ручного ввода кода
if ($_POST['process_code'] && !empty($_POST['auth_code'])) {
    $code = $_POST['auth_code'];
    
    echo '<div style="background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0;">';
    echo '<h3>🔄 Получаем токены...</h3>';
    
    // Перенаправляем на обработчик с кодом
    $processUrl = '/local/digikey_process_code.php?code=' . urlencode($code);
    echo '<p>Переходим к получению токенов: <a href="' . $processUrl . '">' . $processUrl . '</a></p>';
    
    // Автоматическое перенаправление через 2 секунды
    echo '<script>setTimeout(function() { window.location.href = "' . $processUrl . '"; }, 2000);</script>';
    echo '</div>';
}

echo '<hr>';
echo '<p><a href="/local/admin/digikey_import_simple.php">➡ Перейти к импорту товаров</a></p>';

echo '</div>';

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';