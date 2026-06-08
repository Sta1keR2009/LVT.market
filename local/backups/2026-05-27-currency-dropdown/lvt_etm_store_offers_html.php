<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/LvtEtmCatalogLiveHelper.php';

/**
 * HTML и данные блока «Наличие и цены по складам» из ETM API (IB 41).
 *
 * @param array<string, mixed> $arResult
 * @param array<string, mixed> $options layout: desktop|mobile
 * @return array<string, mixed>
 */
function lvt_etm_store_offers_build(array $arResult, array $options = []): array
{
    $iblockId = (int) ($arResult['IBLOCK_ID'] ?? 0);
    if ($iblockId !== 41) {
        return ['ok' => false, 'error' => 'unsupported iblock'];
    }

    $layout = (string) ($options['layout'] ?? 'desktop');
    $codeMeta = LvtEtmCatalogLiveHelper::resolveEtmCodeMeta($arResult);
    $etmCode = (string) ($codeMeta['code'] ?? '');
    $etmCodeSource = (string) ($codeMeta['source'] ?? '');
    if ($etmCode === '') {
        return [
            'ok' => false,
            'error' => 'etm code missing',
            'etm_code_source' => $etmCodeSource,
        ];
    }

    $live = LvtEtmCatalogLiveHelper::fetchLiveData($etmCode);
    if (empty($live['ok'])) {
        return [
            'ok' => false,
            'error' => (string) ($live['error'] ?? 'etm fetch failed'),
            'etm_code' => $etmCode,
            'etm_code_source' => $etmCodeSource,
        ];
    }

    $storeData = is_array($live['store_data'] ?? null) ? $live['store_data'] : [];
    $extendedPrices = is_array($live['extended_prices'] ?? null) ? $live['extended_prices'] : [];
    $minOrderQuantity = max(1, (int) ($live['min_order_quantity'] ?? 1));
    $productId = (int) ($arResult['ID'] ?? 0);
    if (isset($arResult['SKU']['CURRENT']['ID']) && (int) $arResult['SKU']['CURRENT']['ID'] > 0) {
        $productId = (int) $arResult['SKU']['CURRENT']['ID'];
    }

    $articleValue = trim((string) ($arResult['PROPERTIES']['CML2_ARTICLE']['VALUE'] ?? ''));
    if ($articleValue === '') {
        $articleValue = trim((string) ($arResult['NAME'] ?? ''));
    }
    $brandValue = trim((string) ($arResult['BRAND_ITEM']['NAME'] ?? ''));
    if ($brandValue === '') {
        $brandValue = trim((string) ($arResult['PROPERTIES']['BRAND']['VALUE'] ?? ''));
    }
    if ($brandValue === '') {
        $brandValue = '—';
    }

    $storeUsdToRub = 92.5;
    if (class_exists('CCurrencyRates')) {
        $convertedRate = (float) \CCurrencyRates::ConvertCurrency(1, 'USD', 'RUB');
        if ($convertedRate > 0) {
            $storeUsdToRub = $convertedRate;
        }
    }

    $storePriceData = [
        'productId' => $productId,
        'baseProductId' => (int) ($arResult['ID'] ?? 0),
        'currency' => 'RUB',
        'displayCurrency' => 'RUB',
        'usdToRub' => $storeUsdToRub,
        'basePrice' => (float) ($live['price_rub'] ?? 0),
        'minOrderQuantity' => $minOrderQuantity,
        'extendedPrices' => $extendedPrices,
        'stores' => [],
    ];

    foreach ($storeData as $store) {
        $sid = (int) ($store['ID'] ?? 0);
        if ($sid <= 0) {
            continue;
        }
        $storePriceData['stores'][(string) $sid] = [
            'name' => (string) ($store['NAME'] ?? 'ETM'),
            'quantity' => (int) ($store['QUANTITY'] ?? 0),
            'maxQuantity' => (int) ($store['QUANTITY'] ?? 0),
            'deliveryTime' => (string) ($store['DELIVERY_TIME'] ?? ''),
            'cartId' => 'product_' . (int) ($arResult['ID'] ?? 0) . '_store_' . $sid,
        ];
    }

    if ($layout === 'mobile') {
        $html = lvt_etm_store_offers_mobile_html($storeData, $extendedPrices, $minOrderQuantity, $productId, $arResult);
    } else {
        $html = lvt_etm_store_offers_desktop_html(
            $storeData,
            $extendedPrices,
            $minOrderQuantity,
            $productId,
            $articleValue,
            $brandValue,
            $storeUsdToRub
        );
    }

    return [
        'ok' => true,
        'etm_code' => $etmCode,
        'etm_code_source' => $etmCodeSource,
        'html' => $html,
        'storePriceData' => $storePriceData,
        'totalQty' => (int) ($live['quantity'] ?? 0),
        'priceRub' => (float) ($live['price_rub'] ?? 0),
        'cached' => !empty($live['cached']),
    ];
}

