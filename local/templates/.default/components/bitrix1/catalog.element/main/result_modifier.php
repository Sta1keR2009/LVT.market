<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}
use Bitrix\Main\Loader;
use Bitrix\Catalog\PriceTable; // Добавьте этот use

/* получить все свойства раздела элемента, которые отображаются в умном фильтре */
\Bitrix\Main\Loader::includeModule('iblock');

$iblockId = $arParams['IBLOCK_ID'];

// собираем подзапрос в таблицу свойств раздела
$subQuery = \Bitrix\Iblock\SectionPropertyTable::query()->setSelect(['PROPERTY_ID'])->where('IBLOCK_ID', $iblockId)->where('SMART_FILTER', 'Y'); 

// получаем свойства выводимые в умный фильтр
$dbIblockProps = \Bitrix\Iblock\PropertyTable::query() 
	->where('IBLOCK_ID', $iblockId)
	->whereIn('ID', $subQuery)
	->setSelect(["ID", "NAME","CODE"])
	->exec();

while ($arIblockProp = $dbIblockProps->fetch()){
	$arResult['SMART_FILTER'][$arIblockProp['CODE']] = $arIblockProp;
}

if (Loader::includeModule('catalog') && Loader::includeModule('sale')) {
    // Получаем все типы цен
    $priceTypes = \Bitrix\Catalog\GroupTable::getList([
        'select' => ['ID', 'NAME']
    ])->fetchAll();
    
    // Получаем все цены товара
    $prices = \Bitrix\Catalog\PriceTable::getList([
        'filter' => ['PRODUCT_ID' => $arResult['ID']],
        'order' => ['CATALOG_GROUP_ID' => 'ASC', 'QUANTITY_FROM' => 'ASC']
    ])->fetchAll();
    
    // Формируем массив для вывода
    $arResult['EXTENDED_PRICES'] = [];
    foreach ($prices as $price) {
        $typeName = '';
        foreach ($priceTypes as $type) {
            if ($type['ID'] == $price['CATALOG_GROUP_ID']) {
                $typeName = $type['NAME'];
                break;
            }
        }
        
        $arResult['EXTENDED_PRICES'][] = [
            'TYPE' => $typeName,
            'PRICE' => $price['PRICE'],
            'CURRENCY' => $price['CURRENCY'],
            'QUANTITY_FROM' => $price['QUANTITY_FROM'],
            'QUANTITY_TO' => $price['QUANTITY_TO']
        ];
    }
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

$bShowSKU = $arParams['TYPE_SKU'] !== 'TYPE_2'; ?>

<?php
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
// Получаем валюту товара
$arResult['CURRENCY'] = $arResult['PRICES']['BASE']['CURRENCY'];
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
        $obSKU->setDetailURL($arResult['DETAIL_PAGE_URL']);
    }
    $obSKU->getMatrix();

    $arResult['SKU'] = [
        'CURRENT' => $obSKU->currentItem,
        'OFFERS' => $obSKU->items,
        'PROPS' => $obSKU->treeProps,
        'SKU_GROUP' => false,
        'SKU_GROUP_VALUES' => [],
    ];

    if ($arResult['SKU']['CURRENT']) {
        TSolution\Product\Image::setDetailPictureFromPreview($arResult['SKU']['CURRENT'], checkFilledDetailPicture: true);

        $arOfferGallery = $arResult['SKU']['CURRENT']['GALLERY'] = TSolution\Functions::getSliderForItem([
            'TYPE' => 'catalog_detail',
            'PROP_CODE' => $arParams['OFFER_ADD_PICT_PROP'],
            'ADD_DETAIL_SLIDER' => TSolution\Product\Image::isNeedAddDetailPictureToGallery(),
            'ITEM' => $arResult['SKU']['CURRENT'],
            'PARAMS' => $arParams,
        ]);
    }

    $arOfferIDs = array_column($arResult['SKU']['OFFERS'], 'ID');

    if ($arOfferIDs && CBXFeatures::IsFeatureEnabled('CatCompleteSet') && TSolution::isSaleMode()) {
        $offerSet = array_fill_keys($arOfferIDs, false);
        $rsSets = CCatalogProductSet::getList(
            [],
            [
                '@OWNER_ID' => $arOfferIDs,
                '=SET_ID' => 0,
                '=TYPE' => CCatalogProductSet::TYPE_GROUP,
            ],
            false,
            false,
            ['ID', 'OWNER_ID']
        );

        while ($arSet = $rsSets->Fetch()) {
            $arSet['OWNER_ID'] = (int) $arSet['OWNER_ID'];
            $offerSet[$arSet['OWNER_ID']] = true;
            $arResult['SKU']['SKU_GROUP'] = true;
        }

        if ($offerSet[$arResult['ID']]) {
            foreach ($offerSet as &$setOfferValue) {
                if ($setOfferValue === false) {
                    $setOfferValue = true;
                }
            }
            unset($setOfferValue);
            unset($offerSet[$arResult['ID']]);
        }

        if ($arResult['SKU']['SKU_GROUP']) {
            $offerSet = array_filter($offerSet);
            $arResult['SKU']['SKU_GROUP_VALUES'] = array_keys($offerSet);
        }
    }

    foreach ($arResult['SKU']['PROPS'] as $key => $prop) {
        if ($prop['SHOW_MODE'] === 'text') {
            $arResult['SKU']['PROPS'][$key]['FONT'] = 16;
        }
    }

    if ($arResult['SKU']['OFFERS']) {
        if (TSolution::isSaleMode()) {
            $arResult['MAX_PRICE'] = TSolution\Product\Price::getMaxPriceFromOffersExt($arResult['OFFERS']);
            if (!$arResult['MIN_PRICE']) {
                $arResult['MIN_PRICE'] = TSolution\Product\Price::getMinPriceFromOffersExt($arResult['OFFERS']);
            }
        } else {
            $arResult['MIN_PRICE'] = TSolution\Product\Price::getPriceTypeFromOffersProperties([
                'OFFERS' => $arResult['SKU']['OFFERS'],
                'STATIC' => true,
                'TYPE' => 'min',
            ]);
            $arResult['MAX_PRICE'] = TSolution\Product\Price::getPriceTypeFromOffersProperties([
                'OFFERS' => $arResult['SKU']['OFFERS'],
                'STATIC' => true,
                'TYPE' => 'max',
            ]);
        }
    }
} else {
    if (TSolution::isSaleMode() && $arResult['OFFERS']) {
        $arResult['PRICES'] = []; // clear PRICES
        $arResult['PRICES'][] = TSolution\Product\Price::getMinPriceFromOffersExt($arResult['OFFERS']);
        $arResult['HAS_SKU'] = true;
    } else {
        $arResult['HAS_SKU'] = TSolution\SKU::hasSKU($arResult, $arParams);
        if (
            $arResult['HAS_SKU']
            && $arResult['DISPLAY_PROPERTIES']
            && isset($arResult['DISPLAY_PROPERTIES']['PRICE'])
            && (int) $arResult['DISPLAY_PROPERTIES']['PRICE']['VALUE']
        ) {
            $arResult['DISPLAY_PROPERTIES']['PRICE']['VALUE'] = TSolution\Product\Price::addFromTextBeforePrice($arResult['DISPLAY_PROPERTIES']['PRICE']['VALUE']);
        }
    }
}

