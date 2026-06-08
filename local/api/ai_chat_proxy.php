<?php
/**
 * Прокси чата ИИ → Node orchestrator (127.0.0.1:3847).
 * Настройте LVT_AI_BACKEND в окружении PHP или замените значение по умолчанию.
 */
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: https://lvt.market');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Session-Id');
    http_response_code(204);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method']);
    exit;
}

$backend = getenv('LVT_AI_BACKEND') ?: 'http://127.0.0.1:3847';
$backend = rtrim($backend, '/');

$body = file_get_contents('php://input');
if ($body === false || strlen($body) > 65536) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'body']);
    exit;
}

$url = $backend . '/v1/chat';
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'X-Session-Id: ' . ($_SERVER['HTTP_X_SESSION_ID'] ?? ''),
    ],
    CURLOPT_POSTFIELDS => $body,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_TIMEOUT => 120,
]);

$response = curl_exec($ch);
$code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);

if ($response === false) {
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => 'curl', 'detail' => $err]);
    exit;
}

http_response_code($code ?: 502);
echo $response;
