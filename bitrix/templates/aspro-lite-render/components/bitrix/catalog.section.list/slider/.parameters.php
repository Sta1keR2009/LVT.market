<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
use Bitrix\Main\Loader,
	Aspro\Lite\Functions\ExtComponentParameter;

ExtComponentParameter::init(__DIR__, []);

$arFromTheme = $arTmpConfig = [];
/* check for custom option */
if (isset($_REQUEST['src_path'])) {
	$_SESSION['src_path_component'] = $_REQUEST['src_path'];
}
if (strpos($_SESSION['src_path_component'], 'custom') === false) {
	$arFromTheme = ["FROM_THEME" => GetMessage("ASPRO__SELECT_PARAM__FROM_THEME")];
}
ExtComponentParameter::addSelectParameter('IMAGES', [
	'PARENT' => ExtComponentParameter::PARENT_GROUP_ADDITIONAL,
	"VALUES" => $arFromTheme + [
		'ICONS' => GetMessage('ASPRO__SELECT_PARAM__ICONS'),
		'PICTURES' => GetMessage('ASPRO__SELECT_PARAM__PICTURES'),
	],
	"DEFAULT" => "PICTURES",
	'SORT' => 999
]);

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
	'IMAGE_ON_FON' => array(
		'NAME' => GetMessage('T_IMAGE_ON_FON'),
		'TYPE' => 'LIST',
		"VALUES" => $arFromTheme + [
			"Y" => GetMessage('ASPRO__SELECT_PARAM__YES'),
			"N" => GetMessage('ASPRO__SELECT_PARAM__NO'),
		],
		'DEFAULT' => 'Y',
	),
);

ExtComponentParameter::appendTo($arTemplateParameters);
?>
