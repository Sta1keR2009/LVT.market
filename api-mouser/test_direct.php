<?php
// Тест одного конкретного прокси
set_time_limit(60);

echo "<h1>Тестирование одного прокси</h1>";

// Форма для ввода прокси
if (isset($_POST['proxy'])) {
    $proxy = $_POST['proxy'];
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "<h2>Тестируем: " . htmlspecialchars($proxy) . "</h2>";
    
    // Тест httpbin
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://httpbin.org/ip');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_PROXY, $proxy);
    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
    
    if ($username && $password) {
        curl_setopt($ch, CURLOPT_PROXYUSERPWD, $username . ':' . $password);
    }
    
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($error) {
        echo "<div style='color: red;'>❌ Ошибка: " . htmlspecialchars($error) . "</div>";
    } elseif ($http_code == 200) {
        echo "<div style='color: green;'>✅ Прокси работает!</div>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
    } else {
        echo "<div style='color: orange;'>⚠️ HTTP код: $http_code</div>";
    }
    
    curl_close($ch);
    
    echo "<hr><a href='test_single_proxy.php'>Тестировать другой прокси</a>";
    exit;
}
?>

<form method="post">
    <h3>Введите данные прокси:</h3>
    
    <p>
        <label>Прокси (socks5://ip:port):</label><br>
        <input type="text" name="proxy" style="width: 300px;" 
               placeholder="socks5://123.123.123.123:8080" required>
    </p>
    
    <p>
        <label>Логин (если есть):</label><br>
        <input type="text" name="username" style="width: 200px;" 
               placeholder="username">
    </p>
    
    <p>
        <label>Пароль (если есть):</label><br>
        <input type="password" name="password" style="width: 200px;" 
               placeholder="password">
    </p>
    
    <p>
        <input type="submit" value="Тестировать прокси">
    </p>
</form>

<h3>Примеры прокси для теста:</h3>
<ul>
    <li><code>socks5://185.199.229.156:7492</code> (бесплатный, может не работать)</li>
    <li><code>socks5://154.194.108.3:64613</code> (ваш текущий)</li>
    <li><code>socks5://154.209.159.131:62989</code> (ваш текущий)</li>
</ul>

<p><a href="/api-mouser/import_control.php">Вернуться к управлению импортом</a></p>