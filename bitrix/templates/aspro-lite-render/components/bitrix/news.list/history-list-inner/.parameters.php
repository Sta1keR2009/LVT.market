<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>
<?php
use \Bitrix\Main\Localization\Loc;

$arTemplateParameters = [
	'ITEMS_OFFSET' => [
		'PARENT' => 'LIST_SETTINGS',
		'NAME' => Loc::getMessage('ITEMS_OFFSET'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
	],
];