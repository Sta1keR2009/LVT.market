<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main\Localization\Loc,
Aspro\Lite\Functions\ExtComponentParameter;

CBitrixComponent::includeComponentClass('bitrix:catalog.section');

/* get component template pages & params array */
ExtComponentParameter::init(__DIR__, []);
ExtComponentParameter::addBaseParameters(array(
	array(
		array('SECTION' => 'SECTION', 'OPTION' => 'BRANDS_PAGE'),
		'SECTION_ELEMENTS_TYPE_VIEW'
	),
));

$arTemplateParameters = array(
	'SHOW_SECTION_PREVIEW_DESCRIPTION' => array(
		'PARENT' => 'LIST_SETTINGS',
		'SORT' => 500,
		'NAME' => GetMessage('SHOW_SECTION_PREVIEW_DESCRIPTION'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'Y',
	),
);

ExtComponentParameter::appendTo($arTemplateParameters);