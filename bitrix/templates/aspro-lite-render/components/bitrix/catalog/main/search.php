<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
global $arTheme;

$APPLICATION->SetTitle(GetMessage("CMP_TITLE"));

//set params for props from module
TSolution\Functions::replacePropsParams($arParams);

// add cacheable params
TSolution\CacheableUrl::addParams([
    'q',			// query to search
    'section_id',	// filter results by section
    'search_mode',
]);

// set params from module
TSolution\Functions::replaceParamsFromModule($arParams, [
    'USE_LANGUAGE_GUESS' => 'SEARCHTITLE_USE_LANGUAGE_GUESS',
]);
?>
<?$APPLICATION->IncludeComponent(
    "bitrix:catalog.search",
    "main",
    array_merge(
        $arParams,
        Array(
                "IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
                "IBLOCK_ID" => $arParams["IBLOCK_ID"],

                "DISPLAY_COMPARE"	=>	TSolution::GetFrontParametrValue('CATALOG_COMPARE'),
                "SHOW_FAVORITE" => TSolution::GetFrontParametrValue('SHOW_FAVORITE'),

                "IBLOCK_TIZERS_ID" => $arParams["IBLOCK_TIZERS_ID"],
                "STORES" => explode(',', TSolution::GetFrontParametrValue('STORES')),
                "SORT_BUTTONS" => $arParams["SORT_BUTTONS"],
                "SORT_PRICES" => $arParams["SORT_PRICES"],
                "ELEMENT_SORT_FIELD" => $arParams["ELEMENT_SORT_FIELD"],
                "ELEMENT_SORT_ORDER" => $arParams["ELEMENT_SORT_ORDER"],
                "ELEMENT_SORT_FIELD2" => $arParams["ELEMENT_SORT_FIELD2"],
                "ELEMENT_SORT_ORDER2" => $arParams["ELEMENT_SORT_ORDER2"],
                "PAGE_ELEMENT_COUNT" => $arParams["PAGE_ELEMENT_COUNT"],
                "LINE_ELEMENT_COUNT" => $arParams["LINE_ELEMENT_COUNT"],
                "PROPERTY_CODE" => $arParams["LIST_PROPERTY_CODE"],

                "OFFERS_CART_PROPERTIES" => $arParams["OFFERS_CART_PROPERTIES"],
                "OFFERS_LIMIT" => $arParams["LIST_OFFERS_LIMIT"],

                "SHOW_ARTICLE_SKU" => $arParams["SHOW_ARTICLE_SKU"],
                "SHOW_MEASURE_WITH_RATIO" => $arParams["SHOW_MEASURE_WITH_RATIO"],
                "DEFAULT_LIST_TEMPLATE" => $arParams["DEFAULT_LIST_TEMPLATE"],

                "SKU_IBLOCK_ID"	=>	$arParams["SKU_IBLOCK_ID"],
                "SKU_TREE_PROPS"	=>	$arParams["SKU_TREE_PROPS"],
                "SKU_PROPERTY_CODE"	=>	$arParams["SKU_PROPERTY_CODE"],
                "SKU_SORT_FIELD" => $arParams["SKU_SORT_FIELD"],
                "SKU_SORT_ORDER" => $arParams["SKU_SORT_ORDER"],
                "SKU_SORT_FIELD2" => $arParams["SKU_SORT_FIELD2"],
                "SKU_SORT_ORDER2" => $arParams["SKU_SORT_ORDER2"],
                "OFFERS_SORT_FIELD" => $arParams["SKU_SORT_FIELD"],
                "OFFERS_SORT_ORDER" => $arParams["SKU_SORT_ORDER"],
                "OFFERS_SORT_FIELD2" => $arParams["SKU_SORT_FIELD2"],
                "OFFERS_SORT_ORDER2" => $arParams["SKU_SORT_ORDER2"],

                'OFFER_TREE_PROPS' => $arParams['SKU_TREE_PROPS'],
                'OFFERS_PROPERTY_CODE' => $arParams['SKU_PROPERTY_CODE'],
                "OFFERS_FIELD_CODE" => array_merge(['ID', 'NAME'], (array)$arParams["LIST_OFFERS_FIELD_CODE"]),

                "SECTION_URL" => $arParams["SECTION_URL"],
                "DETAIL_URL" => $arParams["DETAIL_URL"],
                "BASKET_URL" => $arParams["BASKET_URL"],
                "ACTION_VARIABLE" => $arParams["ACTION_VARIABLE"],
                "PRODUCT_ID_VARIABLE" => $arParams["PRODUCT_ID_VARIABLE"],
                "PRODUCT_QUANTITY_VARIABLE" => $arParams["PRODUCT_QUANTITY_VARIABLE"],
                "PRODUCT_PROPS_VARIABLE" => $arParams["PRODUCT_PROPS_VARIABLE"],
                "SECTION_ID_VARIABLE" => $arParams["SECTION_ID_VARIABLE"],
                "CACHE_TYPE" => $arParams["CACHE_TYPE"],
                "CACHE_TIME" => $arParams["CACHE_TIME"],
                "USE_COMPARE" => $arParams["USE_COMPARE"],
                "PRICE_CODE" => $arParams["PRICE_CODE"],
                "USE_PRICE_COUNT" => $arParams["USE_PRICE_COUNT"],
                "SHOW_PRICE_COUNT" => $arParams["SHOW_PRICE_COUNT"],
                "PRICE_VAT_INCLUDE" => TSolution::GetFrontParametrValue('PRICE_VAT_INCLUDE'),
                "PRODUCT_PROPERTIES" => $arParams["PRODUCT_PROPERTIES"],
                "USE_PRODUCT_QUANTITY" => $arParams["USE_PRODUCT_QUANTITY"],
                "CONVERT_CURRENCY" => TSolution::GetFrontParametrValue('CONVERT_CURRENCY'),
                "CURRENCY_ID" => TSolution::GetFrontParametrValue('CURRENCY_ID'),
                "DISPLAY_TOP_PAGER" => $arParams["DISPLAY_TOP_PAGER"],
                "DISPLAY_BOTTOM_PAGER" => $arParams["DISPLAY_BOTTOM_PAGER"],
                "PAGER_TITLE" => $arParams["PAGER_TITLE"],
                "PAGER_SHOW_ALWAYS" => $arParams["PAGER_SHOW_ALWAYS"],
                "HIDE_NOT_AVAILABLE" => TSolution::GetFrontParametrValue('HIDE_NOT_AVAILABLE'),
                "PAGER_TEMPLATE" => "main",
                "PAGER_DESC_NUMBERING" => $arParams["PAGER_DESC_NUMBERING"],
                "PAGER_DESC_NUMBERING_CACHE_TIME" => $arParams["PAGER_DESC_NUMBERING_CACHE_TIME"],
                "PAGER_SHOW_ALL" => $arParams["PAGER_SHOW_ALL"],
                "SORT_REGION_PRICE" => $arParams["SORT_REGION_PRICE"],
                "FILTER_NAME" => "searchFilter",
                "SECTION_ID" => "",
                "SECTION_CODE" => "",
                "SECTION_USER_FIELDS" => array(),
                "INCLUDE_SUBSECTIONS" => "Y",
                "SHOW_ALL_WO_SECTION" => "Y",
                "META_KEYWORDS" => "",
                "META_DESCRIPTION" => "",
                "BROWSER_TITLE" => "",
                "ADD_SECTIONS_CHAIN" => "N",
                "SET_TITLE" => "N",
                "SET_STATUS_404" => "N",
                "CACHE_FILTER" => "Y",
                "CACHE_GROUPS" => $arParams["CACHE_GROUPS"],
                "RESTART" => ($arParams["RESTART"] == "Y" ? "Y" : "N"),
                "NO_WORD_LOGIC" => ($arParams["NO_WORD_LOGIC"] != "N" ? "Y" : "N"),
                "USE_LANGUAGE_GUESS" => ($arParams["USE_LANGUAGE_GUESS"] != "N" ? "Y" : "N"),
                "CHECK_DATES" => "Y",
                "DISPLAY_WISH_BUTTONS" => $arParams["DISPLAY_WISH_BUTTONS"],
                "AJAX_CONTROLS" => "N",
                "DISPLAY_COMPARE" => $arParams["DISPLAY_COMPARE"],
                "DEFAULT_COUNT" => $arParams["DEFAULT_COUNT"],
                "SHOW_HINTS" => $arParams["SHOW_HINTS"],
                "SHOW_DISCOUNT_PERCENT" => $arParams["SHOW_DISCOUNT_PERCENT"],
                "SHOW_OLD_PRICE" => $arParams["SHOW_OLD_PRICE"],
                "SALE_STIKER" => $arParams["SALE_STIKER"],
                "SHOW_RATING" => $arParams["SHOW_RATING"],
                "SHOW_DISCOUNT_TIME" => $arParams["SHOW_DISCOUNT_TIME"],
                "ADD_PROPERTIES_TO_BASKET" => (isset($arParams["ADD_PROPERTIES_TO_BASKET"]) ? $arParams["ADD_PROPERTIES_TO_BASKET"] : ''),
                "PARTIAL_PRODUCT_PROPERTIES" => (isset($arParams["PARTIAL_PRODUCT_PROPERTIES"]) ? $arParams["PARTIAL_PRODUCT_PROPERTIES"] : ''),
                "USE_MAIN_ELEMENT_SECTION" => $arParams["USE_MAIN_ELEMENT_SECTION"],
                "OFFER_HIDE_NAME_PROPS" => $arParams["OFFER_HIDE_NAME_PROPS"],
                "SHOW_MEASURE" => $arParams["SHOW_MEASURE"],
                "SHOW_COUNTER_LIST" => $arParams["SHOW_COUNTER_LIST"],
                "HIDE_NOT_AVAILABLE_OFFERS" => TSolution::GetFrontParametrValue('HIDE_NOT_AVAILABLE_OFFERS'),
                "USE_FILTER_PRICE" => $arParams["USE_FILTER_PRICE"],
                "FILTER_PRICE_CODE" => $arParams["FILTER_PRICE_CODE"],
                "DISPLAY_ELEMENT_COUNT" => $arParams["DISPLAY_ELEMENT_COUNT"],
                "AJAX_FILTER_CATALOG" => $arParams["AJAX_FILTER_CATALOG"],
                "SHOW_DISCOUNT_PERCENT_NUMBER" => $arParams["SHOW_DISCOUNT_PERCENT_NUMBER"],
                "SECTIONS_SEARCH_COUNT" => $arParams["SECTIONS_SEARCH_COUNT"],
                "S_ASK_QUESTION" => $arParams["S_ASK_QUESTION"],
                "SHOW_LANDINGS" => $arParams["SHOW_LANDINGS_SEARCH"],
                "LANDING_TITLE" => $arParams["LANDING_SEARCH_TITLE"],
                "LANDING_SECTION_COUNT" => $arParams["LANDING_SEARCH_COUNT"],
                "LANDING_TYPE" => ($arParams["LANDING_TYPE_VIEW"] == "FROM_MODULE" ? $arTheme["CATALOG_PAGE_LANDINGS"]["VALUE"] : $arParams["LANDING_TYPE_VIEW"]),
                "SHOW_ONE_CLICK_BUY" => $arParams["SHOW_ONE_CLICK_BUY"],
                "MAX_GALLERY_ITEMS" => $arParams["MAX_GALLERY_ITEMS"],
                "SHOW_GALLERY" => $arParams["SHOW_GALLERY"],
                'ADD_PICT_PROP' => TSolution::GetFrontParametrValue('GALLERY_PROPERTY_CODE'),
                'OFFER_ADD_PICT_PROP' => TSolution::GetFrontParametrValue('GALLERY_PROPERTY_CODE'),
                "MAX_SCU_COUNT_VIEW" => $arTheme['MAX_SCU_COUNT_VIEW']['VALUE'],
                "DETAIL_ADD_DETAIL_TO_SLIDER" => $arParams["DETAIL_ADD_DETAIL_TO_SLIDER"],
                "LIST_DISPLAY_POPUP_IMAGE" => $arParams["LIST_DISPLAY_POPUP_IMAGE"],
                "SHOW_SORT_IN_FILTER" => $arParams["SHOW_SORT_IN_FILTER"],
                "SORT_PROP" => $arParams['SORT_PROP'],
                "SHOW_SORT_RANK_BUTTON" => $arParams['SHOW_SORT_RANK_BUTTON'],
                "USE_SEARCH_RESULT_ORDER" => $arParams['SEARCH_USE_SEARCH_RESULT_ORDER'],
                "CATALOG_TEMPLATE_PATH" => $this->__folder,
                "HIDE_SMART_SEO" => "Y",
        )
    ),
    $component
);
?>
