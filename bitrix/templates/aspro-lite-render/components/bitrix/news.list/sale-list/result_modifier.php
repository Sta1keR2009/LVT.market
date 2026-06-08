<?
if (is_array($arParams['THEME'])) {
    if ($arParams['ELEMENTS_ROW'] == 'FROM_THEME') {
        $arParams['ELEMENTS_ROW'] = $arParams['THEME']['ELEMENTS_ROW'] ?? $arParams['ELEMENTS_ROW'];
    }
}

foreach ($arResult['ITEMS'] as $key => &$arItem) {
    $arItem['DETAIL_PAGE_URL'] = TSolution::FormatNewsUrl($arItem);
    
	TSolution::getFieldImageData($arResult['ITEMS'][$key], array('PREVIEW_PICTURE'));
}
unset($arItem);
?>