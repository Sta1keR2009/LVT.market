<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();?>
<?
if (is_array($arParams['THEME'])) {
    if ($arParams['IMAGES'] == 'FROM_THEME') {
        $arParams['IMAGES'] = $arParams['THEME']['IMAGES'] ?? $arParams['IMAGES'];
    }
    if ($arParams['IMAGE_ON_FON'] == 'FROM_THEME') {
        $arParams['IMAGE_ON_FON'] = $arParams['THEME']['IMAGE_ON_FON'] ?? $arParams['IMAGE_ON_FON'];
    }
}
?>