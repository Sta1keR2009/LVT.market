<?php
$apiUrl = "https://aaa.na4u.ru/rpc_test/";
$login = "lvtgroup2";
$password = "30316"; 
$customer_id = 148949;
$item_id = 438631;

// Подготовка данных для запроса
$postData = [
    "login" => $login,
    "password" => md5($password), // Хешируем пароль MD5
    "customer_id" => $customer_id,
    "method" => "item_data_get",
    "item_id" => $item_id
];

// Инициализация cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true); // Получить заголовки
curl_setopt($ch, CURLOPT_VERBOSE, true); // Подробный вывод
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

// Выполнение запроса
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);
curl_close($ch);

// Вывод сырого ответа
echo "<h2>HTTP Code: " . $httpCode . "</h2>";
echo "<h3>Заголовки:</h3>";
echo "<pre>" . htmlspecialchars($headers) . "</pre>";
echo "<h3>Тело ответа (сырой JSON):</h3>";
echo "<pre>" . htmlspecialchars($body) . "</pre>";

// Если нужно показать форматированный JSON
if ($httpCode == 200 && !empty($body)) {
    $jsonData = json_decode($body, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "<h3>Форматированный JSON:</h3>";
        echo "<pre>" . json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
    } else {
        echo "<h3>Ошибка парсинга JSON:</h3>";
        echo "Error: " . json_last_error_msg();
    }
}
?>