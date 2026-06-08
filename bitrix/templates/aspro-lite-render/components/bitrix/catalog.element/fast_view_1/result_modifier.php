<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}

$arParams['SHOW_AMOUNT'] = TSolution\Product\Quantity::isShowQuantityAmountByPage('detail');

/* category path */
if (
    $arResult['IBLOCK_SECTION_ID']
    && !$arResult['CATEGORY_PATH']
) {
    $arCategoryPath = [];
    if (isset($arResult['SECTION']['PATH'])) {
        foreach ($arResult['SECTION']['PATH'] as $arCategory) {
            $arCategoryPath[$arCategory['ID']] = $arCategory['NAME'];
        }
    }

    $arResult['CATEGORY_PATH'] = implode('/', $arCategoryPath);
}

$bShowSKU = $arParams['TYPE_SKU'] !== 'TYPE_2';

/* get sku tree props */
if ($bShowSKU) {
    // check catalog
    $bCatalogSKU = false;
    $arSKU = [];
    if ($arResult['MODULES']['catalog']) {
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
        $arResult['SKU_CONFIG']['SHOW_SKU_DESCRIPTION'] = $arParams['SHOW_SKU_DESCRIPTION'];
        $arResult['SKU_CONFIG']['ICONS_PROPS'] = [
            'CLASS' => 'md',
        ];

        // set only existed values for props
        if (TSolution::isSaleMode() && $arResult['OFFERS']) {
            $obSKU->setItems([0 => ['OFFERS' => $arResult['OFFERS']]]);
            $obSKU->getNeedValues();
        }
        $obSKU->getPropsValue();
    }
}

/* get SKU for item */
$arOfferGallery = [];
if ($bShowSKU) {
    if ($arParams['OID']) {
        $obSKU->setSelectedItem($arParams['OID']);
    }

    if ($bCatalogSKU) {
        $obSKU->setItems($arResult['OFFERS']);
        $obSKU->setRegionCanBuy($arParams);
    } else {
        $arFilter = [
            'PROPERTY_'.$obSKU->linkCodeProp => $arResult['ID'],
            'ACTIVE' => 'Y',
            'IBLOCK_ID' => $arParams['SKU_IBLOCK_ID'],
        ];
        $obSKU->getItemsByFilter($arFilter, []);
        $obSKU->getItemsProps($arParams['SKU_IBLOCK_ID']);
    }
    $obSKU->getMatrix();

    $arResult['SKU'] = [
        'CURRENT' => $obSKU->currentItem,
        'OFFERS' => $obSKU->items,
        'PROPS' => $obSKU->treeProps,
    ];

    if ($arResult['SKU']['CURRENT']) {
        TSolution\Product\Image::setDetailPictureFromPreview($arResult['SKU']['CURRENT'], checkFilledDetailPicture: true);

        $arOfferGallery = $arResult['SKU']['CURRENT']['GALLERY'] = TSolution\Functions::getSliderForItem([
            'TYPE' => 'fast_view',
            'PROP_CODE' => $arParams['OFFER_ADD_PICT_PROP'],
            'ADD_DETAIL_SLIDER' => TSolution\Product\Image::isNeedAddDetailPictureToGallery(),
            'ITEM' => $arResult['SKU']['CURRENT'],
            'PARAMS' => $arParams,
        ]);
    }
}

/* main gallery */
$arResult['DETAIL_PICTURE'] = $arResult['DETAIL_PICTURE'] ?: $arResult['PREVIEW_PICTURE'];

$arResult['GALLERY'] = TSolution\Functions::getSliderForItem([
    'TYPE' => 'fast_view',
    'PROP_CODE' => $arParams['ADD_PICT_PROP'],
    'ADD_DETAIL_SLIDER' => TSolution\Product\Image::isNeedAddDetailPictureToGalleryWithCheckProduct($arResult, $arParams),
    'ADDITIONAL_GALLERY' => $arOfferGallery,
    'ITEM' => $arResult,
    'PARAMS' => $arParams,
]);
array_splice($arResult['GALLERY'], $arParams['MAX_GALLERY_ITEMS']);

