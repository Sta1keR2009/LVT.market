<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arExtensions = ['swiper', 'catalog', 'catalog_block'];

if ($arParams['TYPE_SKU'] !== 'TYPE_2') {
	$arExtensions[] = 'select_offer_load';
}
if ($templateData['SHOW_DISCOUNT_COUNTER']) {
	$arExtensions[] = 'countdown';
}

TSolution\Extensions::init($arExtensions);

if (\Bitrix\Main\Loader::includeModule("aspro.lite")) {
	global $arRegion;
	$arRegion = TSolution\Regionality::getCurrentRegion();
}
