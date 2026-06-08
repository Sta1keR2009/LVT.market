<?php
/**
 * Отладочный скрипт: запрос items_data_get по конкретному товару PromElec
 * и вывод сырого и разобранного JSON.
 *
 * URL: https://lvtgroup.ru/api/debug_promelect_item.php
 */

header('Content-Type: application/json; charset=utf-8');

// --- НАСТРОЙКИ API PROMELEC --- //
$apiUrl      = 'https://aaa.na4u.ru/rpc/';
$login       = 'lvtgroup2';
// АКТУАЛЬНЫЙ пароль в виде MD5 (верхний регистр)
$passwordMd5 = 'E3E36F5C779D8A1F738264FD2B8E3222';
$customerId  = 148949;

// INA118UB/2K5 — по логам API ID = 203075
$itemId      = 203075;

// Можно передать другой item_id через GET, например ?item_id=12345
if (isset($_GET['item_id']) && $_GET['item_id'] !== '') {
    $itemId = (int)$_GET['item_id'];
}

// --- СБОРКА ЗАПРОСА --- //
$requestData = [
    'login'       => $login,
    'password'    => $passwordMd5,
    'customer_id' => $customerId,
    'method'      => 'items_data_get',
    'item_id'     => $itemId,
];

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($requestData, JSON_UNESCAPED_UNICODE),
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

$result = [
    'request' => [
        'url'  => $apiUrl,
        'data' => $requestData,
    ],
    'response' => [
        'http_code' => $httpCode,
        'curl_error'=> $curlErr,
        'raw'       => $response,
        'decoded'   => null,
    ],
];

// Пытаемся распарсить JSON
if ($response !== false && $response !== '' && $httpCode === 200) {
    $decoded = json_decode($response, true);
    $result['response']['decoded'] = $decoded;
}

// Красивый вывод
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);