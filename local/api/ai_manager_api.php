<?php
/**
 * JSON API для веб-панели менеджера → Node /v1/manager/*
 */
header('Content-Type: application/json; charset=UTF-8');

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/ai_manager_lib.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$action = isset($_GET['action']) ? (string)$_GET['action'] : '';
$sessionId = isset($_GET['session']) ? trim((string)$_GET['session']) : '';
$token = isset($_GET['token']) ? trim((string)$_GET['token']) : '';

if ($sessionId === '' || $token === '' || !lvt_ai_manager_verify_token($sessionId, $token)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'forbidden'], JSON_UNESCAPED_UNICODE);
    exit;
}

$q = 'session_id=' . rawurlencode($sessionId) . '&token=' . rawurlencode($token);

if ($action === 'session' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $r = lvt_ai_manager_proxy('GET', '/v1/manager/session?' . $q);
    http_response_code($r['code'] ?: 502);
    echo $r['body'] !== '' ? $r['body'] : json_encode(['ok' => false, 'error' => 'backend']);
    exit;
}

if ($action === 'poll' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $since = isset($_GET['since']) ? (int)$_GET['since'] : 0;
    $r = lvt_ai_manager_proxy('GET', '/v1/manager/poll?' . $q . '&since=' . $since);
    http_response_code($r['code'] ?: 502);
    echo $r['body'] !== '' ? $r['body'] : json_encode(['ok' => false, 'error' => 'backend']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'method'], JSON_UNESCAPED_UNICODE);
    exit;
}

$raw = file_get_contents('php://input');
$payload = $raw !== false && $raw !== '' ? json_decode($raw, true) : [];
if (!is_array($payload)) {
    $payload = [];
}
$payload['session_id'] = $sessionId;
$payload['token'] = $token;
$jsonBody = json_encode($payload, JSON_UNESCAPED_UNICODE);

$pathMap = [
    'takeover' => '/v1/manager/takeover',
    'reply' => '/v1/manager/reply',
    'close' => '/v1/manager/close',
];

if (!isset($pathMap[$action])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'action'], JSON_UNESCAPED_UNICODE);
    exit;
}

$r = lvt_ai_manager_proxy('POST', $pathMap[$action], $jsonBody);
http_response_code($r['code'] ?: 502);
echo $r['body'] !== '' ? $r['body'] : json_encode(['ok' => false, 'error' => 'backend'], JSON_UNESCAPED_UNICODE);
