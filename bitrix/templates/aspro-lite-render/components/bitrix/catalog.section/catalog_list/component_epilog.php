<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$arExtensions = ['catalog', 'notice'];
if ($arParams['SHOW_RATING'] === 'Y') {
	$arExtensions[] = 'rating';
}
if ($templateData['HAS_CHARACTERISTICS']) {
    $arExtensions[] = 'chars';
}
if ($arParams['TYPE_SKU'] !== 'TYPE_2') {
	$arExtensions[] = 'select_offer_load';
}
if ($templateData['SHOW_DISCOUNT_COUNTER']) {
	$arExtensions[] = 'countdown';
}
TSolution\Extensions::init($arExtensions);
