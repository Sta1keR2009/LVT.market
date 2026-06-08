<?php
/**
 * Отладка API ETM: логин → запрос каталога, без импорта в Bitrix.
 * Ошибки пишутся в logs/api_etm_errors.log и выводятся на экран.
 */

$logDir = defined('API_ETM_LOGS_DIR') ? API_ETM_LOGS_DIR : __DIR__ . '/logs';
if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
$errLog = $logDir . '/api_etm_errors.log';
$debugLog = $logDir . '/run_debug_' . date('Y-m-d_H-i-s') . '.log';

function apiEtmErr(string $path, string $msg): void {
    $line = date('Y-m-d H:i:s') . " [DEBUG] " . $msg . "\n";
    @file_put_contents($path, $line, FILE_APPEND | LOCK_EX);
}

function apiEtmDebugLog(string $path, string $msg): void {
    $line = date('Y-m-d H:i:s') . " " . $msg . "\n";
    @file_put_contents($path, $line, FILE_APPEND | LOCK_EX);
}

set_error_handler(function ($no, $msg, $file, $line) use ($errLog) {
    apiEtmErr($errLog, "PHP $no $msg at $file:$line");
    return false;
});

// Включаем отображение ошибок для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Увеличиваем время выполнения для отладки
set_time_limit(300); // 5 минут
ini_set('max_execution_time', 300);

// Регистрируем обработчик фатальных ошибок
register_shutdown_function(function() use ($errLog, $debugLog) {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
        $msg = "FATAL ERROR: {$error['message']} in {$error['file']}:{$error['line']}";
        apiEtmErr($errLog, $msg);
        apiEtmDebugLog($debugLog, $msg);
        
        // Если еще не отправлены заголовки, отправляем их
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=utf-8');
        }
        echo "<h2>Критическая ошибка</h2>";
        echo "<pre>" . htmlspecialchars($msg) . "</pre>";
        echo "<p>Проверьте логи: <code>" . htmlspecialchars($errLog) . "</code></p>";
        flush();
    }
});

// Загружаем bootstrap ПЕРЕД отправкой заголовков
try {
    require_once __DIR__ . '/bootstrap.php';
} catch (Throwable $e) {
    // Если bootstrap не загрузился, отправляем заголовки и выводим ошибку
    header('Content-Type: text/html; charset=utf-8');
    $txt = $e->getMessage() . "\n" . $e->getTraceAsString();
    apiEtmErr($errLog, "bootstrap: " . $txt);
    echo "<!DOCTYPE html><html><head><meta charset=utf-8><title>API ETM Debug - Ошибка</title></head><body>";
    echo "<h1>API ETM — отладка</h1>";
    echo "<h2>Ошибка загрузки</h2><pre>" . htmlspecialchars($txt) . "</pre>";
    echo "<p>Лог: <code>" . htmlspecialchars($errLog) . "</code></p><p><a href='index.php'>Назад</a></p></body></html>";
    flush();
    exit(1);
}

// Теперь можно безопасно отправлять заголовки и выводить HTML
header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html><html><head><meta charset=utf-8><title>API ETM Debug</title></head><body><h1>API ETM — отладка</h1>";
flush();

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
$client = new ApiEtmClient(ETM_API_URL, ETM_LOGIN, ETM_PASSWORD);

echo "<h2>1. Авторизация POST /user/login</h2>";
flush();

$startTime = microtime(true);
apiEtmDebugLog($debugLog, "Начало авторизации");
echo "<p>Подключение к API...</p>";
flush();

