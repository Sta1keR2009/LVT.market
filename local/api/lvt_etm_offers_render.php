<?php
/**
 * AJAX: цена и остаток ETM для карточки IB 41 (/katalog/).
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

header('Content-Type: application/json; charset=UTF-8');

try {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/LvtSupplierTrafficHelper.php';
    if (LvtSupplierTrafficHelper::isSearchEngineOrCrawlerBot()) {
        echo json_encode(['ok' => true, 'html' => '', 'source' => 'skipped_bot'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $elementId = (int) ($_POST['element_id'] ?? 0);
    $layout = (string) ($_POST['layout'] ?? 'desktop');
    $layout = $layout === 'mobile' ? 'mobile' : 'desktop';
    if ($elementId <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Invalid element id'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/LvtEtmOffersRenderCache.php';
    $cachedPayload = LvtEtmOffersRenderCache::get($elementId, $layout);
    if ($cachedPayload !== null) {
        $cachedPayload['cached'] = true;
        echo json_encode($cachedPayload, JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (!\Bitrix\Main\Loader::includeModule('iblock')) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => 'Iblock module unavailable'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $res = \CIBlockElement::GetList(
        [],
        ['ID' => $elementId, 'ACTIVE' => 'Y', 'IBLOCK_ID' => 41],
        false,
        false,
        ['ID', 'IBLOCK_ID', 'NAME', 'DETAIL_PAGE_URL']
    );
    $obElement = $res->GetNextElement();
    if (!$obElement) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Element not found'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $fields = $obElement->GetFields();
    $fields['PROPERTIES'] = $obElement->GetProperties();
    if (isset($fields['BRAND_ITEM'])) {
        // keep if already enriched
    }

    require_once $_SERVER['DOCUMENT_ROOT'] . '/local/include/lvt_etm_store_offers_html.php';
    $built = lvt_etm_store_offers_build($fields, ['layout' => $layout]);

    if (empty($built['ok'])) {
        echo json_encode([
            'ok' => false,
            'error' => (string) ($built['error'] ?? 'build failed'),
            'etm_code' => (string) ($built['etm_code'] ?? ''),
            'etm_code_source' => (string) ($built['etm_code_source'] ?? ''),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $response = [
        'ok' => true,
        'html' => (string) ($built['html'] ?? ''),
        'storePriceData' => $built['storePriceData'] ?? null,
        'totalQty' => (int) ($built['totalQty'] ?? 0),
        'priceRub' => (float) ($built['priceRub'] ?? 0),
        'etm_code' => (string) ($built['etm_code'] ?? ''),
        'etm_code_source' => (string) ($built['etm_code_source'] ?? ''),
        'cached' => !empty($built['cached']),
        'layout' => $layout,
    ];

    LvtEtmOffersRenderCache::set($elementId, $layout, $response);

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
