<?php
/**
 * Safe local webhook endpoint for MoySklad invoiceout events.
 *
 * URL example:
 * /local/api/moysklad_invoice_hook.php?checkUrl=<salt>&profile_id=<id>
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;
use Rbs\Moysklad\Config;

header('Content-Type: application/json; charset=UTF-8');

if (!Loader::includeModule('rbs.moysklad')) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Module rbs.moysklad is not installed']);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/InvoicePdfGenerator.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/MoySkladInvoiceHandler.php';

Config::setIgnorePushToMs(true);

if (!Config::checkSalt()) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Forbidden']);
    exit;
}

$raw = file_get_contents('php://input');
$payload = json_decode((string)$raw);
if (!is_object($payload) || empty($payload->events) || !is_array($payload->events)) {
    echo json_encode(['ok' => true, 'message' => 'No events']);
    exit;
}

$results = [];
foreach ($payload->events as $eventHook) {
    $type = (string)($eventHook->meta->type ?? '');
    $action = strtoupper((string)($eventHook->action ?? ''));

    if ($type !== 'invoiceout' || !in_array($action, ['CREATE', 'UPDATE'], true)) {
        continue;
    }

    $results[] = MoySkladInvoiceHandler::processEvent($eventHook);
}

echo json_encode([
    'ok' => true,
    'processed' => count($results),
    'results' => $results,
], JSON_UNESCAPED_UNICODE);