try {
    // Проверяем состояние перед логином
    $stateFile = defined('API_ETM_STATE_FILE') ? API_ETM_STATE_FILE : ($logDir . '/api_etm_state.json');
    if (file_exists($stateFile)) {
        $state = json_decode(file_get_contents($stateFile), true);
        if (isset($state['last_login_at'])) {
            $lastLogin = (int)$state['last_login_at'];
            $waitTime = 120 - (time() - $lastLogin);
            if ($waitTime > 0 && $waitTime <= 10) {
                echo "<p>Ожидание лимита API ({$waitTime} сек)...</p>";
                flush();
            }
        }
    }
    
    $loginOk = $client->login();
    $loginTime = round(microtime(true) - $startTime, 2);
    apiEtmDebugLog($debugLog, "Авторизация завершена за {$loginTime} сек. Результат: " . ($loginOk ? 'OK' : 'FAIL') . ". HTTP: " . $client->lastHttpCode);
} catch (Throwable $e) {
    $txt = $e->getMessage() . "\n" . $e->getTraceAsString();
    apiEtmErr($errLog, "login: " . $txt);
    echo "<p>Ошибка: <pre>" . htmlspecialchars($txt) . "</pre></p></body></html>";
    flush();
    exit(1);
}
echo "<p>HTTP " . $client->lastHttpCode . " — " . ($loginOk ? "<strong style='color:green'>OK</strong>" : "<strong style='color:red'>ошибка</strong>") . " (время: {$loginTime} сек)</p>";
flush();

$rawLogin = $client->lastRawResponse;
if (is_string($rawLogin) && $rawLogin !== '') {
    $preview = mb_strlen($rawLogin) > 2000 ? mb_substr($rawLogin, 0, 2000) . "\n…" : $rawLogin;
    echo "<h3>Ответ сервера:</h3><pre>" . htmlspecialchars($preview) . "</pre>";
    flush();
}

if (!$loginOk) {
    apiEtmDebugLog($debugLog, "Авторизация не удалась. HTTP: " . $client->lastHttpCode . ". Ошибка cURL: " . ($client->lastError ?: 'нет'));
    echo "<h3>Ошибка авторизации</h3>";
    
    if ($client->lastHttpCode === 403) {
        echo "<p><strong style='color:red'>403 Forbidden</strong> — API отклонил запрос авторизации.</p>";
        echo "<p>Возможные причины:</p><ul>";
        echo "<li>Неверные учётные данные (логин/пароль)</li>";
        echo "<li>IP адрес заблокирован</li>";
        echo "<li>Превышен лимит запросов</li>";
        echo "<li>Проблема с SSL сертификатом</li>";
        echo "</ul>";
    } elseif ($client->lastHttpCode === 401) {
        echo "<p><strong style='color:red'>401 Unauthorized</strong> — неверные учётные данные.</p>";
    } elseif ($client->lastHttpCode === 0) {
        echo "<p><strong style='color:red'>Ошибка подключения</strong> — не удалось подключиться к серверу.</p>";
        if ($client->lastError) {
            echo "<p>cURL ошибка: <code>" . htmlspecialchars($client->lastError) . "</code></p>";
        }
    } else {
        echo "<p>HTTP код: <strong>" . $client->lastHttpCode . "</strong></p>";
        if ($client->lastError) {
            echo "<p>cURL ошибка: <code>" . htmlspecialchars($client->lastError) . "</code></p>";
        }
    }
    
    echo "<p><strong>Проверьте:</strong></p>";
    echo "<ul>";
    echo "<li>Логин и пароль в <code>config.php</code></li>";
    echo "<li>URL API: <code>" . htmlspecialchars(ETM_API_URL) . "</code></li>";
    echo "<li>Доступность API сервера</li>";
    echo "</ul>";
    
    echo "<p><a href='test_format.php'>Тест формата</a> | <a href='index.php'>Назад</a></p>";
    apiEtmDebugLog($debugLog, "Скрипт завершен с ошибкой авторизации");
    echo "</body></html>";
    flush();
    exit(1);
}

// Источник данных каталога: report (как в sync_catalog) | fetch (старый перебор URL) | job (полный цикл job, долго)
$catalogSource = isset($_GET['source']) ? strtolower(trim((string)$_GET['source'])) : 'report';
if (!in_array($catalogSource, ['report', 'fetch', 'job'], true)) {
    $catalogSource = 'report';
}

echo "<h2>2. Данные каталога (source=" . htmlspecialchars($catalogSource) . ", limit=$limit)</h2>";
flush();

$fetchStartTime = microtime(true);
$raw = null;
$catalogNote = '';

