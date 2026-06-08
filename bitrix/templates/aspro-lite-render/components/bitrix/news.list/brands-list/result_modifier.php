<?
if (is_array($arParams['THEME'])) {
    if ($arParams['BORDER'] == 'FROM_THEME') {
        $arParams['BORDER'] = $arParams['THEME']['BORDER'] ?? $arParams['BORDER'];
    }
}
$arParams['BORDER'] = $arParams['BORDER'] !== 'N';
foreach($arResult['ITEMS'] as $key => $arItem){
	TSolution::getFieldImageData($arResult['ITEMS'][$key], array('PREVIEW_PICTURE'));
}

if($arParams['SLIDER'] === "Y"){
	$arParams['DISPLAY_BOTTOM_PAGER'] = false;
}
?>