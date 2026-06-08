<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}
$this->setFrameMode(true);

use Bitrix\Main\Loader;

Loader::includeModule('iblock');

global $arTheme, $NextSectionID, $arRegion;
$arSection = $arElement = [];
$bFastViewMode = (isset($_REQUEST['FAST_VIEW']) && $_REQUEST['FAST_VIEW'] == 'Y');
$bReviewsSort = (isset($_REQUEST['reviews_sort']) && $_REQUEST['reviews_sort'] == 'Y');
$arExtensions = ['catalog', 'hover_block'];

$_SESSION['BLOG_MAX_IMAGE_SIZE'] = $arParams['MAX_IMAGE_SIZE'] ?? 0.5;

if ($arResult['VARIABLES']['ELEMENT_ID'] > 0) {
    $arElementFilter = ['IBLOCK_ID' => $arParams['IBLOCK_ID'], 'ID' => $arResult['VARIABLES']['ELEMENT_ID']];
} elseif (strlen(trim($arResult['VARIABLES']['ELEMENT_CODE'])) > 0) {
    $arElementFilter = ['IBLOCK_ID' => $arParams['IBLOCK_ID'], '=CODE' => $arResult['VARIABLES']['ELEMENT_CODE']];
}

if ($arParams['SHOW_DEACTIVATED'] !== 'Y') {
    $arElementFilter['ACTIVE'] = 'Y';
}

if ($GLOBALS[$arParams['FILTER_NAME']]) {
    $arElementFilter = array_merge($arElementFilter, $GLOBALS[$arParams['FILTER_NAME']]);
}

$arElement = TSolution\Cache::CIBLockElement_GetList(['CACHE' => ['MULTI' => 'N', 'TAG' => TSolution\Cache::GetIBlockCacheTag($arParams['IBLOCK_ID'])]], TSolution::makeElementFilterInRegion($arElementFilter), false, false, ['ID', 'IBLOCK_ID', 'IBLOCK_SECTION_ID', 'NAME', 'PREVIEW_TEXT', 'PREVIEW_PICTURE', 'DETAIL_PICTURE', 'TYPE']);

if (!$arElement) {
    Bitrix\Iblock\Component\Tools::process404(
        '', $arParams['SET_STATUS_404'] === 'Y', $arParams['SET_STATUS_404'] === 'Y', $arParams['SHOW_404'] === 'Y', $arParams['FILE_404']
    );
}

if ($arElement['IBLOCK_SECTION_ID']) {
    $sid = ((isset($arElement['IBLOCK_SECTION_ID_SELECTED']) && $arElement['IBLOCK_SECTION_ID_SELECTED']) ? $arElement['IBLOCK_SECTION_ID_SELECTED'] : $arElement['IBLOCK_SECTION_ID']);
    $arSection = TSolution\Cache::CIBlockSection_GetList(['CACHE' => ['MULTI' => 'N', 'TAG' => TSolution\Cache::GetIBlockCacheTag($arParams['IBLOCK_ID'])]], ['GLOBAL_ACTIVE' => 'Y', 'ID' => $sid, 'IBLOCK_ID' => $arElement['IBLOCK_ID']], false, ['ID', 'IBLOCK_ID', 'NAME', 'IBLOCK_SECTION_ID', 'SECTION_PAGE_URL', 'DEPTH_LEVEL', 'LEFT_MARGIN', 'RIGHT_MARGIN']);

    $NextSectionID = $arSection['ID'];
}

// hide compare link from module options
$arParams['USE_COMPARE'] = 'N';

$arParams['SHOW_ONE_CLINK_BUY'] = $arTheme['SHOW_ONE_CLICK_BUY']['VALUE'];

// grupper
$arParams['GRUPPER_PROPS'] = $arTheme['GRUPPER_PROPS']['VALUE'];
if ($arTheme['GRUPPER_PROPS']['VALUE'] != 'NOT') {
    $arParams['PROPERTIES_DISPLAY_TYPE'] = 'TABLE';

    if ($arParams['GRUPPER_PROPS'] == 'GRUPPER' && !Loader::includeModule('redsign.grupper')) {
        $arParams['GRUPPER_PROPS'] = 'NOT';
    }
    if ($arParams['GRUPPER_PROPS'] == 'WEBDEBUG' && !Loader::includeModule('webdebug.utilities')) {
        $arParams['GRUPPER_PROPS'] = 'NOT';
    }
    if ($arParams['GRUPPER_PROPS'] == 'YENISITE_GRUPPER' && !Loader::includeModule('yenisite.infoblockpropsplus')) {
        $arParams['GRUPPER_PROPS'] = 'NOT';
    }
}

$arParams['PRICE_CODE'] = explode(',', TSolution::GetFrontParametrValue('PRICES_TYPE'));
$arParams['STORES'] = explode(',', TSolution::GetFrontParametrValue('STORES'));
if ($arRegion) {
    if ($arRegion['LIST_PRICES']) {
        $arParams['PRICE_CODE'] = array_keys($arRegion['LIST_PRICES']);
    }
    if ($arRegion['LIST_STORES']) {
        $arParams['STORES'] = $arRegion['LIST_STORES'];
    }
}?>
<?php
// set params for props from module
TSolution\Functions::replacePropsParams($arParams);
if (!$arParams['DETAIL_PROPERTY_CODE']) {
    $arParams['DETAIL_PROPERTY_CODE'] = $arParams['LIST_PROPERTY_CODE'];
}

$arParams['DETAIL_OFFERS_PROPERTY_CODE'] = is_array($arParams['DETAIL_OFFERS_PROPERTY_CODE']) ? $arParams['DETAIL_OFFERS_PROPERTY_CODE'] : [];

if (!TSolution::isSaleMode()) {
    if (!in_array('PRICE', $arParams['DETAIL_OFFERS_PROPERTY_CODE'])) {
        $arParams['DETAIL_OFFERS_PROPERTY_CODE'][] = 'PRICE';
    }
}

$arParams['DETAIL_OFFERS_PROPERTY_CODE'] = array_filter($arParams['DETAIL_OFFERS_PROPERTY_CODE']);
$arParams['SKU_PROPERTY_CODE'] = $arParams['DETAIL_OFFERS_PROPERTY_CODE'];
$arParams['SHOW_RATING'] = Loader::includeModule('blog') ? TSolution::GetFrontParametrValue('SHOW_RATING') : false;
$arParams['CONVERT_CURRENCY'] = TSolution::GetFrontParametrValue('CONVERT_CURRENCY');
$arParams['CURRENCY_ID'] = TSolution::GetFrontParametrValue('CURRENCY_ID');
$arParams['PRICE_VAT_INCLUDE'] = TSolution::GetFrontParametrValue('PRICE_VAT_INCLUDE');
$arParams['CALCULATE_DELIVERY'] = TSolution::isSaleMode() ? TSolution::GetFrontParametrValue('CALCULATE_DELIVERY') : 'N';

if (TSolution\Product\Basket::isShopWindowMode()) {
    $arParams['CALCULATE_DELIVERY'] = 'N';
}

if ($bFastViewMode) {
    include_once 'element_fast_view.php';
} elseif ($bReviewsSort) {
    include_once 'element_reviews.php';
} else {
    include_once 'element_normal.php';
}
?>
<!-- noindex -->
<template class="props-template">
    <?TSolution\Functions::showBlockHtml([
        'FILE' => 'catalog/props/list.php',
        'PARAMS' => [
            'ITEM_CLASSES' => 'visible-by-block-presence__condition',
        ]
    ]);?>
</template>
<!-- /noindex -->
<?php TSolution\Extensions::init($arExtensions); ?>
