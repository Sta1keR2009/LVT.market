<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

require_once($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/digikey/DigiKeyAPI.php');

// Список тестовых товаров для проверки
$testProducts = [
    'SN74HC595N',
    'ATMEGA328P-PU',
    'LM317T',
    'ULN2003A',
    'DS18B20',
    'HC-SR04',
    'LM7805',
    'NE555',
    'MCP3008',
    'ADS1115'
];

echo '<h2>Тестирование Digi-Key API</h2>';

try {
    $digikey = new DigiKeyAPI();
    
    echo '<h3>1. Тестовый поиск товаров</h3>';
    
    // Тестируем поиск по первому товару из списка
    $result = $digikey->searchProducts($testProducts[0], 5);
    
    echo '<pre>';
    print_r($result);
    echo '</pre>';
    
    // Если есть результаты, получаем детальную информацию по первому товару
    if (isset($result['Products']) && count($result['Products']) > 0) {
        echo '<h3>2. Детальная информация по товару</h3>';
        
        $firstProduct = $result['Products'][0];
        $detail = $digikey->getProductDetails($firstProduct['DigiKeyPartNumber']);
        
        echo '<pre>';
        print_r($detail);
        echo '</pre>';
    }
    
} catch (Exception $e) {
    echo '<div style="color: red; padding: 10px; border: 1px solid red;">';
    echo '<strong>Ошибка:</strong> ' . $e->getMessage();
    echo '</div>';
    
    echo '<p><a href="/local/php_interface/include/digikey/auth.php" target="_blank">Авторизоваться в Digi-Key</a></p>';
}