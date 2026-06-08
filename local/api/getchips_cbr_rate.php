<?php
/**
 * JSON: актуальный курс USD→RUB (ЦБ РФ) для пересчёта таблицы предложений на клиенте.
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

header('Content-Type: application/json; charset=UTF-8');

try {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/GetchipsCatalogOffersHelper.php';
    $payload = GetchipsCatalogOffersHelper::getSupplierTableCbrUsdPayload();
    echo json_encode([
        'ok' => true,
        'usd_rub' => (float)$payload['usd_rub'],
        'rate_date' => (string)$payload['rate_date'],
        'is_fallback' => !empty($payload['is_fallback']),
    ], JSON_UNESCAPED_UNICODE);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
