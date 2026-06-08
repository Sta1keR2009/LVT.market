<?$bTab = isset($tabCode) && $tabCode === 'gift';?>
<?// show goods block?>
<?if (TSolution::isSaleMode()):?>
    <?$bShowGiftsMain = $bShowGiftsDetail = false;?>
    <?if (!isset($html_gifts_main)):?>
        <?ob_start();?>
        <?if ($arParams['USE_GIFTS_MAIN_PR_SECTION_LIST'] === 'Y'):?>
            <?$APPLICATION->IncludeComponent('bitrix:sale.gift.main.products', 'main',
                [
                    'BLOCK_TITLE' => $arParams['GIFTS_MAIN_PRODUCT_DETAIL_BLOCK_TITLE'],
                    'USE_REGION' => $arParams['USE_REGION'] !== 'N' ? 'Y' : 'N',
                    'STORES' => $arParams['STORES'],
                    'SHOW_UNABLE_SKU_PROPS' => $arParams['SHOW_UNABLE_SKU_PROPS'],
                    'PAGE_ELEMENT_COUNT' => $arParams['GIFTS_MAIN_PRODUCT_DETAIL_PAGE_ELEMENT_COUNT'],
                    'BLOCK_TITLE' => $arParams['GIFTS_MAIN_PRODUCT_DETAIL_BLOCK_TITLE'],

                    'OFFERS_FIELD_CODE' => $arParams['OFFERS_FIELD_CODE'],
                    'OFFERS_PROPERTY_CODE' => $arParams['OFFERS_PROPERTY_CODE'],

                    'AJAX_MODE' => $arParams['AJAX_MODE'],
                    'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],
                    'IBLOCK_ID' => $arParams['IBLOCK_ID'],

                    'ELEMENT_SORT_FIELD' => 'ID',
                    'ELEMENT_SORT_ORDER' => 'DESC',
                    'FILTER_NAME' => 'searchFilter',
                    'SECTION_URL' => $arParams['SECTION_URL'],
                    'DETAIL_URL' => $arParams['DETAIL_URL'],
                    'BASKET_URL' => $arParams['BASKET_URL'],
                    'ACTION_VARIABLE' => $arParams['ACTION_VARIABLE'],
                    'PRODUCT_ID_VARIABLE' => $arParams['PRODUCT_ID_VARIABLE'],
                    'SECTION_ID_VARIABLE' => $arParams['SECTION_ID_VARIABLE'],

                    'CACHE_TYPE' => $arParams['CACHE_TYPE'],
                    'SHOW_ONE_CLICK_BUY' => 'N',
                    'CACHE_TIME' => $arParams['CACHE_TIME'],

                    'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
                    'SET_TITLE' => $arParams['SET_TITLE'],
                    'PROPERTY_CODE' => '',
                    'PRICE_CODE' => $arParams['PRICE_CODE'],
                    'USE_PRICE_COUNT' => $arParams['USE_PRICE_COUNT'],
                    'SHOW_PRICE_COUNT' => $arParams['SHOW_PRICE_COUNT'],

                    'PRICE_VAT_INCLUDE' => $arParams['PRICE_VAT_INCLUDE'],
                    'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
                    'CURRENCY_ID' => $arParams['CURRENCY_ID'],
                    'HIDE_NOT_AVAILABLE' => TSolution::GetFrontParametrValue('HIDE_NOT_AVAILABLE'),
                    'TEMPLATE_THEME' => (isset($arParams['TEMPLATE_THEME']) ? $arParams['TEMPLATE_THEME'] : ''),

                    'ADD_PICT_PROP' => (isset($arParams['ADD_PICT_PROP']) ? $arParams['ADD_PICT_PROP'] : ''),

                    'LABEL_PROP' => (isset($arParams['LABEL_PROP']) ? $arParams['LABEL_PROP'] : ''),
                    'OFFER_ADD_PICT_PROP' => (isset($arParams['OFFER_ADD_PICT_PROP']) ? $arParams['OFFER_ADD_PICT_PROP'] : ''),
                    'OFFER_TREE_PROPS' => (isset($arParams['OFFER_TREE_PROPS']) ? $arParams['OFFER_TREE_PROPS'] : ''),
                    'SHOW_DISCOUNT_PERCENT' => 'N',
                    'SHOW_OLD_PRICE' => (isset($arParams['SHOW_OLD_PRICE']) ? $arParams['SHOW_OLD_PRICE'] : ''),
                    'MESS_BTN_BUY' => (isset($arParams['MESS_BTN_BUY']) ? $arParams['MESS_BTN_BUY'] : ''),
                    'MESS_BTN_ADD_TO_BASKET' => (isset($arParams['MESS_BTN_ADD_TO_BASKET']) ? $arParams['MESS_BTN_ADD_TO_BASKET'] : ''),
                    'MESS_BTN_DETAIL' => (isset($arParams['MESS_BTN_DETAIL']) ? $arParams['MESS_BTN_DETAIL'] : ''),
                    'MESS_NOT_AVAILABLE' => (isset($arParams['MESS_NOT_AVAILABLE']) ? $arParams['MESS_NOT_AVAILABLE'] : ''),
                    'ADD_TO_BASKET_ACTION' => (isset($arParams['ADD_TO_BASKET_ACTION']) ? $arParams['ADD_TO_BASKET_ACTION'] : ''),
                    'SHOW_CLOSE_POPUP' => (isset($arParams['SHOW_CLOSE_POPUP']) ? $arParams['SHOW_CLOSE_POPUP'] : ''),
                    'DISPLAY_COMPARE' => (isset($arParams['DISPLAY_COMPARE']) ? $arParams['DISPLAY_COMPARE'] : ''),
                    'COMPARE_PATH' => (isset($arParams['COMPARE_PATH']) ? $arParams['COMPARE_PATH'] : ''),
                    'SHOW_DISCOUNT_TIME' => 'N',
                    'SHOW_DISCOUNT_PERCENT_NUMBER' => 'N',
                    'SALE_STIKER' => $arParams['SALE_STIKER'],
                    'STIKERS_PROP' => $arParams['STIKERS_PROP'],
                    'SHOW_MEASURE' => $arParams['SHOW_MEASURE'],
                    'DISPLAY_TYPE' => 'block',
                    'SHOW_RATING' => $arParams['SHOW_RATING'],
                    'DISPLAY_WISH_BUTTONS' => $arParams['DISPLAY_WISH_BUTTONS'],
                    'DEFAULT_COUNT' => $arParams['DEFAULT_COUNT'],
                    'ELEMENT_IN_ROW' => $arParams['ELEMENT_IN_ROW'],
                    'ITEM_1200' => 5,
                    'IS_AJAX' => $isAjax,
                    'SHOW_FAST_VIEW' => 'N',
                    'HIDE_BLOCK_TITLE' => $bTab ? 'Y' : $arParams['GIFTS_DETAIL_HIDE_BLOCK_TITLE'],
                ] + [
                    'OFFER_ID' => empty($arResult['OFFERS'][$arResult['OFFERS_SELECTED']]['ID']) ? $arResult['ID'] : $arResult['OFFERS'][$arResult['OFFERS_SELECTED']]['ID'],
                    'SECTION_ID' => $arResult['SECTION']['ID'],
                    'ELEMENT_ID' => $arResult['ID'],
                ],
                $component,
                ['HIDE_ICONS' => 'Y']
            );?>
        <?endif;?>
        <?$html_gifts_main = trim(ob_get_clean());?>

        <?$bShowGiftsMain = $html_gifts_main && strpos($html_gifts_main, 'error') === false;?>
    <?endif;?>

    <?if (!isset($html_gifts_detail)):?>
        <?ob_start();?>
        <?if (isset($templateData['GIFTS']) && $templateData['GIFTS']['POTENTIAL_PRODUCT_TO_BUY']):?>
            <?$APPLICATION->IncludeComponent('bitrix:sale.gift.product', 'main',
                [
                    'AJAX_REQUEST' => $arParams['IS_AJAX'],
                    'BORDERED' => 'Y',
                    'CHECK_REQUEST_BLOCK' => $arParams['CHECK_REQUEST_BLOCK'],
                    'ELEMENTS_ROW' => 1,
                    'GRID_GAP' => '20',
                    'IMAGES' => 'PICTURE',
                    'IMAGE_POSITION' => 'LEFT',
                    'IMG_CORNER' => 'N',
                    'IMG_CORNER' => false,
                    'IS_CATALOG_PAGE' => 'N',
                    'IS_COMPACT_SLIDER' => false,
                    'ITEM_1200' => $arParams['ITEM_1200'] ?? 3,
                    'ITEM_380' => '2',
                    'ITEM_768' => '3',
                    'ITEM_992' => '4',
                    'MAXWIDTH_WRAP' => false,
                    'MOBILE_SCROLLED' => false,
                    'NAME_SIZE' => '18',
                    'NARROW' => 'Y',
                    'POSITION_BTNS' => '4',
                    'RIGHT_LINK' => '',
                    'RIGHT_TITLE' => '',
                    'ROW_VIEW' => true,
                    'SHOW_DISCOUNT_PERCENT' => $arParams['SHOW_DISCOUNT_PERCENT'],
                    'SHOW_DISCOUNT_TIME' => $arParams['SHOW_DISCOUNT_TIME'],
                    'SHOW_OLD_PRICE' => $arParams['SHOW_OLD_PRICE'],
                    'SHOW_PREVIEW' => true,
                    'SHOW_PREVIEW_TEXT' => 'N',
                    'SHOW_RATING' => $arParams['SHOW_RATING'],
                    'SHOW_TITLE' => false,
                    'SLIDER' => true,
                    'SLIDER_BUTTONS_BORDERED' => false,
                    'SUBTITLE' => '',
                    'TEXT_CENTER' => false,
                    'TITLE' => '',
                    'TITLE_POSITION' => '',
                    'TYPE_SKU' => 'TYPE_1',
                    'ADD_PICT_PROP' => $arParams['ADD_PICT_PROP'],
                    'ADD_PROPERTIES_TO_BASKET' => $arParams['ADD_PROPERTIES_TO_BASKET'],
                    'BASKET_URL' => $arParams['BASKET_URL'],
                    'BLOCK_TITLE' => $arParams['GIFTS_DETAIL_BLOCK_TITLE'],
                    'CACHE_GROUPS' => $arParams['CACHE_GROUPS'],
                    'CACHE_TIME' => $arParams['CACHE_TIME'],
                    'CACHE_TYPE' => $arParams['CACHE_TYPE'],
                    "CART_PROPERTIES_{$arParams['SKU_IBLOCK_ID']}" => $arParams['OFFERS_CART_PROPERTIES'],
                    'CONVERT_CURRENCY' => $arParams['CONVERT_CURRENCY'],
                    'CURRENCY_ID' => $arParams['CURRENCY_ID'],
                    'DEFAULT_COUNT' => $arParams['DEFAULT_COUNT'],
                    'DISPLAY_COMPARE' => $arParams['DISPLAY_COMPARE'],
                    'DISPLAY_TYPE' => 'block',
                    'ELEMENT_IN_ROW' => $arParams['ELEMENT_IN_ROW'] ?? 5,
                    'HIDE_BLOCK_TITLE' => $bTab ? 'Y' : $arParams['GIFTS_DETAIL_HIDE_BLOCK_TITLE'],
                    'HIDE_NOT_AVAILABLE' => TSolution::GetFrontParametrValue('HIDE_NOT_AVAILABLE'),
                    'IBLOCK_ID' => $arParams['IBLOCK_ID'],
                    'ITEM_0' => '2',
                    'LINE_ELEMENT_COUNT' => $arParams['GIFTS_DETAIL_PAGE_ELEMENT_COUNT'],
                    'MAX_GALLERY_ITEMS' => $arParams['MAX_GALLERY_ITEMS'] ?? 5,
                    'MESS_BTN_BUY' => $arParams['GIFTS_MESS_BTN_BUY'],
                    'MESS_BTN_DETAIL' => $arParams['MESS_BTN_DETAIL'],
                    'MESS_BTN_SUBSCRIBE' => $arParams['MESS_BTN_SUBSCRIBE'],
                    'OFFERS_PROPERTY_CODE' => $arParams['SKU_PROPERTY_CODE'],
                    'OFFER_ADD_PICT_PROP' => $arParams['OFFER_ADD_PICT_PROP'],
                    'OFFER_HIDE_NAME_PROPS' => $arParams['OFFER_HIDE_NAME_PROPS'],
                    'OFFER_TREE_PROPS' => $arParams['SKU_TREE_PROPS'],
                    "OFFER_TREE_PROPS_{$arParams['SKU_IBLOCK_ID']}" => $arParams['OFFER_TREE_PROPS'],
                    'ORDER_VIEW' => TSolution::GetFrontParametrValue('ORDER_VIEW'),
                    'PAGE_ELEMENT_COUNT' => $arParams['GIFTS_DETAIL_PAGE_ELEMENT_COUNT'],
                    'PARTIAL_PRODUCT_PROPERTIES' => $arParams['PARTIAL_PRODUCT_PROPERTIES'],
                    'PRICE_CODE' => $arParams['PRICE_CODE'],
                    'PRICE_VAT_INCLUDE' => $arParams['PRICE_VAT_INCLUDE'],
                    'PRODUCT_PROPS_VARIABLE' => $arParams['PRODUCT_PROPS_VARIABLE'],
                    'PRODUCT_QUANTITY_VARIABLE' => $arParams['PRODUCT_QUANTITY_VARIABLE'],
                    'PRODUCT_SUBSCRIPTION' => $arParams['PRODUCT_SUBSCRIPTION'],
                    'SALE_STIKER' => $arParams['SALE_STIKER'],
                    'SHOW_DISCOUNT_PERCENT' => 'Y',
                    'SHOW_DISCOUNT_PERCENT_NUMBER' => 'N',
                    'SHOW_DISCOUNT_TIME' => 'N',
                    'SHOW_FAST_VIEW' => 'N',
                    'SHOW_FAVORITE' => $arParams['SHOW_FAVORITE'],
                    'SHOW_GALLERY' => $arParams['SHOW_GALLERY'],
                    'SHOW_IMAGE' => $arParams['GIFTS_SHOW_IMAGE'],
                    'SHOW_MEASURE' => $arParams['SHOW_MEASURE'],
                    'SHOW_NAME' => $arParams['GIFTS_SHOW_NAME'],
                    'SHOW_OLD_PRICE' => $arParams['GIFTS_SHOW_OLD_PRICE'],
                    'SHOW_ONE_CLICK_BUY' => 'N',
                    'SHOW_PRICE_COUNT' => $arParams['SHOW_PRICE_COUNT'],
                    "SHOW_PRODUCTS_{$arParams['IBLOCK_ID']}" => 'Y',
                    'SHOW_RATING' => $arParams['SHOW_RATING'],
                    'SHOW_UNABLE_SKU_PROPS' => $arParams['SHOW_UNABLE_SKU_PROPS'],
                    'SKU_IBLOCK_ID' => $arParams['SKU_IBLOCK_ID'],
                    'SKU_PROPERTY_CODE' => $arParams['SKU_PROPERTY_CODE'],
                    'SKU_TREE_PROPS' => $arParams['SKU_TREE_PROPS'],
                    'SKU_SORT_FIELD' => $arParams['SKU_SORT_FIELD'],
                    'SKU_SORT_FIELD2' => $arParams['SKU_SORT_FIELD2'],
                    'SKU_SORT_ORDER' => $arParams['SKU_SORT_ORDER'],
                    'SKU_SORT_ORDER2' => $arParams['SKU_SORT_ORDER2'],
                    'OFFERS_SORT_FIELD' => $arParams['SKU_SORT_FIELD'],
                    'OFFERS_SORT_FIELD2' => $arParams['SKU_SORT_FIELD2'],
                    'OFFERS_SORT_ORDER' => $arParams['SKU_SORT_ORDER'],
                    'OFFERS_SORT_ORDER2' => $arParams['SKU_SORT_ORDER2'],
                    'STIKERS_PROP' => $arParams['STIKERS_PROP'],
                    'STORES' => $arParams['STORES'],
                    'TEMPLATE_THEME' => $arParams['TEMPLATE_THEME'],
                    'TEXT_LABEL_GIFT' => $arParams['GIFTS_DETAIL_TEXT_LABEL_GIFT'],
                    'USE_PRICE_COUNT' => $arParams['USE_PRICE_COUNT'],
                    'USE_PRODUCT_QUANTITY' => 'N',
                    'USE_REGION' => $arParams['USE_REGION'] !== 'N' ? 'Y' : 'N',
                    'ACTION_VARIABLE' => $arParams['ACTION_VARIABLE'],
                    'ADD_URL_TEMPLATE' => $templateData['GIFTS']['ADD_URL_TEMPLATE'],
                    'BUY_URL_TEMPLATE' => $templateData['GIFTS']['BUY_URL_TEMPLATE'],
                    'COMPARE_URL_TEMPLATE' => $templateData['GIFTS']['COMPARE_URL_TEMPLATE'],
                    'PRODUCT_ID_VARIABLE' => $arParams['PRODUCT_ID_VARIABLE'],
                    'SUBSCRIBE_URL_TEMPLATE' => $templateData['GIFTS']['SUBSCRIBE_URL_TEMPLATE'],

                    'POTENTIAL_PRODUCT_TO_BUY' => $templateData['GIFTS']['POTENTIAL_PRODUCT_TO_BUY'],
                ],
                $component,
                ['HIDE_ICONS' => 'Y']
            );?>
        <?endif;?>
        <?$html_gifts_detail = trim(ob_get_clean());?>

        <?$bShowGiftsDetail = $html_gifts_detail && strpos($html_gifts_detail, 'error') === false;?>
    <?endif;?>

    <?if ($bShowGiftsMain || $bShowGiftsDetail):?>
        <?if ($bTab):?>
            <?if (!isset($arTabs[$tabCode])):?>
                <?$arTabs[$tabCode] = ['classList' => []];?>
            <?else:?>
                <div class="tab-pane<?=TSolution\Utils::implodeClasses($arTabs[$tabCode]['classList'], leadingDelimiter: true);?>" id="gift">
                    <?=$bShowGiftsMain ? $html_gifts_main : '';?>
                    <?=$bShowGiftsDetail ? $html_gifts_detail : '';?>
                </div>
            <?endif;?>
        <?else:?>
            <div class="detail-block ordered-block gift">
                <?=$bShowGiftsMain ? $html_gifts_main : '';?>
                <?=$bShowGiftsDetail ? $html_gifts_detail : '';?>
            </div>
        <?endif;?>
    <?endif;?>
<?endif;?>
