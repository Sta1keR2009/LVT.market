<?php
/**
 * AJAX: добавление позиции Getchips в корзину (товар-заглушка + CUSTOM_PRICE в RUB).
 * Требуется GETCHIPS_STUB_PRODUCT_ID в local/php_interface/getchips_site.php.
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

header('Content-Type: application/json; charset=UTF-8');

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/getchips_site.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/GetchipsCatalogOffersHelper.php';

use Bitrix\Main\Loader;

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (!check_bitrix_sessid('sessid')) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Неверная сессия'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stubId = (int) (defined('GETCHIPS_STUB_PRODUCT_ID') ? GETCHIPS_STUB_PRODUCT_ID : 0);
    if ($stubId <= 0) {
        echo json_encode([
            'ok' => false,
            'error' => 'Не задан GETCHIPS_STUB_PRODUCT_ID (товар-заглушка в каталоге).',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $productId = (int) ($_POST['product_id'] ?? 0);
    if ($productId !== $stubId) {
        echo json_encode(['ok' => false, 'error' => 'Некорректный товар'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $contextCatalogElementId = (int) ($_POST['context_catalog_element_id'] ?? 0);
    if ($contextCatalogElementId > 0 && $stubId === $contextCatalogElementId) {
        echo json_encode([
            'ok' => false,
            'error' => 'GETCHIPS_STUB_PRODUCT_ID совпадает с карточкой каталога: для внешних поставок нужен отдельный тех. товар (другой ID) с допуском покупки при нуле на складе.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $minOrder = max(1, (int) ($_POST['min_order'] ?? 1));
    $orderStep = max(1, (int) ($_POST['order_step'] ?? 1));
    $qty = max(1, (int) ($_POST['quantity'] ?? 1));
    if ($qty < $minOrder) {
        $qty = $minOrder;
    } else {
        $rem = ($qty - $minOrder) % $orderStep;
        if ($rem !== 0) {
            $qty += $orderStep - $rem;
        }
    }

    $tiers = GetchipsCatalogOffersHelper::normalizeTiersRubFromJson((string) ($_POST['tiers_rub_json'] ?? ''));
    $unitRub = null;
    if ($tiers !== []) {
        $unitRub = GetchipsCatalogOffersHelper::unitRubForOrderQty($tiers, $qty);
    }
    if ($unitRub === null || $unitRub <= 0) {
        $unitRub = (float) str_replace(',', '.', (string) ($_POST['price_rub'] ?? '0'));
    }
    if ($unitRub <= 0) {
        echo json_encode(['ok' => false, 'error' => 'Некорректная цена'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $leadDays = max(0, (int) ($_POST['lead_days'] ?? 0));
    $leadText = $leadDays > 0 ? GetchipsCatalogOffersHelper::formatLeadWeeksLabel($leadDays) : '';

    $supplier = mb_substr(trim((string) ($_POST['supplier'] ?? '')), 0, 255);
    $part = mb_substr(trim((string) ($_POST['part_number'] ?? '')), 0, 255);
    $provider = mb_substr(trim((string) ($_POST['provider'] ?? '')), 0, 255);
    $url = mb_substr(trim((string) ($_POST['url'] ?? '')), 0, 1024);
    $srcCcy = mb_substr(strtoupper(trim((string) ($_POST['source_currency'] ?? ''))), 0, 16);
    $srcPrice = mb_substr(trim((string) ($_POST['source_price'] ?? '')), 0, 64);
    $brandDisplay = mb_substr(trim((string) ($_POST['brand_display'] ?? '')), 0, 255);
    $productImage = mb_substr(trim((string) ($_POST['product_image'] ?? '')), 0, 2048);
    $contextDetailUrl = mb_substr(trim((string) ($_POST['context_detail_page_url'] ?? '')), 0, 2048);
    $tiersJsonPosted = mb_substr((string) ($_POST['tiers_rub_json'] ?? ''), 0, 12000);
    $rateDate = date('Y-m-d');

    if ($contextCatalogElementId > 0 && $contextCatalogElementId !== $stubId) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/GetchipsPartnumberSearchHelper.php';
        $freshDetailUrl = GetchipsPartnumberSearchHelper::getElementDetailUrl($contextCatalogElementId);
        if ($freshDetailUrl !== '') {
            $contextDetailUrl = mb_substr($freshDetailUrl, 0, 2048);
        }
    }

    if ($productImage === '' && $contextCatalogElementId > 0 && $contextCatalogElementId !== $stubId && Loader::includeModule('iblock')) {
        $picRes = CIBlockElement::GetList(
            [],
            ['ID' => $contextCatalogElementId],
            false,
            false,
            ['ID', 'DETAIL_PICTURE', 'PREVIEW_PICTURE']
        );
        if ($picRow = $picRes->GetNext()) {
            $fid = (int) ($picRow['DETAIL_PICTURE'] ?? 0);
            if ($fid <= 0) {
                $fid = (int) ($picRow['PREVIEW_PICTURE'] ?? 0);
            }
            if ($fid > 0) {
                $picPath = CFile::GetPath($fid);
                if (is_string($picPath) && $picPath !== '') {
                    $productImage = mb_substr($picPath, 0, 2048);
                }
            }
        }
    }

    $productImage = mb_substr(GetchipsCatalogOffersHelper::toAbsoluteCatalogPublicUrl($productImage), 0, 2048);
    $contextDetailUrl = mb_substr(GetchipsCatalogOffersHelper::toAbsoluteCatalogPublicUrl($contextDetailUrl), 0, 2048);

    if (!Loader::includeModule('sale') || !Loader::includeModule('catalog')) {
        echo json_encode(['ok' => false, 'error' => 'Модули sale/catalog недоступны'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $ctxElementProp = ($contextCatalogElementId > 0 && $contextCatalogElementId !== $stubId) ? (string) $contextCatalogElementId : '';

    $props = [
        ['NAME' => 'Ссылка на карточку', 'CODE' => 'GETCHIPS_CONTEXT_DETAIL_URL', 'VALUE' => $contextDetailUrl, 'SORT' => 90],
        ['NAME' => 'Контекст каталога (ID)', 'CODE' => 'GETCHIPS_CONTEXT_ELEMENT_ID', 'VALUE' => $ctxElementProp, 'SORT' => 91],
        ['NAME' => 'Фото товара', 'CODE' => 'GETCHIPS_IMG', 'VALUE' => $productImage, 'SORT' => 95],
        ['NAME' => 'Ступени цен ₽ (JSON)', 'CODE' => 'GETCHIPS_TIERS_RUB_JSON', 'VALUE' => $tiersJsonPosted, 'SORT' => 105],
        ['NAME' => 'Поставщик', 'CODE' => 'GETCHIPS_SUPPLIER', 'VALUE' => $supplier, 'SORT' => 100],
        ['NAME' => 'Партномер', 'CODE' => 'GETCHIPS_PART', 'VALUE' => $part, 'SORT' => 110],
        ['NAME' => 'Цена (₽)', 'CODE' => 'GETCHIPS_PRICE_RUB', 'VALUE' => (string) $unitRub, 'SORT' => 120],
        ['NAME' => 'Валюта источника', 'CODE' => 'GETCHIPS_SOURCE_CCY', 'VALUE' => $srcCcy, 'SORT' => 130],
        ['NAME' => 'Цена источника', 'CODE' => 'GETCHIPS_SOURCE_PRICE', 'VALUE' => $srcPrice, 'SORT' => 140],
        ['NAME' => 'Провайдер', 'CODE' => 'GETCHIPS_PROVIDER', 'VALUE' => $provider, 'SORT' => 150],
        ['NAME' => 'Ссылка', 'CODE' => 'GETCHIPS_URL', 'VALUE' => $url, 'SORT' => 160],
        ['NAME' => 'Курс (дата)', 'CODE' => 'GETCHIPS_RATE_DATE', 'VALUE' => $rateDate, 'SORT' => 170],
        ['NAME' => 'Срок (дней)', 'CODE' => 'GETCHIPS_LEAD_DAYS', 'VALUE' => $leadDays > 0 ? (string) $leadDays : '', 'SORT' => 175],
        ['NAME' => 'Срок поставки', 'CODE' => 'GETCHIPS_LEAD_TEXT', 'VALUE' => $leadText, 'SORT' => 176],
        ['NAME' => 'Бренд', 'CODE' => 'GETCHIPS_BRAND', 'VALUE' => $brandDisplay, 'SORT' => 177],
    ];

    $result = \Bitrix\Catalog\Product\Basket::addProduct([
        'PRODUCT_ID' => $productId,
        'QUANTITY' => $qty,
        'PRICE' => $unitRub,
        'BASE_PRICE' => $unitRub,
        'CUSTOM_PRICE' => 'Y',
        'CURRENCY' => 'RUB',
        'PROPS' => $props,
    ], [], ['USE_MERGE' => 'N']);

    if (!$result->isSuccess()) {
        echo json_encode([
            'ok' => false,
            'error' => implode('; ', $result->getErrorMessages()),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $rid = $result->getData();
    $basketItemId = isset($rid['ID']) ? (int) $rid['ID'] : 0;
    echo json_encode(['ok' => true, 'basket_item_id' => $basketItemId], JSON_UNESCAPED_UNICODE);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
