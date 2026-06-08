<?$bTab = isset($tabCode) && $tabCode === 'sku';?>
<?// show sku block?>
<?if ($arParams['TYPE_SKU'] === 'TYPE_2'):?>
    <?if (!isset($html_sku)):?>
        <?$GLOBALS['arrSkuFilter'] = ['PROPERTY_CML2_LINK' => $arResult['ID']];?>
        <?ob_start();?>
            <?$APPLICATION->IncludeComponent(
                'bitrix:catalog.section',
                'catalog_table',
                [
                    'IBLOCK_TYPE' => 'aspro_lite_catalog',
                    'IBLOCK_ID' => $arParams['SKU_IBLOCK_ID'],
                    'PAGE_ELEMENT_COUNT' => 999,
                    'PROPERTY_CODE' => $arParams['SKU_PROPERTY_CODE'],
                    'ELEMENT_SORT_FIELD' => $arParams['SKU_SORT_FIELD'],
                    'ELEMENT_SORT_ORDER' => $arParams['SKU_SORT_ORDER'],
                    'ELEMENT_SORT_FIELD2' => $arParams['SKU_SORT_FIELD2'],
                    'ELEMENT_SORT_ORDER2' => $arParams['SKU_SORT_ORDER2'],
                    'FILTER_NAME' => 'arrSkuFilter',
                    'FIELD_CODE' => [
                        0 => 'NAME',
                        1 => 'PREVIEW_TEXT',
                        2 => 'PREVIEW_PICTURE',
                        3 => 'DETAIL_TEXT',
                        4 => '',
                    ],
                    'CHECK_DATES' => 'Y',
                    'DETAIL_URL' => '',
                    'AJAX_MODE' => 'N',
                    'AJAX_OPTION_JUMP' => 'N',
                    'AJAX_OPTION_STYLE' => 'Y',
                    'AJAX_OPTION_HISTORY' => 'N',
                    'CACHE_TYPE' => $arParams['CACHE_TYPE'],
                    'CACHE_TIME' => $arParams['CACHE_TIME'],
                    'CACHE_FILTER' => $arParams['CACHE_FILTER'],
                    'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
                    'PREVIEW_TRUNCATE_LEN' => '',
                    'ACTIVE_DATE_FORMAT' => 'j F Y',
                    'SET_TITLE' => 'N',
                    'SET_STATUS_404' => 'N',
                    'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
                    'ADD_SECTIONS_CHAIN' => 'N',
                    'HIDE_LINK_WHEN_NO_DETAIL' => 'N',
                    'PARENT_SECTION' => '',
                    'PARENT_SECTION_CODE' => '',
                    'INCLUDE_SUBSECTIONS' => 'Y',
                    'PAGER_TEMPLATE' => '.default',
                    'DISPLAY_TOP_PAGER' => 'N',
                    'DISPLAY_BOTTOM_PAGER' => 'Y',
                    'PAGER_TITLE' => '',
                    'PAGER_SHOW_ALWAYS' => 'N',
                    'PAGER_DESC_NUMBERING' => 'N',
                    'PAGER_DESC_NUMBERING_CACHE_TIME' => '36000',
                    'PAGER_SHOW_ALL' => 'N',
                    'COUNT_IN_LINE' => '3',
                    'SHOW_DATE' => 'N',

                    'DISPLAY_COMPARE' => ($arParams['DISPLAY_COMPARE'] ? 'Y' : 'N'),
                    'SHOW_FAVORITE' => $arParams['SHOW_FAVORITE'],

                    'ORDER_VIEW' => $arParams['ORDER_VIEW'],
                    'SHOW_PROPS_TABLE' => 'ROWS',
                    'REPLACED_DETAIL_LINK' => $templateData['DETAIL_PAGE_URL'],

                    'SHOW_SECTION_PREVIEW_DESCRIPTION' => 'N',
                    'SHOW_SECTION_NAME' => 'N',
                    'SHOW_ONE_CLINK_BUY' => $arParams['SHOW_ONE_CLINK_BUY'],
                    'OPT_BUY' => 'N',
                    'HIDE_NO_IMAGE' => 'Y',
                    'DETAIL' => 'Y',
                    'MOBILE_SCROLLED' => false,
                    'USE_FAST_VIEW_PAGE_DETAIL' => 'NO',
                    'USE_REGION' => $arParams['USE_REGION'],
                    'STORES' => $arParams['STORES'],
                    'PRICE_CODE' => $arParams['PRICE_CODE'],
                    'SHOW_CALCULATE_DELIVERY' => $templateData['CALCULATE_DELIVERY'],
                    'EXPRESSION_FOR_CALCULATE_DELIVERY' => $arParams['EXPRESSION_FOR_CALCULATE_DELIVERY'],

                    'USE_STORE' => $arParams['USE_STORE'],
                    'SHOW_EMPTY_STORE' => $arParams['SHOW_EMPTY_STORE'],
                    'SHOW_GENERAL_STORE_INFORMATION' => $arParams['SHOW_GENERAL_STORE_INFORMATION'],
                    'USE_MIN_AMOUNT' => $arParams['USE_MIN_AMOUNT'],
                    'MIN_AMOUNT' => $arParams['MIN_AMOUNT'],
                    'FIELDS' => $arParams['FIELDS'],
                    'USER_FIELDS' => $arParams['USER_FIELDS'],
                    'STORE_PATH' => $arParams['STORE_PATH'],
                    'MAIN_TITLE' => $arParams['MAIN_TITLE'],
                    'STORES_FILTER' => $arParams['STORES_FILTER'],
                    'STORES_FILTER_ORDER' => $arParams['STORES_FILTER_ORDER'],
                    'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
                    'CURRENCY_ID' => $arParams['CURRENCY_ID'],
                    'PRICE_VAT_INCLUDE' => $arParams['PRICE_VAT_INCLUDE'],
                    'COMPATIBLE_MODE' => 'Y',
                    'FILL_ITEM_ALL_PRICES' => 'Y',
                ],
                false, ['HIDE_ICONS' => 'Y']
            );?>
        <?$html_sku = trim(ob_get_clean());?>
    <?endif;?>

    <?if ($html_sku && strpos($html_sku, 'error') === false):?>
        <?if ($bTab):?>
            <?if (!isset($arTabs[$tabCode])):?>
                <?$arTabs[$tabCode] = ['classList' => []];?>
            <?else:?>
                <div class="tab-pane<?=TSolution\Utils::implodeClasses($arTabs[$tabCode]['classList'], leadingDelimiter: true);?>" id="sku">
                    <?=$html_sku;?>
                </div>
            <?endif;?>
        <?else:?>
            <div class="detail-block ordered-block sku" id="sku">
                <h3 class="switcher-title"><?=$arParams['T_SKU'];?></h3>
                <?=$html_sku;?>
            </div>
        <?endif;?>
    <?endif;?>
<?endif;?>