/* big gallery */
if ($arParams['SHOW_BIG_GALLERY'] === 'Y') {
    $arResult['BIG_GALLERY'] = [];

    if (
        $arParams['BIG_GALLERY_PROP_CODE']
        && isset($arResult['PROPERTIES'][$arParams['BIG_GALLERY_PROP_CODE']])
        && $arResult['PROPERTIES'][$arParams['BIG_GALLERY_PROP_CODE']]['VALUE']
    ) {
        foreach ($arResult['PROPERTIES'][$arParams['BIG_GALLERY_PROP_CODE']]['VALUE'] as $img) {
            $arPhoto = CFile::GetFileArray($img);

            $alt = $arPhoto['DESCRIPTION'] ?: $arPhoto['ALT'] ?: $arResult['NAME'];
            $title = $arPhoto['DESCRIPTION'] ?: $arPhoto['TITLE'] ?: $arResult['NAME'];

            $arResult['BIG_GALLERY'][] = [
                'DETAIL' => $arPhoto,
                'PREVIEW' => CFile::ResizeImageGet($img, ['width' => 1500, 'height' => 1500], BX_RESIZE_IMAGE_PROPORTIONAL_ALT, true),
                'THUMB' => CFile::ResizeImageGet($img, ['width' => 60, 'height' => 60], BX_RESIZE_IMAGE_EXACT, true),
                'TITLE' => $title,
                'ALT' => $alt,
            ];
        }
    }
}

/* brand item */
$arBrand = [];
if (
    strlen($arResult['DISPLAY_PROPERTIES']['BRAND']['VALUE'])
    && $arResult['PROPERTIES']['BRAND']['LINK_IBLOCK_ID']
) {
    $arBrand = TSolution\Cache::CIBLockElement_GetList(
        [
            'CACHE' => [
                'MULTI' => 'N',
                'TAG' => TSolution\Cache::GetIBlockCacheTag($arResult['PROPERTIES']['BRAND']['LINK_IBLOCK_ID']),
            ],
        ],
        [
            'IBLOCK_ID' => $arResult['PROPERTIES']['BRAND']['LINK_IBLOCK_ID'],
            'ACTIVE' => 'Y',
            'ID' => $arResult['DISPLAY_PROPERTIES']['BRAND']['VALUE'],
        ],
        false,
        false,
        ['ID', 'NAME', 'CODE', 'PREVIEW_TEXT', 'PREVIEW_TEXT_TYPE', 'DETAIL_TEXT', 'DETAIL_TEXT_TYPE', 'PREVIEW_PICTURE', 'DETAIL_PICTURE', 'DETAIL_PAGE_URL', 'PROPERTY_SITE']
    );
    if ($arBrand) {
        $arBrand['CATALOG_PAGE_URL'] = $arResult['SECTION']['SECTION_PAGE_URL'].'filter/brand-is-'.$arBrand['CODE'].'/apply/';
        if (TSolution::isSmartSeoInstalled() && class_exists('\Aspro\Smartseo\General\Smartseo')) {
            $arBrand['CATALOG_PAGE_URL'] = Aspro\Smartseo\General\Smartseo::replaceRealUrlByNew($arBrand['CATALOG_PAGE_URL']);
        }
        $picture = ($arBrand['PREVIEW_PICTURE'] ? $arBrand['PREVIEW_PICTURE'] : $arBrand['DETAIL_PICTURE']);
        if ($picture) {
            $arBrand['IMAGE'] = CFile::ResizeImageGet($picture, ['width' => 200, 'height' => 40], BX_RESIZE_IMAGE_PROPORTIONAL, true);
            $arBrand['IMAGE']['ALT'] = $arBrand['IMAGE']['TITLE'] = $arBrand['NAME'];

            if ($arBrand['DETAIL_PICTURE']) {
                $arBrand['IMAGE']['INFO'] = CFile::GetFileArray($arBrand['DETAIL_PICTURE']);

                $ipropValues = new Bitrix\Iblock\InheritedProperty\ElementValues($arBrand['IBLOCK_ID'], $arBrand['ID']);
                $arBrand['IMAGE']['IPROPERTY_VALUES'] = $ipropValues->getValues();
                if ($arBrand['IMAGE']['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE']) {
                    $arBrand['IMAGE']['TITLE'] = $arBrand['IMAGE']['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_TITLE'];
                }
                if ($arBrand['IMAGE']['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT']) {
                    $arBrand['IMAGE']['ALT'] = $arBrand['IMAGE']['IPROPERTY_VALUES']['ELEMENT_DETAIL_PICTURE_FILE_ALT'];
                }
                if ($arBrand['IMAGE']['INFO']['DESCRIPTION']) {
                    $arBrand['IMAGE']['ALT'] = $arBrand['IMAGE']['TITLE'] = $arBrand['IMAGE']['INFO']['DESCRIPTION'];
                }
            }
        }
    }
}
$arResult['BRAND_ITEM'] = $arBrand;

// sef folder to include files
$sefFolder = rtrim($arParams['SEF_FOLDER'] ?? dirname($_SERVER['REAL_FILE_PATH']), '/');

