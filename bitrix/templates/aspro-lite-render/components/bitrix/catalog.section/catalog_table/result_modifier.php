<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}

$arDefaultParams = [
    'TYPE_SKU' => 'N',
    'FILTER_HIT_PROP' => 'block',
    'OFFER_TREE_PROPS' => ['-'],
    'SHOW_AMOUNT' => TSolution\Product\Quantity::isShowQuantityAmountByPage('list'),
];
$arParams = array_merge($arDefaultParams, $arParams);

$arParams['ITEMS_OFFSET'] = false;
$arParams['SHOW_GALLERY'] = 'N';
$arResult['SHOW_COLS_PROP'] = false;
$arResult['COLS_PROP'] = [];
$arResult['SHOW_IMAGE'] = $bHideImg = true;
$arNewItemsList = [];

$bShowSKU = $arParams['TYPE_SKU'] !== 'TYPE_2';

if (!empty($arResult['ITEMS'])) {
    $__getchipsHelperPath = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/GetchipsCatalogOffersHelper.php';
    if (is_readable($__getchipsHelperPath)) {
        require_once $__getchipsHelperPath;
    }

    /* get sku tree props */
    if ($bShowSKU) {
        // check catalog
        $bCatalogSKU = false;
        $arSKU = [];
        if (TSolution::isSaleMode() && $arResult['MODULES']['catalog']) {
            $arSKU = (array) CCatalogSKU::GetInfoByProductIBlock($arParams['IBLOCK_ID']);
            $bCatalogSKU = !empty($arSKU) && is_array($arSKU);
            if ($bCatalogSKU) {
                $arParams['SKU_IBLOCK_ID'] = $arSKU['IBLOCK_ID'];
                $arParams['LINK_SKU_PROP_CODE'] = 'CML2_LINK';
                $arParams['USE_CATALOG_SKU'] = true;

                $bUseModuleProps = Bitrix\Main\Config\Option::get('iblock', 'property_features_enabled', 'N') === 'Y';
                if ($bUseModuleProps) {
                    $arParams['OFFERS_CART_PROPERTIES'] = Bitrix\Catalog\Product\PropertyCatalogFeature::getBasketPropertyCodes($arSKU['IBLOCK_ID'], ['CODE' => 'Y']);
                    if ($featureProps = Bitrix\Catalog\Product\PropertyCatalogFeature::getOfferTreePropertyCodes($arSKU['IBLOCK_ID'], ['CODE' => 'Y'])) {
                        $arParams['SKU_TREE_PROPS'] = $featureProps;
                    }
                    if ($featureProps = Bitrix\Iblock\Model\PropertyFeature::getListPageShowPropertyCodes($arSKU['IBLOCK_ID'], ['CODE' => 'Y'])) {
                        $arParams['SKU_PROPERTY_CODE'] = $featureProps;
                    }
                }
                if (!$arParams['SKU_TREE_PROPS'] && isset($arParams['OFFERS_CART_PROPERTIES']) && is_array($arParams['OFFERS_CART_PROPERTIES'])) {
                    $arParams['SKU_TREE_PROPS'] = $arParams['OFFERS_CART_PROPERTIES'];
                }
            }
        }

        $obSKU = new TSolution\SKU($arParams);
        if ($arParams['SKU_IBLOCK_ID'] && $arParams['SKU_TREE_PROPS']) {
            $arTreeFilter = [
                '=IBLOCK_ID' => $arParams['SKU_IBLOCK_ID'],
                'CODE' => $arParams['SKU_TREE_PROPS'],
            ];
            $obSKU->getTreePropsByFilter($arTreeFilter, $arSKU);
            $arResult['SKU_CONFIG'] = $obSKU->config;
            $arResult['SKU_CONFIG']['ADD_PICT_PROP'] = $arParams['ADD_PICT_PROP'];
            $arResult['SKU_CONFIG']['SHOW_GALLERY'] = $arParams['SHOW_GALLERY'];

            // set only existed values for props
            $arFilterSKU = $GLOBALS[$arParams['FILTER_NAME']];
            if (TSolution::isSaleMode() && $arResult['ITEMS']) {
                if ($arFilterSKU && $arFilterSKU['OFFERS_ID']) {
                    foreach ($arResult['ITEMS'] as $key => $arItem) {
                        if ($arItem['OFFERS']) {
                            $arResult['ITEMS'][$key]['OFFERS'] = array_filter($arItem['OFFERS'], function ($arValue) use ($arFilterSKU) {
                                return in_array($arValue['ID'], $arFilterSKU['OFFERS_ID']);
                            });
                        }
                    }
                }
                $obSKU->setItems($arResult['ITEMS']);
                $obSKU->getNeedValues();
            }
            $obSKU->getPropsValue();
        }
    }

    foreach ($arResult['ITEMS'] as $key => $arItem) {
        if ($arItem['PRODUCT_PROPERTIES_FILL']) {
            foreach ($arItem['PRODUCT_PROPERTIES_FILL'] as $propID => $propInfo) {
                if (isset($arItem['PRODUCT_PROPERTIES'][$propID])) {
                    unset($arItem['PRODUCT_PROPERTIES'][$propID]);
                }
            }
        }

        if (is_array($arItem['PROPERTIES']['CML2_ARTICLE']['VALUE']) && $arItem['DISPLAY_PROPERTIES']['CML2_ARTICLE']) {
            $arItem['DISPLAY_PROPERTIES']['CML2_ARTICLE']['VALUE'] = reset($arItem['DISPLAY_PROPERTIES']['CML2_ARTICLE']['VALUE']);
            $arResult['ITEMS'][$key]['DISPLAY_PROPERTIES']['CML2_ARTICLE']['VALUE'] = $arItem['DISPLAY_PROPERTIES']['CML2_ARTICLE']['VALUE'];
        }

        if (($arItem['DETAIL_PICTURE'] && $arItem['PREVIEW_PICTURE']) || (!$arItem['DETAIL_PICTURE'] && $arItem['PREVIEW_PICTURE'])) {
            $arItem['DETAIL_PICTURE'] = $arItem['PREVIEW_PICTURE'];
        }

        if ($arItem['PREVIEW_PICTURE'] || $arItem['DETAIL_PICTURE']) {
            $bHideImg = false;
        }

        if (!empty($arItem['DISPLAY_PROPERTIES'])) {
            foreach ($arItem['DISPLAY_PROPERTIES'] as $propKey => $arDispProp) {
                if ($arDispProp['PROPERTY_TYPE'] == 'F') {
                    unset($arItem['DISPLAY_PROPERTIES'][$propKey]);
                }
            }
        }

        $arItem['PROPS'] = [];
        if (!empty($arItem['DISPLAY_PROPERTIES'])) {
            foreach ($arItem['DISPLAY_PROPERTIES'] as $propKey => $arDispProp) {
                if ($arDispProp['PROPERTY_TYPE'] == 'F' || $arDispProp['CODE'] == $arParams['STIKERS_PROP']) {
                    unset($arItem['DISPLAY_PROPERTIES'][$propKey]);
                }
            }
            $arItem['PROPS'] = TSolution::PrepareItemProps($arItem['DISPLAY_PROPERTIES']);

            if ($arItem['PROPS']) {
                $arResult['SHOW_COLS_PROP'] = true;
                foreach ($arItem['PROPS'] as $code => $arProp) {
                    $arResult['COLS_PROP'][$code] = [
                        'NAME' => $arProp['NAME'],
                        'ID' => $arProp['ID'],
                        'SORT' => $arProp['SORT'],
                        'HINT' => $arProp['HINT'],
                    ];
                }
            }
        }

        if ($arParams['REPLACED_DETAIL_LINK']) {
            $arItem['DETAIL_PAGE_URL'] = $arParams['REPLACED_DETAIL_LINK'];
            $oid = Bitrix\Main\Config\Option::get(VENDOR_MODULE_ID, 'CATALOG_OID', 'oid');
            if ($oid) {
                $arItem['DETAIL_PAGE_URL'] .= '?'.$oid.'='.$arItem['ID'];
            }
        }

        $arItem['LAST_ELEMENT'] = 'N';

        if ($bShowSKU) {
            /* get SKU for item */
            if ($bCatalogSKU) {
                $obSKU->setCurrentSku($arItem, $arParams);
                $obSKU->setItems($arItem['OFFERS']);
                $obSKU->setRegionCanBuy($arParams);
            } else {
                $arFilter = [
                    'PROPERTY_'.$obSKU->linkCodeProp => $arItem['ID'],
                    'ACTIVE' => 'Y',
                    'IBLOCK_ID' => $arParams['SKU_IBLOCK_ID'],
                ];
                if ($arFilterSKU && $arFilterSKU['OFFERS_ID']) {
                    $arFilter['ID'] = $arFilterSKU['OFFERS_ID'];
                }
                $obSKU->getItemsByFilter($arFilter, []);
                $obSKU->getItemsProps($arParams['SKU_IBLOCK_ID']);
            }
            $obSKU->getMatrix();

            $arItem['SKU'] = [
                'CURRENT' => $obSKU->currentItem,
                'OFFERS' => $obSKU->items,
                'PROPS' => $obSKU->treeProps,
            ];
        } else {
            TSolution\SKU::setSku2ItemValues($arItem, $arParams);
        }

        if (class_exists('GetchipsCatalogOffersHelper', false)) {
            $arItem['GETCHIPS_ARTICLE'] = GetchipsCatalogOffersHelper::resolveArticleForCatalogSectionItem($arItem);
            $arItem['GETCHIPS_PREVIEW_SRC'] = GetchipsCatalogOffersHelper::getProductPreviewSrcForGetchipsCartRow($arItem);
        }

        $arNewItemsList[$key] = $arItem;
    }

    $arNewItemsList[$key]['LAST_ELEMENT'] = 'Y';
    $arResult['ITEMS'] = $arNewItemsList;

    unset($arNewItemsList);

    $getchipsSectionAssets = false;
    foreach ($arResult['ITEMS'] as $__gcIt) {
        if (!empty($__gcIt['GETCHIPS_ARTICLE']) && mb_strlen((string) $__gcIt['GETCHIPS_ARTICLE']) >= 3) {
            $getchipsSectionAssets = true;
            break;
        }
    }
    if ($getchipsSectionAssets && class_exists('\Bitrix\Main\Page\Asset')) {
        $asset = \Bitrix\Main\Page\Asset::getInstance();
        $asset->addJs('/local/js/getchips_offers_card.js?v=13');
        $asset->addCss('/local/css/getchips_offers_card.css?v=11');
    }

    if ($arResult['COLS_PROP']) {
        Bitrix\Main\Type\Collection::sortByColumn($arResult['COLS_PROP'], [
            'SORT' => [SORT_NUMERIC, SORT_ASC],
            'ID' => [SORT_NUMERIC, SORT_ASC],
        ], '', null, true);
    }

    if ($arParams['HIDE_NO_IMAGE'] === 'Y') {
        $arResult['SHOW_IMAGE'] = $bHideImg ? false : true;
    }

    $arResult['CUSTOM_RESIZE_OPTIONS'] = [];
    if ($arParams['USE_CUSTOM_RESIZE_LIST'] == 'Y') {
        $arIBlockFields = CIBlock::GetFields($arParams['IBLOCK_ID']);
        if ($arIBlockFields['PREVIEW_PICTURE'] && $arIBlockFields['PREVIEW_PICTURE']['DEFAULT_VALUE']) {
            if ($arIBlockFields['PREVIEW_PICTURE']['DEFAULT_VALUE']['WIDTH'] && $arIBlockFields['PREVIEW_PICTURE']['DEFAULT_VALUE']['HEIGHT']) {
                $arResult['CUSTOM_RESIZE_OPTIONS'] = $arIBlockFields['PREVIEW_PICTURE']['DEFAULT_VALUE'];
            }
        }
    }
}
