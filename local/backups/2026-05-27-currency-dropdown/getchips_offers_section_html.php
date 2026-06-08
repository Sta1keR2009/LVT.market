<?php

/**
 * Единый HTML-блок «Предложения поставщиков» для карточки и AJAX со списков.
 *
 * @param array{
 *   article?: string,
 *   pageCatalogElementId?: int,
 *   detailPageUrl?: string,
 *   productImageSrc?: string,
 *   withAssets?: bool,
 *   supplier_price_usd?: bool,
 *   skip_heading?: bool
 * } $ctx
 */
function getchips_offers_section_html(array $ctx): string
{
    if (!defined('B_PROLOG_INCLUDED')) {
        define('B_PROLOG_INCLUDED', true);
    }

    require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/GetchipsCatalogOffersHelper.php';

    $article = GetchipsCatalogOffersHelper::normalizeArticle((string) ($ctx['article'] ?? ''));
    if ($article === '' || mb_strlen($article) < 3) {
        return '';
    }

    $offers = GetchipsCatalogOffersHelper::fetchOffersCached($article, 1);
    if ($offers === []) {
        return '';
    }

    $offers = GetchipsCatalogOffersHelper::filterOffersExcludeLvtMarket($offers);
    if ($offers === []) {
        return '';
    }

    $offers = GetchipsCatalogOffersHelper::enrichOffersForGetchipsTable($offers);
    $stubProductId = (int) (defined('GETCHIPS_STUB_PRODUCT_ID') ? GETCHIPS_STUB_PRODUCT_ID : 0);
    $pageCatalogElementId = (int) ($ctx['pageCatalogElementId'] ?? 0);
    $stubConflictsWithCard = ($stubProductId > 0 && $pageCatalogElementId > 0 && $stubProductId === $pageCatalogElementId);
    $detailPageUrl = (string) ($ctx['detailPageUrl'] ?? '');
    $productImageSrc = GetchipsCatalogOffersHelper::toAbsoluteSiteUrl(trim((string) ($ctx['productImageSrc'] ?? '')));
    $withAssets = !isset($ctx['withAssets']) || (bool) $ctx['withAssets'];
    $supplierPriceUsd = !empty($ctx['supplier_price_usd']);
    $skipHeading = !empty($ctx['skip_heading']);

    $sessIdEsc = htmlspecialchars(bitrix_sessid(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $imgAttr = htmlspecialchars($productImageSrc, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $detailUrlAttr = htmlspecialchars($detailPageUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $offersCount = count($offers);
    $visibleGetchipsRows = 3;
    $usdToRub = GetchipsCatalogOffersHelper::getUsdToRubByCbr();
    if ($usdToRub <= 0) {
        $usdToRub = 74.8806;
    }
    $rateDate = GetchipsCatalogOffersHelper::getCbrRateDate();
    $rateDateLabel = htmlspecialchars($rateDate !== '' ? $rateDate : date('d.m.Y'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $usdRateLabel = htmlspecialchars(number_format($usdToRub, 2, ',', ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $defaultCurrency = $supplierPriceUsd ? 'USD' : 'RUB';
    $priceHeaderSwitchHtml = '<span class="getchips-currency-switch-wrap">'
        . '<span class="getchips-currency-switch-label">Цена:</span>'
        . '<span class="getchips-currency-switch js-getchips-currency-switch" data-display-currency="' . $defaultCurrency . '">'
        . '<button type="button" class="getchips-currency-switch__trigger">'
        . '<span class="js-getchips-currency-label">' . ($supplierPriceUsd ? '$ USD' : '₽ RUB') . '</span>'
        . '<span class="getchips-currency-switch__caret" aria-hidden="true"></span>'
        . '</button>'
        . '<span class="getchips-currency-switch__menu">'
        . '<button type="button" class="getchips-currency-switch__item' . ($supplierPriceUsd ? '' : ' is-active') . '" data-currency="RUB">🇷🇺 ₽ RUB</button>'
        . '<button type="button" class="getchips-currency-switch__item' . ($supplierPriceUsd ? ' is-active' : '') . '" data-currency="USD">🇺🇸 $ USD</button>'
        . '</span>'
        . '</span>'
        . '<span class="getchips-currency-rate js-getchips-cbr-rate" data-rate-date="' . $rateDateLabel . '">USD ' . $usdRateLabel . ' ₽</span>'
        . '<span class="getchips-currency-alert js-getchips-usd-alert" data-notice="Курс ЦБ получен: ' . $rateDateLabel . '.">!</span>'
        . '<span class="getchips-currency-alert js-getchips-rub-alert" data-notice="Цена в рублях ориентировочная: если курс ЦБ изменится более чем на 2%, итоговую сумму уточним по актуальному курсу.">!</span>'
        . '</span>';

    ob_start();
    ?>
    <span class="hidden" data-bitrix-sessid="<?= $sessIdEsc ?>" aria-hidden="true"></span>
    <div class="detail-block ordered-block getchips-offers-section" data-page-catalog-element-id="<?= $pageCatalogElementId ?>"
         data-product-image-src="<?= $imgAttr ?>"
         data-context-detail-url="<?= $detailUrlAttr ?>">
        <?php if (!$skipHeading) { ?>
        <h3 class="getchips-offers__title">Предложения поставщиков (Getchips)</h3>
        <?php } ?>
        <p class="getchips-offers__note">По артикулу <strong><?= htmlspecialchars($article, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong>.
            <?php if ($supplierPriceUsd) { ?>
                В колонке «Цена» — значение в валюте поставщика (USD/EUR) из ответа API; пересчёт в ₽ для корзины сохраняется по курсам магазина.
            <?php } else { ?>
            Цены в ₽ по курсам валют из настроек магазина (USD, EUR). Ступени цен — из ответа Getchips (<code>priceBreak</code>).
            <?php } ?></p>
        <?php if ($stubConflictsWithCard) { ?>
            <p class="getchips-offers__warn">Товар-заглушка для корзины совпадает с этой карточкой (ID <?= $pageCatalogElementId ?>): действуют остатки и минимум заказа каталога.
                Создайте отдельный тех. товар в каталоге и укажите его ID в <code>GETCHIPS_STUB_PRODUCT_ID</code> в <code>local/php_interface/getchips_site.php</code>.</p>
        <?php } ?>
        <div class="getchips-offers__table-wrap">
            <table class="getchips-offers__table js-getchips-offers-table<?= $offersCount > $visibleGetchipsRows ? ' getchips-offers__table--collapsible' : '' ?>" data-usd-to-rub="<?=htmlspecialchars((string)$usdToRub, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')?>" data-display-currency="<?= $supplierPriceUsd ? 'USD' : 'RUB' ?>">
                <thead>
                <tr>
                    <th>
                        <span class="getchips-offers__th-sort">
                            Наименование
                            <button type="button" class="getchips-offers__sort-btn js-getchips-sort" data-sort="supplier" data-dir="asc">↑</button>
                            <button type="button" class="getchips-offers__sort-btn js-getchips-sort" data-sort="supplier" data-dir="desc">↓</button>
                        </span>
                    </th>
                    <th>Бренд</th>
                    <th>
                        <span class="getchips-offers__th-sort">
                            <?= $priceHeaderSwitchHtml ?>
                            <button type="button" class="getchips-offers__sort-btn js-getchips-sort" data-sort="price" data-dir="asc" title="По цене ↑">↑</button>
                            <button type="button" class="getchips-offers__sort-btn js-getchips-sort" data-sort="price" data-dir="desc" title="По цене ↓">↓</button>
                        </span>
                    </th>
                    <th>Доступно, шт.</th>
                    <th>
                        <span class="getchips-offers__th-sort">
                            Срок
                            <button type="button" class="getchips-offers__sort-btn js-getchips-sort" data-sort="lead" data-dir="asc" title="По сроку ↑">↑</button>
                            <button type="button" class="getchips-offers__sort-btn js-getchips-sort" data-sort="lead" data-dir="desc" title="По сроку ↓">↓</button>
                        </span>
                    </th>
                    <th>Кол-во</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $offerIndex = 0;
                foreach ($offers as $row) {
                    $supplier = (string) ($row['supplier'] ?? '');
                    $part = (string) ($row['part_number'] ?? $article);
                    $provider = (string) ($row['provider'] ?? '');
                    $leadDays = isset($row['lead_time_days']) && $row['lead_time_days'] !== '' && $row['lead_time_days'] !== null
                        ? (int) $row['lead_time_days']
                        : 0;
                    $leadLabel = (string) ($row['lead_weeks_label'] ?? '—');
                    $url = (string) ($row['url'] ?? '');
                    $srcCcy = strtoupper(trim((string) ($row['currency'] ?? '')));
                    $srcPrice = $row['unit_price'] ?? null;
                    $tiersRub = isset($row['price_tiers_rub']) && is_array($row['price_tiers_rub']) ? $row['price_tiers_rub'] : [];
                    $tiersSrc = isset($row['price_tiers_source']) && is_array($row['price_tiers_source']) ? $row['price_tiers_source'] : [];
                    if ($supplierPriceUsd && $tiersSrc !== []) {
                        $priceHtml = GetchipsCatalogOffersHelper::formatPriceTiersSourceHtml(
                            $tiersSrc,
                            $srcCcy !== '' ? $srcCcy : 'USD'
                        );
                    } elseif ($supplierPriceUsd && $srcPrice !== null && $srcPrice !== '' && is_numeric($srcPrice)) {
                        $priceHtml = '<span class="getchips-offers__usd">' . htmlspecialchars(number_format((float) $srcPrice, 4, '.', ' '), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                            . ' ' . htmlspecialchars($srcCcy !== '' ? $srcCcy : 'USD', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span>';
                        $rubSecondary = GetchipsCatalogOffersHelper::formatPriceTiersHtml($tiersRub);
                        if ($rubSecondary !== '—') {
                            $priceHtml .= '<div class="getchips-offers__rub-hint">' . $rubSecondary . '</div>';
                        }
                    } else {
                        $priceHtml = GetchipsCatalogOffersHelper::formatPriceTiersHtml($tiersRub);
                    }
                    $stock = $row['stock'] ?? null;
                    $stockDisp = ($stock !== null && $stock !== '') ? (string) (int) $stock : '—';
                    $stockMax = ($stock !== null && $stock !== '' && is_numeric($stock)) ? max(0, (int)$stock) : 0;
                    $firstRub = (float) ($row['price_sort_value'] ?? 0);
                    $canAdd = $stubProductId > 0 && !$stubConflictsWithCard && $firstRub > 0;
                    $hideSource = $url === '' || GetchipsCatalogOffersHelper::isOfferUrlSameDetailPath($url, $detailPageUrl);
                    $bc = isset($row['brand_card']) && is_array($row['brand_card']) ? $row['brand_card'] : ['NAME' => '', 'URL' => '', 'IMG_SRC' => ''];
                    $brandApi = trim((string) ($row['brand'] ?? ''));
                    $brandTitle = ($bc['NAME'] ?? '') !== '' ? (string) $bc['NAME'] : $brandApi;
                    $brandUrl = (string) ($bc['URL'] ?? '');
                    $brandImg = (string) ($bc['IMG_SRC'] ?? '');
                    $tiersJson = htmlspecialchars(
                        json_encode($tiersRub, JSON_UNESCAPED_UNICODE),
                        ENT_QUOTES | ENT_SUBSTITUTE,
                        'UTF-8'
                    );
                    $minOrder = (int) ($row['min_order'] ?? 1);
                    $orderStep = (int) ($row['order_step'] ?? 1);
                    $packNorm = (int) ($row['pack_norm'] ?? 1);
                    $brandForPost = $brandTitle !== '' ? $brandTitle : $brandApi;
                    $leadSort = (float) ($row['lead_sort_value'] ?? 999999);
                    $rowExtraClass = $offerIndex >= $visibleGetchipsRows ? ' getchips-offers__row--extra' : '';
                    ?>
                    <tr class="js-getchips-offer-row<?= $rowExtraClass ?>"
                        data-sort-lead="<?= htmlspecialchars((string) $leadSort, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                        data-sort-price="<?= htmlspecialchars((string) $firstRub, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                        data-tiers-rub-json="<?= $tiersJson ?>"
                        data-min-order="<?= (int) $minOrder ?>"
                        data-order-step="<?= (int) $orderStep ?>"
                        data-available-qty="<?= (int)$stockMax ?>">
                        <td class="getchips-offers__name-cell">
                            <div class="getchips-offers__part"><?= htmlspecialchars($part, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
                            <div class="getchips-offers__supplier"><?= htmlspecialchars($supplier, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
                        </td>
                        <td class="getchips-offers__brand-cell">
                            <?php if ($brandUrl !== '' && $brandTitle !== '') { ?>
                                <a class="getchips-offers__brand-link" href="<?= htmlspecialchars($brandUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                                    <?php if ($brandImg !== '') { ?>
                                        <span class="getchips-offers__brand-logo-wrap">
                                            <img class="getchips-offers__brand-logo" src="<?= htmlspecialchars($brandImg, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" alt="" loading="lazy">
                                        </span>
                                    <?php } ?>
                                    <span class="getchips-offers__brand-name"><?= htmlspecialchars($brandTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                                </a>
                            <?php } else { ?>
                                <span class="getchips-offers__brand-fallback">
                                    <?php if ($brandImg !== '') { ?>
                                        <img class="getchips-offers__brand-logo" src="<?= htmlspecialchars($brandImg, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" alt="" loading="lazy">
                                    <?php } ?>
                                    <span class="getchips-offers__brand-name"><?= htmlspecialchars($brandApi !== '' ? $brandApi : '—', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                                </span>
                            <?php } ?>
                        </td>
                        <td class="getchips-offers__price-cell"><?= $priceHtml ?></td>
                        <td><?= htmlspecialchars($stockDisp, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($leadLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></td>
                        <td class="getchips-offers__qty-cell">
                            <div class="getchips-offers__qty-input-row">
                                <input type="number"
                                       class="getchips-offers__qty-input js-getchips-qty-input"
                                       value="<?= (int) $minOrder ?>"
                                       max="<?= (int)$stockMax ?>"
                                       step="<?= max(1, (int) $orderStep) ?>"
                                       inputmode="numeric"
                                       autocomplete="off"
                                       aria-label="Количество"
                                       data-min-order="<?= (int) $minOrder ?>"
                                       data-order-step="<?= (int) $orderStep ?>">
                                <?php if ($canAdd) { ?>
                                    <div class="store-add-to-cart-button getchips-offers__cart-btn-wrap">
                                        <button type="button"
                                                class="btn btn-default btn-sm js-getchips-add-basket getchips-offers__add-basket has-ripple"
                                                data-stub-product-id="<?= $stubProductId ?>"
                                                data-price-rub="<?= htmlspecialchars((string) $firstRub, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                                data-tiers-rub-json="<?= $tiersJson ?>"
                                                data-source-currency="<?= htmlspecialchars($srcCcy, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                                data-source-price="<?= htmlspecialchars($srcPrice !== null ? (string) $srcPrice : '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                                data-supplier="<?= htmlspecialchars($supplier, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                                data-part="<?= htmlspecialchars($part, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                                data-provider="<?= htmlspecialchars($provider, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                                data-url="<?= htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                                data-lead-days="<?= (int) $leadDays ?>"
                                                data-brand-display="<?= htmlspecialchars($brandForPost, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                                data-min-order="<?= (int) $minOrder ?>"
                                                data-order-step="<?= (int) $orderStep ?>"
                                        >В корзину</button>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="getchips-offers__qty-hint js-getchips-qty-hint" role="status" aria-live="polite"></div>
                            <div class="getchips-offers__qty-meta">
                                <span>MIN: <?= (int) $minOrder ?></span>
                                <span>Кратность: <?= (int) $orderStep ?></span>
                                <span>Норма уп.: <?= (int) $packNorm ?></span>
                                <span class="getchips-offers__row-total js-getchips-row-total"></span>
                            </div>
                            <?php if (!$canAdd) { ?>
                                <span class="getchips-offers__muted">—</span>
                            <?php } ?>
                            <?php if (!$hideSource) { ?>
                                <div><a class="getchips-offers__link" href="<?= htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
                                       target="_blank" rel="noopener noreferrer">Источник</a></div>
                            <?php } ?>
                        </td>
                    </tr>
                <?php
                    ++$offerIndex;
                } ?>
                </tbody>
            </table>
            <?php if ($offersCount > $visibleGetchipsRows) { ?>
                <button type="button"
                        class="getchips-offers__show-all js-getchips-show-all"
                        aria-expanded="false"
                        data-label-expand="Показать все"
                        data-label-collapse="Свернуть">
                    Показать все
                </button>
            <?php } ?>
        </div>
        <?php if ($withAssets) { ?>
            <link rel="stylesheet" href="/local/css/getchips_offers_card.css?v=12">
            <script src="/local/js/getchips_offers_card.js?v=25" defer></script>
        <?php } ?>
    </div>
    <?php

    return (string) ob_get_clean();
}

/**
 * Пустая таблица с заголовками (когда Getchips API не вернул строк, но есть склад/Promelec/Mouser).
 *
 * @param array{
 *   pageCatalogElementId?: int,
 *   detailPageUrl?: string,
 *   productImageSrc?: string,
 *   supplier_price_usd?: bool,
 *   article?: string
 * } $ctx
 */
function getchips_offers_table_shell_html(array $ctx): string
{
    require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/GetchipsCatalogOffersHelper.php';

    $pageCatalogElementId = (int) ($ctx['pageCatalogElementId'] ?? 0);
    $detailPageUrl = (string) ($ctx['detailPageUrl'] ?? '');
    $productImageSrc = GetchipsCatalogOffersHelper::toAbsoluteSiteUrl(trim((string) ($ctx['productImageSrc'] ?? '')));
    $supplierPriceUsd = !empty($ctx['supplier_price_usd']);
    $article = htmlspecialchars(trim((string) ($ctx['article'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    $sessIdEsc = htmlspecialchars(bitrix_sessid(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $imgAttr = htmlspecialchars($productImageSrc, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $detailUrlAttr = htmlspecialchars($detailPageUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $usdToRub = GetchipsCatalogOffersHelper::getUsdToRubByCbr();
    if ($usdToRub <= 0) {
        $usdToRub = 74.8806;
    }
    $rateDate = GetchipsCatalogOffersHelper::getCbrRateDate();
    $rateDateLabel = htmlspecialchars($rateDate !== '' ? $rateDate : date('d.m.Y'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $usdRateLabel = htmlspecialchars(number_format($usdToRub, 2, ',', ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $defaultCurrency = $supplierPriceUsd ? 'USD' : 'RUB';
    $priceHeaderSwitchHtml = '<span class="getchips-currency-switch-wrap">'
        . '<span class="getchips-currency-switch-label">Цена:</span>'
        . '<span class="getchips-currency-switch js-getchips-currency-switch" data-display-currency="' . $defaultCurrency . '">'
        . '<button type="button" class="getchips-currency-switch__trigger">'
        . '<span class="js-getchips-currency-label">' . ($supplierPriceUsd ? '$ USD' : '₽ RUB') . '</span>'
        . '<span class="getchips-currency-switch__caret" aria-hidden="true"></span>'
        . '</button>'
        . '<span class="getchips-currency-switch__menu">'
        . '<button type="button" class="getchips-currency-switch__item' . ($supplierPriceUsd ? '' : ' is-active') . '" data-currency="RUB">🇷🇺 ₽ RUB</button>'
        . '<button type="button" class="getchips-currency-switch__item' . ($supplierPriceUsd ? ' is-active' : '') . '" data-currency="USD">🇺🇸 $ USD</button>'
        . '</span>'
        . '</span>'
        . '<span class="getchips-currency-rate js-getchips-cbr-rate" data-rate-date="' . $rateDateLabel . '">USD ' . $usdRateLabel . ' ₽</span>'
        . '<span class="getchips-currency-alert js-getchips-usd-alert" data-notice="Курс ЦБ получен: ' . $rateDateLabel . '.">!</span>'
        . '<span class="getchips-currency-alert js-getchips-rub-alert" data-notice="Цена в рублях ориентировочная: если курс ЦБ изменится более чем на 2%, итоговую сумму уточним по актуальному курсу.">!</span>'
        . '</span>';

    $articleNote = $article !== '' ? '<p class="getchips-offers__note">По артикулу <strong>' . $article . '</strong>.</p>' : '';

    return '<span class="hidden" data-bitrix-sessid="' . $sessIdEsc . '" aria-hidden="true"></span>'
        . '<div class="detail-block ordered-block getchips-offers-section" data-page-catalog-element-id="' . $pageCatalogElementId . '"'
        . ' data-product-image-src="' . $imgAttr . '" data-context-detail-url="' . $detailUrlAttr . '">'
        . $articleNote
        . '<div class="getchips-offers__table-wrap">'
        . '<table class="getchips-offers__table js-getchips-offers-table" data-usd-to-rub="' . htmlspecialchars((string) $usdToRub, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" data-display-currency="' . $defaultCurrency . '">'
        . '<thead><tr>'
        . '<th><span class="getchips-offers__th-sort">Наименование'
        . '<button type="button" class="getchips-offers__sort-btn js-getchips-sort" data-sort="supplier" data-dir="asc">↑</button>'
        . '<button type="button" class="getchips-offers__sort-btn js-getchips-sort" data-sort="supplier" data-dir="desc">↓</button></span></th>'
        . '<th>Бренд</th>'
        . '<th><span class="getchips-offers__th-sort">' . $priceHeaderSwitchHtml
        . '<button type="button" class="getchips-offers__sort-btn js-getchips-sort" data-sort="price" data-dir="asc">↑</button>'
        . '<button type="button" class="getchips-offers__sort-btn js-getchips-sort" data-sort="price" data-dir="desc">↓</button></span></th>'
        . '<th>Доступно, шт.</th>'
        . '<th><span class="getchips-offers__th-sort">Срок'
        . '<button type="button" class="getchips-offers__sort-btn js-getchips-sort" data-sort="lead" data-dir="asc">↑</button>'
        . '<button type="button" class="getchips-offers__sort-btn js-getchips-sort" data-sort="lead" data-dir="desc">↓</button></span></th>'
        . '<th>Кол-во</th>'
        . '</tr></thead><tbody></tbody></table></div></div>';
}