// include text
ob_start();
$APPLICATION->IncludeFile($sefFolder.'/index_garanty.php', [], ['MODE' => 'html', 'NAME' => GetMessage('TITLE_INCLUDE')]);
$arResult['INCLUDE_CONTENT'] = ob_get_contents();
ob_end_clean();

$arResult['CHARACTERISTICS'] = $arResult['VIDEO'] = $arResult['VIDEO_IFRAME'] = $arResult['POPUP_VIDEO'] = $arResult['TIZERS'] = [];

if ($arResult['SECTION']) {
    $arSectionSelect = [
        'UF_INCLUDE_TEXT',
        'UF_TABLE_SIZES',
    ];

    // get display properties
    $arDetailPageShowProps = Bitrix\Iblock\Model\PropertyFeature::getDetailPageShowPropertyCodes(
        $arParams['IBLOCK_ID'],
        ['CODE' => 'Y']
    );
    if ($arDetailPageShowProps === null) {
        $arDetailPageShowProps = [];
    }

    if (
        in_array('POPUP_VIDEO', $arParams['PROPERTY_CODE'])
        || in_array('POPUP_VIDEO', $arDetailPageShowProps)
    ) {
        $arSectionSelect[] = 'UF_POPUP_VIDEO';
    }

    $arInherite = TSolution::getSectionInheritedUF([
        'sectionId' => $arResult['IBLOCK_SECTION_ID'],
        'iblockId' => $arParams['IBLOCK_ID'],
        'select' => $arSectionSelect,
        'filter' => [
            'GLOBAL_ACTIVE' => 'Y',
        ],
    ]);

    if ($arInherite['UF_INCLUDE_TEXT']) {
        $arResult['INCLUDE_CONTENT'] = $arInherite['UF_INCLUDE_TEXT'];
    }

    if ($arInherite['UF_POPUP_VIDEO']) {
        $arResult['POPUP_VIDEO'] = $arInherite['UF_POPUP_VIDEO'];
    }

    if ($arInherite['UF_TABLE_SIZES']) {
        $rsTypes = CUserFieldEnum::GetList([], ['ID' => $arInherite['UF_TABLE_SIZES']]);

        if ($arType = $rsTypes->GetNext()) {
            $tableSizes = $arType['XML_ID'];
        }

        if ($tableSizes) {
            $arResult['SIZE_PATH'] = SITE_DIR.'/include/table_sizes/detail_'.strtolower($tableSizes).'.php';
            $arResult['SIZE_PATH'] = str_replace('//', '/', $arResult['SIZE_PATH']);
        }
    }
}

if ($arResult['PROPERTIES']['INCLUDE_TEXT']['~VALUE']['TEXT']) {
    $arResult['INCLUDE_CONTENT'] = $arResult['PROPERTIES']['INCLUDE_TEXT']['~VALUE']['TEXT'];
}

if (
    array_key_exists($docsProp, $arResult['DISPLAY_PROPERTIES'])
    && is_array($arResult['DISPLAY_PROPERTIES'][$docsProp])
    && $arResult['DISPLAY_PROPERTIES'][$docsProp]['VALUE']
) {
    foreach ($arResult['DISPLAY_PROPERTIES'][$docsProp]['VALUE'] as $key => $value) {
        if (!intval($value)) {
            unset($arResult['DISPLAY_PROPERTIES'][$docsProp]['VALUE'][$key]);
        }
    }
}

if ($arResult['DISPLAY_PROPERTIES']) {
    if (is_array($arResult['PROPERTIES']['CML2_ARTICLE']['VALUE']) && $arResult['DISPLAY_PROPERTIES']['CML2_ARTICLE']) {
        $arResult['DISPLAY_PROPERTIES']['CML2_ARTICLE']['VALUE'] = reset($arResult['DISPLAY_PROPERTIES']['CML2_ARTICLE']['VALUE']);
    }

    $arResult['CHARACTERISTICS'] = TSolution::PrepareItemProps($arResult['DISPLAY_PROPERTIES']);

    foreach ($arResult['DISPLAY_PROPERTIES'] as $PCODE => $arProp) {
        if (
            $arProp['VALUE']
            || strlen($arProp['VALUE'])
        ) {
            if ($arProp['USER_TYPE'] === 'video') {
                if (count($arProp['PROPERTY_VALUE_ID']) >= 1) {
                    foreach ($arProp['VALUE'] as $val) {
                        if ($val['path']) {
                            $arResult['VIDEO'][] = $val;
                        }
                    }
                } elseif ($arProp['VALUE']['path']) {
                    $arResult['VIDEO'][] = $arProp['VALUE'];
                }
            } elseif ($arProp['CODE'] === 'POPUP_VIDEO') {
                $arResult['POPUP_VIDEO'] = $arProp['VALUE'];
            }
        }
    }
}
