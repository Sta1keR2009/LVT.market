<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc,
Aspro\Lite\Functions\ExtComponentParameter;

/* get component template pages & params array */
CBitrixComponent::includeComponentClass('bitrix:catalog.section');

/* get component template pages & params array */
ExtComponentParameter::init(__DIR__, []);
ExtComponentParameter::addBaseParameters();

$arTemplateParameters = array(
	'SHOW_SECTION_PREVIEW_DESCRIPTION' => [
		'NAME' => Loc::getMessage('SHOW_SECTION_PREVIEW_DESCRIPTION'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'Y',
	],
);

ExtComponentParameter::appendTo($arTemplateParameters);
?>