<?php
/**
 * Тестирование формата данных API ETM.
 * 1) Логин POST /user/login  2) GET / (документация)  3) GET /goods с авторизацией.
 */

header('Content-Type: text/html; charset=utf-8');

$logDir = defined('API_ETM_LOGS_DIR') ? API_ETM_LOGS_DIR : __DIR__ . '/logs';
if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
$errLog = $logDir . '/api_etm_errors.log';
$logFile = $logDir . '/test_format_' . date('Y-m-d_H-i-s') . '.log';

function logLine(string $path, string $msg): void {
    $line = date('Y-m-d H:i:s') . ' ' . $msg . "\n";
    @file_put_contents($path, $line, FILE_APPEND | LOCK_EX);
}

try {
    require_once __DIR__ . '/bootstrap.php';
} catch (Throwable $e) {
    $txt = $e->getMessage() . "\n" . $e->getTraceAsString();
    logLine($errLog, "[TEST_FORMAT] bootstrap: " . $txt);
    echo '<h1>Ошибка загрузки</h1><pre>' . htmlspecialchars($txt) . '</pre>';
    echo '<p>Лог: <code>' . htmlspecialchars($errLog) . '</code></p><p><a href="index.php">Назад</a></p>';
    exit(1);
}

$client = new ApiEtmClient(ETM_API_URL, ETM_LOGIN, ETM_PASSWORD);
$customPath = isset($_GET['path']) ? ('/' . ltrim($_GET['path'], '/')) : '/goods';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;

echo "<h1>Формат данных API ETM (тест)</h1>";
echo "<p>Base URL: <code>" . htmlspecialchars(ETM_API_URL) . "</code></p>";

$out = [];

try {
    $out[] = '<h2>1. Авторизация POST /user/login</h2>';
    logLine($logFile, 'POST /user/login');
    $ok = $client->login();
    $out[] = '<p>HTTP ' . $client->lastHttpCode;
    if ($client->lastError) $out[] = ' | cURL: ' . htmlspecialchars($client->lastError);
    $out[] = ' &nbsp; ' . ($ok ? '<strong style="color:green">OK</strong>' : '<strong style="color:red">ошибка</strong>') . '</p>';

    $raw = $client->lastRawResponse;
    if (is_string($raw)) {
        $dec = json_decode($raw, true);
        if (is_array($dec)) {
            $out[] = '<p>Ответ (JSON):</p><pre>' . htmlspecialchars(json_encode($dec, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) . '</pre>';
        } else {
            $out[] = '<p>Ответ (не JSON):</p><pre>' . htmlspecialchars(mb_substr($raw, 0, 2000)) . '</pre>';
        }
    }

    $out[] = '<h2>2. GET «/» — документация API (HTML)</h2>';
    logLine($logFile, 'GET /');
    $root = $client->rawGet('/');
    $out[] = '<p>HTTP ' . $client->lastHttpCode . '</p>';
    if ($root !== null && isset($root['_raw'])) {
        $out[] = '<p>Страница документации (RapiDoc). Спека: <code>/ns2000/yaml/cli.yaml</code>.</p>';
        $out[] = '<pre>' . htmlspecialchars(mb_substr($root['_raw'], 0, 800)) . "\n…</pre>";
    } else {
        $out[] = '<pre>' . htmlspecialchars(json_encode($root, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) . '</pre>';
    }

    $path = $customPath . (strpos($customPath, '?') !== false ? '&' : '?') . 'limit=' . $limit;
    $out[] = '<h2>3. GET «' . htmlspecialchars($path) . '» (с авторизацией)</h2>';
    logLine($logFile, 'GET ' . $path);
    $data = $client->rawGet($path);
    $out[] = '<p>HTTP ' . $client->lastHttpCode;
    if ($client->lastError) $out[] = ' | cURL: ' . htmlspecialchars($client->lastError);
    $out[] = '</p>';
    if ($client->lastHttpCode === 403) {
        $out[] = '<p><strong>403 Forbidden</strong> — проверьте, что логин прошёл (шаг 1) и ключ сессии передаётся в заголовке Authorization.</p>';
    }

    if ($data !== null) {
        if (isset($data['_raw'])) {
            $out[] = '<p><strong>Ответ не JSON.</strong> Первые 2000 символов:</p>';
            $out[] = '<pre>' . htmlspecialchars(mb_substr($data['_raw'], 0, 2000)) . '</pre>';
        } else {
            $out[] = '<p><strong>Ключи:</strong> ' . implode(', ', array_map('htmlspecialchars', array_keys($data))) . '</p>';
            $out[] = '<pre>' . htmlspecialchars(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) . '</pre>';
            foreach ($data as $k => $v) {
                if (is_array($v) && !empty($v) && isset($v[0]) && is_array($v[0])) {
                    $out[] = '<p>Пример элемента из <code>' . htmlspecialchars($k) . '</code>:</p>';
                    $out[] = '<pre>' . htmlspecialchars(json_encode($v[0], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) . '</pre>';
                    break;
                }
            }
        }
    } else {
        $out[] = '<p>Запрос не удался.</p>';
    }

} catch (Throwable $e) {
    $txt = $e->getMessage() . "\n" . $e->getTraceAsString();
    logLine($errLog, "[TEST_FORMAT] " . $txt);
    $out[] = '<h2>Ошибка</h2><pre>' . htmlspecialchars($txt) . '</pre>';
}

$out[] = '<p><small>Путь для п.3: <code>?path=/goods</code> или <code>?path=/catalog</code>, <code>?limit=5</code>.</small></p>';
$out[] = '<p><a href="index.php">Статистика и логи</a> | <a href="run_debug.php">Отладка</a> | <a href="import.php">Импорт</a></p>';
$out[] = '<p>Лог: <code>' . htmlspecialchars($logFile) . '</code></p>';

echo implode("\n", $out);
