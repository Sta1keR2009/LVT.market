<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?$APPLICATION->AddChainItem(GetMessage("CATALOG_COMPARE_HEADER_TITLE"));?>
<?$APPLICATION->SetPageProperty("title", GetMessage("CATALOG_COMPARE_HEADER_TITLE"));?>
<?$APPLICATION->SetTitle(GetMessage("CATALOG_COMPARE_HEADER_TITLE"));?>
<?$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH.'/js/sly.js');?>
<?$APPLICATION->SetPageProperty("MENU", "N")?>
<?
global $arTheme, $arRegion;

$arParams["PRICE_CODE"] = explode(',', TSolution::GetFrontParametrValue('PRICES_TYPE'));
$arParams["STORES"] = explode(',', TSolution::GetFrontParametrValue('STORES'));
if ($arRegion) {
	if ($arRegion['LIST_PRICES']) {
		$arParams["PRICE_CODE"] = array_keys($arRegion['LIST_PRICES']);
	}
	if ($arRegion['LIST_STORES']) {
		$arParams["STORES"] = $arRegion['LIST_STORES'];
	}
}

if(!in_array('PREVIEW_PICTURE', (array)$arParams["COMPARE_FIELD_CODE"]))
	$arParams["COMPARE_FIELD_CODE"][] = 'PREVIEW_PICTURE';
if(!in_array('ID', (array)$arParams["COMPARE_OFFERS_FIELD_CODE"]))
	$arParams["COMPARE_OFFERS_FIELD_CODE"][] = 'ID';
if(!in_array('QUANTITY', (array)$arParams["COMPARE_OFFERS_FIELD_CODE"]))
	$arParams["COMPARE_OFFERS_FIELD_CODE"][] = 'QUANTITY';

$arNeedMainProps = ['CML2_ARTICLE'];
$arParams["COMPARE_PROPERTY_CODE"] = array_merge((array)$arParams["COMPARE_PROPERTY_CODE"], $arNeedMainProps);

$arNeedOffersProps = ['ARTICLE', 'CML2_ARTICLE'];
$arParams["COMPARE_OFFERS_PROPERTY_CODE"] = array_merge((array)$arParams["COMPARE_OFFERS_PROPERTY_CODE"], $arNeedOffersProps);

?>
<?$APPLICATION->IncludeComponent(
	"bitrix:catalog.compare.result",
	"main",
	array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"BASKET_URL" => $arParams["BASKET_URL"],
		"ACTION_VARIABLE" => $arParams["ACTION_VARIABLE"],
		"PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
		"SECTION_ID_VARIABLE" => $arParams["SECTION_ID_VARIABLE"],
		"FIELD_CODE" => $arParams["COMPARE_FIELD_CODE"],
		"PROPERTY_CODE" => $arParams["COMPARE_PROPERTY_CODE"],
		"NAME" => $arParams["COMPARE_NAME"],
		"SHOW_MEASURE" => $arParams["SHOW_MEASURE"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"PRICE_CODE" => $arParams["PRICE_CODE"],
		"SKU_DETAIL_ID" => $arParams["SKU_DETAIL_ID"],
		"USE_PRICE_COUNT" => "N",
		"SHOW_GALLERY" => "N",
		"USE_REGION" => ($arRegion ? "Y" : "N"),
		"STORES" => $arParams['STORES'],
		// "USE_PRICE_COUNT" => $arParams["USE_PRICE_COUNT"],
		"SHOW_PRICE_COUNT" => $arParams["SHOW_PRICE_COUNT"],
		"PRICE_VAT_INCLUDE" => TSolution::GetFrontParametrValue('PRICE_VAT_INCLUDE'),
		"PRICE_VAT_SHOW_VALUE" => $arParams["PRICE_VAT_SHOW_VALUE"],
		"DISPLAY_ELEMENT_SELECT_BOX" => $arParams["DISPLAY_ELEMENT_SELECT_BOX"],
		"ELEMENT_SORT_FIELD_BOX" => $arParams["ELEMENT_SORT_FIELD_BOX"],
		"ELEMENT_SORT_ORDER_BOX" => $arParams["ELEMENT_SORT_ORDER_BOX"],
		"ELEMENT_SORT_FIELD_BOX2" => $arParams["ELEMENT_SORT_FIELD_BOX2"],
		"ELEMENT_SORT_ORDER_BOX2" => $arParams["ELEMENT_SORT_ORDER_BOX2"],
		"ELEMENT_SORT_FIELD" => $arParams["COMPARE_ELEMENT_SORT_FIELD"],
		"ELEMENT_SORT_ORDER" => $arParams["COMPARE_ELEMENT_SORT_ORDER"],
		"DETAIL_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["element"],
		"OFFERS_FIELD_CODE" => $arParams["COMPARE_OFFERS_FIELD_CODE"],
		"OFFERS_PROPERTY_CODE" => $arParams["COMPARE_OFFERS_PROPERTY_CODE"],
		"OFFERS_CART_PROPERTIES" => $arParams["OFFERS_CART_PROPERTIES"],
		'CONVERT_CURRENCY' => TSolution::GetFrontParametrValue('CONVERT_CURRENCY'),
		'CURRENCY_ID' => TSolution::GetFrontParametrValue('CURRENCY_ID'),
		'HIDE_NOT_AVAILABLE' => 'N',
		'TEMPLATE_THEME' => (isset($arParams['TEMPLATE_THEME']) ? $arParams['TEMPLATE_THEME'] : ''),
		'IMG_CORNER' => $arParams['SECTION_ITEM_LIST_IMG_CORNER'] === 'Y',
		"ORDER_VIEW" => TSolution::GetFrontParametrValue('ORDER_VIEW') == 'Y',
		"USE_COMPARE_GROUP" => $arParams["USE_COMPARE_GROUP"],

		"ADD_PROPERTIES_TO_BASKET" => $arParams['ADD_PROPERTIES_TO_BASKET'],
		"PARTIAL_PRODUCT_PROPERTIES" => $arParams['PARTIAL_PRODUCT_PROPERTIES'],
		"PRODUCT_PROPERTIES" =>	$arParams['PRODUCT_PROPERTIES'],
		"USE_PRODUCT_QUANTITY" => $arParams['USE_PRODUCT_QUANTITY'],
		"DISPLAY_COMPARE"	=>	TSolution::GetFrontParametrValue('CATALOG_COMPARE'),
        'SHOW_BASKET_COUNTER' => TSolution::GetFrontParametrValue('USE_PRODUCT_QUANTITY_LIST') == 'Y',
	),
	$component,
	array("HIDE_ICONS" => "Y")
);?>
