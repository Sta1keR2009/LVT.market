<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$iblockId = (int) ($arResult['IBLOCK_ID'] ?? 0);
$elementId = (int) ($arResult['ID'] ?? 0);
if ($iblockId !== 41 || $elementId <= 0) {
    return;
}

$lvtRequestPath = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
if (strpos($lvtRequestPath, '/katalog/') !== 0) {
    return;
}

$lvtEtmOffersSsr = null;
$lvtEtmOffersCacheFile = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/LvtEtmOffersRenderCache.php';
if (is_file($lvtEtmOffersCacheFile)) {
    require_once $lvtEtmOffersCacheFile;
    $lvtEtmOffersSsr = LvtEtmOffersRenderCache::get($elementId, 'desktop');
}
?>
<div class="detail-block ordered-block lvt-etm-stores-under-desc">
    <h2 class="store-prices__title-new">Наличие и цены по складам:</h2>
    <link rel="stylesheet" href="/local/css/getchips_offers_card.css?v=14">
    <link rel="stylesheet" href="/local/css/lvt_etm_store_offers.css?v=4">
    <?php if (!empty($lvtEtmOffersSsr['html'])): ?>
    <script type="application/json" id="lvt-etm-offers-ssr-json"><?= \Bitrix\Main\Web\Json::encode($lvtEtmOffersSsr, JSON_UNESCAPED_UNICODE) ?></script>
    <?php endif; ?>
    <div class="store-prices-block lvt-etm-store-offers"
         id="lvt-etm-store-offers-root"
         data-element-id="<?=$elementId?>"
         data-layout="desktop"
         data-min-order-quantity="1"<?= !empty($lvtEtmOffersSsr['html']) ? ' data-ssr-ready="Y"' : '' ?>>
        <div class="lvt-etm-store-offers__loading" role="status" aria-live="polite"<?= !empty($lvtEtmOffersSsr['html']) ? ' style="display:none"' : '' ?>>
            <span class="lvt-etm-store-offers__spinner" aria-hidden="true"></span>
            <span>Загрузка цен и остатков ETM...</span>
        </div>
        <div class="lvt-etm-store-offers__content">
            <?php if (!empty($lvtEtmOffersSsr['html'])): ?>
                <?= $lvtEtmOffersSsr['html'] ?>
            <?php else: ?>
            <div class="getchips-offers__table-wrap store-offers-table-wrap">
                <table class="getchips-offers__table store-offers-table">
                    <tbody>
                    <tr><td colspan="6"><div class="lvt-etm-store-offers__skeleton-line"></div></td></tr>
                    <tr><td colspan="6"><div class="lvt-etm-store-offers__skeleton-line"></div></td></tr>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="/local/js/getchips_offers_card.js?v=42"></script>
    <script src="/local/js/lvt_etm_store_offers.js?v=7"></script>
    <?php if (!empty($lvtEtmOffersSsr['html'])): ?>
    <script>
    (function () {
        var jsonNode = document.getElementById('lvt-etm-offers-ssr-json');
        if (!jsonNode || !jsonNode.textContent) {
            return;
        }
        try {
            var payload = JSON.parse(jsonNode.textContent);
            if (window.LvtEtmStoreOffers && typeof window.LvtEtmStoreOffers.syncFromPayload === 'function') {
                window.LvtEtmStoreOffers.syncFromPayload(payload);
            } else if (payload && payload.ok) {
                var qtyNode = document.getElementById('lvt-catalog-available-qty-sum');
                var totalQty = parseInt(payload.totalQty || '0', 10) || 0;
                if (qtyNode) {
                    qtyNode.textContent = String(totalQty);
                }
                var priceNode = document.querySelector('.js-lvt-etm-card-price');
                var priceRub = parseFloat(payload.priceRub || '0');
                if (priceNode && priceRub > 0) {
                    priceNode.textContent = 'от ' + priceRub.toFixed(2).replace('.', ',') + ' ₽';
                }
            }
        } catch (e) {}
    })();
    </script>
    <?php endif; ?>
</div>
