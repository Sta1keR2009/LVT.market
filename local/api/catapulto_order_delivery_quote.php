<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/CatapultoOrderDeliveryService.php';

    $city = isset($_GET['city']) ? (string)$_GET['city'] : null;
    $includePvz = !isset($_GET['include_pvz']) || (string)$_GET['include_pvz'] !== '0';
    $address = isset($_GET['address']) ? trim((string)$_GET['address']) : null;
    if ($address === '') {
        $address = null;
    }

    $result = CatapultoOrderDeliveryService::quoteForCurrentBasket($city, $includePvz, $address);
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (\Throwable $e) {
    http_response_code(200);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
