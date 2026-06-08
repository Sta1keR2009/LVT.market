<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (!check_bitrix_sessid()) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'bad_sessid'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/CatapultoOrderDeliveryService.php';

    $request = \Bitrix\Main\Context::getCurrent()->getRequest();
    $action = trim((string)$request->getPost('action'));

    if ($action === 'clear') {
        CatapultoOrderDeliveryService::clearSelectedRate();
        echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $result = CatapultoOrderDeliveryService::saveSelectedRate([
        'rateKey' => $request->getPost('rateKey'),
        'operator' => $request->getPost('operator'),
        'operatorName' => $request->getPost('operatorName'),
        'rateName' => $request->getPost('rateName'),
        'shippingType' => $request->getPost('shippingType'),
        'deliveryMode' => $request->getPost('deliveryMode'),
        'price' => $request->getPost('price'),
        'periodText' => $request->getPost('periodText'),
        'pvzAddress' => $request->getPost('pvzAddress'),
        'pvzId' => $request->getPost('pvzId'),
        'pvzCode' => $request->getPost('pvzCode'),
        'deliveryAddress' => $request->getPost('deliveryAddress'),
    ]);

    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (\Throwable $e) {
    http_response_code(200);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
