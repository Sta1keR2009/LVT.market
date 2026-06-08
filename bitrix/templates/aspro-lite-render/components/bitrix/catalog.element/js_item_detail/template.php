<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}
$this->setFrameMode(false);

echo Bitrix\Main\Web\Json::encode([
    'ITEM_PRICES' => $arResult['ITEM_PRICES'],
    'ITEM_PRICE_SELECTED' => $arResult['ITEM_PRICE_SELECTED'],
    'ITEM_ALL_PRICES' => $arResult['ITEM_ALL_PRICES'],
    'ID' => $arResult['ID'],
    'IBLOCK_ID' => $arResult['IBLOCK_ID'],
    'NAME' => $arResult['NAME'],
    'PREVIEW_PICTURE' => $arResult['PREVIEW_PICTURE'],
    'DETAIL_PICTURE' => $arResult['DETAIL_PICTURE'],
    'DISPLAY_PROPERTIES' => $arResult['DISPLAY_PROPERTIES'],
    // 'PROPERTIES' => $arResult['PROPERTIES'],
    'CATALOG_QUANTITY' => $arResult['CATALOG_QUANTITY'],
    'CATALOG_SUBSCRIBE' => $arResult['CATALOG_SUBSCRIBE'],
    'PRICES' => $arResult['PRICES'],
    'CATALOG_MEASURE_RATIO' => $arResult['CATALOG_MEASURE_RATIO'],
    'TOTAL_COUNT' => $arResult['TOTAL_COUNT'],
    'CATALOG_MEASURE' => $arResult['CATALOG_MEASURE'],
    'CATALOG_MEASURE_NAME' => $arResult['CATALOG_MEASURE_NAME'],
    'CATALOG_MEASURE_RATIO' => $arResult['CATALOG_MEASURE_RATIO'],
    'PRODUCT' => $arResult['PRODUCT'],
    'DETAIL_TEXT' => $arResult['DETAIL_TEXT'],
    'PREVIEW_TEXT' => $arResult['PREVIEW_TEXT'],
]);
