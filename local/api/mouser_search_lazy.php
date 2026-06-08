<?php
declare(strict_types=1);

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/lvt_mouser_integration.php';

header('Content-Type: application/json; charset=UTF-8');

if (!lvt_is_mouser_api_enabled()) {
    echo json_encode([
        'ok' => false,
        'redirect' => '',
        'html' => '',
        'message' => '',
        'disabled' => true,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/GetchipsPartnumberSearchHelper.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/MouserCatalogSearchBridge.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/include/lvt_mouser_search_results_html.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/mouser/mouser_client.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/MouserCatalogElementHelper.php';

$query = trim((string) ($_GET['q'] ?? ''));
$catalogIblockId = (int) ($_GET['catalog_iblock'] ?? 11);

if ($query === '' || mb_strlen($query) < 3) {
    echo json_encode([
        'ok' => false,
        'html' => '',
        'message' => 'Слишком короткий запрос.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$localIds = GetchipsPartnumberSearchHelper::findLocalProductElementIds($catalogIblockId, $query);

if (count($localIds) === 1) {
    $url = GetchipsPartnumberSearchHelper::getElementDetailUrl((int) reset($localIds));
    echo json_encode([
        'ok' => true,
        'redirect' => $url,
        'html' => '',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

if (count($localIds) === 0) {
    $partRes = mouser_part_number_search($query);
    $parts = is_array($partRes['data']['SearchResults']['Parts'] ?? null)
        ? $partRes['data']['SearchResults']['Parts']
        : [];

    if (!empty($partRes['ok']) && count($parts) === 1 && is_array($parts[0])) {
        $mouserPn = trim((string) ($parts[0]['MouserPartNumber'] ?? ''));
        if ($mouserPn !== '') {
            $elementId = MouserCatalogElementHelper::upsertFromMouserPartNumber($catalogIblockId, $mouserPn);
            $url = $elementId > 0 ? GetchipsPartnumberSearchHelper::getElementDetailUrl($elementId) : '';
            if ($url !== '') {
                echo json_encode([
                    'ok' => true,
                    'redirect' => $url,
                    'html' => '',
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }
    }
}

$rows = MouserCatalogSearchBridge::searchPartsCached($query);
$html = lvt_mouser_search_results_html($rows, $catalogIblockId);

echo json_encode([
    'ok' => true,
    'redirect' => '',
    'html' => $html,
    'message' => $html === '' ? 'По запросу ничего не найдено.' : '',
], JSON_UNESCAPED_UNICODE);