/**
 * @param list<array<string, mixed>> $storeData
 * @param list<array<string, mixed>> $extendedPrices
 */
function lvt_etm_store_offers_desktop_html(
    array $storeData,
    array $extendedPrices,
    int $minOrderQuantity,
    int $productId,
    string $articleValue,
    string $brandValue,
    float $storeUsdToRub
): string {
    $basePrices = $extendedPrices;
    usort($basePrices, static function ($a, $b) {
        return (int) ($a['QUANTITY_FROM'] ?? 0) - (int) ($b['QUANTITY_FROM'] ?? 0);
    });
    if ($basePrices === []) {
        $basePrices[] = [
            'QUANTITY_FROM' => max(1, $minOrderQuantity),
            'PRICE' => 0,
            'CURRENCY' => 'RUB',
        ];
    }

    $rateDateLabel = date('d.m.Y');
    $usdRateLabel = number_format($storeUsdToRub, 2, ',', '');
    $sessid = htmlspecialcharsbx(bitrix_sessid());

    ob_start();
    ?>
    <div class="detail-block ordered-block getchips-offers-section"
         data-page-catalog-element-id="<?=$productId?>"
         data-context-detail-url="">
    <div class="getchips-offers__table-wrap store-offers-table-wrap">
        <table class="getchips-offers__table js-getchips-offers-table store-offers-table"
               data-usd-to-rub="<?=htmlspecialcharsbx((string)$storeUsdToRub)?>"
               data-display-currency="RUB">
            <thead>
            <tr>
                <th>Наименование</th>
                <th>Бренд</th>
                <th>
                    <span class="getchips-currency-switch-wrap">
                        <span class="getchips-currency-switch-label">Цена:</span>
                        <span class="getchips-currency-switch js-getchips-currency-switch" data-display-currency="RUB">
                        <button type="button" class="getchips-currency-switch__trigger">
                            <span class="js-getchips-currency-label">🇷🇺 ₽ RUB</span>
                            <span class="getchips-currency-switch__caret" aria-hidden="true"></span>
                        </button>
                        <span class="getchips-currency-switch__menu">
                            <button type="button" class="getchips-currency-switch__item is-active" data-currency="RUB">🇷🇺 ₽ RUB</button>
                            <button type="button" class="getchips-currency-switch__item" data-currency="USD">🇺🇸 $ USD</button>
                        </span>
                        </span>
                        <span class="getchips-currency-rate js-getchips-cbr-rate" data-rate-date="<?=htmlspecialcharsbx($rateDateLabel)?>">USD <?=htmlspecialcharsbx($usdRateLabel)?> ₽</span>
                        <span class="getchips-currency-alert js-getchips-usd-alert" data-notice="Курс ЦБ получен: <?=htmlspecialcharsbx($rateDateLabel)?>.">!</span>
                        <span class="getchips-currency-alert js-getchips-rub-alert" data-notice="Цена в рублях ориентировочная: если курс ЦБ изменится более чем на 2%, итоговую сумму уточним по актуальному курсу.">!</span>
                    </span>
                </th>
                <th>Доступно, шт.</th>
                <th>Срок</th>
                <th>Кол-во</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($storeData as $store): ?>
                <?php
                $storeId = (int) ($store['ID'] ?? 0);
                $qtyMax = max(0, (int) ($store['QUANTITY'] ?? 0));
                $supplierLabel = trim((string) ($store['NAME'] ?? 'ETM'));
                if ($supplierLabel === '') {
                    $supplierLabel = 'ETM';
                }
                $leadLabel = trim((string) ($store['DELIVERY_TIME'] ?? ''));
                if ($leadLabel === '') {
                    $leadLabel = '—';
                }
                $leadSort = 999999.0;
                if (preg_match('/(\d+(?:[.,]\d+)?)/u', $leadLabel, $m)) {
                    $leadSort = (float) str_replace(',', '.', $m[1]);
                    if (preg_match('/нед|week/ui', $leadLabel)) {
                        $leadSort *= 7;
                    } elseif (preg_match('/мес/ui', $leadLabel)) {
                        $leadSort *= 30;
                    }
                } elseif (mb_stripos($leadLabel, 'налич') !== false) {
                    $leadSort = 0.0;
                }

                $tiersRub = [];
                $priceHtmlLines = [];
                $firstRub = 0.0;
                foreach ($basePrices as $idx => $price) {
                    $tierQty = max(1, (int) ($price['QUANTITY_FROM'] ?? 1));
                    $tierCurrency = strtoupper((string) ($price['CURRENCY'] ?? 'RUB'));
                    $tierPrice = (float) ($price['PRICE'] ?? 0);
                    $tierPriceRub = $tierCurrency === 'USD' ? ($tierPrice * $storeUsdToRub) : $tierPrice;
                    if ($idx === 0) {
                        $firstRub = $tierPriceRub;
                    }
                    $tiersRub[] = [
                        'qty' => $tierQty,
                        'rub' => $tierPriceRub,
                    ];
                    $priceHtmlLines[] = '<div class="getchips-offers__price-tier js-getchips-price-tier" data-tier-qty="' . $tierQty . '" data-price-rub="' . htmlspecialcharsbx((string) $tierPriceRub) . '">х ' . $tierQty . ' шт. ' . number_format($tierPriceRub, 2, '.', '') . ' руб.;</div>';
                }
                $priceHtml = $priceHtmlLines ? implode('', $priceHtmlLines) : '—';
                $rowMinOrder = max(1, $minOrderQuantity > 0 ? $minOrderQuantity : (int) ($tiersRub[0]['qty'] ?? 1));
                $rowStep = $rowMinOrder;
                $packNorm = $rowMinOrder;
                $tiersJson = htmlspecialcharsbx((string) json_encode($tiersRub, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                ?>
                <tr class="js-getchips-offer-row lvt-bitrix-store-row"
                    data-sort-lead="<?=htmlspecialcharsbx((string)$leadSort)?>"
                    data-sort-price="<?=htmlspecialcharsbx((string)$firstRub)?>"
                    data-tiers-rub-json="<?=$tiersJson?>"
                    data-min-order="<?=$rowMinOrder?>"
                    data-order-step="<?=$rowStep?>"
                    data-available-qty="<?=$qtyMax?>">
                    <td class="getchips-offers__name-cell">
                        <div class="getchips-offers__part"><?=htmlspecialcharsbx($articleValue)?></div>
                        <div class="getchips-offers__supplier"><?=htmlspecialcharsbx($supplierLabel)?></div>
                    </td>
                    <td class="getchips-offers__brand-cell">
                        <span class="getchips-offers__brand-fallback"><span class="getchips-offers__brand-name"><?=htmlspecialcharsbx($brandValue)?></span></span>
                    </td>
                    <td class="getchips-offers__price-cell"><?=$priceHtml?></td>
                    <td><?=$qtyMax?></td>
                    <td><?=htmlspecialcharsbx($leadLabel)?></td>
                    <td class="getchips-offers__qty-cell">
                        <div class="getchips-offers__qty-input-row">
                            <input type="number"
                                   class="getchips-offers__qty-input js-getchips-qty-input js-bitrix-store-qty"
                                   value="<?=$rowMinOrder?>"
                                   min="<?=$rowMinOrder?>"
                                   max="<?=$qtyMax?>"
                                   step="<?=$rowStep?>"
                                   inputmode="numeric"
                                   autocomplete="off"
                                   aria-label="Количество"
                                   data-min-order="<?=$rowMinOrder?>"
                                   data-order-step="<?=$rowStep?>"
                                   data-store-id="<?=$storeId?>">
                            <div class="store-add-to-cart-button getchips-offers__cart-btn-wrap">
                                <button type="button"
                                        class="btn btn-default btn-sm js-getchips-add-basket getchips-offers__add-basket has-ripple js-bitrix-store-add"
                                        data-bitrix-store-id="<?=$storeId?>"
                                        data-product-id="<?=$productId?>"
                                        data-sessid="<?=$sessid?>"
                                        data-price-rub="<?=htmlspecialcharsbx((string)$firstRub)?>"
                                        data-tiers-rub-json="<?=$tiersJson?>"
                                        data-min-order="<?=$rowMinOrder?>"
                                        data-order-step="<?=$rowStep?>"
                                        data-lead-days="<?=htmlspecialcharsbx((string)$leadSort)?>"
                                        data-brand-display="<?=htmlspecialcharsbx($brandValue)?>"
                                        data-source-currency="RUB"
                                        data-source-price="<?=htmlspecialcharsbx((string)$firstRub)?>"
                                        data-supplier="<?=htmlspecialcharsbx($supplierLabel)?>"
                                        data-part="<?=htmlspecialcharsbx($articleValue)?>"
                                        data-provider="bitrix-store"
                                        data-url="">
                                    В корзину
                                </button>
                            </div>
                        </div>
                        <div class="getchips-offers__qty-hint js-getchips-qty-hint"></div>
                        <div class="getchips-offers__qty-meta">
                            <span>MIN: <?=$rowMinOrder?></span>
                            <span>Кратность: <?=$rowStep?></span>
                            <span>Норма уп.: <?=$packNorm?></span>
                            <span class="getchips-offers__row-total js-getchips-row-total"></span>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    </div>
    </div>
    <?php
    return (string) ob_get_clean();
}

/**
 * @param list<array<string, mixed>> $storeData
 * @param list<array<string, mixed>> $extendedPrices
 * @param array<string, mixed> $arResult
 */
function lvt_etm_store_offers_mobile_html(
    array $storeData,
    array $extendedPrices,
    int $minOrderQuantity,
    int $productId,
    array $arResult
): string {
    $basePrices = $extendedPrices;
    $basePriceValue = (float) ($basePrices[0]['PRICE'] ?? 0);
    ob_start();
    include $_SERVER['DOCUMENT_ROOT'] . '/include/blocks/store_prices_mobile.php';
    return (string) ob_get_clean();
}