try {
    if ($catalogSource === 'report') {
        $reportPath = defined('API_ETM_REPORT_FILE') ? API_ETM_REPORT_FILE : '';
        if ($reportPath !== '' && is_readable($reportPath)) {
            $json = file_get_contents($reportPath);
            $decoded = json_decode((string)$json, true);
            if (is_array($decoded)) {
                $slice = array_slice($decoded, 0, max(1, $limit));
                // Оборачиваем в ключ goods — extractProducts умеет такой список
                $raw = ['goods' => $slice, '_source' => 'goods_report.json', '_path' => $reportPath];
                $catalogNote = 'Использован локальный кэш выгрузки ETM: ' . $reportPath;
                apiEtmDebugLog($debugLog, 'Каталог: slice из goods_report.json, элементов: ' . count($slice));
            } else {
                $catalogNote = 'Файл goods_report.json не является валидным JSON-массивом.';
                apiEtmDebugLog($debugLog, 'Каталог: невалидный JSON в ' . $reportPath);
            }
        } else {
            $catalogNote = 'Файл выгрузки не найден. Запустите cron/sync_catalog.php или откройте run_debug.php?source=fetch';
            apiEtmDebugLog($debugLog, 'Каталог: report файл не найден: ' . ($reportPath ?: '(не задан)'));
        }
    }

    if ($raw === null && $catalogSource === 'job') {
        echo "<p><strong>Внимание:</strong> режим job может занять до 20 минут (как sync_catalog).</p>";
        flush();
        apiEtmDebugLog($debugLog, 'Каталог: запуск job createGoodsJob');
        $job = $client->createGoodsJob();
        if (!$job || empty($job['uuid'])) {
            apiEtmDebugLog($debugLog, 'Каталог: createGoodsJob вернул пусто. HTTP ' . $client->lastHttpCode);
            $catalogNote = 'Не удалось запустить job выгрузки. См. логи и sync_catalog.php.';
        } else {
            $uuid = (string)$job['uuid'];
            apiEtmDebugLog($debugLog, 'Каталог: job UUID ' . $uuid);
            $reportUrl = null;
            $maxWait = 1200;
            $waited = 0;
            while ($waited < $maxWait) {
                sleep(15);
                $waited += 15;
                $status = $client->getJobStatus($uuid);
                if (!$status) {
                    continue;
                }
                $jobState = $status['state'] ?? '';
                $done = $status['completed'] ?? 'false';
                if ($done === 'true' || $jobState === '1') {
                    $reportUrl = $status['urls'][0]['url'] ?? null;
                    break;
                }
                if ($jobState === '2') {
                    $catalogNote = 'Job завершился с ошибкой.';
                    break;
                }
            }
            if ($reportUrl) {
                $content = $client->downloadReportFile($reportUrl);
                if ($content !== false) {
                    $decoded = json_decode((string)$content, true);
                    if (is_array($decoded)) {
                        $slice = array_slice($decoded, 0, max(1, $limit));
                        $raw = ['goods' => $slice, '_source' => 'job_download', '_url' => $reportUrl];
                        $catalogNote = 'Скачан отчёт по job, показан срез первых ' . count($slice) . ' позиций.';
                        apiEtmDebugLog($debugLog, 'Каталог: job OK, slice ' . count($slice));
                    }
                } else {
                    $catalogNote = 'Не удалось скачать отчёт по URL job.';
                    apiEtmDebugLog($debugLog, 'Каталог: downloadReportFile failed HTTP ' . $client->lastHttpCode);
                }
            } elseif ($catalogNote === '') {
                $catalogNote = 'URL отчёта не получен за отведённое время.';
            }
        }
    }

    if ($raw === null && ($catalogSource === 'fetch' || $catalogSource === 'report')) {
        apiEtmDebugLog($debugLog, "Каталог: fallback fetchCatalog(limit=$limit)");
        $raw = $client->fetchCatalog($limit, 0);
        apiEtmDebugLog($debugLog, 'Каталог: fetchCatalog завершён. HTTP: ' . $client->lastHttpCode);
    }

    $fetchTime = round(microtime(true) - $fetchStartTime, 2);
    apiEtmDebugLog($debugLog, "Запрос каталога (итог) за {$fetchTime} сек. HTTP последнего запроса клиента: " . $client->lastHttpCode);
} catch (Throwable $e) {
    $txt = $e->getMessage() . "\n" . $e->getTraceAsString();
    apiEtmErr($errLog, "fetch: " . $txt);
    echo "<h3>Ошибка запроса</h3><pre>" . htmlspecialchars($txt) . "</pre>";
    echo "<p><a href='test_format.php'>Тест формата</a> | <a href='index.php'>Назад</a></p></body></html>";
    flush();
    exit(1);
}

