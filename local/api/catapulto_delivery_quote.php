<?php
/**
 * Safe local endpoint for product-card delivery quotes via Catapulto.
 *
 * Example:
 * /local/api/catapulto_delivery_quote.php?product_id=12345&quantity=1&city=Москва
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
        exit;
    }

    $productId = (int)($_GET['product_id'] ?? 0);
    $quantity = (int)($_GET['quantity'] ?? 1);
    $city = isset($_GET['city']) ? (string)$_GET['city'] : null;

    if ($productId <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'product_id is required']);
        exit;
    }

    require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/CatapultoProductDeliveryCalculator.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/LvtProductInboundDays.php';

    $inbound = LvtProductInboundDays::maxDaysForProduct($productId);
    $shift = (int)($inbound['days'] ?? 0);

    $calculator = new CatapultoProductDeliveryCalculator();
    $result = $calculator->calculate($productId, $quantity, $city, $shift);
    $result['citySource'] = $calculator->getLastCityResolveSource();
    $result['inboundDaysToLytkarino'] = $shift;
    $result['inboundShiftNote'] = (string)($inbound['note'] ?? '');

    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (\Throwable $e) {
    http_response_code(200);
    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
