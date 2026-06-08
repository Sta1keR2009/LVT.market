<?php
/**
 * AJAX: актуальный курс USD ЦБ (не кешируется в HTML рендера).
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

try {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/GetchipsCatalogOffersHelper.php';

    $rate = GetchipsCatalogOffersHelper::getUsdToRubByCbr();
    $rateDate = GetchipsCatalogOffersHelper::getCbrRateDate();

    echo json_encode([
        'ok' => true,
        'rate' => round($rate, 4),
        'rate_date' => $rateDate,
    ], JSON_UNESCAPED_UNICODE);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
