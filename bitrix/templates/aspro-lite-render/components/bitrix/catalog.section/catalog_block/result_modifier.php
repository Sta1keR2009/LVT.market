<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}

if (($arParams['BIG_DATA_MODE'] ?? 'N') === 'Y') {
    $component = $this->getComponent();
    $arParams = $component->applyTemplateModifications();
}

$arDefaultParams = [
    'TYPE_SKU' => 'N',
    'FILTER_HIT_PROP' => 'block',
    'OFFER_TREE_PROPS' => ['-'],
    'BIG_DATA_MODE' => 'N',
    'BIGDATA_COUNT' => '10',
    'SHOW_AMOUNT' => TSolution\Product\Quantity::isShowQuantityAmountByPage('list'),
];
$arParams = array_merge($arDefaultParams, $arParams);

$bShowSKU = $arParams['TYPE_SKU'] !== 'TYPE_2';

if ($arParams['SHOW_PROPS'] == 'Y') {
    $arParams['SHOW_GALLERY'] = 'N';
}

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
            $arResult['SKU_CONFIG']['ICONS_PROPS'] = [
                'CLASS' => 'md',
            ];

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

    $arNewItemsList = [];
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

        if (!empty($arItem['DISPLAY_PROPERTIES'])) {
            foreach ($arItem['DISPLAY_PROPERTIES'] as $propKey => $arDispProp) {
                if ($arDispProp['PROPERTY_TYPE'] == 'F' || $arDispProp['CODE'] == $arParams['STIKERS_PROP']) {
                    unset($arItem['DISPLAY_PROPERTIES'][$propKey]);
                }
            }
            $arItem['SHOW_PROPERTIES'] = TSolution::PrepareItemProps($arItem['DISPLAY_PROPERTIES']);
        }

        $arItem['LAST_ELEMENT'] = 'N';

        /* get SKU for item */
        $arOfferGallery = [];
        if ($bShowSKU) {
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

            if ($arItem['SKU']['CURRENT'] && $arParams['SHOW_GALLERY'] === 'Y') {
                TSolution\Product\Image::setDetailPictureFromPreview($arItem['SKU']['CURRENT']);

                $arOfferGallery = $arItem['SKU']['CURRENT']['GALLERY'] = TSolution\Functions::getSliderForItem([
                    'TYPE' => 'catalog_block',
                    'PROP_CODE' => $arParams['OFFER_ADD_PICT_PROP'],
                    'ADD_DETAIL_SLIDER' => TSolution\Product\Image::isNeedAddDetailPictureToGallery(),
                    'ITEM' => $arItem['SKU']['CURRENT'],
                    'PARAMS' => $arParams,
                ]);
            }
        } else {
            TSolution\SKU::setSku2ItemValues($arItem, $arParams);
        }

        $arItem['GALLERY'] = TSolution\Functions::getSliderForItem([
            'TYPE' => 'catalog_block',
            'PROP_CODE' => $arParams['ADD_PICT_PROP'],
            'ADD_DETAIL_SLIDER' => TSolution\Product\Image::isNeedAddDetailPictureToGalleryWithCheckProduct($arItem, $arParams),
            'ADDITIONAL_GALLERY' => $arOfferGallery,
            'ITEM' => $arItem,
            'PARAMS' => $arParams,
        ]);
        array_splice($arItem['GALLERY'], $arParams['MAX_GALLERY_ITEMS']);

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
