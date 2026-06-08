<?php
/**
 * Тестовый запрос к API PromElec (без ограничений боевого обмена).
 * Использует api_url_test — лимиты дневных вызовов не тратятся.
 *
 * Запрос: login, password (MD5), customer_id, method (item_data_get).
 * По выбранному коду товара (item_id) возвращает данные одного товара.
 *
 * Не обращается к Битрикс и не меняет боевой обмен.
 *
 * Использование:
 *   Веб:  /api/test/?item_id=203075   или  /api/test/?code=203075
 *   CLI:  php index.php 203075       или  php index.php --item_id=203075
 */

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SERVER['DOCUMENT_ROOT']) || !$_SERVER['DOCUMENT_ROOT']) {
    $_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../..');
}

$configFile = $_SERVER['DOCUMENT_ROOT'] . '/config/api_promelec.php';
if (!file_exists($configFile)) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Конфиг не найден',
        'config_file' => $configFile,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return;
}

$config = require $configFile;
$login = $config['login'] ?? '';
$password = $config['password'] ?? '';
$customerId = (int)($config['customer_id'] ?? 0);
$apiUrl = $config['api_url_test'] ?? $config['api_url'] ?? '';

if (!$apiUrl) {
    http_response_code(500);
    echo json_encode(['error' => 'В конфиге не задан api_url_test или api_url'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return;
}

$passwordMd5 = strtoupper(md5($password));

// Код товара: из GET или из аргументов CLI
$itemId = null;
if (php_sapi_name() === 'cli') {
    foreach (array_slice($_SERVER['argv'] ?? [], 1) as $arg) {
        if (strpos($arg, '--item_id=') === 0) {
            $itemId = (int) substr($arg, 10);
            break;
        }
        if (strpos($arg, '--code=') === 0) {
            $itemId = trim(substr($arg, 7));
            if (is_numeric($itemId)) {
                $itemId = (int) $itemId;
            }
            break;
        }
    }
    if ($itemId === null && isset($_SERVER['argv'][1])) {
        $itemId = $_SERVER['argv'][1];
        if (is_numeric($itemId)) {
            $itemId = (int) $itemId;
        }
    }
} else {
    if (isset($_GET['item_id']) && $_GET['item_id'] !== '') {
        $itemId = is_numeric($_GET['item_id']) ? (int) $_GET['item_id'] : trim($_GET['item_id']);
    } elseif (isset($_GET['code']) && $_GET['code'] !== '') {
        $itemId = is_numeric($_GET['code']) ? (int) $_GET['code'] : trim($_GET['code']);
    }
}

if ($itemId === null || $itemId === '') {
    echo json_encode([
        'error' => 'Укажите код товара (item_id или code)',
        'usage_web' => '/api/test/?item_id=203075  или  /api/test/?code=203075',
        'usage_cli' => 'php index.php 203075  или  php index.php --item_id=203075',
        'request_format' => [
            'login' => $login,
            'password' => 'MD5(пароль)',
            'customer_id' => $customerId,
            'method' => 'item_data_get',
            'item_id' => '<ID товара>',
        ],
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return;
}

// Один товар по ID — метод item_data_get (тестовый endpoint, без лимитов)
$requestData = [
    'login' => $login,
    'password' => $passwordMd5,
    'customer_id' => $customerId,
    'method' => 'item_data_get',
    'item_id' => is_int($itemId) ? $itemId : (int) $itemId,
];

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($requestData, JSON_UNESCAPED_UNICODE),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
curl_close($ch);

$decoded = null;
if ($response !== false && $response !== '' && $httpCode === 200) {
    $decoded = json_decode($response, true, 512, JSON_UNESCAPED_UNICODE);
}

$result = [
    'test_api' => true,
    'api_url' => $apiUrl,
    'request' => [
        'login' => $login,
        'password' => '(MD5)',
        'customer_id' => $customerId,
        'method' => 'item_data_get',
        'item_id' => $itemId,
    ],
    'response' => [
        'http_code' => $httpCode,
        'curl_error' => $curlErr ?: null,
        'raw_length' => $response !== false ? strlen($response) : 0,
        'data' => $decoded,
    ],
];

if ($decoded === null && $response !== false && $response !== '') {
    $result['response']['raw_preview'] = mb_substr($response, 0, 500);
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
