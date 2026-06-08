<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$this->setFrameMode(true);?>
<?use \Bitrix\Main\Localization\Loc,
      \Bitrix\Main\Web\Json;?>
<?if($arResult["ITEMS"]):?>
    <?
    $templateData['ITEMS'] = true;
    $templateData['SHOW_DISCOUNT_COUNTER'] = $arParams["SHOW_DISCOUNT_TIME"] === "Y" && $arParams['SHOW_DISCOUNT_TIME_IN_LIST'] !== 'N';

    $bHasBottomPager = $arParams["DISPLAY_BOTTOM_PAGER"] == "Y" && $arResult["NAV_STRING"];
    $bUseSchema = !(isset($arParams["NO_USE_SHCEMA_ORG"]) && $arParams["NO_USE_SHCEMA_ORG"] == "Y");
    $bAjax = $arParams["AJAX_REQUEST"]=="Y";
    $bMobileScrolledItems = $arParams['MOBILE_SCROLLED'];

    $bOrderViewBasket = $arParams['ORDER_VIEW'];
    $basketURL = (strlen(trim($arTheme['ORDER_VIEW']['DEPENDENT_PARAMS']['URL_BASKET_SECTION']['VALUE'])) ? trim($arTheme['ORDER_VIEW']['DEPENDENT_PARAMS']['URL_BASKET_SECTION']['VALUE']) : '');
    $bOptBuy = false;

    $bHideProps = $arParams['SHOW_PROPS_TABLE'] == 'not';
    $bRowProps = $arParams['SHOW_PROPS_TABLE'] == 'rows';
    $bColProps = $arParams['SHOW_PROPS_TABLE'] == 'cols';

    $bShowCalculateDelivery = $arParams['SHOW_CALCULATE_DELIVERY'];
    $gridClass .= ' grid-list--items-1';

    if (!$bHideProps) {
        $gridClass .= ' table-props-rows';
    }
    if ($bColProps && $arResult['SHOW_COLS_PROP']) {
        $gridClass .= ' table-props-cols scrollbar scroller';
    }

    if($bMobileScrolledItems){
        $gridClass .= ' mobile-scrolled mobile-scrolled--items-3 mobile-offset';
    } else {
        $gridClass .= ' grid-list--compact';
    }

    if (!$arParams['ITEMS_OFFSET']) {
        $gridClass .= ' grid-list--no-gap';
    } elseif ($arParams['GRID_GAP']) {
        $gridClass .= ' grid-list--gap-'.$arParams['GRID_GAP'];
    }

    $itemClass = ' bordered shadow-hovered shadow-hovered-f600 shadow-no-border-hovered side-icons-hover bg-theme-parent-hover border-theme-parent-hover js-popup-block';
    // $itemClass .= ' rounded-4';

    $bBottomButtons = (isset($arParams['POSITION_BTNS']) && $arParams['POSITION_BTNS'] == '4');
    $bShowCompare = $arParams['DISPLAY_COMPARE'];
    $bShowFavorit = $arParams['SHOW_FAVORITE'] == 'Y';
    $bShowRating = $arParams['SHOW_RATING'] == 'Y';
    $bDetail = isset($arParams['DETAIL']) && $arParams['DETAIL'] === 'Y';
    $bUseSelectOffer = false;
    ?>
    <?$templateData['HAS_CHARACTERISTICS'] = false;?>
    <?if(!$bAjax):?>
    <div class="catalog-items <?=$templateName;?>_template <?=$arParams['IS_COMPACT_SLIDER'] ? 'compact-catalog-slider' : ''?>">
        <!-- noindex -->
        <template class="props-template">
            <?TSolution\Functions::showBlockHtml(['FILE' => 'catalog/props/table.php']);?>
        </template>
        <!-- /noindex -->
        <div class="fast_view_params" data-params="<?=urlencode(serialize($arTransferParams));?>"></div>
        <?if ($arResult['SKU_CONFIG']):?><div class="js-sku-config" data-value='<?=str_replace('\'', '"', CUtil::PhpToJSObject($arResult['SKU_CONFIG'], false, true))?>'></div><?endif;?>
        <div class="catalog-table" <?if ($bUseSchema):?>itemscope itemtype="http://schema.org/ItemList"<?endif;?> >
            <div class="catalog-table__outer-wrapper catalog-table--hidden bordered outer-rounded-x">
            <?if($bOptBuy):?>
                <div class="product-info-headnote opt-buy">
                    <div class="line-block flexbox--justify-beetwen flex-1">
                        <div class="line-block__item">
                            <div class="form-checkbox">
                                <input type="checkbox" class="form-checkbox__input" id="check_all_item" name="check_all_item" value="Y">
                                <label for="check_all_item" class="form-checkbox__label form-checkbox__label--sm">
                                    <span>
                                        <?=Loc::getMessage("SELECT_ALL_ITEMS");?>
                                    </span>
                                    <span class="form-checkbox__box"></span>
                                </label>
                            </div>
                        </div>
                        <div class="line-block__item opt-buy__buttons">
                            <div class="line-block line-block--8 line-block--8-vertical flexbox--wrap flexbox--justify-center">
                                <div class="line-block__item flex-1">
                                    <span class="opt_action btn btn-default btn-sm btn-wide no-action" data-action="basket" data-iblock_id="<?=$arParams["IBLOCK_ID"]?>">
                                        <span>
                                            <?=\Bitrix\Main\Config\Option::get(VENDOR_MODULE_ID, "EXPRESSION_ADDTOBASKET_BUTTON_DEFAULT", GetMessage("BUTTON_TO_CART"));?>
                                        </span>
                                    </span>
                                </div>
                                <?if (
                                    (
                                        $bShowCompare ||
                                        $bShowFavorit
                                    ) &&
                                    (
                                        !$arItem['HAS_SKU'] ||
                                        $arParams['TYPE_SKU'] !== 'TYPE_2' ||
                                        $arBasketConfig['ACTION'] !== 'MORE'
                                    )
                                ):?>
                                    <div class="line-block__item js-replace-icons">
                                        <?if ($bShowFavorit):?>
                                            <?=\TSolution\Product\Common::getActionIcon([
                                                'ITEM' => $arItem,
                                                'PARAMS' => $arParams,
                                                'CLASS' => 'auto opt_action no-action',
                                            ])?>
                                        <?endif;?>
                                        <?if ($bShowCompare):?>
                                            <?=\TSolution\Product\Common::getActionIcon([
                                                'ITEM' => $arItem,
                                                'PARAMS' => $arParams,
                                                'TYPE' => 'compare',
                                                'CLASS' => 'auto opt_action no-action',
                                                'SVG_SIZE' => ['WIDTH' => 20,'HEIGHT' => 16],
                                            ])?>
                                        <?endif;?>
                                    </div>
                                <?endif;?>
                            </div>
                        </div>
                    </div>
                </div>
            <?endif;?>
            <div id="table-scroller-wrapper" class="js_append ajax_load list grid-list <?=$gridClass?>">
                <div id="table-scroller-wrapper__header" class="hide-600">

                    <?if ($arResult['SHOW_COLS_PROP']  && $bColProps):?>
                        <?$templateData['HAS_CHARACTERISTICS'] = true;?>
                        <div class="product-info-head catalog-table__item bordered grey-bg hide-991">
                            <div class="flexbox flexbox--direction-row">
                                <?if ($bOptBuy):?>
                                    <div class="catalog-table__item-wrapper">
                                        <div class="form-checkbox">
                                            <label class="form-checkbox__label form-checkbox__label--no-text"></label>
                                        </div>
                                    </div>
                                <?endif;?>
                                <div class="catalog-table__item-wrapper"><div class="image-list"></div></div>
                                <div class="flex-1 flexbox flexbox--direction-row">
                                    <div class="catalog-table__info-top">
                                        <div class="catalog-table__item-wrapper">
                                            <div class="font_13 color_999"><?=Loc::getMessage('NAME_PRODUCT')?></div>
                                        </div>
                                    </div>
                                    <?foreach ($arResult['COLS_PROP'] as $arProp):?>
                                        <div class="catalog-table__item-wrapper props hide-991">
                                            <div class="font_13 color_999 font_short properties__title">
                                                <?=$arProp['NAME'];?>
                                                <?if($arProp["HINT"] && $arParams["SHOW_HINTS"]=="Y"):?>
                                                    <div class="hint hint--down">
                                                        <span class="hint__icon rounded bg-theme-hover border-theme-hover bordered"><i>?</i></span>
                                                        <div class="tooltip"><?=$arProp["HINT"]?></div>
                                                    </div>
                                                <?endif;?>
                                            </div>
                                        </div>
                                    <?endforeach;?>
                                    <div class="catalog-table__info-bottom">
                                        <div class="catalog-table__item-wrapper">
                                            <div class="font_13 color_999"><?=Loc::getMessage('PRICE_PRODUCT')?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?endif;?>
                </div>
    <?endif;?>
        <?foreach($arResult["ITEMS"] as $key => $arItem){?>
            <?$this->AddEditAction($arItem['ID'], $arItem['EDIT_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_EDIT"));
            $this->AddDeleteAction($arItem['ID'], $arItem['DELETE_LINK'], CIBlock::GetArrayByID($arParams["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BCS_ELEMENT_DELETE_CONFIRM')));

            $item_id = $arItem["ID"];

            if (isset($arParams['ID_FOR_TABS']) && $arParams['ID_FOR_TABS'] == 'Y') {
                $arItem["strMainID"] = $this->GetEditAreaId($arItem['ID'])."_".$arParams["FILTER_HIT_PROP"];
            } else {
                $arItem["strMainID"] = $this->GetEditAreaId($arItem['ID']);
            }

            $elementName = ((isset($arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']) && $arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']) ? $arItem['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'] : $arItem['NAME']);

            $dataItem = TSolution::getDataItem($arItem);

            $article = $arItem['DISPLAY_PROPERTIES']['CML2_ARTICLE']['VALUE'] ?? $arItem['DISPLAY_PROPERTIES']['ARTICLE']['VALUE'];

            //unset($arItem['OFFERS']); // get correct totalCount
            $totalCount = TSolution\Product\Quantity::getTotalCount([
                'ITEM' => $arItem,
                'PARAMS' => $arParams
            ]);
            $arStatus = TSolution\Product\Quantity::getStatus([
                'ITEM' => $arItem,
                'PARAMS' => $arParams,
                'TOTAL_COUNT' => $totalCount,
                'IS_DETAIL' => $bDetail,
            ]);

            /* sku replace start */
            $arCurrentOffer = $arItem['SKU']['CURRENT'];

            if ($arCurrentOffer) {
                $arItem['PARENT_IMG'] = '';
                if ($arItem['PREVIEW_PICTURE']) {
                    $arItem['PARENT_IMG'] = $arItem['PREVIEW_PICTURE'];
                } elseif ($arItem['DETAIL_PICTURE']) {
                    $arItem['PARENT_IMG'] = $arItem['DETAIL_PICTURE'];
                }

                $oid = \Bitrix\Main\Config\Option::get(VENDOR_MODULE_ID, 'CATALOG_OID', 'oid');
                if ($oid) {
                    $arItem['DETAIL_PAGE_URL'].= '?'.$oid.'='.$arCurrentOffer['ID'];
                    $arCurrentOffer['DETAIL_PAGE_URL'] = $arItem['DETAIL_PAGE_URL'];
                }
                if ($arCurrentOffer['PREVIEW_PICTURE'] || $arCurrentOffer['DETAIL_PICTURE']) {
                    if ($arCurrentOffer['PREVIEW_PICTURE']) {
                        $arItem['PREVIEW_PICTURE'] = $arCurrentOffer['PREVIEW_PICTURE'];
                    } elseif ($arCurrentOffer['DETAIL_PICTURE']) {
                        $arItem['PREVIEW_PICTURE'] = $arCurrentOffer['DETAIL_PICTURE'];
                    }
                }
                if (!$arCurrentOffer['PREVIEW_PICTURE'] && !$arCurrentOffer['DETAIL_PICTURE']) {
                    if ($arItem['PREVIEW_PICTURE']) {
                        $arCurrentOffer['PREVIEW_PICTURE'] = $arItem['PREVIEW_PICTURE'];
                    } elseif ($arItem['DETAIL_PICTURE']) {
                        $arCurrentOffer['PREVIEW_PICTURE'] = $arItem['DETAIL_PICTURE'];
                    }
                }

                if ($arCurrentOffer["DISPLAY_PROPERTIES"]["CML2_ARTICLE"]["VALUE"] || $arCurrentOffer["DISPLAY_PROPERTIES"]["ARTICLE"]["VALUE"]) {
                    $article = $arCurrentOffer['DISPLAY_PROPERTIES']['CML2_ARTICLE']['VALUE'] ?? $arCurrentOffer["DISPLAY_PROPERTIES"]["ARTICLE"]["VALUE"];
                }

                $arItem["DISPLAY_PROPERTIES"]["FORM_ORDER"] = $arCurrentOffer["DISPLAY_PROPERTIES"]["FORM_ORDER"];
                $arItem["DISPLAY_PROPERTIES"]["PRICE"] = $arCurrentOffer["DISPLAY_PROPERTIES"]["PRICE"];

                if($arParams["SET_SKU_TITLE"] === "Y" && isset($arCurrentOffer['NAME'])){
                    $arItem['NAME'] = $elementName = $arCurrentOffer['NAME'];
                   }

                $arItem['OFFER_PROP'] = TSolution::PrepareItemProps($arCurrentOffer['DISPLAY_PROPERTIES']);

                $dataItem = TSolution::getDataItem($arCurrentOffer);

                $totalCount = TSolution\Product\Quantity::getTotalCount([
                    'ITEM' => $arCurrentOffer,
                    'PARAMS' => $arParams
                ]);
                $arStatus = TSolution\Product\Quantity::getStatus([
                    'ITEM' => $arCurrentOffer,
                    'PARAMS' => $arParams,
                    'TOTAL_COUNT' => $totalCount
                ]);
            }
            $bOrderButton = ($arItem["DISPLAY_PROPERTIES"]["FORM_ORDER"]["VALUE_XML_ID"] == "YES");

            $status = $arStatus['NAME'];
            $statusCode = $arStatus['CODE'];
            /* sku replace end */
            ?>

            <? $propsHTML = ''; ?>
            <? if (!$bHideProps): ?>
                <?
                $templateData['HAS_CHARACTERISTICS'] = true;
                $cntChars = count($arItem['PROPS']) + count((array)$arItem['OFFER_PROP']);
                ?>
                <? ob_start(); ?>
                <div class="catalog-table__item-wrapper hide-600 <?=($bColProps ? 'visible-991' : '')?>">
                    <?TSolution\Functions::showBlockHtml([
                        'FILE' => '/catalog/props_in_section.php',
                        'VIEW' => 'table',
                        'ITEM' => $arItem,
                        'PARAMS' => [
                            'IS_DETAIL' => $bDetail,
                            'ITEM_CLASSES' => 'font_14',
                            'SHOW_HINTS' => $arParams['SHOW_HINTS'] === 'Y',
                            'WRAPPER_CLASSES' => 'line-block line-block--gap line-block--align-normal flexbox--wrap js-offers-prop'
                                .($bDetail ? ' flexbox' : '')
                                .(!$cntChars ? ' hidden' : ''),
                        ],
                    ])?>
                </div>
                <? $propsHTML = ob_get_clean(); ?>
            <? endif; ?>

            <div class="catalog-table__wrapper grid-list__item grid-list-border-outer">
                <?if (TSolution::isSaleMode()):?>
                    <div class="basket_props_block" id="bx_basket_div_<?=$arItem["ID"];?>_<?=$arParams["FILTER_HIT_PROP"]?>" style="display: none;">
                        <?if (!empty($arItem['PRODUCT_PROPERTIES_FILL'])):?>
                            <?foreach ($arItem['PRODUCT_PROPERTIES_FILL'] as $propID => $propInfo):?>
                                <input type="hidden" name="<?=$arParams['PRODUCT_PROPS_VARIABLE'];?>[<?=$propID;?>]" value="<?=htmlspecialcharsbx($propInfo['ID']);?>">
                                <?
                                if (isset($arItem['PRODUCT_PROPERTIES'][$propID])){
                                    unset($arItem['PRODUCT_PROPERTIES'][$propID]);
                                }
                                ?>
                            <?endforeach;?>
                        <?endif;?>
                        <?if ($arItem['PRODUCT_PROPERTIES']):?>
                            <div class="wrapper">
                                <?foreach($arItem['PRODUCT_PROPERTIES'] as $propID => $propInfo):?>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group fill-animate">
                                                <?if(
                                                    'L' == $arItem['PROPERTIES'][$propID]['PROPERTY_TYPE'] &&
                                                    'C' == $arItem['PROPERTIES'][$propID]['LIST_TYPE']
                                                ):?>
                                                    <label class="font_14"><span><?=$arItem['PROPERTIES'][$propID]['NAME']?></span></label>
                                                    <?foreach($propInfo['VALUES'] as $valueID => $value):?>
                                                        <div class="form-radiobox">
                                                            <label class="form-radiobox__label">
                                                                <input class="form-radiobox__input" type="radio" name="<?=$arParams['PRODUCT_PROPS_VARIABLE']?>[<?=$propID?>]" value="<?=$valueID?>">
                                                                <span class="bx_filter_input_checkbox">
                                                                    <span><?=$value?></span>
                                                                </span>
                                                                <span class="form-radiobox__box"></span>
                                                            </label>
                                                        </div>
                                                    <?endforeach;?>
                                                <?else:?>
                                                    <label class="font_14"><span><?=$arItem['PROPERTIES'][$propID]['NAME']?></span></label>
                                                    <div class="input">
                                                        <select class="form-control" name="<?=$arParams['PRODUCT_PROPS_VARIABLE']?>[<?=$propID?>]">
                                                            <?foreach($propInfo['VALUES'] as $valueID => $value):?>
                                                                <option value="<?=$valueID?>" <?=($valueID == $propInfo['SELECTED'] ? '"selected"' : '')?>><?=$value?></option>
                                                            <?endforeach;?>
                                                        </select>
                                                    </div>
                                                <?endif;?>
                                            </div>
                                        </div>
                                    </div>
                                <?endforeach;?>
                            </div>
                        <?endif;?>
                    </div>
                <?endif;?>

                <div class="catalog-table__item<?= !$bOptBuy && !$key ? ' catalog-table__item--hide-top-border' : ''; ?> <?=$itemClass?>" id="<?=$arItem["strMainID"]?>">
                    <?if ($arItem['SKU']['PROPS']):?>
                        <?=TSolution\SKU::getTemplateWithJsonOffers($arItem["SKU"]["OFFERS"])?>

                        <?$bUseSelectOffer = true;?>
                    <?endif;?>
                    <div class="catalog-table__inner flexbox flexbox--direction-row height-100" <?if ($bUseSchema):?>itemprop="itemListElement" itemscope="" itemtype="http://schema.org/Product"<?endif;?>>
                        <?if ($bUseSchema):?>
                            <?/*<meta itemprop="position" content="<?=(++$positionProduct)?>" />*/?>
                            <meta itemprop="description" content="<?=htmlspecialcharsbx(strip_tags($arItem['PREVIEW_TEXT'] ?: $arItem['NAME']))?>" />
                        <?endif;?>

                        <?if($bOptBuy):?>
                            <div class="catalog-table__item-wrapper <?=(!($bOrderViewBasket) ? 'opacity0 no-opt-action' : '')?>">
                                <div class="form-checkbox">
                                    <input type="checkbox" class="form-checkbox__input" id="check_item_<?=$arItem['ID'];?>" name="check_item" value="Y" <?=(!($bOrderViewBasket) ? 'disabled' : '')?>>
                                    <label for="check_item_<?=$arItem['ID'];?>" class="form-checkbox__label form-checkbox__label--no-text">
                                        <span>

                                        </span>
                                        <span class="form-checkbox__box"></span>
                                    </label>
                                </div>
                            </div>
                        <?endif;?>

                        <?$arImgConfig = [
                            'TYPE' => 'catalog_table',
                            'ADDITIONAL_IMG_CLASS' => 'js-replace-img',
                            'FV_WITH_ICON' => 'Y',
                            'FV_WITH_TEXT' => 'N',
                            'FV_BTN_CLASS' => 'fv-icon',
                            'WRAP_LINK' => !$bDetail,
                        ];?>
                        <div class="catalog-table__item-wrapper js-config-img <?=($arResult['SHOW_IMAGE']) ? '' : 'catalog-table__item-wrapper--no-padding' ;?>" data-img-config='<?=str_replace('\'', '"', CUtil::PhpToJSObject($arImgConfig, false, true))?>'>
                            <?if ($arResult['SHOW_IMAGE']):?>
                                <?=TSolution\Product\Image::showImage(
                                    array_merge(
                                        [
                                            'ITEM' => $arItem,
                                            'PARAMS' => $arParams,
                                        ],
                                        $arImgConfig
                                    )
                                )?>
                            <?endif;?>
                        </div>

                        <?if ($bUseSchema):?>
                            <meta itemprop="name" content="<?=$arItem['NAME']?>">
                            <meta itemprop="image" content="<?=$arItem['PREVIEW_PICTURE']['SRC'] ?: $arItem['DETAIL_PICTURE']['SRC']?>">
                            <link itemprop="url" href="<?=$arItem['DETAIL_PAGE_URL']?>">
                        <?endif;?>
                        <div class="catalog-table__info flex-1 flexbox flexbox--direction-row1 color-theme-parent-all"
                            data-id="<?=$arCurrentOffer ? $arCurrentOffer['ID'] : $arItem['ID'];?>"
                            data-item="<?=$dataItem;?>"
                            <?if ($bUseSchema):?>itemprop="offers" itemscope itemtype="http://schema.org/Offer"<?endif;?>
                        >
                            <div class="flex-1 flexbox flexbox--direction-row catalog-table__info-wrapper">
                                <div class="catalog-table__info-top">
                                    <div class="catalog-table__info-inner catalog-table__item-wrapper">
                                        <?// element title?>
                                        <div class="catalog-table__info-title linecamp-4 height-auto-t600 <?= $bDetail ? 'font_weight--500 color_222' : 'font_15 font_14--to-600'; ?> font_large">
                                            <?if ($bUseSchema):?>
                                                <link itemprop="url" href="<?=$arItem['DETAIL_PAGE_URL']?>">
                                            <?endif;?>
                                            <?if ($arItem["DETAIL_PAGE_URL"]):?>
                                                <?if(!$arParams['DETAIL']):?><a href="<?=$arItem["DETAIL_PAGE_URL"]?>" class="dark_link switcher-title js-popup-title js-replace-link color-theme-target"><?endif;?>
                                                    <span><?=$elementName;?></span>
                                                <?if(!$arParams['DETAIL']):?></a><?endif;?>
                                            <?else:?>
                                                <div class="color_222 switcher-title js-popup-title"><span><?=$elementName;?></span></div>
                                            <?endif;?>
                                        </div>
                                        <?if ($bShowRating || strlen($status) || strlen($article)):?>
                                            <div class="catalog-table__info-tech">
                                                <div class="line-block line-block--12 flexbox--wrap js-popup-info">
                                                    <?// rating?>
                                                    <?if ($bShowRating):?>
                                                        <div class="line-block__item font_13 font_12--to-600">
                                                            <?=\TSolution\Product\Common::getRatingHtml([
                                                                'ITEM' => $arItem,
                                                                'PARAMS' => $arParams,
                                                            ])?>
                                                        </div>
                                                    <?endif;?>
                                                    <?// status?>
                                                    <?if (strlen($status)):?>
                                                        <div class="line-block__item font_13 font_12--to-600">
                                                            <?TSolution\Product\Quantity::show(
                                                                $statusCode,
                                                                $status,
                                                                [
                                                                    'USE_SHEMA_ORG' => $bUseSchema,
                                                                ]
                                                            );?>
                                                        </div>
                                                    <?endif;?>
                                                    <?// article?>
                                                    <?if (strlen($article)):?>
                                                        <div class="line-block__item font_13 font_12--to-600 color_999">
                                                            <span class="article"><?=GetMessage('S_ARTICLE')?>&nbsp;<span
                                                             class="js-replace-article"
                                                             data-value="<?=$arItem['DISPLAY_PROPERTIES']['CML2_ARTICLE']['VALUE']?>"
                                                            ><?=$article?></span></span>
                                                        </div>
                                                    <?endif;?>
                                                </div>
                                            </div>
                                        <?endif;?>
                                        <?php include $_SERVER['DOCUMENT_ROOT'] . '/local/include/getchips_section_teaser.php'; ?>
                                    </div>

                                    <? // sku2 props ?>
                                    <?= $bDetail ? $propsHTML : ''; ?>
                                </div>
                                <?if ($arItem['PROPS'] && $bColProps):?>
                                    <?$templateData['HAS_CHARACTERISTICS'] = true;?>
                                    <?foreach ($arResult['COLS_PROP'] as $key => $arProp):?>
                                        <div class="catalog-table__item-wrapper props hide-991">
                                            <?if ($arItem['PROPS'] && $arItem['PROPS'][$key]):?>
                                                <div class="properties__value color_222 font_14 font_short">
                                                    <?if(is_array($arItem['PROPS'][$key]["DISPLAY_VALUE"]) && count($arItem['PROPS'][$key]["DISPLAY_VALUE"]) > 1):?>
                                                        <?=implode(', ', $arItem['PROPS'][$key]["DISPLAY_VALUE"]);?>
                                                    <?else:?>
                                                        <?=$arItem['PROPS'][$key]["DISPLAY_VALUE"];?>
                                                    <?endif;?>
                                                </div>
                                            <?endif;?>
                                        </div>
                                    <?endforeach;?>
                                <?endif;?>
                                <div class="catalog-table__info-bottom flexbox flexbox--direction-row">
                                    <?// element price?>
                                    <?$arPriceConfig = [
                                        'PRICE_CODE' => $arParams['PRICE_CODE'],
                                        'PRICE_FONT' => '18 font_14--to-600',
                                    ];?>
                                    <div class="js-popup-price catalog-table__item-wrapper" data-price-config='<?=str_replace('\'', '"', CUtil::PhpToJSObject($arPriceConfig, false, true))?>'>
                                        <?=TSolution\Product\Price::show([
                                            'ITEM' => ($arCurrentOffer ? $arCurrentOffer : $arItem),
                                            'PARAMS' => $arParams,
                                            'SHOW_SCHEMA' => $bUseSchema,
                                            'BASKET' => $bOrderViewBasket,
                                            'PRICE_FONT' => '18 font_14--to-600',
                                            'TEST' => 'Y',
                                        ]);?>
                                    </div>
                                    <?// element btns?>
                                    <div class="line-block--8-vertical catalog-table__item-wrapper">
                                        <div class="line-block__item line-block line-block--8 line-block--8-vertical flexbox--wrap flexbox--justify-center ">
                                            <?if ($arItem['DETAIL_PAGE_URL']):?>
                                                <div class="line-block__item js-btn-state-wrapper flex-1 <?=($arCurrentOffer ? 'hide-600' : '');?> catalog-wide-button">
                                                    <a href="<?=htmlspecialcharsbx($arItem['DETAIL_PAGE_URL']);?>" class="btn btn-default btn-sm btn-wide">
                                                        <span>Купить</span>
                                                    </a>
                                                </div>
                                                <?if ($arCurrentOffer):?>
                                                    <div class="visible-600 line-block__item flex-1 catalog-wide-button">
                                                        <a href="<?=htmlspecialcharsbx($arItem['DETAIL_PAGE_URL']);?>" class="btn btn-default btn-sm btn-wide">
                                                            <span>Купить</span>
                                                        </a>
                                                    </div>
                                                <?endif;?>
                                            <?endif;?>
                                            <?if ($bShowCompare || $bShowFavorit):?>
                                                <div class="line-block__item js-replace-icons">
                                                    <?if ($bShowFavorit):?>
                                                        <?=\TSolution\Product\Common::getActionIcon([
                                                            'ITEM' => ($arCurrentOffer ? $arCurrentOffer : $arItem),
                                                            'PARAMS' => $arParams,
                                                        ])?>
                                                    <?endif;?>
                                                    <?if ($bShowCompare):?>
                                                        <?=\TSolution\Product\Common::getActionIcon([
                                                            'ITEM' => (($arCurrentOffer && \TSolution::isSaleMode()) ? $arCurrentOffer : $arItem),
                                                            'PARAMS' => $arParams,
                                                            'TYPE' => 'compare',
                                                            'SVG_SIZE' => ['WIDTH' => 20,'HEIGHT' => 16],
                                                        ])?>
                                                    <?endif;?>
                                                </div>
                                            <?endif;?>
                                        </div>

                                        <? if ($bShowCalculateDelivery): ?>
                                            <div class="line-block__item line-block">
                                                <div class="line-block__item font_13">
                                                    <?
                                                    $arConfig = [
                                                        'NAME' => $arParams['EXPRESSION_FOR_CALCULATE_DELIVERY'],
                                                        'SVG_NAME' => 'delivery',
                                                        'SVG_SIZE' => ['WIDTH' => 16, 'HEIGHT' => 15],
                                                        'SVG_PATH' => '/catalog/item_order_icons.svg#delivery',
                                                        'WRAPPER' => 'stroke-dark-light',
                                                        'DATA_ATTRS' => [
                                                            'event' => 'jqm',
                                                            'param-form_id' => 'delivery',
                                                            'name' => 'delivery',
                                                            'param-product_id' => $arItem['ID'],
                                                        ]
                                                    ];

                                                    if ($arParams['USE_REGION'] === 'Y' && $arParams['STORES'] && is_array($arParams['STORES'])) {
                                                        $arConfig['DATA_ATTRS']['param-region_stores_id'] = implode(',', $arParams['STORES']);
                                                    }
                                                    ?>
                                                    <?= TSolution\Product\Common::showModalBlock($arConfig); ?>
                                                    <? unset($arConfig); ?>
                                                </div>
                                            </div>
                                        <? endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?if ($arItem['SKU']['PROPS']/* && !$bHideProps*/):?>
                                <div class="catalog-table__item-wrapper hide-600">
                                    <div
                                     class="sku-props sku-props--list"
                                     data-site-id="<?=SITE_ID;?>"
                                     data-item-id="<?=$arItem['ID'];?>"
                                     data-iblockid="<?=$arItem['IBLOCK_ID'];?>"
                                     data-offer-id="<?=$arCurrentOffer['ID'];?>"
                                     data-offer-iblockid="<?=$arCurrentOffer['IBLOCK_ID'];?>"
                                     data-offers-id='<?=str_replace('\'', '"', CUtil::PhpToJSObject($GLOBALS[$arParams['FILTER_NAME']]['OFFERS_ID'], false, true))?>'
                                    >
                                        <div class="line-block line-block--flex-wrap line-block--40 line-block--align-flex-end">
                                            <?=TSolution\SKU\Template::showSkuPropsHtml($arItem['SKU']['PROPS'])?>
                                        </div>

                                    </div>
                                </div>
                            <?endif;?>

                            <? // list items props ?>
                            <?= !$bDetail ? $propsHTML : ''; ?>
                        </div>

                    </div>
                </div>
            </div>
        <?}?>

        <?if ($bHasBottomPager && $bMobileScrolledItems):?>
            <?if($bAjax):?>
                <div class="wrap_nav bottom_nav_wrapper">
            <?endif;?>

                <?$bHasNav = (strpos($arResult["NAV_STRING"], 'more_text_ajax') !== false);?>
                <div class="bottom_nav mobile_slider <?=($bHasNav ? '' : ' hidden-nav');?>" data-parent=".catalog-table" data-append=".grid-list" <?=($bAjax ? "style='display: none; '" : "");?>>
                    <?=$arResult["NAV_STRING"]?>
                </div>

            <?if($bAjax):?>
                </div>
            <?endif;?>
        <?endif;?>

    <?if(!$bAjax):?>
                </div>
            </div> <?//.js_append ajax_load block grid-list?>
    <?endif;?>

        <?if($bAjax):?>
            <div class="wrap_nav bottom_nav_wrapper">
        <?endif;?>

        <?$this->SetViewTarget("more_text_title");?>
            <?TSolution\Functions::showBlockHtml([
                'FILE' => '/catalog/element_count_in_section.php',
                'PARAMS' => [
                    'HEADING_COUNT_ELEMENTS' => $arParams['HEADING_COUNT_ELEMENTS'] == 'Y',
                    'COUNT_ELEMENTS' => $arResult['NAV_RESULT']->NavRecordCount,
                ]
            ])?>
        <?$this->EndViewTarget();?>

        <div class="bottom_nav_wrapper nav-compact">
            <div class="bottom_nav <?=($bMobileScrolledItems ? 'hide-600' : '');?>" <?=($arParams['AJAX_REQUEST'] == "Y" ? "style='display: none; '" : "");?> data-all_count="<?=TSolution\Functions::declOfNum($arResult['NAV_RESULT']->NavRecordCount, [Loc::getMessage('ONE_ITEM'), Loc::getMessage('TWO_ITEM'), Loc::getMessage('FIVE_ITEM')]);?>" data-count="<?=$arResult['NAV_RESULT']->NavRecordCount;?>" data-parent=".catalog-table" data-append=".grid-list">
                <?if($arParams['DISPLAY_BOTTOM_PAGER']):?>
                    <?=$arResult['NAV_STRING']?>
                <?endif;?>
            </div>
        </div>

    <?if($bAjax):?>
        </div>
    <?endif;?>

    <?if(!$bAjax):?>
        </div> <?//.catalog-block?>
    </div> <?//.catalog-items?>
        <script><?if ($bColProps):?>var tableScrollerOb= new TableScroller('table-scroller-wrapper');<?endif;?></script>
    <?endif;?>
    <script>
        if (typeof initCountdown === "function") initCountdown();
    </script>
    <?if($bUseSelectOffer):?>
        <script>typeof useOfferSelect === 'function' && useOfferSelect()</script>
    <?endif;?>
<?elseif($arParams['IS_CATALOG_PAGE'] == 'Y'):?>
    <div class="no_goods catalog_block_view">
        <div class="no_products">
            <div class="wrap_text_empty">
                <?if($_REQUEST["set_filter"]){?>
                    <?$APPLICATION->IncludeFile(SITE_DIR."include/section_no_products_filter.php", Array(), Array("MODE" => "html",  "NAME" => GetMessage('EMPTY_CATALOG_DESCR')));?>
                <?}else{?>
                    <?$APPLICATION->IncludeFile(SITE_DIR."include/section_no_products.php", Array(), Array("MODE" => "html",  "NAME" => GetMessage('EMPTY_CATALOG_DESCR')));?>
                <?}?>
            </div>
        </div>
    </div>
<?endif;?>
