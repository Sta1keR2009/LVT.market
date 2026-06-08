<?$bTab = isset($tabCode) && $tabCode === 'services';?>
<?// show services block?>
<?if ($templateData['SERVICES']['VALUE'] && $templateData['SERVICES']['IBLOCK_ID']):?>
    <?if (!isset($html_services)):?>
        <?php
        $GLOBALS['arrServicesFilter'] = ['ID' => $templateData['SERVICES']['VALUE']];
        $GLOBALS['arrServicesFilter'] = TSolution\Regionality::mergeFilterWithRegionFilter($GLOBALS['arrServicesFilter']);
        $bCheckAjaxBlock = TSolution::checkRequestBlock('services-list-inner');
        ?>
        <?ob_start();?>
            <?$APPLICATION->IncludeComponent(
                'bitrix:news.list',
                'blog-list',
                [
                    'IBLOCK_TYPE' => 'aspro_lite_content',
                    'IBLOCK_ID' => $templateData['SERVICES']['IBLOCK_ID'],
                    'NEWS_COUNT' => '20',
                    'SORT_BY1' => 'SORT',
                    'SORT_ORDER1' => 'ASC',
                    'SORT_BY2' => 'ID',
                    'SORT_ORDER2' => 'DESC',
                    'FILTER_NAME' => 'arrServicesFilter',
                    'FIELD_CODE' => [
                        0 => 'NAME',
                        1 => 'PREVIEW_TEXT',
                        2 => 'PREVIEW_PICTURE',
                        4 => '',
                    ],
                    'PROPERTY_CODE' => [
                        0 => 'PRICE',
                        1 => 'PRICEOLD',
                        2 => 'ECONOMY',
                        3 => 'PERIOD',
                        4 => 'REDIRECT',
                        5 => '',
                    ],
                    'CHECK_DATES' => 'Y',
                    'DETAIL_URL' => '',
                    'AJAX_MODE' => 'N',
                    'AJAX_OPTION_JUMP' => 'N',
                    'AJAX_OPTION_STYLE' => 'Y',
                    'AJAX_OPTION_HISTORY' => 'N',
                    'CACHE_TYPE' => 'A',
                    'CACHE_TIME' => '36000000',
                    'CACHE_FILTER' => 'Y',
                    'CACHE_GROUPS' => 'N',
                    'PREVIEW_TRUNCATE_LEN' => '150',
                    'ACTIVE_DATE_FORMAT' => 'd.m.Y',
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
                    'DISPLAY_BOTTOM_PAGER' => 'N',
                    'PAGER_TITLE' => '',
                    'PAGER_SHOW_ALWAYS' => 'N',
                    'PAGER_DESC_NUMBERING' => 'N',
                    'PAGER_DESC_NUMBERING_CACHE_TIME' => '36000',
                    'PAGER_SHOW_ALL' => 'N',
                    'COUNT_IN_LINE' => '2',

                    'ROW_VIEW' => true,
                    'BORDER' => true,
                    'DARK_HOVER' => false,
                    'ITEM_HOVER_SHADOW' => true,
                    'ROUNDED' => true,
                    'ROUNDED_IMAGE' => true,
                    'ITEM_PADDING' => true,
                    'ELEMENTS_ROW' => 4,
                    'MAXWIDTH_WRAP' => 'N',
                    'MOBILE_SCROLLED' => 'Y',
                    'NARROW' => false,
                    'ITEMS_OFFSET' => false,
                    'IMAGES' => 'PICTURE',
                    'IMAGE_POSITION' => 'LEFT',
                    'SHOW_PREVIEW' => true,
                    'SHOW_TITLE' => false,
                    'SHOW_SECTION' => 'Y',
                    'PRICE_POSITION' => 'RIGHT',
                    'TITLE_POSITION' => '',
                    'TITLE' => '',
                    'RIGHT_TITLE' => '',
                    'RIGHT_LINK' => '',
                    'CHECK_REQUEST_BLOCK' => $bCheckAjaxBlock,
                    'IS_AJAX' => TSolution::checkAjaxRequest() && $bCheckAjaxBlock,
                    'NAME_SIZE' => '18',
                    'SUBTITLE' => '',
                    'SHOW_PREVIEW_TEXT' => 'N',
                ],
                false, ['HIDE_ICONS' => 'Y']
            );?>
        <?$html_services = trim(ob_get_clean());?>
    <?endif;?>

    <?if ($html_services && strpos($html_services, 'error') === false):?>
        <?if ($bTab):?>
            <?if (!isset($arTabs[$tabCode])):?>
                <?$arTabs[$tabCode] = ['classList' => []];?>
            <?else:?>
                <div class="tab-pane<?=TSolution\Utils::implodeClasses($arTabs[$tabCode]['classList'], leadingDelimiter: true);?>" id="services">
                    <?=$html_services;?>
                </div>
            <?endif;?>
        <?else:?>
            <div class="detail-block ordered-block services">
                <h3 class="switcher-title"><?=$arParams['T_SERVICES'];?></h3>
                <?=$html_services;?>
            </div>
        <?endif;?>
    <?endif;?>
<?endif;?>
