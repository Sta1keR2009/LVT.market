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

// Путь к файлу лога
$logFile = __DIR__ . '/log.txt';

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
        return null;
    }
    curl_close($ch);
    return json_decode($response, true);
}

// Функция для записи в лог
function logMessage($message, $logFile) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

// Логируем начало работы
logMessage("Начало работы скрипта.", $logFile);

// Переменные для подсчета товаров
$totalItemsCount = 0; // Общий счетчик товаров
$lastItemId = null;   // Последний item_id для следующего запроса

do {
    // Логируем начало нового запроса
    logMessage("Отправка запроса к API. Последний item_id: $lastItemId", $logFile);

    // Формируем данные для запроса
    $requestData = [
        "login" => $login,
        "password" => $passwordHash,
        "method" => $method,
        "customer_id" => $customer_id,
        "item_id" => $lastItemId // item_id для следующего запроса
    ];

    // Отправляем запрос к API
    $response = sendRequest($apiUrl, $requestData);

    if ($response && is_array($response)) {
        $items = $response;

        // Подсчитываем количество товаров в текущей порции
        $totalItemsCount += count($items);

        // Логируем результат запроса
        logMessage("Получено товаров: " . count($items), $logFile);

        // Если товаров нет, завершаем цикл
        if (count($items) === 0) {
            logMessage("Получен пустой ответ. Завершение цикла.", $logFile);
            break;
        }

        // Обновляем lastItemId для следующего запроса
        $lastItemId = end($items)['item_id'];
        logMessage("Обновлен последний item_id: $lastItemId", $logFile);
    } else {
        logMessage("Ошибка при получении данных. Ответ API: " . print_r($response, true), $logFile);
        break;
    }
} while (count($items) === 1000); // Продолжаем, пока размер порции равен 1000

// Логируем завершение работы
logMessage("Завершение работы скрипта. Общее количество товаров: $totalItemsCount", $logFile);

// Выводим общее количество товаров
echo "Общее количество товаров: " . $totalItemsCount . "\n";