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

$bShowSKU = $arParams['TYPE_SKU'] !== 'TYPE_2';

$bShowHintTextItem = in_array('INCLUDE_TEXT', $arParams['PROPERTY_CODE']);

if (!empty($arResult['ITEMS'])) {
    $__getchipsHelperPath = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/GetchipsCatalogOffersHelper.php';
    if (is_readable($__getchipsHelperPath)) {
        require_once $__getchipsHelperPath;
    }

    if ($bShowHintTextItem) {?>
		<?ob_start(); ?>
			<?$APPLICATION->IncludeComponent(
			    'bitrix:main.include',
			    '',
			    [
			        'AREA_FILE_SHOW' => 'page',
			        'AREA_FILE_SUFFIX' => 'help_text',
			        'EDIT_TEMPLATE' => '',
			    ]
			); ?>
		<?$help_text = ob_get_contents();
        ob_end_clean();
        $bshowHelpTextFromFile = true;
        $arResult['INCLUDE_TEXT_FILE'] = false;
        if (strlen(trim($help_text)) < 1) {
            $bshowHelpTextFromFile = false;
        } else {
            $bIsBitrixDiv = (strpos($help_text, 'bx_incl_area') !== false);
            $textWithoutTags = strip_tags($help_text);
            if ($bIsBitrixDiv && (strlen(trim($textWithoutTags)) < 1)) {
                $bshowHelpTextFromFile = false;
            }
        }

        if ($bshowHelpTextFromFile) {
            $arResult['INCLUDE_TEXT'] = $help_text;
            $arResult['INCLUDE_TEXT_FILE'] = true;
        }
    }

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

        /* get sku tree props */
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
            $arResult['SKU_CONFIG']['SHOW_SKU_DESCRIPTION'] = $arParams['SHOW_SKU_DESCRIPTION'];

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

    $arNewItemsList = $arGoodsSectionsIDs = [];
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
        }

        if ($arItem['IBLOCK_SECTION_ID']) {
            if ($bShowHintTextItem) {
                $resGroups = CIBlockElement::GetElementGroups($arItem['ID'], true, ['ID']);
                while ($arGroup = $resGroups->Fetch()) {
                    $arItem['SECTIONS'][$arGroup['ID']] = $arGroup['ID'];
                }
            }

            /* get UF_INCLUDE_TEXT */
            if ($bShowHintTextItem) {
                $sectionHelpText = '';
                $sectionID = $arItem['SECTIONS'] ? reset($arItem['SECTIONS']) : $arItem['IBLOCK_SECTION_ID'];
                $arSection = TSolution\Cache::CIBlockSection_GetList(['CACHE' => ['MULTI' => 'N', 'TAG' => TSolution\Cache::GetIBlockCacheTag($arParams['IBLOCK_ID'])]], ['GLOBAL_ACTIVE' => 'Y', 'ID' => $sectionID, 'IBLOCK_ID' => $arParams['IBLOCK_ID']], false, ['ID', 'IBLOCK_ID', 'IBLOCK_SECTION_ID', 'DEPTH_LEVEL', 'LEFT_MARGIN', 'RIGHT_MARGIN', 'UF_INCLUDE_TEXT']);

                if (strlen($arSection['UF_INCLUDE_TEXT'])) {
                    $sectionHelpText = $arSection['UF_INCLUDE_TEXT'];
                }
                if (!$sectionHelpText) {
                    if ($arSection['DEPTH_LEVEL'] > 2) {
                        $arSectionParent = TSolution\Cache::CIBlockSection_GetList(['CACHE' => ['MULTI' => 'N', 'TAG' => TSolution\Cache::GetIBlockCacheTag($arParams['IBLOCK_ID'])]], ['GLOBAL_ACTIVE' => 'Y', 'ID' => $arSection['IBLOCK_SECTION_ID'], 'IBLOCK_ID' => $arParams['IBLOCK_ID']], false, ['ID', 'IBLOCK_ID', 'UF_INCLUDE_TEXT']);
                        if (strlen($arSectionParent['UF_INCLUDE_TEXT'])) {
                            $sectionHelpText = $arSectionParent['UF_INCLUDE_TEXT'];
                        }

                        if (!$sectionHelpText) {
                            $arSectionRoot = TSolution\Cache::CIBlockSection_GetList(['CACHE' => ['MULTI' => 'N', 'TAG' => TSolution\Cache::GetIBlockCacheTag($arParams['IBLOCK_ID'])]], ['GLOBAL_ACTIVE' => 'Y', '<=LEFT_BORDER' => $arSection['LEFT_MARGIN'], '>=RIGHT_BORDER' => $arSection['RIGHT_MARGIN'], 'DEPTH_LEVEL' => 1, 'IBLOCK_ID' => $arParams['IBLOCK_ID']], false, ['ID', 'IBLOCK_ID', 'UF_INCLUDE_TEXT']);
                            if (strlen($arSectionRoot['UF_INCLUDE_TEXT'])) {
                                $sectionHelpText = $arSectionRoot['UF_INCLUDE_TEXT'];
                            }
                        }
                    } else {
                        $arSectionRoot = TSolution\Cache::CIBlockSection_GetList(['CACHE' => ['MULTI' => 'N', 'TAG' => TSolution\Cache::GetIBlockCacheTag($arParams['IBLOCK_ID'])]], ['GLOBAL_ACTIVE' => 'Y', '<=LEFT_BORDER' => $arSection['LEFT_MARGIN'], '>=RIGHT_BORDER' => $arSection['RIGHT_MARGIN'], 'DEPTH_LEVEL' => 1, 'IBLOCK_ID' => $arParams['IBLOCK_ID']], false, ['ID', 'IBLOCK_ID', 'UF_INCLUDE_TEXT']);
                        if (strlen($arSectionRoot['UF_INCLUDE_TEXT'])) {
                            $sectionHelpText = $arSectionRoot['UF_INCLUDE_TEXT'];
                        }
                    }
                }
            }
        }

        if ($bShowHintTextItem) {
            if ($arItem['DISPLAY_PROPERTIES']['INCLUDE_TEXT']['~VALUE']) {
                $arItem['INCLUDE_TEXT'] = $arItem['DISPLAY_PROPERTIES']['INCLUDE_TEXT']['~VALUE']['TEXT'];
            } elseif ($sectionHelpText) {
                $arItem['INCLUDE_TEXT'] = $sectionHelpText;
            } elseif ($arResult['INCLUDE_TEXT_FILE']) {
                $arItem['INCLUDE_TEXT'] = $arResult['INCLUDE_TEXT'];
            }
        }

        $arItem['LAST_ELEMENT'] = 'N';

        $arOfferGallery = [];
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
                $obSKU->getPropsValue();
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
                    'TYPE' => 'catalog_list',
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
            'TYPE' => 'catalog_list',
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
?>
