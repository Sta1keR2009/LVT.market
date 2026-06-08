<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/digikey/DigiKeyAPI.php');

if (isset($_GET['code'])) {
    try {
        $digikey = new DigiKeyAPI();
        $tokens = $digikey->getAccessToken($_GET['code']);
        
        echo "Успешная авторизация! Токены получены.";
        echo "<pre>";
        print_r($tokens);
        echo "</pre>";
        
    } catch (Exception $e) {
        echo "Ошибка авторизации: " . $e->getMessage();
    }
} elseif (isset($_GET['error'])) {
    echo "Ошибка от Digi-Key: " . $_GET['error'];
} else {
    echo "Неверный запрос";
}