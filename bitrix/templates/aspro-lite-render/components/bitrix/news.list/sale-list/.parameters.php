<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$arFromTheme = $arTmpConfig = [];
/* check for custom option */
if (isset($_REQUEST['src_path'])) {
	$_SESSION['src_path_component'] = $_REQUEST['src_path'];
}
if (strpos($_SESSION['src_path_component'], 'custom') === false) {
	$arFromTheme = ["FROM_THEME" => GetMessage("V_FROM_THEME")];
}

$arTemplateParameters = array(
	'TITLE' => array(
		'NAME' => GetMessage('T_TITLE'),
		'TYPE' => 'STRING',
		'DEFAULT' => GetMessage('V_TITLE'),
	),
	'RIGHT_TITLE' => array(
		'NAME' => GetMessage('T_RIGHT_TITLE'),
		'TYPE' => 'STRING',
		'DEFAULT' => GetMessage('V_RIGHT_TITLE'),
	),
	'RIGHT_LINK' => array(
		'NAME' => GetMessage('T_RIGHT_LINK'),
		'TYPE' => 'STRING',
		'DEFAULT' => 'company/reviews/',
	),
	"ELEMENTS_ROW" => Array(
		"NAME" => GetMessage("T_ELEMENTS_ROW"),
		"TYPE" => "LIST",
		"VALUES" => $arFromTheme + [
			3 => 3,
			4 => 4,
		],
		"DEFAULT" => "1",
	),
	'MAXWIDTH_WRAP' => array(
		'NAME' => GetMessage('T_MAXWIDTH_WRAP'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'Y',
	),
);
