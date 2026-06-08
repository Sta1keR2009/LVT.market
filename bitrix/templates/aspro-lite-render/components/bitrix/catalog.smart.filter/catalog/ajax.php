<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$bHideSmartSeo = isset($arParams['HIDE_SMART_SEO']) && $arParams['HIDE_SMART_SEO'] === "Y";

if (!$bHideSmartSeo && TSolution::isSmartSeoInstalled() && $arResult && class_exists(\Aspro\Smartseo\General\Smartseo::class)) {
	$url = \Aspro\Smartseo\General\Smartseo::getUrlByReal(htmlspecialcharsback($arResult['FILTER_AJAX_URL']), SITE_ID);
	
	if ($url) {
		$arResult['FILTER_AJAX_URL'] = $url;
		$arResult['SEF_SET_FILTER_URL'] = $url;
		$arResult['FILTER_URL'] = $url;
		$arResult['FILTER_URL'] = $url;
		$arResult['JS_FILTER_PARAMS']['SEF_SET_FILTER_URL'] = $url;
	}
}
$APPLICATION->RestartBuffer();
unset($arResult["COMBO"]);
echo CUtil::PHPToJSObject($arResult, true);
?>