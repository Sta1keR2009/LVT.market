<?
use \Bitrix\Main\Localization\Loc;

$bTab = isset($tabCode) && $tabCode === 'sale';
?>
<?//show sale block?>

<?if($templateData['SALE']['VALUE'] && $templateData['SALE']['IBLOCK_ID']):?>
    <?if(!isset($html_sale)):?>
        <?$GLOBALS['arrSaleFilter'] = array('ID' => $templateData['SALE']['VALUE']);?>
        <?$GLOBALS['arrSaleFilter'] = TSolution\Regionality::mergeFilterWithRegionFilter($GLOBALS['arrSaleFilter']);?>
        <?ob_start();?>
            <?$bCheckAjaxBlock = TSolution::checkRequestBlock("sale-list-inner");?>
            <?$APPLICATION->IncludeComponent(
                "bitrix:news.list",
                "blog-list",
                array(
                    "IBLOCK_TYPE" => "aspro_lite_content",
                    "IBLOCK_ID" => $templateData['SALE']['IBLOCK_ID'],
                    "NEWS_COUNT" => "20",
                    "SORT_BY1" => "SORT",
                    "SORT_ORDER1" => "ASC",
                    "SORT_BY2" => "ID",
                    "SORT_ORDER2" => "DESC",
                    "FILTER_NAME" => "arrSaleFilter",
                    "FIELD_CODE" => array(
                        0 => "NAME",
                        1 => "PREVIEW_TEXT",
                        2 => "PREVIEW_PICTURE",
                        3 => "DATE_ACTIVE_FROM",
                        4 => "ACTIVE_TO",
                        5 => "",
                    ),
                    "PROPERTY_CODE" => array(
                        0 => "PERIOD",
                        1 => "REDIRECT",
                        2 => "SALE_NUMBER",
                        3 => "",
                    ),
                    "CHECK_DATES" => "Y",
                    "DETAIL_URL" => "",
                    "AJAX_MODE" => "N",
                    "AJAX_OPTION_JUMP" => "N",
                    "AJAX_OPTION_STYLE" => "Y",
                    "AJAX_OPTION_HISTORY" => "N",
                    "CACHE_TYPE" => "A",
                    "CACHE_TIME" => "36000000",
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
                    "PAGER_TEMPLATE" => ".default",
                    "DISPLAY_TOP_PAGER" => "N",
                    "DISPLAY_BOTTOM_PAGER" => "N",
                    "PAGER_TITLE" => "",
                    "PAGER_SHOW_ALWAYS" => "N",
                    "PAGER_DESC_NUMBERING" => "N",
                    "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
                    "PAGER_SHOW_ALL" => "N",
                    "VIEW_TYPE" => "table",
                    "BIG_BLOCK" => "Y",
                    "COUNT_IN_LINE" => "2",

                    "ROW_VIEW" => true,
                    "BORDER" => true,
                    "ITEM_HOVER_SHADOW" => true,
                    "DARK_HOVER" => false,
                    "ROUNDED" => true,
                    "ROUNDED_IMAGE" => true,
                    "ITEM_PADDING" => true,
                    "ELEMENTS_ROW" => 3,
                    "MAXWIDTH_WRAP" => "N",
                    "MOBILE_SCROLLED" => false,
                    "NARROW" => false,
                    "ITEMS_OFFSET" => false,
                    "IMAGES" => "PICTURE",
                    "IMAGE_POSITION" => "LEFT",
                    "SHOW_PREVIEW" => false,
                    "SHOW_TITLE" => false,
                    "SHOW_SECTION" => "N",
                    "TITLE_POSITION" => "",
                    "TITLE" => "",
                    "RIGHT_TITLE" => "",
                    "RIGHT_LINK" => "",
                    "CHECK_REQUEST_BLOCK" => $bCheckAjaxBlock,
                    "IS_AJAX" => TSolution::checkAjaxRequest() && $bCheckAjaxBlock,
                    "NAME_SIZE" => "18",
                    "SUBTITLE" => "",
                    "SHOW_PREVIEW_TEXT" => "N",
                ),
                false, array("HIDE_ICONS" => "Y")
            );?>
        <?$html_sale = trim(ob_get_clean());?>
    <?endif;?>

    <?if($html_sale && strpos($html_sale, 'error') === false):?>
        <?if($bTab):?>
            <?if(!isset($bShow_sale)):?>
                <?$bShow_sale = true;?>
            <?else:?>
                <div class="tab-pane <?=(!($iTab++) ? 'active' : '')?>" id="sale">
                    <h3 class="switcher-title"><?=$arParams["T_SALE"]?></h3>
                    <?=$html_sale?>
                </div>
            <?endif;?>
        <?else:?>
            <div class="detail-block ordered-block sale">
                <h3 class="switcher-title"><?=$arParams["T_SALE"]?></h3>
                <?=$html_sale?>
            </div>
        <?endif;?>
    <?endif;?>
<?endif;?>