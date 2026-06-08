<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

global $USER;
if (!$USER->IsAdmin()) {
    die("Доступ запрещен");
}

// Отключаем вывод ошибок шаблона
define('STOP_STATISTICS', true);
define('NO_AGENT_CHECK', true);
define('PUBLIC_AJAX_MODE', true);

echo "<h1>Поиск рабочих прокси для Mouser API</h1>";

// Ваши текущие прокси (вероятно, не работают)
$current_proxies = [
    '154.194.108.3:64613',
    '154.209.159.131:62989', 
    '185.128.41.239:63813'
];

// Список бесплатных SOCKS5 прокси для тестирования
$free_proxies = [
    // SOCKS5 прокси (бесплатные, но нестабильные)
    '185.199.229.156:7492',
    '185.199.228.220:7300',
    '185.199.231.45:8382',
    '188.74.210.207:6286',
    '188.74.183.10:8279',
    '188.74.210.21:6100',
    '45.155.68.129:8133',
    '154.95.36.199:6893',
    '45.94.47.66:8110',
    // Добавьте другие прокси при необходимости
];

// Тестовый URL для проверки прокси
$test_url = 'https://httpbin.org/ip';
$mouser_test_url = 'https://api.mouser.com/api/v1/search/keyword?apiKey=d9fd95c5-186e-4640-a6ee-fffa19b837fe';

$api_test_data = json_encode([
    "SearchByKeywordRequest" => [
        "keyword" => "resistor",
        "records" => 1,
        "startingRecord" => 0
    ]
]);

echo "<h2>1. Проверка ваших текущих прокси:</h2>";

foreach ($current_proxies as $index => $proxy) {
    echo "<h3>Прокси #" . ($index + 1) . ": " . htmlspecialchars($proxy) . "</h3>";
    
    // Формируем строку прокси
    $proxy_string = "socks5://LM8Efc73:7cvVyzHi@" . $proxy;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $test_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_PROXY, $proxy_string);
    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($error) {
        echo "<div style='color: red;'>Ошибка: " . htmlspecialchars($error) . "</div>";
    } elseif ($http_code == 200) {
        echo "<div style='color: green;'>Работает! HTTP 200</div>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
        
        // Проверяем доступ к Mouser
        echo "<h4>Проверка доступа к Mouser API:</h4>";
        
        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_URL, $mouser_test_url);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_POST, true);
        curl_setopt($ch2, CURLOPT_POSTFIELDS, $api_test_data);
        curl_setopt($ch2, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch2, CURLOPT_PROXY, $proxy_string);
        curl_setopt($ch2, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        curl_setopt($ch2, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
        
        $response2 = curl_exec($ch2);
        $error2 = curl_error($ch2);
        $http_code2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
        
        if ($http_code2 == 200) {
            echo "<div style='color: green;'>✓ Доступ к Mouser API есть!</div>";
            $data = json_decode($response2, true);
            if (isset($data['SearchResults']['Parts'])) {
                echo "<div>Найдено товаров: " . count($data['SearchResults']['Parts']) . "</div>";
            }
        } elseif ($http_code2 == 403) {
            echo "<div style='color: orange;'>✗ Доступ к Mouser запрещен (прокси тоже заблокирован)</div>";
        } else {
            echo "<div style='color: orange;'>HTTP код: $http_code2</div>";
        }
        
        curl_close($ch2);
    } else {
        echo "<div style='color: red;'>Не работает. HTTP код: $http_code</div>";
    }
    
    curl_close($ch);
    echo "<hr>";
}

echo "<h2>2. Проверка бесплатных прокси (без авторизации):</h2>";

$working_proxies = [];

foreach ($free_proxies as $index => $proxy) {
    echo "<h3>Тестируем прокси: " . htmlspecialchars($proxy) . "</h3>";
    
    $proxy_string = "socks5://" . $proxy;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $test_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_PROXY, $proxy_string);
    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($error) {
        echo "<div style='color: red;'>Ошибка: " . htmlspecialchars($error) . "</div>";
    } elseif ($http_code == 200) {
        echo "<div style='color: green;'>✓ Работает! HTTP 200</div>";
        $working_proxies[] = $proxy;
        
        // Быстрая проверка Mouser
        echo "<div>Проверяем Mouser... ";
        
        $ch2 = curl_init();
        curl_setopt($ch2, CURLOPT_URL, $mouser_test_url);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_POST, true);
        curl_setopt($ch2, CURLOPT_POSTFIELDS, $api_test_data);
        curl_setopt($ch2, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch2, CURLOPT_PROXY, $proxy_string);
        curl_setopt($ch2, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        curl_setopt($ch2, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
        
        $response2 = curl_exec($ch2);
        $http_code2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
        curl_close($ch2);
        
        if ($http_code2 == 200) {
            echo "<span style='color: green;'>✓ Mouser доступен!</span></div>";
        } elseif ($http_code2 == 403) {
            echo "<span style='color: orange;'>✗ Mouser заблокирован для этого прокси</span></div>";
        } else {
            echo "<span style='color: orange;'>HTTP $http_code2</span></div>";
        }
    } else {
        echo "<div style='color: red;'>✗ Не работает</div>";
    }
    
    curl_close($ch);
    echo "<hr>";
}

echo "<h2>3. Результаты поиска:</h2>";

if (!empty($working_proxies)) {
    echo "<div style='color: green; padding: 15px; background: #d4edda; border-radius: 5px;'>";
    echo "<h3>Найдены рабочие прокси:</h3>";
    echo "<ul>";
    foreach ($working_proxies as $proxy) {
        echo "<li><strong>" . htmlspecialchars($proxy) . "</strong> (SOCKS5)</li>";
    }
    echo "</ul>";
    
    echo "<h3>Код для config.php:</h3>";
    echo "<pre style='background: white; padding: 10px; border-radius: 5px;'>";
    echo htmlspecialchars('// Прокси сервера
$proxies = [
    \'socks5://' . implode('\',
    \'socks5://', $working_proxies) . '\'
];

// Учетные данные для прокси
$proxy_auth = [
    \'username\' => \'\',
    \'password\' => \'\'
];');
    echo "</pre>";
    echo "</div>";
} else {
    echo "<div style='color: red; padding: 15px; background: #f8d7da; border-radius: 5px;'>";
    echo "<h3>Рабочих прокси не найдено!</h3>";
    echo "<p>Нужно найти другие SOCKS5 прокси.</p>";
    echo "</div>";
}

echo "<h2>4. Решение проблемы:</h2>";
echo "<p><strong>Вариант 1:</strong> Купить качественные прокси</p>";
echo "<ul>";
echo "<li>Proxy6.net (SOCKS5 прокси)</li>";
echo "<li>Proxy-sale.com</li>";
echo "<li>Fineproxy.org</li>";
echo "</ul>";

echo "<p><strong>Вариант 2:</strong> Использовать VPN на сервере</p>";
echo "<pre style='background: #f8f9fa; padding: 10px;'>
# Установка OpenVPN на сервер
sudo apt-get install openvpn

# Подключение через VPN, затем запуск импорта
</pre>";

echo "<p><strong>Вариант 3:</strong> Временное решение - использовать веб-сервисы парсинга</p>";
echo "<p>Можно использовать сервисы вроде:</p>";
echo "<ul>";
echo "<li>Apify.com</li>";
echo "<li>Scrapingbee.com</li>";
echo "<li>Scraperapi.com</li>";
echo "</ul>";

echo "<hr>";
echo "<p><a href='/api-mouser/import_control.php'>Вернуться к управлению импортом</a></p>";

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog.php');
?>