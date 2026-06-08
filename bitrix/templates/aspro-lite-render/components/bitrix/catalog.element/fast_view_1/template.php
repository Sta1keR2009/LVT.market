<?
if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
$this->setFrameMode(true);

global $arTheme;
use \Bitrix\Main\Localization\Loc;

$bOrderViewBasket = $arParams['ORDER_VIEW'];
$basketURL = isset($arTheme['BASKET_PAGE_URL']) && strlen(trim($arTheme['BASKET_PAGE_URL']['VALUE'])) ? $arTheme['BASKET_PAGE_URL']['VALUE'] : SITE_DIR.'cart/';
$dataItem = TSolution::getDataItem($arResult);
$bOrderButton = $arResult['PROPERTIES']['FORM_ORDER']['VALUE_XML_ID'] == 'YES';
$bAskButton = $arResult['PROPERTIES']['FORM_QUESTION']['VALUE_XML_ID'] == 'YES';
$bOcbButton = $arParams['SHOW_ONE_CLINK_BUY'] != 'N';
$cntVisibleChars = $arParams['VISIBLE_PROP_COUNT'] >= 0 ? $arParams['VISIBLE_PROP_COUNT'] : 6;

/*set array props for component_epilog*/
$templateData = array(
    'DETAIL_PAGE_URL' => $arResult['DETAIL_PAGE_URL'],
    'ORDER' => $bOrderViewBasket,
    'SKU' => [
        'IBLOCK_ID' => $arParams['SKU_IBLOCK_ID'],
        'VALUE' => array_column($arResult['OFFERS'], 'ID'),
    ],
);

$article = $arResult['DISPLAY_PROPERTIES']['CML2_ARTICLE']['VALUE'];

$bShowRating = $arParams['SHOW_RATING'] == 'Y';
$bShowCompare = $arParams['DISPLAY_COMPARE'] == 'Y';
$bShowFavorit = $arParams['SHOW_FAVORITE'] == 'Y';
$bShowCheaperForm = $arParams['SHOW_CHEAPER_FORM'] === 'Y';
$bPopupVideo = !!$arResult['POPUP_VIDEO'];
$bShowCalculateDelivery = $arParams["CALCULATE_DELIVERY"] === 'Y';
$bShowSKUDescription = $arParams["SHOW_SKU_DESCRIPTION"] === 'Y';

if ($bPopupVideo) {
    $topGalleryClassList .= " detail-gallery-big--with-video";
}

//unset($arResult['OFFERS']); // get correct totalCount
$totalCount = TSolution\Product\Quantity::getTotalCount([
    'ITEM' => $arResult,
    'PARAMS' => $arParams
]);
$arStatus = TSolution\Product\Quantity::getStatus([
    'ITEM' => $arResult,
    'PARAMS' => $arParams,
    'TOTAL_COUNT' => $totalCount,
    'IS_DETAIL' => true,
]);

$templateData["USE_OFFERS_SELECT"] = false;

