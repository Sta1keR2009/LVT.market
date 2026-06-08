<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Базовый тест окружения</h1>";

// Проверяем основные требования
echo "<h2>Проверка требований:</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "cURL: " . (function_exists('curl_version') ? "✅ Доступен" : "❌ Не доступен") . "<br>";
echo "JSON: " . (function_exists('json_encode') ? "✅ Доступен" : "❌ Не доступен") . "<br>";

// Проверяем существование файлов
echo "<h2>Проверка файлов:</h2>";
$files = [
    '/local/php_interface/include/digikey/DigiKeyAPI.php',
    '/vendor/autoload.php'
];

foreach ($files as $file) {
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . $file;
    echo $file . ": " . (file_exists($fullPath) ? "✅ Существует" : "❌ Не существует") . "<br>";
}

// Проверяем Composer autoload
echo "<h2>Проверка Composer:</h2>";
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php')) {
    echo "✅ vendor/autoload.php существует<br>";
    
    try {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
        echo "✅ Composer autoload загружен успешно<br>";
    } catch (Exception $e) {
        echo "❌ Ошибка загрузки Composer: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Composer не установлен<br>";
    echo "Выполните в корне сайта: <code>composer install</code><br>";
}