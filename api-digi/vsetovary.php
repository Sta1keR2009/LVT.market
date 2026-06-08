<?php

// Настройки подключения к API
$apiUrl = "https://aaa.na4u.ru/rpc_test/";
$login = "lvtgroup2";
$password = "30316"; // Пароль в открытом виде
$customer_id = 148949; // Идентификатор клиента (замените на ваш customer_id)

// Формирование MD5 хэша пароля
$passwordHash = md5($password);

// Метод для получения данных о товарах
$method = "items_data_get";

// Функция для отправки запроса к API
function sendRequest($url, $data) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo "Ошибка cURL: " . curl_error($ch);
        return null;
    }
    curl_close($ch);
    return json_decode($response, true);
}

// Запрос для получения первой порции товаров
$requestData = [
    "login" => $login,
    "password" => $passwordHash,
    "method" => $method,
    "customer_id" => $customer_id,
    "item_id" => null // Первый вызов без item_id
];

$response = sendRequest($apiUrl, $requestData);

if ($response && isset($response['result'])) {
    $items = $response['result'];

    // Вывод первых 5 товаров
    $count = 0;
    foreach ($items as $item) {
        if ($count >= 5) {
            break;
        }
        echo "ID товара: " . $item['item_id'] . "\n";
        echo "Наименование: " . $item['name'] . "\n";
        echo "Цена: " . $item['price'] . "\n";
        echo "Количество: " . $item['quantity'] . "\n";
        echo "-------------------------\n";
        $count++;
    }
} else {
    echo "Ошибка при получении данных: ";
    print_r($response);
}