<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$arTmpConfig = $arFromTheme = [];
/* check for custom option */
if (isset($_REQUEST['src_path'])) {
	$_SESSION['src_path_component'] = $_REQUEST['src_path'];
}
if (strpos($_SESSION['src_path_component'], 'custom') === false) {
	$arTmpConfig = ["ADDITIONAL_VALUES" => "Y"];
	$arFromTheme = ["FROM_THEME" => GetMessage("V_FROM_THEME")];
}

$arTemplateParameters = array(
	'REGION' => array(
		'NAME' => GetMessage('REGION'),
		'TYPE' => 'STRING',
		'DEFAULT' => '={$arRegion}',
	),
);

$arTemplateParameters += array(
	"IMAGE" => Array(
		"NAME" => GetMessage("T_IMAGE"),
		"TYPE" => "LIST",
		"VALUES" => array_merge(
			$arFromTheme,
			[
				"Y" => GetMessage("V_YES"),
				"N" => GetMessage("V_NO"),
			],
		),
		"DEFAULT" => "N",
	),
	"BORDERED" => Array(
		"NAME" => GetMessage("T_BORDERED"),
		"TYPE" => "LIST",
		"VALUES" => array_merge(
			$arFromTheme,
			[
				"Y" => GetMessage("V_YES"),
				"N" => GetMessage("V_NO"),
			],
		),
		"DEFAULT" => "Y",
	),
);