<?php
/**
 * Заявка с сайта (ИИ-консультант) → Telegram.
 * Окружение php-fpm: TELEGRAM_LEAD_BOT_TOKEN, TELEGRAM_LEAD_CHAT_ID
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: https://lvt.market');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method']);
    exit;
}

$raw = file_get_contents('php://input');
$data = is_string($raw) ? json_decode($raw, true) : null;
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'json']);
    exit;
}

$name = trim((string)($data['name'] ?? ''));
$phone = trim((string)($data['phone'] ?? ''));
$email = trim((string)($data['email'] ?? ''));
$message = trim((string)($data['message'] ?? ''));
$sessionId = trim((string)($data['session_id'] ?? ''));
$productId = (int)($data['product_id'] ?? 0);
$pageUrl = trim((string)($data['page_url'] ?? ''));
$consent = !empty($data['consent']);

if (!$consent) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'consent']);
    exit;
}

if ($name === '' || mb_strlen($name) > 200) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'name']);
    exit;
}
if ($phone === '' || mb_strlen($phone) > 40) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'phone']);
    exit;
}
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'email']);
    exit;
}

$ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
$rateFile = $_SERVER['DOCUMENT_ROOT'] . '/upload/tmp/ai_lead_rate_' . md5($ip) . '.txt';
$now = time();
if (is_file($rateFile)) {
    $last = (int)file_get_contents($rateFile);
    if ($last > 0 && ($now - $last) < 60) {
        http_response_code(429);
        echo json_encode(['ok' => false, 'error' => 'rate']);
        exit;
    }
}
@file_put_contents($rateFile, (string)$now);

/**
 * TELEGRAM_LEAD_* из php-fpm или fallback local/php_interface/telegram_lead_config.php
 */
$leadCfg = ['bot_token' => '', 'chat_id' => '', 'proxy_url' => ''];
$envToken = getenv('TELEGRAM_LEAD_BOT_TOKEN');
$envChat = getenv('TELEGRAM_LEAD_CHAT_ID');
$envProxy = getenv('TELEGRAM_PROXY_URL');
if (is_string($envToken) && $envToken !== '') {
    $leadCfg['bot_token'] = $envToken;
}
if (is_string($envChat) && $envChat !== '') {
    $leadCfg['chat_id'] = $envChat;
}
if (is_string($envProxy) && $envProxy !== '') {
    $leadCfg['proxy_url'] = $envProxy;
}
$configFile = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/telegram_lead_config.php';
if (is_file($configFile)) {
    $localCfg = include $configFile;
    if (is_array($localCfg)) {
        if ($leadCfg['bot_token'] === '' && !empty($localCfg['bot_token'])) {
            $leadCfg['bot_token'] = (string)$localCfg['bot_token'];
        }
        if ($leadCfg['chat_id'] === '' && !empty($localCfg['chat_id'])) {
            $leadCfg['chat_id'] = (string)$localCfg['chat_id'];
        }
        if ($leadCfg['proxy_url'] === '' && !empty($localCfg['proxy_url'])) {
            $leadCfg['proxy_url'] = (string)$localCfg['proxy_url'];
        }
    }
}
$token = $leadCfg['bot_token'];
$chatId = $leadCfg['chat_id'];
if ($token === '' || $chatId === '') {
    http_response_code(503);
    echo json_encode(['ok' => false, 'error' => 'telegram_not_configured']);
    exit;
}
$envProxyNow = getenv('TELEGRAM_PROXY_URL');
if ($leadCfg['proxy_url'] !== '' && ($envProxyNow === false || $envProxyNow === '')) {
    putenv('TELEGRAM_PROXY_URL=' . $leadCfg['proxy_url']);
}

$emailLine = $email !== '' ? $email : 'не указан';
$text = "Заявка с консультанта lvt.market\n"
    . "Имя: " . $name . "\n"
    . "Тел: " . $phone . "\n"
    . "Email: " . $emailLine . "\n"
    . "Согласие ПДн: да\n";
if ($productId > 0) {
    $text .= "Товар ID: " . $productId . "\n";
}
if ($sessionId !== '') {
    $text .= "Сессия: " . $sessionId . "\n";
}
if ($pageUrl !== '') {
    $text .= "Страница: " . $pageUrl . "\n";
}
if ($message !== '') {
    $text .= "Комментарий:\n" . mb_substr($message, 0, 2000) . "\n";
}

$url = 'https://api.telegram.org/bot' . $token . '/sendMessage';
$post = [
    'chat_id' => $chatId,
    'text' => $text,
    'disable_web_page_preview' => true,
];

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/LvtTelegramProxy.php';

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($post),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
]);
LvtTelegramProxy::applyToCurl($ch);
$resp = curl_exec($ch);
$code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($resp === false || $code !== 200) {
    $logDir = $_SERVER['DOCUMENT_ROOT'] . '/upload/tmp';
    if (is_dir($logDir) && is_writable($logDir)) {
        @file_put_contents(
            $logDir . '/ai_lead_telegram_error.log',
            date('c') . " HTTP {$code}\n" . (string)$resp . "\n---\n",
            FILE_APPEND
        );
    }
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => 'telegram_send']);
    exit;
}

echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
