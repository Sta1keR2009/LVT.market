<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$arTemplateParameters = array(
	"TITLE" => Array(
		"NAME" => GetMessage("T_TITLE"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("BLOCK_NAME"),
	),
	'SHOW_PREVIEW_TEXT' => array(
		'NAME' => GetMessage('T_SHOW_PREVIEW_TEXT'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'Y',
		'REFRESH' => 'Y',
	),
);

$arTemplateParameters = array_merge(
	$arTemplateParameters,
	[
		"ALL_URL" => Array(
			"NAME" => GetMessage("T_ALL_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		'ELEMENT_IN_ROW' => array(
			'PARENT' => 'LIST_SETTINGS',
			'NAME' => GetMessage('T_SECTION-ELEMENTS_ELEMENTS_COUNT'),
			'TYPE' => 'LIST',
			'VALUES' => array(
				'FROM_MODULE' => GetMessage('FROM_MODULE_PARAMS'),
				'2' => GetMessage('2'),
				'3' => GetMessage('3'),
				'4' => GetMessage('4'),
			),
			'DEFAULT' => 'FROM_MODULE',
		),
		'ITEMS_TYPE' => array(
			'PARENT' => 'LIST_SETTINGS',
			'NAME' => GetMessage('T_ITEMS_TYPE'),
			'TYPE' => 'LIST',
			'VALUES' => array(
				'FROM_MODULE' => GetMessage('FROM_MODULE_PARAMS'),
				'PHOTOS' => GetMessage('ITEMS_TYPE_PHOTOS'),
				'ALBUM' => GetMessage('ITEMS_TYPE_ALBUM'),
			),
			'DEFAULT' => 'FROM_MODULE',
		),
	]
);
?>
