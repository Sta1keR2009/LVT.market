<?php

$arResult = TSolution::getChilds2($arResult);

global $arTheme;
$MENU_TYPE = $arTheme['MEGA_MENU_TYPE']['VALUE'];

if ($arParams['ONLY_CATALOG'] === 'Y' && $arParams['USE_NLO_MENU'] === 'Y') {
    $catalogDir = reset($arResult)['LINK'];
    $menuParams = TSolution::GetDirMenuParametrs($_SERVER['DOCUMENT_ROOT'].$catalogDir);

    if (($menuParams['MENU_SHOW_SECTIONS'] ?? 'Y') === 'N') {
        $arParams['USE_NLO_MENU'] = 'N';
    }
}

if ($MENU_TYPE == 3) {
    TSolution::replaceMenuChilds($arResult, $arParams);

    // if items do not have links, select the first available item
    $isSelected = false;
    foreach ($arResult as &$arItem) {
        foreach ($arItem['CHILD'] as $index => &$arChild) {
            if ($arChild['SELECTED']) {
                if (!$isSelected) {
                    $isSelected = true;
                    continue;
                }

                $arChild['SELECTED'] = false;
            }
        }
        unset($arChild);
    }
    unset($arItem);
}

if ($arParams['CATALOG_WIDE'] === 'Y' && is_array($arResult) && count($arResult) > 0) {
    $arResult = reset($arResult)['CHILD'];
}

$arResult = LvtCatalogMenuFilter::applyIfNeeded($arResult);