?>

<?/* main gallery */
$arResult['DETAIL_PICTURE'] = $arResult['DETAIL_PICTURE'] ?: $arResult['PREVIEW_PICTURE'];

$arResult['GALLERY'] = TSolution\Functions::getSliderForItem([
    'TYPE' => 'catalog_detail',
    'PROP_CODE' => $arParams['ADD_PICT_PROP'],
    'ADD_DETAIL_SLIDER' => TSolution\Product\Image::isNeedAddDetailPictureToGalleryWithCheckProduct($arResult, $arParams),
    'ADDITIONAL_GALLERY' => $arOfferGallery,
    'ITEM' => $arResult,
    'PARAMS' => $arParams,
]);

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

            $alt = $arPhoto['DESCRIPTION'] ?: ($arPhoto['ALT'] ?: $arResult['NAME']);
            $title = $arPhoto['DESCRIPTION'] ?: ($arPhoto['TITLE'] ?: $arResult['NAME']);

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

/* complect */
$arResult['SET_ITEMS_QUANTITY'] = $arResult['SET_ITEMS'] = [];

if (TSolution::isSaleMode()) {
    if ($arParams['SHOW_KIT_PARTS'] == 'Y' && $arResult['CATALOG_TYPE'] == CCatalogProduct::TYPE_SET) {
        $arSetItems = $arSetItemsOtherID = [];
        $arSets = CCatalogProductSet::getAllSetsByProduct($arResult['ID'], 1);

        if (is_array($arSets) && !empty($arSets)) {
            foreach ($arSets as $key => $set) {
                Bitrix\Main\Type\Collection::sortByColumn($set['ITEMS'], ['SORT' => SORT_ASC]);

                foreach ($set['ITEMS'] as $i => $val) {
                    $arSetItems[] = $val['ITEM_ID'];
                    $arSetItemsOtherID[$val['ITEM_ID']]['SORT'] = $val['SORT'];
                    $arSetItemsOtherID[$val['ITEM_ID']]['QUANTITY'] = $val['QUANTITY'];
                }
            }
        }

        if (!empty($arSetItems)) {
            $db_res = CIBlockElement::GetList(['SORT' => 'ASC'], ['ID' => $arSetItems], false, false, ['ID', 'PROPERTY_CML2_LINK']);

            while ($res = $db_res->GetNext()) {
                if ($res['PROPERTY_CML2_LINK_VALUE']) {
                    $res['OFFER_ID'] = $res['ID'];
                }

                $res['SORT'] = $arSetItemsOtherID[$res['ID']]['SORT'];
                $res['QUANTITY'] = $arSetItemsOtherID[$res['ID']]['QUANTITY'];
                $res['ID'] = $res['PROPERTY_CML2_LINK_VALUE'] ?? $res['ID'];
                $arResult['SET_ITEMS'][$res['ID']] = $res;
            }

            Bitrix\Main\Type\Collection::sortByColumn($arResult['SET_ITEMS'], ['SORT' => SORT_ASC], false, false, true);
        }
    }
}