/* sku replace start */
$arCurrentOffer = $arResult['SKU']['CURRENT'];
$elementName = !empty($arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']) ? $arResult['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'] : $arResult['NAME'];

if ($arCurrentOffer) {
    $arResult['PARENT_IMG'] = '';
    if ($arResult['PREVIEW_PICTURE']) {
        $arResult['PARENT_IMG'] = $arResult['PREVIEW_PICTURE'];
    } elseif ($arResult['DETAIL_PICTURE']) {
        $arResult['PARENT_IMG'] = $arResult['DETAIL_PICTURE'];
    }

    $oid = \Bitrix\Main\Config\Option::get(VENDOR_MODULE_ID, 'CATALOG_OID', 'oid');
    if ($oid) {
        $arResult['DETAIL_PAGE_URL'].= '?'.$oid.'='.$arCurrentOffer['ID'];
        $arCurrentOffer['DETAIL_PAGE_URL'] = $arResult['DETAIL_PAGE_URL'];
    }
    if ($arCurrentOffer['GALLERY']) {
        $arResult['GALLERY'] = array_merge($arCurrentOffer['GALLERY'], $arResult['GALLERY']);
    }

    if ($arCurrentOffer["DISPLAY_PROPERTIES"]["CML2_ARTICLE"]["VALUE"] || $arCurrentOffer["DISPLAY_PROPERTIES"]["ARTICLE"]["VALUE"]) {
        $article = $arCurrentOffer['DISPLAY_PROPERTIES']['CML2_ARTICLE']['VALUE'] ?? $arCurrentOffer["DISPLAY_PROPERTIES"]["ARTICLE"]["VALUE"];
    }

    $arResult["DISPLAY_PROPERTIES"]["FORM_ORDER"] = $arCurrentOffer["DISPLAY_PROPERTIES"]["FORM_ORDER"];
    $arResult["DISPLAY_PROPERTIES"]["PRICE"] = $arCurrentOffer["DISPLAY_PROPERTIES"]["PRICE"];

    if($arParams["SET_SKU_TITLE"] === "Y"){
        $tmpOfferName = ((isset($arCurrentOffer['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']) && $arCurrentOffer['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE']) ? $arCurrentOffer['IPROPERTY_VALUES']['ELEMENT_PAGE_TITLE'] : $arCurrentOffer['NAME']);
        if(!empty($tmpOfferName)){
            $arItem["NAME"] = $elementName = $tmpOfferName;
        }
    }

    $arResult['OFFER_PROP'] = TSolution::PrepareItemProps($arCurrentOffer['DISPLAY_PROPERTIES']);

    $dataItem = TSolution::getDataItem($arCurrentOffer);

    $totalCount = TSolution\Product\Quantity::getTotalCount([
        'ITEM' => $arCurrentOffer,
        'PARAMS' => $arParams
    ]);
    $arStatus = TSolution\Product\Quantity::getStatus([
        'ITEM' => $arCurrentOffer,
        'PARAMS' => $arParams,
        'TOTAL_COUNT' => $totalCount,
        'IS_DETAIL' => true,
    ]);
}

$status = $arStatus['NAME'];
$statusCode = $arStatus['CODE'];
/* sku replace end */
?>

<?if (TSolution::isSaleMode()):?>
    <div class="basket_props_block" id="bx_basket_div_<?=$arResult["ID"];?>" style="display: none;">
        <?if (!empty($arResult['PRODUCT_PROPERTIES_FILL'])):?>
            <?foreach ($arResult['PRODUCT_PROPERTIES_FILL'] as $propID => $propInfo):?>
                <input type="hidden" name="<?=$arParams['PRODUCT_PROPS_VARIABLE']; ?>[<?=$propID;?>]" value="<?=htmlspecialcharsbx($propInfo['ID']);?>">
                <?
                if (isset($arResult['PRODUCT_PROPERTIES'][$propID])){
                    unset($arResult['PRODUCT_PROPERTIES'][$propID]);
                }
                ?>
            <?endforeach;?>
        <?endif;?>
        <?if ($arResult['PRODUCT_PROPERTIES']):?>
            <div class="wrapper">
                <?foreach($arResult['PRODUCT_PROPERTIES'] as $propID => $propInfo):?>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group fill-animate">
                                <?if(
                                    'L' == $arResult['PROPERTIES'][$propID]['PROPERTY_TYPE'] &&
                                    'C' == $arResult['PROPERTIES'][$propID]['LIST_TYPE']
                                ):?>
                                    <label class="font_14"><span><?=$arResult['PROPERTIES'][$propID]['NAME']?></span></label>
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
                                    <label class="font_14"><span><?=$arResult['PROPERTIES'][$propID]['NAME']?></span></label>
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

<?// detail description?>
<?$templateData['DETAIL_TEXT'] = boolval(strlen($arResult['DETAIL_TEXT']));?>
<?if(strlen($arResult['DETAIL_TEXT'])):?>
    <?$this->SetViewTarget('PRODUCT_DETAIL_TEXT_INFO');?>
        <div class="content" itemprop="description">
            <?=$arResult['DETAIL_TEXT'];?>
        </div>
    <?$this->EndViewTarget();?>
<?endif;?>

<?// props content?>
<?$templateData['CHARACTERISTICS'] = boolval($arResult['CHARACTERISTICS']);?>
<?if($arResult['CHARACTERISTICS']):?>
    <?$this->SetViewTarget('PRODUCT_PROPS_INFO');?>
        <?$strGrupperType = $arParams["GRUPPER_PROPS"];?>
        <?if($strGrupperType == "GRUPPER"):?>
            <div class="props_block bordered rounded-4">
                <div class="props_block__wrapper">
                    <?$APPLICATION->IncludeComponent(
                        "redsign:grupper.list",
                        "",
                        Array(
                            "CACHE_TIME" => "3600000",
                            "CACHE_TYPE" => "A",
                            "COMPOSITE_FRAME_MODE" => "A",
                            "COMPOSITE_FRAME_TYPE" => "AUTO",
                            "DISPLAY_PROPERTIES" => $arResult["CHARACTERISTICS"]
                        ),
                        $component, array('HIDE_ICONS'=>'Y')
                    );?>
                </div>
            </div>
        <?elseif($strGrupperType == "WEBDEBUG"):?>
            <div class="props_block bordered rounded-4">
                <div class="props_block__wrapper">
                    <?$APPLICATION->IncludeComponent(
                        "webdebug:propsorter",
                        "linear",
                        array(
                            "IBLOCK_TYPE" => $arResult['IBLOCK_TYPE'],
                            "IBLOCK_ID" => $arResult['IBLOCK_ID'],
                            "PROPERTIES" => $arResult['CHARACTERISTICS'],
                            "EXCLUDE_PROPERTIES" => array(),
                            "WARNING_IF_EMPTY" => "N",
                            "WARNING_IF_EMPTY_TEXT" => "",
                            "NOGROUP_SHOW" => "Y",
                            "NOGROUP_NAME" => "",
                            "MULTIPLE_SEPARATOR" => ", "
                        ),
                        $component, array('HIDE_ICONS'=>'Y')
                    );?>
                </div>
            </div>
        <?elseif($strGrupperType == "YENISITE_GRUPPER"):?>
            <div class="props_block bordered rounded-4">
                <div class="props_block__wrapper">
                    <?$APPLICATION->IncludeComponent(
                        'yenisite:ipep.props_groups',
                        '',
                        array(
                            'DISPLAY_PROPERTIES' => $arResult['CHARACTERISTICS'],
                            'IBLOCK_ID' => $arParams['IBLOCK_ID']
                        ),
                        $component, array('HIDE_ICONS'=>'Y')
                    )?>
                </div>
            </div>
        <?else:?>
            <?if($arParams["PROPERTIES_DISPLAY_TYPE"] != "TABLE"):?>
                <div class="props_block">
                    <div class="props_block__wrapper flexbox row js-offers-prop">
                        <?foreach($arResult["CHARACTERISTICS"] as $propCode => $arProp):?>
                            <div class="char col-lg-3 col-md-4 col-xs-6 bordered js-prop-replace" itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue">
                                <div class="char_name font_15 color_666">
                                    <div class="props_item js-prop-title <?if($arProp["HINT"] && $arParams["SHOW_HINTS"] == "Y"){?>whint<?}?>">
                                        <span itemprop="name"><?=$arProp["NAME"]?></span>
                                    </div>
                                    <?if($arProp["HINT"] && $arParams["SHOW_HINTS"]=="Y"):?><div class="hint hint--down"><span class="hint__icon rounded bg-theme-hover border-theme-hover bordered"><i>?</i></span><div class="tooltip"><?=$arProp["HINT"]?></div></div><?endif;?>
                                </div>
                                <div class="char_value font_15 color_222 js-prop-value" itemprop="value">
                                    <?if(is_array($arProp["DISPLAY_VALUE"]) && count($arProp["DISPLAY_VALUE"]) > 1):?>
                                        <?=implode(', ', $arProp["DISPLAY_VALUE"]);?>
                                    <?else:?>
                                        <?=$arProp["DISPLAY_VALUE"];?>
                                    <?endif;?>
                                </div>
                            </div>
                        <?endforeach;?>
                        <?if ($arResult['OFFER_PROP']):?>
                            <?foreach($arResult["OFFER_PROP"] as $propCode => $arProp):?>
                                <div class="char col-lg-3 col-md-4 col-xs-6 bordered js-prop" itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue">
                                    <div class="char_name font_15 color_666">
                                        <div class="props_item <?if($arProp["HINT"] && $arParams["SHOW_HINTS"] == "Y"){?>whint<?}?>">
                                            <span itemprop="name"><?=$arProp["NAME"]?></span>
                                        </div>
                                        <?if($arProp["HINT"] && $arParams["SHOW_HINTS"]=="Y"):?><div class="hint hint--down"><span class="hint__icon rounded bg-theme-hover border-theme-hover bordered"><i>?</i></span><div class="tooltip"><?=$arProp["HINT"]?></div></div><?endif;?>
                                    </div>
                                    <div class="char_value font_15 color_222" itemprop="value">
                                        <?if(is_array($arProp["VALUE"]) && count($arProp["VALUE"]) > 1):?>
                                            <?=implode(', ', $arProp["VALUE"]);?>
                                        <?else:?>
                                            <?=$arProp["VALUE"];?>
                                        <?endif;?>
                                    </div>
                                </div>
                            <?endforeach;?>
                        <?endif;?>
                    </div>
                </div>
            <?else:?>
                <div class="props_block props_block--table props_block--nbg bordered rounded-4">
                    <table class="props_block__wrapper ">
                        <tbody class="js-offers-prop">
                            <?foreach($arResult["CHARACTERISTICS"] as $arProp):?>
                                <tr class="char js-prop-replace" itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue">
                                    <td class="char_name font_15 color_666">
                                        <div class="props_item js-prop-title <?if($arProp["HINT"] && $arParams["SHOW_HINTS"] == "Y"){?>whint<?}?>">
                                            <span itemprop="name"><?=$arProp["NAME"]?></span>
                                            <?if($arProp["HINT"] && $arParams["SHOW_HINTS"]=="Y"):?><div class="hint hint--down"><span class="hint__icon rounded bg-theme-hover border-theme-hover bordered"><i>?</i></span><div class="tooltip"><?=$arProp["HINT"]?></div></div><?endif;?>
                                        </div>
                                    </td>
                                    <td class="char_value font_15 color_222 js-prop-value">
                                        <span itemprop="value">
                                            <?if(count((array)$arProp["DISPLAY_VALUE"]) > 1):?>
                                                <?=implode(', ', $arProp["DISPLAY_VALUE"]);?>
                                            <?else:?>
                                                <?=$arProp["DISPLAY_VALUE"];?>
                                            <?endif;?>
                                        </span>
                                    </td>
                                </tr>
                            <?endforeach;?>
                            <?if ($arResult['OFFER_PROP']):?>
                                <?foreach($arResult["OFFER_PROP"] as $arProp):?>
                                    <tr class="char js-prop" itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue">
                                        <td class="char_name font_15 color_666">
                                            <div class="props_item <?if($arProp["HINT"] && $arParams["SHOW_HINTS"] == "Y"){?>whint<?}?>">
                                                <span itemprop="name"><?=$arProp["NAME"]?></span>
                                                <?if($arProp["HINT"] && $arParams["SHOW_HINTS"]=="Y"):?><div class="hint hint--down"><span class="hint__icon rounded bg-theme-hover border-theme-hover bordered"><i>?</i></span><div class="tooltip"><?=$arProp["HINT"]?></div></div><?endif;?>
                                            </div>
                                        </td>
                                        <td class="char_value font_15 color_222">
                                            <span itemprop="value">
                                                <?if(is_array($arProp["VALUE"]) && count($arProp["VALUE"]) > 1):?>
                                                    <?=implode(', ', $arProp["VALUE"]);?>
                                                <?else:?>
                                                    <?=$arProp["VALUE"];?>
                                                <?endif;?>
                                            </span>
                                        </td>
                                    </tr>
                                <?endforeach;?>
                            <?endif;?>
                        </tbody>
                    </table>
                </div>
            <?endif;?>
        <?endif;?>
    <?$this->EndViewTarget();?>
<?endif;?>

<?ob_start();?>
<?if ($arParams["SHOW_DISCOUNT_TIME"] === "Y" && $arParams['SHOW_DISCOUNT_TIME_IN_LIST'] !== 'N'):?>
        <?
        $discountDateTo = '';
        if (TSolution::isSaleMode()) {
            $arDiscount = TSolution\Product\Price::getDiscountByItemID($arResult['ID']);
            $discountDateTo = $arDiscount ? $arDiscount['ACTIVE_TO'] : '';
        } else {
            $discountDateTo = $arResult['DISPLAY_PROPERTIES']['DATE_COUNTER']['VALUE'];
        }
        ?>
        <?if ($discountDateTo):?>
            <?TSolution\Functions::showDiscountCounter([
                'ICONS' => true,
                'SHADOWED' => true,
                'DATE' => $discountDateTo,
                'ITEM' => $arResult
            ]);?>
        <?endif;?>
    <?endif;?>
<?$itemDiscount = ob_get_clean();?>

<?$this->SetViewTarget('PRODUCT_SIDE_INFO');?>
    <?ob_start();?>
    <div class="catalog-detail__buy-block" itemprop="offers" itemscope itemtype="http://schema.org/Offer" data-id="<?=$arResult['ID']?>"
        data-item="<?=$dataItem;?>"
    >
        <?TSolution\Product\Common::showStickers([
            'TYPE' => '',
            'ITEM' => $arResult,
            'PARAMS' => $arParams,
            'DOP_CLASS' => 'sticker--static',
            'CONTENT' => $itemDiscount,
        ]);?>

        <a href="<?=$arResult['DETAIL_PAGE_URL']?>" class="catalog-detail__title js-popup-title switcher-title font_24 dark_link"><?=$elementName?></a>

        <?
        $bShowBrand = $arResult['BRAND_ITEM'] && $arResult['BRAND_ITEM']['IMAGE'];
        ?>
        <?if(
            strlen($article)
            || $bShowRating
            || $bShowCompare
            || $bShowFavorit
        ):?>
            <div class="catalog-detail__info-tech">
                <div class="line-block line-block--20 line-block--align-normal flexbox--justify-beetwen flexbox--wrap">
                    <?if(
                        strlen($article)
                        || $bShowRating
                    ):?>
                        <div class="line-block__item">
                            <div class="catalog-detail__info-tech">
                                <div class="line-block line-block--20 flexbox--wrap js-popup-info">
                                    <?// rating?>
                                    <?if ($bShowRating):?>
                                        <div class="line-block__item font_14 color_222">
                                            <?=\TSolution\Product\Common::getRatingHtml([
                                                'ITEM' => $arResult,
                                                'PARAMS' => $arParams,
                                                // 'SHOW_REVIEW_COUNT' => true,
                                                'SVG_SIZE' => [
                                                    'WIDTH' => 16,
                                                    'HEIGHT' => 16,
                                                ]
                                            ])?>
                                        </div>
                                    <?endif;?>

                                    <?// element article?>
                                    <?if(strlen($article)):?>
                                        <div class="line-block__item font_13 color_999">
                                            <span class="article"><?=GetMessage('S_ARTICLE')?>&nbsp;<span
                                                class="js-replace-article"
                                                data-value="<?=$arResult['DISPLAY_PROPERTIES']['CML2_ARTICLE']['VALUE']?>"
                                            ><?=$article?></span></span>
                                        </div>
                                    <?endif;?>
                                </div>
                            </div>
                        </div>
                    <?endif;?>

                    <? if ($bShowCompare || $bShowFavorit): ?>
                        <div class="line-block__item ">
                            <div class="flexbox flexbox--row flexbox--wrap">
                                <?if (!$bSKU2):?>
                                    <div class="js-replace-icons">
                                        <? if ($bShowFavorit): ?>
                                            <?= \TSolution\Product\Common::getActionIcon([
                                                'ITEM' => ($arCurrentOffer ? $arCurrentOffer : $arResult),
                                                'PARAMS' => $arParams,
                                                'CLASS' => 'md',
                                            ]); ?>
                                        <? endif; ?>

                                        <? if ($bShowCompare): ?>
                                            <?= \TSolution\Product\Common::getActionIcon([
                                                'ITEM' => (($arCurrentOffer && \TSolution::isSaleMode()) ? $arCurrentOffer : $arResult),
                                                'PARAMS' => $arParams,
                                                'TYPE' => 'compare',
                                                'CLASS' => 'md',
                                                'SVG_SIZE' => ['WIDTH' => 20,'HEIGHT' => 16],
                                            ]); ?>
                                        <? endif; ?>
                                    </div>
                                <?endif;?>
                            </div>
                        </div>
                    <? endif; ?>
                </div>
            </div>
        <?endif;?>

        <?if ($arResult['SKU']['PROPS']):?>
            <div class="catalog-block__offers1">
                <div
                class="sku-props sku-props--detail"
                data-site-id="<?=SITE_ID;?>"
                data-item-id="<?=$arResult['ID'];?>"
                data-iblockid="<?=$arResult['IBLOCK_ID'];?>"
                data-offer-id="<?=$arCurrentOffer['ID'];?>"
                data-offer-iblockid="<?=$arCurrentOffer['IBLOCK_ID'];?>"
                data-offers-id='<?=str_replace('\'', '"', CUtil::PhpToJSObject($GLOBALS[$arParams['FILTER_NAME']]['OFFERS_ID'], false, true))?>'
                >
                    <div class="line-block line-block--flex-wrap line-block--flex-100 line-block--40 line-block--align-flex-end">
                        <?=TSolution\SKU\Template::showSkuPropsHtml($arResult['SKU']['PROPS']);?>
                    </div>
                </div>
            </div>
        <?endif;?>
        <? // table sizes ?>
        <? if ($arResult['SIZE_PATH']): ?>
            <div class="line-block__item">
                <div class="catalog-detail__pseudo-link catalog-detail__pseudo-link--with-gap table-sizes">
                    <span class="font_13 fill-dark-light-block dark_link"
                        data-event="jqm"
                        data-param-form_id="include_block"
                        data-param-url="<?= $arResult['SIZE_PATH']; ?>"
                        data-param-block_title="<?= urlencode(TSolution::formatJsName(GetMessage('TABLE_SIZES')));?>"
                        data-name="include_block"
                    >
                        <?= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH . '/images/svg/catalog/item_icons.svg#table_sizes', '', [
                            'WIDTH' => 18,
                            'HEIGHT' => 11
                        ]); ?>
                        <span class="dotted"><?= GetMessage("TABLES_SIZE"); ?></span>
                    </span>
                </div>
            </div>
        <? endif; ?>

        <?
        $arPriceConfig = [
            'PRICE_CODE' => $arParams['PRICE_CODE'],
            'PRICE_FONT' => 24,
            'PRICEOLD_FONT' => 16,
        ];

        $priceHtml = TSolution\Product\Price::show([
            'ITEM' => ($arCurrentOffer ? $arCurrentOffer : $arResult),
            'PARAMS' => $arParams,
            // 'SHOW_SCHEMA' => true,
            'BASKET' => $bOrderViewBasket,
            'PRICE_FONT' => 24,
            'PRICEOLD_FONT' => 16,
            'RETURN' => true,
        ]);
        ?>

        <div class="line-block line-block--20 line-block--16-vertical line-block--align-normal flexbox--wrap flexbox--justify-beetwen<?=($priceHtml ? '' : ' hidden')?>">
            <div class="line-block__item catalog-detail__price js-popup-price" data-price-config='<?=str_replace('\'', '"', CUtil::PhpToJSObject($arPriceConfig, false, true))?>'>
                <?=$priceHtml?>
            </div>
        </div>

        <?
        $arBtnConfig = [
            'DETAIL_PAGE' => true,
            'BASKET_URL' => false,
            'BASKET' => $bOrderViewBasket,
            'ORDER_BTN' => $bOrderButton,
            'BTN_CLASS' => 'btn-elg btn-wide',
            'BTN_CLASS_MORE' => 'bg-theme-target border-theme-target btn-wide',
            'BTN_IN_CART_CLASS' => 'btn-elg btn-wide',
            'BTN_CALLBACK_CLASS' => 'btn-transparent-border',
            'BTN_OCB_CLASS' => 'btn-md btn-wide btn-transparent btn-ocb',
            'BTN_ORDER_CLASS' => 'btn-elg btn-wide btn-transparent-border',
            'SHOW_COUNTER' => false,
            'ONE_CLICK_BUY' => $bOcbButton,
            'QUESTION_BTN' => $bAskButton,
            'DISPLAY_COMPARE' => $arParams['DISPLAY_COMPARE'],
            'CATALOG_IBLOCK_ID' => $arResult['IBLOCK_ID'],
            'ITEM_ID' => $arResult['ID'],
            'IS_DETAIL' => true,
            'SHOW_BASKET_COUNTER' => $arParams['SHOW_BASKET_COUNTER'],
        ];

        $arBasketConfig = TSolution\Product\Basket::getOptions(array_merge(
            $arBtnConfig,
            [
                'ITEM' => ($arCurrentOffer ? $arCurrentOffer : $arResult),
                'PARAMS' => $arParams,
                'TOTAL_COUNT' => $totalCount,
            ]
        ));
        ?>

        <div class="catalog-detail__cart js-replace-btns js-config-btns<?=($arBasketConfig['HTML'] ? '' : ' hidden')?>" data-btn-config='<?=str_replace('\'', '"', CUtil::PhpToJSObject($arBtnConfig, false, true))?>'>
            <?=$arBasketConfig['HTML']?>
        </div>

        <div class="catalog-detail__forms grid-list grid-list--items-1 font_14">
            <?// status?>
            <? if (strlen($status)): ?>
                <div class="grid-list__item">
                    <?= TSolution\Product\Common::showModalBlock([
                        'NAME' => $status,
                        'NAME_CLASS' => "js-replace-status status-icon ".($bSKU2 ? "" : $statusCode),
                        'SVG_PATH' => '/catalog/item_status_icons.svg#'.$statusCode.'_lg',
                        'SVG_SIZE' => ['WIDTH' => 16, 'HEIGHT' => 16],
                        'USE_SIZE_IN_PATH' => false,
                        'ICON_CLASS' => 'status__svg-icon '.$statusCode,
                        'WRAPPER' => 'status-container color_222 ' . $statusCode,
                        'DATA_ATTRS' => [
                            'state' => $statusCode,
                            'code' => $arResult['DISPLAY_PROPERTIES']['STATUS']['VALUE_XML_ID'],
                            'value' => $arResult['DISPLAY_PROPERTIES']['STATUS']['VALUE'],
                        ]
                    ]); ?>
                </div>
            <? endif; ?>

            <? // calculate delivery?>
            <? if (
                $bShowCalculateDelivery &&
                !$bSKU2
            ): ?>
                <div class="grid-list__item">
                <?
                $arConfig = [
                    'NAME' => $arParams['EXPRESSION_FOR_CALCULATE_DELIVERY'],
                    'SVG_NAME' => 'delivery',
                    'SVG_SIZE' => ['WIDTH' => 16, 'HEIGHT' => 15],
                    'SVG_PATH' => '/catalog/item_order_icons.svg#delivery',
                    'WRAPPER' => 'stroke-dark-light-block dark_link animate-load',
                    'DATA_ATTRS' => [
                        'event' => 'jqm',
                        'param-form_id' => 'delivery',
                        'name' => 'delivery',
                        'param-product_id' => $arCurrentSKU ? $arCurrentSKU['ID'] : $arResult['ID'],
                    ]
                ];

                if ($arParams['USE_REGION'] === 'Y' && $arParams['STORES'] && is_array($arParams['STORES'])) {
                    $arConfig['DATA_ATTRS']['param-region_stores_id'] = implode(',', $arParams['STORES']);
                }
                ?>
                <?= TSolution\Product\Common::showModalBlock($arConfig); ?>
                <? unset($arConfig); ?>
                </div>
            <? endif; ?>
        </div>
    </div>
    <?=$buyBlockHtml = ob_get_clean();?>
