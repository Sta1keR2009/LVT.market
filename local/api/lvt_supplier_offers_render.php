<?php
/**
 * AJAX: ленивый рендер блока "Предложения поставщиков" для карточки товара.
 */

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

header('Content-Type: application/json; charset=UTF-8');

try {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/lvt_mouser_integration.php';
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

    $elementId = (int)($_POST['element_id'] ?? 0);
    if ($elementId <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Invalid element id'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $source = strtolower(trim((string)($_POST['source'] ?? 'all')));
    $allowed = ['all', 'bitrix', 'promelec', 'getchips'];
    if (!in_array($source, $allowed, true)) {
        $source = 'all';
    }

    require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/LvtSupplierOffersRenderCache.php';
    $cachedPayload = LvtSupplierOffersRenderCache::get($elementId, $source);
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
        ['ID' => $elementId, 'ACTIVE' => 'Y'],
        false,
        false,
        ['ID', 'IBLOCK_ID', 'NAME', 'DETAIL_PAGE_URL', 'XML_ID']
    );

    $obElement = $res->GetNextElement();
    if (!$obElement) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Element not found'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $fields = $obElement->GetFields();
    $properties = $obElement->GetProperties();
    $fields['PROPERTIES'] = $properties;

    $iblockIdAjax = (int) ($fields['IBLOCK_ID'] ?? 0);
    $elementIdAjax = (int) ($fields['ID'] ?? 0);
    if ($iblockIdAjax > 0 && $elementIdAjax > 0) {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/GetchipsCatalogOffersHelper.php';
        $rsPa = \CIBlockElement::GetProperty(
            $iblockIdAjax,
            $elementIdAjax,
            ['sort' => 'asc'],
            ['CODE' => 'pr_article']
        );
        if ($rowPa = $rsPa->Fetch()) {
            $fields['PROPERTIES']['pr_article'] = $rowPa;
        }
        foreach (['pr_article', 'CML2_ARTICLE', 'promelec'] as $propCode) {
            $cur = $fields['PROPERTIES'][$propCode] ?? null;
            $raw = '';
            if (is_array($cur)) {
                $raw = trim((string) ($cur['~VALUE'] ?? $cur['VALUE'] ?? ''));
                if (is_array($cur['~VALUE'] ?? null)) {
                    $raw = trim((string) reset($cur['~VALUE']));
                }
                if ($raw === '' && is_array($cur['VALUE'] ?? null)) {
                    $raw = trim((string) reset($cur['VALUE']));
                }
            }
            if ($raw !== '') {
                continue;
            }
            $rsProp = \CIBlockElement::GetProperty($iblockIdAjax, $elementIdAjax, ['sort' => 'asc'], ['CODE' => $propCode]);
            if ($row = $rsProp->Fetch()) {
                $fields['PROPERTIES'][$propCode] = $row;
            }
        }

        $rsProp501 = \CIBlockElement::GetProperty($iblockIdAjax, $elementIdAjax, ['sort' => 'asc'], ['ID' => 501]);
        if ($row501 = $rsProp501->Fetch()) {
            $fields['PROPERTIES']['501'] = $row501;
            if (!empty($row501['CODE'])) {
                $fields['PROPERTIES'][$row501['CODE']] = $row501;
            }
        }

        $articleProbePrimary = \GetchipsCatalogOffersHelper::resolvePrArticle($fields);
        if (mb_strlen((string) $articleProbePrimary) < 3 && \Bitrix\Main\Loader::includeModule('catalog') && class_exists('\CCatalogSKU')) {
            $offersMap = \CCatalogSKU::getOffersList(
                [$elementIdAjax],
                $iblockIdAjax,
                ['ACTIVE' => 'Y'],
                ['ID', 'IBLOCK_ID', 'NAME'],
                ['CODE' => ['pr_article', 'CML2_ARTICLE']]
            );
            $skuOffers = array_values((array) ($offersMap[$elementIdAjax] ?? []));
            if ($skuOffers) {
                foreach ($skuOffers as &$offer) {
                    if (!is_array($offer)) {
                        continue;
                    }
                    $offerIblockId = (int) ($offer['IBLOCK_ID'] ?? 0);
                    $offerId = (int) ($offer['ID'] ?? 0);
                    if ($offerIblockId <= 0 || $offerId <= 0) {
                        continue;
                    }
                    $prFromOffer = '';
                    $prCur = $offer['PROPERTIES']['pr_article'] ?? null;
                    if (is_array($prCur)) {
                        $prFromOffer = trim((string) ($prCur['~VALUE'] ?? $prCur['VALUE'] ?? ''));
                    }
                    if ($prFromOffer === '') {
                        $rsOfferPa = \CIBlockElement::GetProperty(
                            $offerIblockId,
                            $offerId,
                            ['sort' => 'asc'],
                            ['CODE' => 'pr_article']
                        );
                        if ($rowOfferPa = $rsOfferPa->Fetch()) {
                            $offer['PROPERTIES']['pr_article'] = $rowOfferPa;
                        }
                    }
                }
                unset($offer);

                $fields['OFFERS'] = $skuOffers;
                $fields['OFFERS_SELECTED'] = 0;
            }
        }
    }

    $options = [
        'wrap_section' => $source === 'all',
        'include_bitrix' => in_array($source, ['all', 'bitrix'], true),
        'include_promelec' => in_array($source, ['all', 'promelec'], true),
        'include_getchips' => in_array($source, ['all', 'getchips'], true),
        'include_mouser' => false,
    ];

    require_once $_SERVER['DOCUMENT_ROOT'] . '/local/include/lvt_supplier_offers_section_html.php';
    $html = lvt_supplier_offers_section_html($fields, $options);

    $payload = ['ok' => true, 'html' => (string) $html, 'source' => $source, 'cached' => false];
    if (in_array($source, ['promelec', 'all'], true)) {
        $diag = lvt_supplier_offers_promelec_diag();
        if ($diag !== []) {
            $payload['promelec'] = $diag;
        }
    }

    LvtSupplierOffersRenderCache::set($elementId, $source, $payload);

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