// sef folder to include files
$arResult['INCLUDE_FOLDER_PATH'] = rtrim($arParams['SEF_FOLDER'] ?? dirname($_SERVER['REAL_FILE_PATH']), '/');

// include text
ob_start();
$APPLICATION->IncludeFile($arResult['INCLUDE_FOLDER_PATH'].'/index_garanty.php', [], ['MODE' => 'html', 'NAME' => GetMessage('TITLE_INCLUDE')]);
$arResult['INCLUDE_CONTENT'] = ob_get_contents();
ob_end_clean();

// price text
ob_start();
$APPLICATION->IncludeFile($arResult['INCLUDE_FOLDER_PATH'].'/index_price.php', [], ['MODE' => 'html', 'NAME' => GetMessage('TITLE_PRICE')]);
$arResult['INCLUDE_PRICE'] = ob_get_contents();
ob_end_clean();

// ask question text
ob_start();
$APPLICATION->IncludeFile($arResult['INCLUDE_FOLDER_PATH'].'/index_ask.php', [], ['MODE' => 'html', 'NAME' => GetMessage('TITLE_ASK')]);
$arResult['INCLUDE_ASK'] = ob_get_contents();
ob_end_clean();

$arResult['CHARACTERISTICS'] = $arResult['VIDEO'] = $arResult['VIDEO_IFRAME'] = $arResult['POPUP_VIDEO'] = $arResult['TIZERS'] = [];
$arResult['GALLERY_SIZE'] = $arParams['GALLERY_SIZE'];

/* docs property code */
$docsProp = $arParams['DETAIL_DOCS_PROP'] ? $arParams['DETAIL_DOCS_PROP'] : 'DOCUMENTS';

