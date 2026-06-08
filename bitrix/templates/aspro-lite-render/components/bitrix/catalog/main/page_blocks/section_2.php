<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}

if ($arParams['SECTION_TYPE_VIEW'] === 'FROM_MODULE') {
    $blockTemplateOptions = $GLOBALS['arTheme']['SECTION_TYPE_VIEW_CATALOG']['LIST'][$GLOBALS['arTheme']['SECTION_TYPE_VIEW_CATALOG']['VALUE']];
    $bordered = $blockTemplateOptions['ADDITIONAL_OPTIONS']['SECTIONS_BORDERED']['VALUE'];
    $images = $blockTemplateOptions['ADDITIONAL_OPTIONS']['SECTIONS_IMAGES']['VALUE'];
    $elementsInRow = $blockTemplateOptions['ADDITIONAL_OPTIONS']['SECTIONS_ELEMENTS_COUNT']['VALUE'];
} else {
    $bordered = $arParams['SECTION_BORDERED'];
    $images = $arParams['SECTION_IMAGES'];
    $elementsInRow = $arParams['SECTION_ELEMENTS_COUNT'];
}
?>
<?$APPLICATION->IncludeComponent(
    'bitrix:catalog.section.list',
    'main',
    [
        'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],
        'IBLOCK_ID' => $arParams['IBLOCK_ID'],
        'CACHE_TYPE' => $arParams['CACHE_TYPE'],
        'CACHE_TIME' => $arParams['CACHE_TIME'],
        'CACHE_FILTER' => $arParams['CACHE_FILTER'],
        'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
        'COUNT_ELEMENTS' => $arParams['SECTION_COUNT_ELEMENTS'],
        'COUNT_ELEMENTS' => 'N',
        'FILTER_NAME' => 'arSubSectionFilter',
        'TOP_DEPTH' => $arParams['SECTION_TOP_DEPTH'],
        'TOP_DEPTH' => 1,
        'SECTION_URL' => $arResult['FOLDER'].$arResult['URL_TEMPLATES']['section'],
        'ADD_SECTIONS_CHAIN' => ((!$iSectionsCount || $arParams['INCLUDE_SUBSECTIONS'] !== 'N') ? 'N' : 'Y'),
        'COMPONENT_TEMPLATE' => 'main',
        'SECTION_ID' => $arResult['VARIABLES']['SECTION_ID'],
        'SECTION_CODE' => $arResult['VARIABLES']['SECTION_CODE'],
        'SECTION_FIELDS' => [
            0 => 'NAME',
            1 => 'PICTURE',
            2 => '',
        ],
        'SECTION_USER_FIELDS' => [
            0 => 'UF_CATALOG_ICON',
            1 => 'UF_SVG_INLINE',
            2 => '',
        ],
        'BORDERED' => $bordered,
        'IMAGES' => $images,
        'ELEMENTS_IN_ROW' => $elementsInRow,
        'NARROW' => 'Y',
        'CHECK_REQUEST_BLOCK' => TSolution::checkRequestBlock('catalog_sections'),
        'IS_AJAX' => TSolution::checkAjaxRequest(),
        'MOBILE_SCROLLED' => TSolution::GetFrontParametrValue('MOBILE_SCROLL_LIST_SECTIONS'),
        'MOBILE_COMPACT' => 'N',
        'COMPOSITE_FRAME_MODE' => 'Y',
        'COMPOSITE_FRAME_TYPE' => 'STATIC',
    ],
    false
);?>
