<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
$page = 1;

$arFromTheme = $arTmpConfig = [];
/* check for custom option */
if (isset($_REQUEST['src_path'])) {
	$_SESSION['src_path_component'] = $_REQUEST['src_path'];
}
if (strpos($_SESSION['src_path_component'], 'custom') === false) {
	$arFromTheme = ["FROM_THEME" => GetMessage("V_FROM_THEME")];
}

$arTemplateParameters = array(
	'USE_FILTER_ELEMENTS' => array(
		'NAME' => GetMessage('USE_FILTER_ELEMENTS'),
		'TYPE' => 'CHECKBOX',
		'DEFAULT' => 'N',
		'REFRESH' => 'Y',
	),	
	"IMAGE_POSITION" => Array(
		"NAME" => GetMessage("T_IMAGE_POSITION"),
		"TYPE" => "LIST",
		"VALUES" =>array_merge(
			$arFromTheme, [
				'LEFT' => GetMessage('V_IMAGE_LEFT'),
				'TOP' => GetMessage('V_IMAGE_TOP'),
			],
		),
		"REFRESH" => "Y",
		"DEFAULT" => "LEFT",
	),
	"ITEMS_COUNT" => Array(
		"NAME" => GetMessage("T_ITEMS_COUNT"),
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
if($arCurrentValues['USE_FILTER_ELEMENTS'] == 'Y')
{
	$arTemplateParameters['PAGE'] = array(
		'NAME' => GetMessage('PAGE_FILTER_TITLE'),
		'TYPE' => 'TEXT',
		'DEFAULT' => '={$APPLICATION->GetCurPage()}',
	);
}
?>
