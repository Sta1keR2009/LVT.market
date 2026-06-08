<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$arExtensions = [];
if ($templateData['SHOW_DISCOUNT_COUNTER']) {
	$arExtensions[] = 'countdown';
}

TSolution\Extensions::init($arExtensions);