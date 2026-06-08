<?
global $arTheme;
$arScripts = ['dropdown_select'];
if($arTheme['USE_BIG_MENU']['VALUE'] === 'Y'){
    $arScripts[] = 'menu_aim';
    $arScripts[] = 'menu_many_items';
}
\Aspro\Lite\Functions\Extensions::init($arScripts);?>