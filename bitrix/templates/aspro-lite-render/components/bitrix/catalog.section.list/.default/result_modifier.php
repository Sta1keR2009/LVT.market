<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>
<?
if (is_array($arParams['THEME'])) {
    if ($arParams['BORDERED'] == 'FROM_THEME') {
        $arParams['BORDERED'] = $arParams['THEME']['BORDERED'] ?? $arParams['BORDERED'];
    }
    if ($arParams['IMAGES'] == 'FROM_THEME') {
        $arParams['IMAGES'] = $arParams['THEME']['IMAGES'] ?? $arParams['IMAGES'];
    }
}
?>