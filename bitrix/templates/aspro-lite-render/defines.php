<?php

global $arRegion, $arSite, $bActiveTheme, $isMenu, $is404, $isForm, $isBlog, $isCabinet, $isIndex, $isCatalog, $isBasket, $isOrder;

global $sMenuContent;
$sMenuContent = '';

$arSite = CSite::GetByID(SITE_ID)->Fetch();

$bActiveTheme = ($arTheme['THEME_SWITCHER']['VALUE'] == 'Y');
$isMenu = ($APPLICATION->GetProperty('MENU') !== 'N' ? true : false);
$is404 = defined('ERROR_404') && ERROR_404 === 'Y';

$isForm = TSolution::IsFormPage();
$isBlog = TSolution::IsBlogPage();
$isCabinet = TSolution::isPersonalPage();
$isIndex = TSolution::IsMainPage();
$isCatalog = TSolution::IsCatalogPage();
$isBasket = TSolution::IsBasketPage();
$isOrder = TSolution::IsOrderPage();

$GLOBALS['arrPopularSections'] = ['UF_POPULAR' => 1];
$GLOBALS['arFrontFilter'] = ['PROPERTY_SHOW_ON_INDEX_PAGE_VALUE' => 'Y'];
$GLOBALS['arFilterLeftBlock'] = ['PROPERTY_SHOW_ON_LEFT_BLOCK_VALUE' => 'Y'];
$GLOBALS['arFilterBestItem'] = ['PROPERTY_BEST_ITEM_VALUE' => 'Y'];

if ($isIndex) {
    $GLOBALS['arRegionLinkFront'] = [
        'PROPERTY_SHOW_ON_INDEX_PAGE_VALUE' => 'Y',
    ];
}

if (
    $arRegion
    && $arTheme['USE_REGIONALITY']['DEPENDENT_PARAMS']['REGIONALITY_FILTER_ITEM']['VALUE'] == 'Y'
) {
    $GLOBALS['arRegionLink'] = [
        'PROPERTY_LINK_REGION' => $arRegion['ID'],
    ];

    if ($isIndex) {
        $GLOBALS['arRegionLinkFront']['PROPERTY_LINK_REGION'] = $arRegion['ID'];
        TSolution::makeSectionFilterInRegion($GLOBALS['arrPopularSections']);
    }
}

// Выбранный город из Sale Locations (кука выставляется local/api/apply_sale_city.php)
if (!empty($arRegion) && is_array($arRegion) && !empty($_COOKIE['lvt_display_city'])) {
    $lvtDisp = rawurldecode((string)$_COOKIE['lvt_display_city']);
    if ($lvtDisp !== '') {
        $arRegion['NAME'] = $lvtDisp;
    }
}
