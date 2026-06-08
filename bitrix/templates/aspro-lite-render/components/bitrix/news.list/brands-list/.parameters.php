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
		'NAME' => GetMessage('TITLE'),
		'TYPE' => 'STRING',
		'DEFAULT' => GetMessage('TITLE_DEFAULT'),
	),
	'SUBTITLE' => array(
		'NAME' => GetMessage('T_OVER_TITLE_TEXT'),
		'TYPE' => 'STRING',
		'DEFAULT' => '',
	),
	"RIGHT_TITLE" => Array(
		"NAME" => GetMessage("TITLE_BLOCK_ALL_NAME"),
		"TYPE" => "STRING",
		"DEFAULT" => GetMessage("BLOCK_ALL_NAME"),
	),
	"RIGHT_LINK" => Array(
		"NAME" => GetMessage("ALL_URL_NAME"),
		"TYPE" => "STRING",
		"DEFAULT" => "company/partners/",
	),
	'SHOW_PREVIEW_TEXT' => array(
		'NAME' => GetMessage('T_SHOW_PREVIEW_TEXT'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'Y',
	),
	'SLIDER' => array(
		'NAME' => GetMessage('T_SLIDER'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
	),
	"BORDER" => Array(
		"NAME" => GetMessage("T_BORDER"),
		"TYPE" => "LIST",
		"VALUES" => $arFromTheme + [
			"Y" => GetMessage('T_BORDER_YES'),
			"N" => GetMessage('T_BORDER_NOT'),
		],
		"DEFAULT" => "Y",
	),
	'MAXWIDTH_WRAP' => array(
		'NAME' => GetMessage('T_MAXWIDTH_WRAP'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'Y',
	),
);
?>
