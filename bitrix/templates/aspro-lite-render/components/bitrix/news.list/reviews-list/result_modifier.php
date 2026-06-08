<?
foreach($arResult['ITEMS'] as $key => $arItem){
	TSolution::getFieldImageData($arResult['ITEMS'][$key], array('PREVIEW_PICTURE'));
}
?>