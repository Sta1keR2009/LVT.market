<?php
/**
 * Прокси чата виджета с оператором → Node orchestrator /v1/widget/human/*
 */
header('Content-Type: application/json; charset=UTF-8');

$origin = 'https://lvt.market';
if (!empty($_SERVER['HTTP_ORIGIN'])) {
    $o = $_SERVER['HTTP_ORIGIN'];
    if (strpos($o, 'https://lvt.market') === 0 || strpos($o, 'https://www.lvt.market') === 0) {
        $origin = $o;
    }
}
header('Access-Control-Allow-Origin: ' . $origin);
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Session-Id');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$action = isset($_GET['action']) ? (string)$_GET['action'] : '';
$allowed = ['enter', 'message', 'poll', 'leave'];
if (!in_array($action, $allowed, true)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'action']);
    exit;
}

$backend = getenv('LVT_AI_BACKEND') ?: 'http://127.0.0.1:3847';
$backend = rtrim($backend, '/');

$sessionHdr = $_SERVER['HTTP_X_SESSION_ID'] ?? '';

if ($action === 'poll') {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'method']);
        exit;
    }
    $sid = isset($_GET['session_id']) ? (string)$_GET['session_id'] : $sessionHdr;
    $since = isset($_GET['since']) ? (int)$_GET['since'] : 0;
    if ($sid === '') {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'session_id']);
        exit;
    }
    $url = $backend . '/v1/widget/human/poll?' . http_build_query([
        'session_id' => $sid,
        'since' => $since,
    ]);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_HTTPGET => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 30,
    ]);
} else {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'method']);
        exit;
    }
    $body = file_get_contents('php://input');
    if ($body === false || strlen($body) > 65536) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'body']);
        exit;
    }
    $url = $backend . '/v1/widget/human/' . $action;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-Session-Id: ' . $sessionHdr,
        ],
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 60,
    ]);
}

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
