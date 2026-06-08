<?php

// Ваши учетные данные
$login = 'lvtgroup2';
$password = '30316'; // пароль в открытом виде
$password_md5 = md5($password); // преобразуем пароль в MD5 хэш

// URL API
$api_url = 'https://aaa.na4u.ru/rpc/';

// Метод, который мы вызываем
$method = 'providers_data_get';

// Формируем тело запроса
$post_data = array(
    'login' => $login,
    'password' => $password_md5,
    'method' => $method
);

// Инициализируем cURL
$ch = curl_init();

// Настраиваем cURL
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // отключаем проверку SSL, если нет валидного сертификата

// Выполняем запрос
$response = curl_exec($ch);

// Проверяем на ошибки
if (curl_errno($ch)) {
    echo 'Ошибка cURL: ' . curl_error($ch);
} else {
    // Выводим ответ от сервера (обычно JSON)
    echo "Ответ от сервера:\n";
    echo $response;
}

// Закрываем cURL
curl_close($ch);