<?$this->EndViewTarget();?>

<div class="flex-1">
    <div class="catalog-detail__top-info flexbox flexbox--direction-row flexbox--wrap-nowrap">
        <?
        // add to viewed
        TSolution\Product\Common::addViewed([
            'ITEM' => $arCurrentOffer ?: $arResult
        ]);
        ?>

        <? //meta?>
        <meta itemprop="name" content="<?=$name = strip_tags($elementName)?>" />
        <link itemprop="url" href="<?=$arResult['DETAIL_PAGE_URL']?>" />
        <meta itemprop="category" content="<?=$arResult['CATEGORY_PATH']?>" />
        <meta itemprop="description" content="<?=(strlen(strip_tags($arResult['PREVIEW_TEXT'])) ? strip_tags($arResult['PREVIEW_TEXT']) : (strlen(strip_tags($arResult['DETAIL_TEXT'])) ? strip_tags($arResult['DETAIL_TEXT']) : $name))?>" />
        <meta itemprop="sku" content="<?=$arResult['ID'];?>" />

        <?if ($arResult['SKU_CONFIG']):?><div class="js-sku-config" data-value='<?=str_replace('\'', '"', CUtil::PhpToJSObject($arResult['SKU_CONFIG'], false, true))?>'></div><?endif;?>
        <?if ($arResult['SKU']['PROPS']):?>
            <?=TSolution\SKU::getTemplateWithJsonOffers($arResult["SKU"]["OFFERS"])?>

            <?$templateData["USE_OFFERS_SELECT"] = true;?>
        <?endif;?>

        <div class="detail-gallery-big swipeignore image-list__link">
            <div class="detail-gallery-big-inner">
                <div class="detail-gallery-big-wrapper <?= $topGalleryClassList; ?>">
                    <? if($bPopupVideo): ?>
                        <?TSolution\Functions::showBlockHtml([
                            'FILE' => 'catalog/video_block.php',
                            'LINK' => $arResult['POPUP_VIDEO'],
                            'TITLE' => Loc::getMessage('VIDEO'),
                        ]);?>
                    <? endif; ?>
                    <?
                    $countPhoto = count($arResult['GALLERY']);
                    $arFirstPhoto = reset($arResult['GALLERY']);
                    $urlFirstPhoto = $arFirstPhoto['BIG']['src'] ? $arFirstPhoto['BIG']['src'] : $arFirstPhoto['SRC'];
                    ?>
                    <?
                    $gallerySetting = [
                        'SLIDE_CLASS_LIST' => 'detail-gallery-big__item detail-gallery-big__item--big swiper-slide',
                        'PLUGIN_OPTIONS' => [
                            'direction' => 'horizontal',
                            'init' => false,
                            'keyboard' => [
                                'enabled' => true,
                            ],
                            // 'loop' => ($countPhoto > 1 ? true : false),
                            'pagination' => [
                                'enabled' => true,
                                'el' => '#fast_view_item .swiper-pagination',
                            ],
                            'slidesPerView' => 1,
                            // 'thumbs' => [
                            // 	'swiper' => '.gallery-slider-thumb',
                            // ],
                            'type' => 'detail_gallery_main',
                        ],
                    ];
                    ?>
                    <link href="<?=$urlFirstPhoto?>" itemprop="image"/>
                    <div class="detail-gallery-big-slider big js-detail-img swiper slider-solution"
                            data-slide-class-list="<?= $gallerySetting['SLIDE_CLASS_LIST']; ?>"
                            <? if (isset($gallerySetting['PLUGIN_OPTIONS']) && count($gallerySetting['PLUGIN_OPTIONS'])): ?>
                            data-plugin-options='<?= \Bitrix\Main\Web\Json::encode($gallerySetting['PLUGIN_OPTIONS']); ?>'
                            <? endif; ?>
                        >
                        <?if($countPhoto>0):?>
                            <div class="detail-gallery-big-slider__wrapper swiper-wrapper">
                                <? foreach ($arResult['GALLERY'] as $i => $arImage): ?>
                                    <?
                                        $alt = $arImage['ALT'];
                                        $title = $arImage['TITLE'];
                                        $url = $arImage['BIG']['src'] ? $arImage['BIG']['src'] : $arImage['SRC'];
                                    ?>
                                    <div id="big-photo-<?=$i?>" class="<?= $gallerySetting['SLIDE_CLASS_LIST']; ?>">
                                        <a href="<?=$url?>" data-fancybox="gallery_fast_view" class="detail-gallery-big__link popup_link fancy fancy-thumbs" title="<?= $title; ?>">
                                            <img class="detail-gallery-big__picture" src="<?= $url; ?>" alt="<?= $alt; ?>" title="<?= $title; ?>" />
                                        </a>
                                    </div>
                                <? endforeach; ?>
                            </div>

                            <div class="slider-nav swiper-button-prev" style="display: none">
                                <?= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH . '/images/svg/arrows.svg#left-7-12', 'stroke-dark-light', [
                                    'WIDTH' => 7,
                                    'HEIGHT' => 12
                                ]); ?>
                            </div>

                            <div class="slider-nav swiper-button-next" style="display: none">
                                <?= TSolution::showSpriteIconSvg(SITE_TEMPLATE_PATH . '/images/svg/arrows.svg#right-7-12', 'stroke-dark-light', [
                                    'WIDTH' => 7,
                                    'HEIGHT' => 12
                                ]); ?>
                            </div>
                        <?else:?>
                            <div class="detail-gallery-big__item detail-gallery-big__item--big detail-gallery-big__item--no-image swiper-slide">
                                    <span class="detail-gallery-big__link">
                                        <img class="detail-gallery-big__picture" src="<?=SITE_TEMPLATE_PATH.'/images/svg/noimage_product.svg'?>" />
                                    </span>
                                </div>
                        <?endif;?>
                    </div>
                    <div class="swiper-pagination swiper-pagination--bottom"></div>
                </div>
                <div class="btn-wrapper">
                    <a href="<?=$arResult['DETAIL_PAGE_URL']?>" class="btn btn-default btn-sm btn-transparent-border btn-wide js-replace-more" title="<?=Loc::getMessage('MORE_TEXT_ITEM')?>"><span><?=Loc::getMessage('MORE_TEXT_ITEM')?></span></a>
                </div>
            </div>
        </div>

        <div class="catalog-detail__main scrollbar">
            <div class="catalog-detail__main-wrapper">
                <?=$buyBlockHtml?>

                <div class="char-side">
                    <div class="list font_14">
                        <?$cntChars = count($arResult['CHARACTERISTICS']) + count((array)$arResult['OFFER_PROP']);?>
                        <?TSolution\Functions::showBlockHtml([
                            'FILE' => '/catalog/props_in_section.php',
                            'ITEM' => $arResult,
                            'PARAMS' => [
                                'TITLE' => ($arParams["T_CHARACTERISTICS"] ?: Loc::getMessage("T_CHARACTERISTICS")),
                                'SHOW_HINTS' => $arParams['SHOW_HINTS'] === 'Y',
                                'WRAPPER_CLASSES' => ($cntChars ? '' : 'hidden'),
                            ],
                        ]);?>
                    </div>
                </div>

                <?php
                $previewText = $arResult['PREVIEW_TEXT'] ?? '';
                $isPreviewTextHTML = $arResult['PREVIEW_TEXT_TYPE'] != 'text';
                if ($bShowSKUDescription && strlen($arResult['SKU']['CURRENT']['PREVIEW_TEXT'])) {
                    $previewText = $arResult['SKU']['CURRENT']['PREVIEW_TEXT'];
                    $isPreviewTextHTML = $arResult['SKU']['CURRENT']['PREVIEW_TEXT_TYPE'] != 'text';
                }
                ?>
                <div class="catalog-detail__previewtext<?=$previewText ? '' : ' hidden';?>" itemprop="description">
                    <div class="text-block font_14 color_666 js-preview-description">
                        <?// element preview text?>
                        <?if ($previewText):?>
                            <?if ($isPreviewTextHTML):?>
                                <?=$previewText;?>
                            <?else:?>
                                <p><?=$previewText;?></p>
                            <?endif;?>
                        <?endif;?>
                    </div>
                </div>

                <? if ($arResult['BRAND_ITEM'] && $arResult['BRAND_ITEM']["IMAGE"]): ?>
                    <div class="brand-detail flexbox line-block--gap line-block--gap-12">
                        <div class="brand-detail-info">
                            <div class="brand-detail-info__image rounded-x">
                                <a href="<?=$arResult['BRAND_ITEM']["DETAIL_PAGE_URL"];?>">
                                    <img src="<?=$arResult['BRAND_ITEM']["IMAGE"]["src"];?>" alt="<?=$arResult['BRAND_ITEM']["NAME"];?>" title="<?=$arResult['BRAND_ITEM']["NAME"];?>" itemprop="image">
                                </a>
                            </div>
                        </div>

                        <div class="brand-detail-info__preview line-block line-block--gap line-block--gap-8 flexbox--wrap font_14">
                            <div class="line-block__item">
                                <a class="chip chip--transparent bordered" href="<?=$arResult['BRAND_ITEM']["DETAIL_PAGE_URL"];?>" target="_blank">
                                    <span class="chip__label"><?=GetMessage("ITEMS_BY_BRAND", array("#BRAND#" => $arResult['BRAND_ITEM']["NAME"]))?></span>
                                </a>
                            </div>
                            <?if($arResult['SECTION']):?>
                                <div class="line-block__item">
                                    <a class="chip chip--transparent bordered" href="<?= $arResult['BRAND_ITEM']['CATALOG_PAGE_URL'] ?>" target="_blank">
                                        <span class="chip__label"><?=GetMessage("ITEMS_BY_SECTION")?></span>
                                    </a>
                                </div>
                            <?endif;?>
                        </div>
                    </div>
                <? endif; ?>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        var navs = $('#popup_iframe_wrapper .navigation-wrapper-fast-view .fast-view-nav');
        if(navs.length) {
            var ajaxData = {
                element: "<?=$arResult['ID']?>",
                iblock: "<?=$arParams['IBLOCK_ID']?>",
                section: "<?=$arResult['IBLOCK_SECTION_ID']?>",
            };

            if($('.smart-filter-filter').length && $('.smart-filter-filter').text().length) {
                try {
                    var text = $('.smart-filter-filter').text().replace(/var filter\s*=\s*/g, '');
                    JSON.parse(text);
                    ajaxData.filter = text;
                }
                catch (e) {}
            }

            if($('.smart-filter-sort').length && $('.smart-filter-sort').text().length) {
                try {
                    var text = $('.smart-filter-sort').text().replace(/var sort\s*=\s*/g, '');
                    JSON.parse(text);
                    ajaxData.sort = text;
                }
                catch (e) {}
            }

            navs.data('ajax', ajaxData);
        }
    </script>
</div>
