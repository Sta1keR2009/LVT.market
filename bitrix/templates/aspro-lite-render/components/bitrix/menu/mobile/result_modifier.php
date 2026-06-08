<?
$arResult = TSolution::getChilds($arResult);
global $arTheme;
$MENU_TYPE = $arTheme['MEGA_MENU_TYPE']['VALUE'];

if ($MENU_TYPE == 3) {
	TSolution::replaceMenuChilds($arResult, $arParams);
}
?>