echo "<p>Время получения данных каталога: " . round(microtime(true) - $fetchStartTime, 2) . " сек</p>";
if ($catalogNote !== '') {
    echo "<p><em>" . htmlspecialchars($catalogNote) . "</em></p>";
}
echo "<p>Подсказка: <code>?source=report</code> (по умолчанию), <code>?source=fetch</code>, <code>?source=job</code> (долго).</p>";
flush();

echo "<p>HTTP (последний запрос клиента) " . $client->lastHttpCode;
if ($client->lastError) {
    echo "; cURL: " . htmlspecialchars($client->lastError);
}
echo ".</p>";
flush();

if ($raw === null) {
    apiEtmErr($errLog, "Каталог: нет данных. HTTP " . $client->lastHttpCode . " " . $client->lastError);
    echo "<p>Данные каталога не получены.</p>";
    echo "<p>Для ETM на этом проекте список товаров обычно приходит из выгрузки <code>goods_report.json</code> (см. <code>cron/sync_catalog.php</code>), а не из <code>GET /goods?limit=</code> — последний часто даёт 404.</p>";
    echo "<p><a href=\"run_debug.php?source=report&amp;limit=" . (int)$limit . "\">Повторить с report</a> | ";
    echo "<a href=\"run_debug.php?source=fetch&amp;limit=" . (int)$limit . "\">Попробовать fetch</a> | ";
    echo "<a href='test_format.php'>Тест формата</a> | <a href='index.php'>Назад</a></p></body></html>";
    flush();
    exit(1);
}

apiEtmDebugLog($debugLog, "Начало обработки данных каталога");
try {
    $products = ApiEtmDataMapper::extractProducts($raw);
    apiEtmDebugLog($debugLog, "Товаров извлечено: " . count($products));
    echo "<p>Товаров извлечено: " . count($products) . "</p>";
    flush();
    
    $rawJson = json_encode($raw, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    if ($rawJson === false) {
        $jsonError = json_last_error_msg();
        apiEtmDebugLog($debugLog, "Ошибка JSON: " . $jsonError);
        echo "<p style='color:orange'>Предупреждение: не удалось преобразовать ответ в JSON: " . htmlspecialchars($jsonError) . "</p>";
        flush();
        $rawJson = print_r($raw, true);
    }
    
    if (mb_strlen($rawJson) > 50000) {
        $rawJson = mb_substr($rawJson, 0, 50000) . "\n... (обрезано)";
    }
    echo "<h3>Сырой ответ (фрагмент)</h3><pre>" . htmlspecialchars($rawJson) . "</pre>";
    flush();
    
    if (!empty($products)) {
        $productJson = json_encode($products[0], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        if ($productJson === false) {
            $productJson = print_r($products[0], true);
        }
        echo "<h3>Первый товар (нормализованный)</h3><pre>" . htmlspecialchars($productJson) . "</pre>";
        flush();
    }
} catch (Throwable $e) {
    $txt = $e->getMessage() . "\n" . $e->getTraceAsString();
    apiEtmErr($errLog, "extractProducts: " . $txt);
    apiEtmDebugLog($debugLog, "Ошибка при обработке данных: " . $txt);
    echo "<h3>Ошибка при обработке данных</h3><pre>" . htmlspecialchars($txt) . "</pre>";
    flush();
}

apiEtmDebugLog($debugLog, "Завершение скрипта");
$totalTime = round(microtime(true) - $startTime, 2);

echo "<p><a href='test_format.php'>Тест формата</a> | <a href='import.php?limit=" . (int)$limit . "'>Импорт</a> | <a href='index.php'>Назад</a></p>";
echo "<p>Ошибки: <code>" . htmlspecialchars($errLog) . "</code></p>";
echo "<p><small>Общее время выполнения: {$totalTime} сек</small></p>";
if (file_exists($debugLog)) {
    echo "<p><small>Лог отладки: <code>" . htmlspecialchars($debugLog) . "</code></small></p>";
}
flush();

apiEtmDebugLog($debugLog, "Скрипт завершен успешно. Общее время: {$totalTime} сек");
echo "</body></html>";
flush();
