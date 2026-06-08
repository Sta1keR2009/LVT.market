<?php
// Конфигурация скрипта
define('MOUSER_API_KEY', 'd9fd95c5-186e-4640-a6ee-fffa19b837fe');
define('INFOBLOCK_ID', 40);
define('API_ENDPOINT', 'https://api.mouser.com/api/v1');

// Прокси сервера
$proxies = [
    'socks5://45.152.118.62:64849',
    'socks5://91.210.69.82:64371',
    'socks5://154.195.179.162:63379',
    'socks5://185.96.36.128:64449'
];

// Учетные данные для прокси
$proxy_auth = [
    'username' => 'LM8Efc73',
    'password' => '7cvVyzHi'
];

// 50 КАТЕГОРИЙ ДЛЯ ИМПОРТА
$search_terms = [
    // 1. Пассивные компоненты
    'resistor',
    'capacitor', 
    'inductor',
    'transformer',
    'potentiometer',
    'varistor',
    'thermistor',
    
    // 2. Полупроводники
    'transistor',
    'diode',
    'MOSFET',
    'IGBT',
    'thyristor',
    'triac',
    
    // 3. Микросхемы
    'IC',
    'microcontroller',
    'microprocessor',
    'memory',
    'op amp',
    'comparator',
    'voltage regulator',
    'ADC',
    'DAC',
    
    // 4. Разъемы
    'connector',
    'USB connector',
    'HDMI connector',
    'RJ45',
    'terminal block',
    'header',
    'socket',
    
    // 5. Переключатели
    'switch',
    'relay',
    'button',
    'dip switch',
    'rocker switch',
    
    // 6. Датчики
    'sensor',
    'temperature sensor',
    'pressure sensor',
    'motion sensor',
    'optical sensor',
    
    // 7. Кварцы и осцилляторы
    'crystal',
    'oscillator',
    'crystal oscillator',
    'clock oscillator',
    
    // 8. Светодиоды и дисплеи
    'LED',
    'display',
    'LCD',
    'OLED',
    '7 segment display',
    
    // 9. Источники питания
    'battery',
    'power supply',
    'DC-DC converter',
    'AC-DC converter',
    
    // 10. Защитные элементы
    'fuse',
    'circuit breaker',
    'surge protector',
    
    // 11. Дополнительно
    'heatsink',
    'fan',
    'enclosure',
    'cable',
    'wire'
];

// Лимиты
define('ITEMS_PER_PAGE', 50); // Максимум по API
define('MAX_ITEMS_PER_CATEGORY', 100); // 1000 товаров на категорию
define('REQUEST_DELAY', 2); // Задержка 2 секунды между запросами
define('IMAGE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/upload/mouser_images/');
define('LOG_PATH', $_SERVER['DOCUMENT_ROOT'] . '/api-mouser/logs/');
?>