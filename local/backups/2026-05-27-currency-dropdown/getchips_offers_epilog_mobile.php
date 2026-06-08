<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/include/lvt_supplier_offers_section_html.php';

$elementId = (int)($arResult['ID'] ?? 0);
if ($elementId <= 0) {
    return;
}
?>
<link rel="stylesheet" href="/local/css/lvt_supplier_offers.css">
<link rel="stylesheet" href="/local/css/getchips_offers_card.css?v=15">
<link rel="stylesheet" href="/local/css/lvt_supplier_offers_responsive.css?v=4">
<script src="/local/js/lvt_supplier_offers_lazy.js?v=3"></script>
<script src="/local/js/getchips_offers_card.js?v=38" defer></script>
<section class="lvt-supplier-offers" id="lvt-supplier-offers" data-element-id="<?=$elementId?>">
<script>
(function() {
    var el = document.currentScript && document.currentScript.parentElement;
    if (!el || !window.LvtSupplierOffersLazy) {
        return;
    }
    window.LvtSupplierOffersLazy.markCachedSectionIfReady(el, '<?=$elementId?>');
})();
</script>
    <h2 class="lvt-supplier-offers__title">Предложения поставщиков</h2>
    <p class="lvt-supplier-offers__muted">Загрузка предложений поставщиков...</p>
    <div class="lvt-supplier-offers__content">
        <div class="lvt-supplier-skeleton">
            <table class="getchips-offers__table">
                <thead>
                <tr>
                    <th>Наименование</th><th>Бренд</th><th>Цена</th><th>Доступно, шт.</th><th>Срок</th><th>Кол-во</th>
                </tr>
                </thead>
                <tbody>
                <tr><td colspan="6"><div class="lvt-supplier-skeleton__line"></div></td></tr>
                <tr><td colspan="6"><div class="lvt-supplier-skeleton__line"></div></td></tr>
                <tr><td colspan="6"><div class="lvt-supplier-skeleton__line"></div></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</section>
<style>
.lvt-supplier-skeleton__line {
    height: 14px;
    border-radius: 8px;
    background: linear-gradient(90deg, #eef2f6 25%, #f7f9fb 50%, #eef2f6 75%);
    background-size: 220% 100%;
    animation: lvt-supplier-shimmer 1.2s ease-in-out infinite;
    margin: 8px 0;
}
@keyframes lvt-supplier-shimmer {
    0% { background-position: 100% 0; }
    100% { background-position: -100% 0; }
}
.lvt-supplier-offers--cached .lvt-supplier-offers__muted,
.lvt-supplier-offers--cached .lvt-supplier-skeleton {
    display: none !important;
}
.lvt-supplier-offers--cache-hidden {
    display: none !important;
}
</style>
<script>
(function() {
    var container = document.getElementById('lvt-supplier-offers');
    if (container && window.LvtSupplierOffersLazy) {
        window.LvtSupplierOffersLazy.init(container);
    }
})();
</script>
