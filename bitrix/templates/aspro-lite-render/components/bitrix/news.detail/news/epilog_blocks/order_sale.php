<?use \Bitrix\Main\Localization\Loc;?>
<?//show order and sale block?>

<?if(
    ($templateData['SALE']['VALUE'] && $templateData['SALE']['IBLOCK_ID']) ||
    $templateData['ORDER_BTN']
):?>
    <?$html = '';?>
    <?if($templateData['SALE']['VALUE'] && $templateData['SALE']['IBLOCK_ID']):?>
        <?$GLOBALS['arrSalesFilter'] = array('ID' => $templateData['SALE']['VALUE']);?>
        <?ob_start();?>
            <?$APPLICATION->IncludeComponent(
                "bitrix:news.list",
                "sale-linked",
                array(
                    "IBLOCK_TYPE" => "aspro_lite_content",
                    "IBLOCK_ID" => $templateData['SALE']['IBLOCK_ID'],
                    "NEWS_COUNT" => "20",
                    "SORT_BY1" => "SORT",
                    "SORT_ORDER1" => "ASC",
                    "SORT_BY2" => "ID",
                    "SORT_ORDER2" => "DESC",
                    "FILTER_NAME" => "arrSalesFilter",
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
                    "ACTIVE_DATE_FORMAT" => "d.m.Y",
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
                    "DISPLAY_BOTTOM_PAGER" => "Y",
                    "PAGER_TITLE" => "",
                    "PAGER_SHOW_ALWAYS" => "N",
                    "PAGER_DESC_NUMBERING" => "N",
                    "PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
                    "PAGER_SHOW_ALL" => "N",
                    "VIEW_TYPE" => "table",
                    "BIG_BLOCK" => "Y",
                    "COUNT_IN_LINE" => "2",

                    // "COMPACT" => true,
                    "ELEMENT_TITLE" => Loc::getMessage("SERVICE"),
                ),
                false, array("HIDE_ICONS" => "Y")
            );?>
        <?$html = trim(ob_get_clean());?>
    <?endif?>
    <?if(($html && strpos($html, 'error') === false) || $templateData['ORDER_BTN']):?>
        <div class="detail-block ordered-block order_sale">
		    <div class="outer-rounded-x bordered grey-bg">
                <?//$APPLICATION->ShowViewContent('detail_order_sale')?>
                <?$APPLICATION->ShowViewContent('PRODUCT_ORDER_SALE_INFO')?>
                <?=$html?>
            </div>
        </div>
    <?endif;?>
<?endif;?>