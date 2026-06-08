<?php
/**
 * Read-only diagnostics for invoice integration.
 *
 * Example:
 * /local/api/invoice_healthcheck.php
 * /local/api/invoice_healthcheck.php?ms=1&checkUrl=<salt>&profile_id=<id>
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Bitrix\Main\Loader;
use Rbs\Moysklad\ApiNew;
use Rbs\Moysklad\Config;

header('Content-Type: application/json; charset=UTF-8');

$report = [
    'ok' => true,
    'checks' => [],
];

$addCheck = static function (string $name, bool $ok, array $extra = []) use (&$report): void {
    $report['checks'][$name] = array_merge(['ok' => $ok], $extra);
    if (!$ok) {
        $report['ok'] = false;
    }
};

$addCheck('module_main_loaded', Loader::includeModule('main'));
$addCheck('module_sale_loaded', Loader::includeModule('sale'));
$msLoaded = Loader::includeModule('rbs.moysklad');
$addCheck('module_rbs_moysklad_loaded', $msLoaded);

$requiredFiles = [
    '/local/php_interface/classes/InvoicePdfGenerator.php',
    '/local/php_interface/classes/MoySkladInvoiceHandler.php',
    '/local/php_interface/classes/InvoiceMailEventInstaller.php',
    '/local/templates/invoice/invoice_template.php',
    '/local/api/moysklad_invoice_hook.php',
];
foreach ($requiredFiles as $relPath) {
    $absPath = $_SERVER['DOCUMENT_ROOT'] . $relPath;
    $addCheck('file_' . trim(str_replace('/', '_', $relPath), '_'), is_readable($absPath), ['path' => $relPath]);
}

$eventTypeExists = false;
$eventTypeRes = CEventType::GetList(['TYPE_ID' => 'SALE_INVOICE_READY']);
while ($row = $eventTypeRes->Fetch()) {
    $eventTypeExists = true;
    break;
}
$addCheck('mail_event_type_sale_invoice_ready', $eventTypeExists);

$eventTemplateExists = false;
$eventTemplateRes = CEventMessage::GetList('', '', [
    'EVENT_NAME' => 'SALE_INVOICE_READY',
    'ACTIVE' => 'Y',
]);
if ($eventTemplateRes->Fetch()) {
    $eventTemplateExists = true;
}
$addCheck('mail_event_template_sale_invoice_ready', $eventTemplateExists);

// PDF smoke-test (no order write, no email).
$pdfSmokeOk = false;
$pdfSmokeError = '';
try {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/InvoicePdfGenerator.php';
    $testData = [
        'invoice_number' => 'TEST-' . date('YmdHis'),
        'invoice_date' => date('d.m.Y'),
        'currency' => 'RUB',
        'positions' => [
            ['name' => 'Тестовая позиция', 'quantity' => 1, 'price' => 123.45, 'sum' => 123.45],
        ],
        'total' => 123.45,
        'company' => ['name' => 'LVT GROUP', 'inn' => '', 'kpp' => '', 'bank' => '', 'account' => ''],
        'buyer' => ['name' => 'Тестовый клиент', 'inn' => '', 'email' => 'test@example.com'],
    ];

    $pdfPath = InvoicePdfGenerator::generate($testData, 'healthcheck_invoice');
    $pdfSmokeOk = is_readable($pdfPath) && filesize($pdfPath) > 0;
    if ($pdfSmokeOk) {
        @unlink($pdfPath);
    } else {
        $pdfSmokeError = 'PDF not generated or empty';
    }
} catch (\Throwable $e) {
    $pdfSmokeError = $e->getMessage();
}
$addCheck('pdf_generation_smoke_test', $pdfSmokeOk, ['error' => $pdfSmokeError]);

// Optional: API connectivity test. Only with explicit ms=1 and valid salt.
$needMsCheck = isset($_GET['ms']) && (string)$_GET['ms'] === '1';
if ($needMsCheck) {
    if (!$msLoaded) {
        $addCheck('moysklad_api_ping', false, ['error' => 'rbs.moysklad module unavailable']);
    } else {
        Config::setIgnorePushToMs(true);
        $saltOk = Config::checkSalt();
        $addCheck('moysklad_api_ping_salt', $saltOk);

        if ($saltOk) {
            try {
                $ping = ApiNew::get('/entity/customerorder', ['limit' => 1]);
                $ok = is_object($ping) && empty($ping->hasErrors);
                $extra = [];
                if (!$ok) {
                    $extra['error'] = is_object($ping) ? json_encode($ping, JSON_UNESCAPED_UNICODE) : 'invalid API response';
                } else {
                    $extra['rows'] = isset($ping->rows) && is_array($ping->rows) ? count($ping->rows) : 0;
                }
                $addCheck('moysklad_api_ping', $ok, $extra);
            } catch (\Throwable $e) {
                $addCheck('moysklad_api_ping', false, ['error' => $e->getMessage()]);
            }
        }
    }
}

// Optional: payload shape check, no processing.
$raw = file_get_contents('php://input');
if ($raw) {
    $payload = json_decode($raw);
    $shapeOk = is_object($payload) && !empty($payload->events) && is_array($payload->events);
    $invoiceEvents = 0;
    if ($shapeOk) {
        foreach ($payload->events as $eventHook) {
            $type = (string)($eventHook->meta->type ?? '');
            $action = strtoupper((string)($eventHook->action ?? ''));
            if ($type === 'invoiceout' && in_array($action, ['CREATE', 'UPDATE'], true)) {
                $invoiceEvents++;
            }
        }
    }
    $addCheck('webhook_payload_shape', $shapeOk, ['invoiceout_events' => $invoiceEvents]);
}

echo json_encode($report, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
