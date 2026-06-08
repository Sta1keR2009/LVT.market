<?
use \Bitrix\Main\Localization\Loc;
?>
<?//show news block?>
<?if($templateData['SECTIONS']):?>
    <?if(!isset($html_landings)):?>
        <?$GLOBALS["arLandingSections"] = array("PROPERTY_SECTION" => $templateData['SECTIONS'], "!ID" => $arResult["ID"]);?>
        <?$GLOBALS['arLandingSections'] = TSolution\Regionality::mergeFilterWithRegionFilter($GLOBALS['arLandingSections']);?>
        <?ob_start();?>
            <?$APPLICATION->IncludeComponent(
                "bitrix:news.list",
                "landings_list",
                array(
                    "IBLOCK_TYPE" => "aspro_lite_content",
                    "IBLOCK_ID" => $arParams['IBLOCK_ID'],
                    // "CUR_PAGE" => $APPLICATION->GetCurPage(),
                    "SHOW_COUNT" => $arParams["LANDING_SECTION_COUNT_VISIBLE"],
                    "NEWS_COUNT" => $arParams["LANDING_SECTION_COUNT"],
                    "SORT_BY1" => "SORT",
                    "SORT_ORDER1" => "ASC",
                    "SORT_BY2" => "ID",
                    "SORT_ORDER2" => "DESC",
                    "FILTER_NAME" => "arLandingSections",
                    "FIELD_CODE" => array(
                        0 => "",
                        1 => "",
                    ),
                    "PROPERTY_CODE" => array(
                        0 => "LINK",
                        1 => "",
                    ),
                    "CHECK_DATES" => "Y",
                    "DETAIL_URL" => "",
                    "AJAX_MODE" => "N",
                    "AJAX_OPTION_JUMP" => "N",
                    "AJAX_OPTION_STYLE" => "Y",
                    "AJAX_OPTION_HISTORY" => "N",
                    "CACHE_TYPE" =>$arParams["CACHE_TYPE"],
                    "CACHE_TIME" => $arParams["CACHE_TIME"],
                    "SEF_CATALOG_URL" => $arResult["URL_TEMPLATES"]["smart_filter"],
                    "CACHE_FILTER" => "Y",
                    "CACHE_GROUPS" => "N",
                    "PREVIEW_TRUNCATE_LEN" => "",
                    "ACTIVE_DATE_FORMAT" => "j F Y",
                    "SET_TITLE" => "N",
                    "SET_STATUS_404" => "N",
                    "INCLUDE_IBLOCK_INTO_CHAIN" => "N",
                    "ADD_SECTIONS_CHAIN" => "N",
                    "HIDE_LINK_WHEN_NO_DETAIL" => "N",
                    "PARENT_SECTION" => "",
                    "PARENT_SECTION_CODE" => "",
                    "INCLUDE_SUBSECTIONS" => "Y",
                    "PAGER_TEMPLATE" => "",
                    "DISPLAY_TOP_PAGER" => "N",
                    "DISPLAY_BOTTOM_PAGER" => "N",
                    "PAGER_TITLE" => "",
                    "PAGER_SHOW_ALWAYS" => "N",
                    "PAGER_DESC_NUMBERING" => "N",
                    "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
                    "PAGER_SHOW_ALL" => "N",
                    "AJAX_OPTION_ADDITIONAL" => "",
                    "COMPONENT_TEMPLATE" => "next",
                    "SET_BROWSER_TITLE" => "N",
                    "SET_META_KEYWORDS" => "N",
                    "SET_META_DESCRIPTION" => "N",
                    "SET_LAST_MODIFIED" => "N",
                    "PAGER_BASE_LINK_ENABLE" => "N",
                    "SHOW_404" => "N",
                    "MESSAGE_404" => ""
                ),
                false, array("HIDE_ICONS" => "Y")
            );?>
        <?$html_landings = trim(ob_get_clean());?>
    <?endif;?>

    <?if($html_landings && strpos($html_landings, 'error') === false):?>
        <div class="detail-block ordered-block landings">
            <?if ($arParams["T_LANDINGS"]):?>
                <h3 class="switcher-title"><?=$arParams["T_LANDINGS"]?></h3>
            <?endif;?>
            <?=$html_landings?>
        </div>
    <?endif;?>
<?endif;?>