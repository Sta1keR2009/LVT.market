<?php

function lvt_ai_manager_secret(): string
{
    $s = getenv('MANAGER_PANEL_SECRET') ?: getenv('WIDGET_BRIDGE_INTERNAL_KEY');
    if (is_string($s) && $s !== '') {
        return $s;
    }
    $path = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/ai_manager_config.php';
    if (is_file($path)) {
        $cfg = include $path;
        if (is_array($cfg) && !empty($cfg['secret'])) {
            return (string)$cfg['secret'];
        }
    }
    return '';
}

function lvt_ai_manager_backend(): string
{
    $b = getenv('LVT_AI_BACKEND') ?: 'http://127.0.0.1:3847';
    $path = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/ai_manager_config.php';
    if (is_file($path)) {
        $cfg = include $path;
        if (is_array($cfg) && !empty($cfg['backend'])) {
            $b = (string)$cfg['backend'];
        }
    }
    return rtrim($b, '/');
}

function lvt_ai_manager_verify_token(string $sessionId, string $token): bool
{
    $secret = lvt_ai_manager_secret();
    if ($secret === '' || $sessionId === '' || $token === '') {
        return false;
    }
    $parts = explode('.', $token, 2);
    if (count($parts) !== 2) {
        return false;
    }
    $exp = (int)$parts[0];
    $sig = $parts[1];
    if ($exp < time() || $sig === '') {
        return false;
    }
    $payload = $sessionId . '.' . $exp;
    $expected = rtrim(strtr(base64_encode(hash_hmac('sha256', $payload, $secret, true)), '+/', '-_'), '=');
    return hash_equals($expected, $sig);
}

function lvt_ai_manager_proxy(string $method, string $path, ?string $body = null): array
{
    $url = lvt_ai_manager_backend() . $path;
    $ch = curl_init($url);
    $headers = ['Content-Type: application/json'];
    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTPHEADER => $headers,
    ];
    if (strtoupper($method) === 'GET') {
        $opts[CURLOPT_HTTPGET] = true;
    } else {
        $opts[CURLOPT_POST] = true;
        $opts[CURLOPT_POSTFIELDS] = $body ?? '{}';
    }
    curl_setopt_array($ch, $opts);
    $response = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    return [
        'code' => $code,
        'body' => $response === false ? '' : $response,
        'err' => $err,
    ];
}
