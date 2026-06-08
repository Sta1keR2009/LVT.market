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
    array(
		array('SECTION' => 'SECTION', 'OPTION' => 'BRANDS_DETAIL_PAGE'),
		'ELEMENT_TYPE_VIEW'
	),
));

$arTemplateParameters = array(
	'SECTION_ELEMENTS_TYPE_VIEW' => array (
    	'PARENT' => 'BASE',
    	'SORT' => 1,
    	'TYPE' => 'LIST',
    	'NAME' => GetMessage("T_SECTION_ELEMENTS_TYPE_VIEW"),
    	'VALUES' => array (
			"FROM_MODULE" => GetMessage("FROM_MODULE_PARAMS"),
			'list_elements_1' => 'list_elements_1',
		),
    	'DEFAULT' => 'FROM_MODULE',
		'REFRESH' => 'Y',
	),
  	'ELEMENT_TYPE_VIEW' => array (
    	'PARENT' => 'BASE',
    	'SORT' => 1,
    	'TYPE' => 'LIST',
    	'NAME' => GetMessage("T_ELEMENT_TYPE_VIEW"),
    	'VALUES' => array (
			"FROM_MODULE" => GetMessage("FROM_MODULE_PARAMS"),
			'element_1' => 'element_1',
            'element_2' => 'element_2',
		),
    	'DEFAULT' => 'FROM_MODULE',
        'REFRESH' => 'Y',
	),
);

if (strpos($arCurrentValues['SECTION_ELEMENTS_TYPE_VIEW'], 'list_elements_1') !== false) {

    $arTemplateParameters['ELEMENT_IN_ROW'] = array(
        'PARENT' => 'LIST_SETTINGS',
        'NAME' => GetMessage('SECTION-ELEMENTS_ELEMENTS_COUNT'),
        'TYPE' => 'LIST',
        'VALUES' => array(
            '2' => '2',
            '3' => '3',
            '4' => '4',
        ),
        'DEFAULT' => '4',
	);
    $arTemplateParameters['ITEMS_TYPE'] = array(
		'PARENT' => 'LIST_SETTINGS',
		'NAME' => GetMessage('ITEMS_TYPE'),
		'TYPE' => 'LIST',
		'VALUES' => array(
			'PHOTOS' => GetMessage('ITEMS_TYPE_PHOTOS'),
			'ALBUM' => GetMessage('ITEMS_TYPE_ALBUM'),
		),
		'DEFAULT' => 'ALBUM',
	);
};

ExtComponentParameter::appendTo($arTemplateParameters);
?>
