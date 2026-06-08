<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

// get total count
$arResult['TOTAL_COUNT'] = TSolution\Product\Quantity::getTotalCount([
    'ITEM' => $arResult, 
    'PARAMS' => $arParams
]);


foreach ($arResult['DISPLAY_PROPERTIES'] as $keyProp => $valProp) {
    $arResult['DISPLAY_PROPERTIES'][$keyProp] = array_filter($valProp, function($key){
        return strpos($key, '~') !== 0;
    }, ARRAY_FILTER_USE_KEY);
}