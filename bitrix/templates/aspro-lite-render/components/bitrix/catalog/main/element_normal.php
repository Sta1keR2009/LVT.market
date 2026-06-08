<?php

global $arTheme, $APPLICATION;

// Убираем дублирующий h1 из page_title — на карточке товара h1 выводится в catalog.element
define('ASPRO_PAGE_WO_TITLE', true);

$APPLICATION->AddViewContent('right_block_class', 'catalog_page ');
$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH.'/css/animation/animate.min.css');
$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/jquery.history.js');

// cart
$bOrderViewBasket = ('Y' === trim($arTheme['ORDER_VIEW']['VALUE']));

if ($arSection) {
    $arInherite = TSolution::getSectionInheritedUF([
        'sectionId' => $arSection['ID'],
        'iblockId' => $arSection['IBLOCK_ID'],
        'select' => [
            'UF_ELEMENT_DETAIL',
            'UF_OFFERS_TYPE',
            'UF_GALLERY_SIZE',
        ],
        'filter' => [
            'GLOBAL_ACTIVE' => 'Y',
        ],
        'enums' => [
            'UF_ELEMENT_DETAIL',
            'UF_OFFERS_TYPE',
            'UF_GALLERY_SIZE',
        ],
    ]);
}

TSolution::CheckComponentTemplatePageBlocksParams($arParams, __DIR__);

$sViewElementTemplate = TSolution\Functions::getValueWithSection([
    'CODE' => 'CATALOG_PAGE_DETAIL',
    'SECTION_VALUE' => $arInherite['UF_ELEMENT_DETAIL'],
    'CUSTOM_VALUE' => ('FROM_MODULE' === $arParams['ELEMENT_TYPE_VIEW'] ? $arTheme['CATALOG_PAGE_DETAIL']['VALUE'] : $arParams['ELEMENT_TYPE_VIEW']),
]);
$typeSKU = TSolution\Functions::getValueWithSection([
    'CODE' => 'CATALOG_PAGE_DETAIL_SKU',
    'SECTION_VALUE' => $arInherite['UF_OFFERS_TYPE'],
]);
if (TSolution::isSaleMode() && Bitrix\Catalog\ProductTable::TYPE_SKU != $arElement['TYPE']) {
    $typeSKU = TSolution::GetBackParametrsValues(SITE_ID)['CATALOG_PAGE_DETAIL_SKU'];
}
$gallerySize = TSolution\Functions::getValueWithSection([
    'CODE' => 'CATALOG_PAGE_DETAIL_GALLERY_SIZE',
    'SECTION_VALUE' => $arInherite['UF_GALLERY_SIZE'],
]);

$arParams['OID'] = 0;
if ('TYPE_1' == $typeSKU) {
    $arParams['OID'] = TSolution\SKU::getOIDFromRequest($arElement);
}

// is need left block or sticky panel?
$APPLICATION->SetPageProperty('MENU', 'N');
$bWithStickyBlock = false;
if (false !== strpos($sViewElementTemplate, 'element_1')) {
    $bShowLeftBlock = false;
    $bWithStickyBlock = true;
} else {
    $bShowLeftBlock = 'Y' === $arTheme['LEFT_BLOCK_CATALOG_DETAIL']['VALUE'];
}
$bShowLeftBlock &= !defined('ERROR_404');
?>
<div class="main-wrapper flexbox flexbox--direction-row <?= $bShowLeftBlock || $bWithStickyBlock ? '' : 'catalog-maxwidth'; ?>">
	<div class="section-content-wrapper flex-1 <?= $bShowLeftBlock ? 'with-leftblock' : ''; ?>">
		<?TSolution::AddMeta(
		    [
		        'og:description' => $arElement['PREVIEW_TEXT'],
		        'og:image' => (($arElement['PREVIEW_PICTURE'] || $arElement['DETAIL_PICTURE']) ? CFile::GetPath($arElement['PREVIEW_PICTURE'] ? $arElement['PREVIEW_PICTURE'] : $arElement['DETAIL_PICTURE']) : false),
		    ]
		); ?>

		<?if ('Y' == $arParams['AJAX_MODE'] && false !== strpos($_SERVER['REQUEST_URI'], 'bxajaxid')) { ?>
			<script type="text/javascript">
				setStatusButton();
			</script>
		<?}?>

		<div class="product-container detail <?= $sViewElementTemplate; ?> clearfix" itemscope itemtype="http://schema.org/Product">
			<div class="catalog-detail js-popup-block flexbox flexbox--direction-row">
				<div class="catalog-detail__item flex-1">
					<?php
		            // cross sales for product
		            global $arCrossItems;
$oCrossSales = new Aspro\Lite\CrossSales($arElement['ID'], $arParams);
$arRules = $oCrossSales->getRules();
$arCrossItems = [];
$bUseAssociated = $bUseExpandables = false;

// similar goods from cross sales
if ($arRules['ASSOCIATED']) {
    $arCrossItems['ASSOCIATED'] = $oCrossSales->getItems('ASSOCIATED');
    if (!empty($arCrossItems['ASSOCIATED'])) {
        $bUseAssociated = true;
    }
}

// accessories goods from cross sales
if ($arRules['EXPANDABLES']) {
    $arCrossItems['EXPANDABLES'] = $oCrossSales->getItems('EXPANDABLES');
    if (!empty($arCrossItems['EXPANDABLES'])) {
        $bUseExpandables = true;
    }
}
?>
					<?@include_once 'page_blocks/'.$sViewElementTemplate.'.php'; ?>
				</div>
			</div>
		</div>

        <?TSolution::checkBreadcrumbsChain($arParams, $arSection, $arElement);?>

		<?php
		$lvtBackUrl = ((isset($arSection) && $arSection) ? $arSection['SECTION_PAGE_URL'] : $arResult['FOLDER'].$arResult['URL_TEMPLATES']['news']);
		$APPLICATION->SetPageProperty('lvt_show_back_link', 'Y');
		$APPLICATION->SetPageProperty('lvt_back_link_url', $lvtBackUrl);
		$APPLICATION->SetPageProperty('lvt_back_link_text', ($arParams['T_PREV_LINK'] ? $arParams['T_PREV_LINK'] : GetMessage('BACK_LINK')));
		?>
	</div>
	<?if ($bShowLeftBlock) { ?>
		<?TSolution::ShowPageType('left_block'); ?>
	<?}?>
</div>
