<?$bTab = isset($tabCode) && $tabCode === 'complect';?>
<?if (
    isset($templateData['CATALOG_SETS'])
    && $templateData['CATALOG_SETS']['SET_ITEMS']
    && ($arProductSet = array_column($templateData['CATALOG_SETS']['SET_ITEMS'], 'ID'))
):?>
    <?php
    $GLOBALS['arSetsFilter'] = [
        'ID' => $arProductSet,
        'ITEMS' => $templateData['CATALOG_SETS']['SET_ITEMS'],
    ];
    ?>
    <?ob_start();?>
        <?$APPLICATION->IncludeComponent(
            'bitrix:catalog.section',
            'catalog_complect',
            [
                'IBLOCK_TYPE' => 'aspro_lite_catalog',
                'ELEMENT_IN_ROW' => 6,
                'IBLOCK_ID' => $arParams['IBLOCK_ID'],
                'PAGE_ELEMENT_COUNT' => 999,
                'PROPERTY_CODE' => [
                ],
                'FILTER_NAME' => 'arSetsFilter',
                'FIELD_CODE' => [
                    'NAME',
                    'PREVIEW_PICTURE',
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
                'SHOW_GALLERY' => 'N',

                'ORDER_VIEW' => TSolution::GetFrontParametrValue('ORDER_VIEW') == 'Y',
                'SHOW_PROPS_TABLE' => 'ROWS',

                'SHOW_SECTION_PREVIEW_DESCRIPTION' => 'N',
                'SHOW_SECTION_NAME' => 'N',
                'SHOW_ONE_CLINK_BUY' => $arParams['SHOW_ONE_CLINK_BUY'],
                'OPT_BUY' => 'N',
                'DETAIL' => 'Y',
                'HIDE_NO_IMAGE' => 'Y',
                'MOBILE_SCROLLED' => true,
                'USE_FAST_VIEW_PAGE_DETAIL' => 'NO',
                'USE_REGION' => $arParams['USE_REGION'],
                'STORES' => $arParams['STORES'],
                'PRICE_CODE' => $arParams['PRICE_CODE'],

                'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
                'CURRENCY_ID' => $arParams['CURRENCY_ID'],
                'PRICE_VAT_INCLUDE' => $arParams['PRICE_VAT_INCLUDE'],
                'COMPATIBLE_MODE' => 'Y',
                'OFFERS_FIELD_CODE' => ['ID', 'NAME', 'DETAIL_PAGE_URL'],
                'SHOW_ALL_WO_SECTION' => 'Y',
            ],
            false,
            ['HIDE_ICONS' => 'Y']
        );?>
    <?$html_sets = trim(ob_get_clean());?>

    <?if ($bTab):?>
        <?if (!isset($arTabs[$tabCode])):?>
            <?$arTabs[$tabCode] = ['classList' => []];?>
        <?else:?>
            <div class="tab-pane ordered-block--hide-icons<?=TSolution\Utils::implodeClasses($arTabs[$tabCode]['classList'], leadingDelimiter: true);?>" id="complect">
                <?=$html_sets;?>
            </div>
        <?endif;?>
    <?else:?>
        <div class="detail-block ordered-block ordered-block--hide-icons complect">
            <h3 class="switcher-title"><?=$arParams['T_COMPLECT'];?></h3>
            <?=$html_sets;?>
        </div>
    <?endif;?>
<?endif;?>
