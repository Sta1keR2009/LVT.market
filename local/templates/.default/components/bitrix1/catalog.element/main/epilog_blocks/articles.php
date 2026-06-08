<?$bTab = isset($tabCode) && $tabCode === 'articles';?>
<?// show news block?>
<?if ($templateData['ARTICLES']['VALUE'] && $templateData['ARTICLES']['IBLOCK_ID']):?>
    <?if (!isset($html_articles)):?>
        <?php
        $GLOBALS['arrArticlesFilter'] = ['ID' => $templateData['ARTICLES']['VALUE']];
        $GLOBALS['arrArticlesFilter'] = TSolution\Regionality::mergeFilterWithRegionFilter($GLOBALS['arrArticlesFilter']);
        $bCheckAjaxBlock = TSolution::checkRequestBlock('articles-list-inner');
        ?>
        <?ob_start();?>
            <?$APPLICATION->IncludeComponent(
                'bitrix:news.list',
                'blog-list',
                [
                    'IBLOCK_TYPE' => 'aspro_lite_content',
                    'IBLOCK_ID' => $templateData['ARTICLES']['IBLOCK_ID'],
                    'NEWS_COUNT' => '20',
                    'SORT_BY1' => 'ACTIVE_FROM',
                    'SORT_ORDER1' => 'DESC',
                    'SORT_BY2' => 'SORT',
                    'SORT_ORDER2' => 'ASC',
                    'FILTER_NAME' => 'arrArticlesFilter',
                    'FIELD_CODE' => [
                        0 => 'NAME',
                        2 => 'PREVIEW_PICTURE',
                        3 => 'DATE_ACTIVE_FROM',
                        4 => '',
                    ],
                    'PROPERTY_CODE' => [
                        0 => 'PERIOD',
                        1 => 'REDIRECT',
                        2 => '',
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
                    'DISPLAY_BOTTOM_PAGER' => 'N',
                    'PAGER_TITLE' => '',
                    'PAGER_SHOW_ALWAYS' => 'N',
                    'PAGER_DESC_NUMBERING' => 'N',
                    'PAGER_DESC_NUMBERING_CACHE_TIME' => '36000',
                    'PAGER_SHOW_ALL' => 'N',
                    'COUNT_IN_LINE' => '3',
                    'SHOW_DATE' => 'N',

                    'ROW_VIEW' => true,
                    'BORDER' => true,
                    'ITEM_HOVER_SHADOW' => true,
                    'DARK_HOVER' => false,
                    'ROUNDED' => true,
                    'ROUNDED_IMAGE' => true,
                    'ITEM_PADDING' => true,
                    'ELEMENTS_ROW' => 4,
                    'MAXWIDTH_WRAP' => 'N',
                    'MOBILE_SCROLLED' => true,
                    'NARROW' => false,
                    'ITEMS_OFFSET' => false,
                    'IMAGES' => 'PICTURE',
                    'IMAGE_POSITION' => 'LEFT',
                    'SHOW_PREVIEW' => true,
                    'SHOW_TITLE' => false,
                    'SHOW_SECTION' => 'Y',
                    'TITLE_POSITION' => '',
                    'TITLE' => '',
                    'RIGHT_TITLE' => '',
                    'RIGHT_LINK' => '',
                    'CHECK_REQUEST_BLOCK' => $bCheckAjaxBlock,
                    'IS_AJAX' => TSolution::checkAjaxRequest() && $bCheckAjaxBlock,
                    'NAME_SIZE' => '18',
                    'SUBTITLE' => '',
                    'SHOW_PREVIEW_TEXT' => 'N',
                    'SHOW_SECTION_NAME' => 'Y',
                ],
                false, ['HIDE_ICONS' => 'Y']
            );?>
        <?$html_articles = trim(ob_get_clean());?>
    <?endif;?>

    <?if ($html_articles && strpos($html_articles, 'error') === false):?>
        <?if ($bTab):?>
            <?if (!isset($arTabs[$tabCode])):?>
                <?$arTabs[$tabCode] = ['classList' => []];?>
            <?else:?>
                <div class="tab-pane<?=TSolution\Utils::implodeClasses($arTabs[$tabCode]['classList'], leadingDelimiter: true);?>" id="articles">
                    <?=$html_articles;?>
                </div>
            <?endif;?>
        <?else:?>
            <div class="detail-block ordered-block articles">
                <h3 class="switcher-title"><?=$arParams['T_ARTICLES'];?></h3>
                <?=$html_articles;?>
            </div>
        <?endif;?>
    <?endif;?>
<?endif;?>
