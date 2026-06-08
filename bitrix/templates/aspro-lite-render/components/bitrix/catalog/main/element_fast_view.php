<?php
global $arTheme, $APPLICATION;

// $APPLICATION->ShowHeadScripts();
$APPLICATION->ShowAjaxHead();

// cart
$bOrderViewBasket = ('Y' === trim($arTheme['ORDER_VIEW']['VALUE']));

if ($arSection) {
    $arInherite = TSolution::getSectionInheritedUF([
        'sectionId' => $arSection['ID'],
        'iblockId' => $arSection['IBLOCK_ID'],
        'select' => [
            'UF_OFFERS_TYPE',
        ],
        'filter' => [
            'GLOBAL_ACTIVE' => 'Y',
        ],
        'enums' => [
            'UF_OFFERS_TYPE',
        ],
    ]);
}

$typeSKU = TSolution\Functions::getValueWithSection([
    'CODE' => 'CATALOG_PAGE_DETAIL_SKU',
    'SECTION_VALUE' => $arInherite['UF_OFFERS_TYPE'],
]);
if (TSolution::isSaleMode() && Bitrix\Catalog\ProductTable::TYPE_SKU != $arElement['TYPE']) {
    $typeSKU = TSolution::GetBackParametrsValues(SITE_ID)['CATALOG_PAGE_DETAIL_SKU'];
}

$arParams['OID'] = 0;
if ('TYPE_1' == $typeSKU) {
    $arParams['OID'] = TSolution\SKU::getOIDFromRequest($arElement);
}
?>
<div class="product-container detail clearfix1" itemscope itemtype="http://schema.org/Product">
	<div class="catalog-detail js-popup-block">
		<?@include_once 'page_blocks/'.$arTheme['USE_FAST_VIEW_PAGE_DETAIL']['VALUE'].'.php'; ?>
	</div>
</div>
<?php
if ($arRegion) {
    $arTagSeoMarks = [];
    foreach ($arRegion as $key => $value) {
        if (false !== strpos($key, 'PROPERTY_REGION_TAG') && false === strpos($key, '_VALUE_ID')) {
            $tag_name = str_replace(['PROPERTY_', '_VALUE'], '', $key);
            $arTagSeoMarks['#'.$tag_name.'#'] = $key;
        }
    }

    if ($arTagSeoMarks) {
        TSolution\Regionality::addSeoMarks($arTagSeoMarks);
    }
}

$arExtensions = ['fancybox', 'detail', 'swiper', 'swiper_events', 'gallery', 'video', 'catalog', 'rating'];
TSolution\Extensions::init($arExtensions);
?>
