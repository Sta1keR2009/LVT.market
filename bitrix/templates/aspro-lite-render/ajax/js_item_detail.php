<?php

use Aspro\Lite\Grupper;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;

include_once 'const.php';

if (isset($_REQUEST['site_id'])) {
    $SITE_ID = htmlspecialchars($_REQUEST['site_id']);
    define('SITE_ID', $SITE_ID);
}
if (isset($_REQUEST['site_dir'])) {
    $SITE_DIR = htmlspecialchars($_REQUEST['site_dir']);
    define('SITE_DIR', $SITE_DIR);
}

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

// need for solution class and variables
if (!include_once ('../vendor/php/solution.php')) {
    return false;
}

$context = Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();
try {
    $arPost = Json::decode($request->getInput());
} catch (Throwable $th) {
    $arPost = $request->getPostList()->toArray();
}
$clearCache = $request->getQuery('clear_cache');

global $APPLICATION;
$arPost = $APPLICATION->ConvertCharsetArray($arPost, 'UTF-8', LANG_CHARSET);

$bDetail = $arPost['IS_DETAIL'] === 'Y';

$offerID = $arPost['SELECTED_OFFER_ID'];
$maiItemID = $arPost['ID'];

$arOffer = [];

if ($arPost['PARAMS']) {
    /* get info for current offer only */
    if ($offerID) {
        global $USER;
        $USER_ID = $USER->GetID();
        $arUserGroups = $USER->GetUserGroupArray();

        if (!$arPost['PARAMS']['ADD_PICT_PROP']) {
            $arPost['PARAMS']['ADD_PICT_PROP'] = 'MORE_PHOTO';
        }

        $obSKU = new TSolution\SKU($arPost['PARAMS']);

        $bPriceVat = $obSKU->config['PRICE_VAT_INCLUDE'] === true || $obSKU->config['PRICE_VAT_INCLUDE'] === 'true';
        // get only need prop code
        $needProps = array_diff($obSKU->config['SKU_PROPERTY_CODE'], $arPost['PARAMS']['SKU_TREE_PROPS'], ['']);

        if ($offerID) {
            ob_start();
            $APPLICATION->IncludeComponent(
                'bitrix:catalog.element',
                'js_item_detail',
                [
                    'CACHE_GROUPS' => $arPost['PARAMS']['CACHE_GROUPS'],
                    'CACHE_TIME' => $arPost['PARAMS']['CACHE_TIME'],
                    'CACHE_TYPE' => $arPost['PARAMS']['CACHE_TYPE'],
                    'ELEMENT_ID' => $offerID,
                    'IBLOCK_ID' => $arPost['SKU_IBLOCK_ID'],
                    'IBLOCK_TYPE' => 'aspro_lite_catalog',
                    'PRICE_CODE' => $arPost['PRICE_PARAMS']['PRICE_CODE'],
                    'PRICE_VAT_INCLUDE' => $bPriceVat ? 'Y' : 'N',
                    'COMPONENT_TEMPLATE' => 'js_item_detail',
                    'COMPATIBLE_MODE' => 'Y',
                    'PROPERTY_CODE' => $needProps,
                    'STORES' => $arPost['PARAMS']['STORES'],
                    'USE_REGION' => $arPost['PARAMS']['USE_REGION'],
                    'CONVERT_CURRENCY' => $obSKU->config['CONVERT_CURRENCY'],
                    'CURRENCY_ID' => $obSKU->config['CURRENCY_ID'],
                    'SET_VIEWED_IN_COMPONENT' => 'N',
                    'DISABLE_INIT_JS_IN_COMPONENT' => 'Y',
                    'SHOW_404' => 'N',
                    'SET_STATUS_404' => 'N',
                    'FILL_ITEM_ALL_PRICES' => 'Y',
                    // "CLEAR_PROPS" => $arPost["PARAMS"]['SKU_TREE_PROPS'],
                ],
                false,
                ['HIDE_ICONS' => 'Y']
            );
            $json = ob_get_clean();

            $arRes = Json::decode($json);
            $arOffer = $arRes;
        }

        if ($arOffer) {
            /* get main element */
            $arElement = TSolution\Cache::CIBlockElement_GetList(
                [
                    'CACHE' => [
                        'TAG' => TSolution\Cache::GetIBlockCacheTag($arPost['IBLOCK_ID']),
                        'MULTI' => 'N',
                    ],
                ],
                [
                    'ID' => $arPost['ID'],
                    'IBLOCK_ID' => $arPost['IBLOCK_ID'],
                    // 'ACTIVE' => 'Y'
                ],
                false,
                false,
                [
                    'ID',
                    'NAME',
                    'IBLOCK_ID',
                    'PREVIEW_PICTURE',
                    'DETAIL_PICTURE',
                    'PREVIEW_TEXT',
                    'DETAIL_TEXT',
                    'DETAIL_PAGE_URL',
                    'PROPERTY_'.$arPost['PARAMS']['ADD_PICT_PROP'],
                    'PROPERTY_HIT',
                    'PROPERTY_SALE_TEXT',
                ]
            );

            if (
                (!$bDetail && ($arElement['DETAIL_PICTURE'] && $arElement['PREVIEW_PICTURE']))
                || (!$arElement['DETAIL_PICTURE'] && $arElement['PREVIEW_PICTURE'])
            ) {
                $arElement['DETAIL_PICTURE'] = $arElement['PREVIEW_PICTURE'];
            }
            if ($arElement['PROPERTY_'.$arPost['PARAMS']['ADD_PICT_PROP'].'_VALUE']) {
                $arElement['PROPERTIES'][$arPost['PARAMS']['ADD_PICT_PROP']]['PROPERTY_TYPE'] = 'F';
                foreach ((array) $arElement['PROPERTY_'.$arPost['PARAMS']['ADD_PICT_PROP'].'_VALUE'] as $value) {
                    $arElement['PROPERTIES'][$arPost['PARAMS']['ADD_PICT_PROP']]['VALUE'][] = $value;
                }
            }

            $bShowSkuDescription = isset($arPost['PARAMS']['SHOW_SKU_DESCRIPTION']) && $arPost['PARAMS']['SHOW_SKU_DESCRIPTION'] === 'Y';
            if ($bShowSkuDescription) {
                if (!empty($arOffer['DETAIL_TEXT'])) {
                    $arItemTmp['DETAIL_TEXT'] = $arOffer['DETAIL_TEXT'];
                } else {
                    $arItemTmp['DETAIL_TEXT'] = $arElement['DETAIL_TEXT'];
                }

                if (!empty($arOffer['PREVIEW_TEXT'])) {
                    $arItemTmp['PREVIEW_TEXT'] = $arOffer['PREVIEW_TEXT'];
                } else {
                    $arItemTmp['PREVIEW_TEXT'] = $arElement['PREVIEW_TEXT'];
                }
            }

            $arOffer['DETAIL_TEXT'] = $bShowSkuDescription ? $arItemTmp['DETAIL_TEXT'] : '';
            $arOffer['PREVIEW_TEXT'] = $bShowSkuDescription ? $arItemTmp['PREVIEW_TEXT'] : '';

            $bShowCompare = $arPost['PARAMS']['DISPLAY_COMPARE'] !== 'false' && $arPost['PARAMS']['DISPLAY_COMPARE'] !== false;
            $bShowFavorit = $arPost['PARAMS']['SHOW_FAVORITE'] == 'Y';

            if ($arElement['DETAIL_PAGE_URL']) {
                $arOffer['DETAIL_PAGE_URL'] = $arElement['DETAIL_PAGE_URL'];
                if ($arPost['OID']) {
                    $arOffer['DETAIL_PAGE_URL'] .= '?'.$arPost['OID'].'='.$arOffer['ID'];
                }
            }

            if ($arPost['PRICE_PARAMS']['PARAMS']['IS_GIFT']) {
                foreach ($arOffer['PRICES'] as $priceCode => $value) {
                    $arOffer['PRICES'][$priceCode]['DISCOUNT_VALUE'] = $arOffer['PRICES'][$priceCode]['DISCOUNT_DIFF'];
                    $arOffer['PRICES'][$priceCode]['PRINT_DISCOUNT_VALUE'] = $arOffer['PRICES'][$priceCode]['PRINT_DISCOUNT_DIFF'];
                }
            }

            $arPrices = TSolution\Product\Price::show(
                array_merge(
                    (array) $arPost['PRICE_PARAMS'],
                    [
                        'ITEM' => $arOffer,
                        'SHOW_SCHEMA' => false,
                        'RETURN' => true,
                    ]
                )
            );

            if (is_array($arPrices)) {
                $arOffer['PRICES_HTML'] = $arPrices['PRICES'];

                if ($arPrices['ECONOMY']) {
                    $arOffer['DISCOUNT_PRICES_HTML'] = $arPrices['ECONOMY'];
                }
            } else {
                $arOffer['PRICES_HTML'] = $arPrices;
            }

            $pictureID = $arOffer['PREVIEW_PICTURE']['ID'] ?? $arOffer['PREVIEW_PICTURE'];
            if (!$pictureID && $arOffer['DETAIL_PICTURE']) {
                $pictureID = $arOffer['DETAIL_PICTURE']['ID'] ?? $arOffer['DETAIL_PICTURE'];
            }
            if ($pictureID) {
                $arOffer['PICTURE_SRC'] = CFile::GetPath($pictureID);
            }
            $bPreviewFromElement = false;
            if ($arElement['PREVIEW_PICTURE'] && !$pictureID) {
                $arOffer['PREVIEW_PICTURE'] = $arElement['PREVIEW_PICTURE'];
                $bPreviewFromElement = true;
            }

            if (!$bPreviewFromElement && $arOffer['PREVIEW_PICTURE']) {
                if (!$bDetail) {
                    $arOffer['DETAIL_PICTURE'] = $arOffer['PREVIEW_PICTURE'];
                } elseif (!$arOffer['DETAIL_PICTURE']) {
                    $arOffer['DETAIL_PICTURE'] = $arOffer['PREVIEW_PICTURE'];
                }
            }

            $arOfferGallery = TSolution\Functions::getSliderForItem([
                'TYPE' => $arPost['IMG_PARAMS']['TYPE'],
                'PROP_CODE' => $arPost['PARAMS']['ADD_PICT_PROP'],
                'ADD_DETAIL_SLIDER' => TSolution\Product\Image::isNeedAddDetailPictureToGallery(),
                'ITEM' => $arOffer,
                'PARAMS' => $arPost['PARAMS'],
            ]);

            $arOffer['GALLERY'] = TSolution\Functions::getSliderForItem([
                'TYPE' => $arPost['IMG_PARAMS']['TYPE'],
                'PROP_CODE' => $arPost['PARAMS']['ADD_PICT_PROP'],
                'ADD_DETAIL_SLIDER' => $arPost['PARAMS']['ADD_DETAIL_SLIDER'],
                'ADDITIONAL_GALLERY' => $arOfferGallery,
                'ITEM' => $arElement,
                'PARAMS' => $arPost['PARAMS'],
            ]);

            $arOffer['GALLERY'] = array_merge($arOfferGallery, $arOffer['GALLERY']);

            if (!$bDetail) {
                array_splice($arOffer['GALLERY'], $arPost['MAX_GALLERY_ITEMS']);
            }

            $arOffer['GALLERY_HTML'] = TSolution\Product\Image::showImage(
                array_merge(
                    (array) $arPost['IMG_PARAMS'],
                    [
                        'ITEM' => $arOffer,
                        'PARAMS' => $arPost['PARAMS'],
                        'RETURN' => true,
                    ]
                )
            );

            $arOffer['ICONS_HTML'] = '';
            $arIconProps = $arPost['PARAMS']['ICONS_PROPS'] ?? [];
            if ($bShowFavorit) {
                $arOffer['ICONS_HTML'] .= TSolution\Product\Common::getActionIcon(array_merge([
                    'ITEM' => $arOffer,
                    'PARAMS' => $arPost['PARAMS'],
                    'CATALOG_IBLOCK_ID' => $arOffer['IBLOCK_ID'],
                    'ITEM_ID' => $arOffer['ID'],
                ], $arIconProps));
            }

            if ($bShowCompare) {
                $arOffer['ICONS_HTML'] .= TSolution\Product\Common::getActionIcon(array_merge([
                    'ITEM' => TSolution::isSaleMode() ? $arOffer : $arElement,
                    'PARAMS' => $arPost['PARAMS'],
                    'TYPE' => 'compare',
                    'SVG_SIZE' => ['WIDTH' => 20, 'HEIGHT' => 16],
                    'CATALOG_IBLOCK_ID' => $arElement['IBLOCK_ID'],
                    'ITEM_ID' => $arElement['ID'],
                ], $arIconProps));
            }

            $arOffer['BASKET_JSON'] = TSolution::getDataItem($arOffer, false);
            if ($arOffer['DETAIL_PAGE_URL']) {
                $arOffer['BASKET_JSON']['DETAIL_PAGE_URL'] = $arOffer['DETAIL_PAGE_URL'];
            }

            // props for display
            $arOffer['OFFER_PROP'] = TSolution::PrepareItemProps($arOffer['DISPLAY_PROPERTIES']);

            // unset HINT in prop if not show HINTS
            if ($arOffer['OFFER_PROP'] && $arPost['PARAMS']['SHOW_HINTS'] !== 'Y') {
                foreach ($arOffer['OFFER_PROP'] as $key => $arProp) {
                    if ($arProp['HINT']) {
                        unset($arOffer['OFFER_PROP'][$key]['HINT']);
                    }
                }
            }

            // grupper
            if ($bDetail) {
                $grupperProps = Grupper::get($arPost['SITE_ID']);
                if ($grupperProps) {
                    $arPost['PARAMS']['PROPERTIES_DISPLAY_TYPE'] = 'TABLE';

                    if (
                        (
                            $grupperProps == 'GRUPPER'
                            && !Loader::includeModule('redsign.grupper')
                        )
                        || (
                            $grupperProps == 'WEBDEBUG'
                            && !Loader::includeModule('webdebug.utilities')
                        )
                        || (
                            $grupperProps == 'YENISITE_GRUPPER'
                            && !Loader::includeModule('yenisite.infoblockpropsplus')
                        )
                    ) {
                        $grupperProps = 'NOT';
                    }
                }

                if (
                    $grupperProps === 'ASPRO_PROPS_GROUP'
                    || $grupperProps === 'NOT'
                ) {
                    ob_start();
                    $APPLICATION->IncludeComponent(
                        'aspro:props.group.lite',
                        TSolution::solutionName,
                        [
                            'DISPLAY_PROPERTIES' => $arOffer['OFFER_PROP'],
                            'IBLOCK_ID' => $arPost['SKU_IBLOCK_ID'],
                            'MODULE_ID' => TSolution::moduleID,
                            'SHOW_HINTS' => $arPost['PARAMS']['SHOW_HINTS'],
                            'OFFERS_MODE' => 'Y',
                            'PROPERTIES_DISPLAY_TYPE' => $arPost['PARAMS']['PROPERTIES_DISPLAY_TYPE'],
                        ],
                        false,
                        ['HIDE_ICONS' => 'Y']
                    );
                    $htmlProps = ob_get_clean();
                    $arOffer['PROPS_GROUP_HTML'] = $htmlProps;
                }
            }

            // unset HINT in prop if not show HINTS
            if ($arOffer['OFFER_PROP'] && $arPost['PARAMS']['SHOW_HINTS'] !== 'Y') {
                foreach ($arOffer['OFFER_PROP'] as $key => $arProp) {
                    if ($arProp['HINT']) {
                        unset($arOffer['OFFER_PROP'][$key]['HINT']);
                    }
                }
            }

            // avaible
            $arOffer['STATUS'] = TSolution\Product\Quantity::getStatus([
                'ITEM' => $arOffer,
                'PARAMS' => $arPost['PARAMS'],
                'TOTAL_COUNT' => $arOffer['TOTAL_COUNT'],
                'IS_DETAIL' => $bDetail,
            ]);

            // basket buttons
            $arBasketConfig = TSolution\Product\Basket::getOptions(array_merge(
                (array) $arPost['BASKET_PARAMS'],
                [
                    'ITEM' => $arOffer,
                    'IS_OFFER' => true,
                    'PARAMS' => $obSKU->config,
                    'TOTAL_COUNT' => $arOffer['TOTAL_COUNT'],
                    // 'ORDER_BTN' => ($arOffer["DISPLAY_PROPERTIES"]["FORM_ORDER"]["VALUE_XML_ID"] == "YES"),
                    'SHOW_COUNTER' => false,
                ]
            ));
            $arOffer['BASKET_HTML'] = $arBasketConfig['HTML'];

            $arOneClickConfig = TSolution\Product\Basket::showOneClickBuyButton(array_merge(
                (array) $arPost['ONE_CLICK_PARAMS'],
                [
                    'ONE_CLICK_BUY' => $arBasketConfig['ACTION'] == 'ADD',
                    'ITEM' => [
                        'NAME' => $arOffer['NAME'],
                    ],
                ]
            ));
            $arOffer['ONE_CLICK_BUY_HTML'] = $arOneClickConfig;

            // viewed
            if ($bDetail) {
                $arOffer['VIEWED_PARAMS'] = TSolution\Product\Common::getViewedParams([
                    'ITEM' => $arOffer,
                ]);
            }

            // remove CATALOG_* fields
            $arOfferTmp = array_filter($arOffer, fn ($key) => strpos($key, 'CATALOG_') === false, ARRAY_FILTER_USE_KEY);

            // remove unused properties
            if (isset($arOfferTmp['DISPLAY_PROPERTIES']['CML2_ARTICLE']) || isset($arOfferTmp['DISPLAY_PROPERTIES']['ARTICLE'])) {
                $arOfferTmp['DISPLAY_PROPERTIES'] = ['CML2_ARTICLE' => $arOfferTmp['DISPLAY_PROPERTIES']['CML2_ARTICLE'] ?? $arOfferTmp['DISPLAY_PROPERTIES']['ARTICLE']];
            } else {
                unset($arOfferTmp['DISPLAY_PROPERTIES']);
            }

            $arOffer = $arOfferTmp;
        }
    }

    exit(Json::encode($arOffer));
}