// get display properties
$arDetailPageShowProps = Bitrix\Iblock\Model\PropertyFeature::getDetailPageShowPropertyCodes($arParams['IBLOCK_ID'], ['CODE' => 'Y']);

if ($arDetailPageShowProps === null) {
    $arDetailPageShowProps = [];
}
if ($arResult['SECTION']) {
    $arSectionSelect = [
        'UF_SECTION_TIZERS',
        'UF_TABLE_SIZES',
        'UF_HELP_TEXT',
        'UF_VIDEO',
        'UF_VIDEO_IFRAME',
    ];

    if (
        in_array($docsProp, $arParams['PROPERTY_CODE'])
        || in_array($docsProp, $arDetailPageShowProps)
    ) {
        $arSectionSelect[] = 'UF_FILES';
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

    if ($arInherite['UF_SECTION_TIZERS']) {
        $arResult['TIZERS'] = $arInherite['UF_SECTION_TIZERS'];
    }

    if ($arInherite['UF_HELP_TEXT']) {
        $arResult['INCLUDE_CONTENT'] = $arInherite['UF_HELP_TEXT'];
    }

    if ($arInherite['UF_POPUP_VIDEO']) {
        $arResult['POPUP_VIDEO'] = $arInherite['UF_POPUP_VIDEO'];
    }

    if ($arInherite['UF_FILES']) {
        $arResult['DOCUMENTS'] = $arInherite['UF_FILES'];
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

$arCrossLinkedProps = [
    'LINK_SALE',
    'LINK_ARTICLES',
    'SERVICES',
    'LINK_FAQ',
];

foreach ($arCrossLinkedProps as $prop) {
    if (
        (in_array($prop, $arDetailPageShowProps) || in_array($prop, $arParams))
        && !isset($arResult['DISPLAY_PROPERTIES'][$prop])
    ) {
        $arResult['DISPLAY_PROPERTIES'][$prop] = true;
    }
}

if ($arResult['DISPLAY_PROPERTIES']['LINK_TIZERS']['VALUE']) {
    $arResult['TIZERS'] = $arResult['DISPLAY_PROPERTIES']['LINK_TIZERS']['VALUE'];
}

if ($arResult['PROPERTIES']['HELP_TEXT']['~VALUE']) {
    $arResult['INCLUDE_CONTENT'] = $arResult['PROPERTIES']['HELP_TEXT']['~VALUE'];
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

    if ($arResult['DISPLAY_PROPERTIES'][$docsProp]['VALUE']) {
        $arResult['DOCUMENTS'] = array_values($arResult['DISPLAY_PROPERTIES'][$docsProp]['VALUE']);
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
            } elseif ($arProp['CODE'] === 'VIDEO_YOUTUBE') {
                $arProp['VIDEO_FRAMES'] = TSolution\Video\Iframe::getVideoBlock($arProp['~VALUE']);
                if ($arProp['VIDEO_FRAMES']) {
                    $arResult['VIDEO'] = array_merge($arResult['VIDEO'], $arProp['VIDEO_FRAMES']);
                }
            } elseif ($arProp['CODE'] === 'POPUP_VIDEO') {
                $arResult['POPUP_VIDEO'] = $arProp['VALUE'];
            }
        }
    }
}

/* video block */
$videoProps = ['VIDEO_YOUTUBE', 'VIDEO_FILE'];
$arResult['VIDEO'] = TSolution\Functions::getFilesFromProperties($arResult['PROPERTIES'], $videoProps);

if ($arInherite['UF_VIDEO']) {
    $arResult['VIDEO']['VIDEO_FILE'] = array_merge((array) $arResult['VIDEO']['VIDEO_FILE'], array_map(fn ($arVideoFile) => unserialize($arVideoFile), $arInherite['UF_VIDEO']));
}

if ($arInherite['UF_VIDEO_IFRAME']) {
    $arResult['VIDEO']['VIDEO_YOUTUBE'] = array_merge((array) $arResult['VIDEO']['VIDEO_YOUTUBE'], $arInherite['UF_VIDEO_IFRAME']);
}
