<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}
use Bitrix\Main\Loader;
use Bitrix\Catalog\PriceTable; // Добавьте этот use

/* получить все свойства раздела элемента, которые отображаются в умном фильтре */
\Bitrix\Main\Loader::includeModule('iblock');

$iblockId = $arParams['IBLOCK_ID'];

// Карточки импорта Mouser/Getchips (ИБ 11): в шаблоне показывать базовую цену с приставкой «от:»
if ((int) ($arResult['IBLOCK_ID'] ?? 0) === 11) {
    $xml = (string) ($arResult['XML_ID'] ?? $arResult['~XML_ID'] ?? '');
    if ($xml !== '' && (strncasecmp($xml, 'MOUSER_', 7) === 0 || strncasecmp($xml, 'GETCHIPS_PN_', 12) === 0)) {
        $arResult['LVT_SHOW_PRICE_FROM'] = true;
    }
}

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
            'CATALOG_GROUP_ID' => $price['CATALOG_GROUP_ID'],
            'PRICE' => $price['PRICE'],
            'CURRENCY' => $price['CURRENCY'],
            'QUANTITY_FROM' => $price['QUANTITY_FROM'],
            'QUANTITY_TO' => $price['QUANTITY_TO']
        ];
    }
    
    // ДОБАВЛЕНО: Получаем информацию о складах с остатками
    $storeData = [];
    $productId = $arResult['ID'];
    
    // Получаем остатки по складам
    $storeProducts = \Bitrix\Catalog\StoreProductTable::getList([
        'filter' => [
            '=PRODUCT_ID' => $productId,
            '>AMOUNT' => 0
        ],
        'select' => ['ID', 'STORE_ID', 'AMOUNT', 'PRODUCT_ID']
    ]);
    
    while ($storeProduct = $storeProducts->fetch()) {
        // Получаем информацию о складе через новое API
        $store = \Bitrix\Catalog\StoreTable::getList([
            'filter' => ['ID' => $storeProduct['STORE_ID']],
            'select' => ['ID', 'TITLE', 'ADDRESS', 'UF_SROK_DOST', 'ACTIVE', 'SORT']
        ])->fetch();
        
        if ($store && $store['ACTIVE'] == 'Y') {
            // Исключаем склады Digi-key и Mouser Electronics
            if (!in_array($store['ID'], [4, 5]) && 
                stripos($store['TITLE'], 'digi-key') === false && 
                stripos($store['TITLE'], 'mouser') === false) {
                
                // ДОБАВЛЯЕМ ПОЛЕ СРОКА ПОСТАВКИ - получаем реальное значение
                $storeData[] = [
                    'ID' => $store['ID'],
                    'NAME' => $store['TITLE'],
                    'ADDRESS' => $store['ADDRESS'],
                    'QUANTITY' => $storeProduct['AMOUNT'],
                    'DELIVERY_TIME' => $store['UF_SROK_DOST'] ?: '4-5 недель', // Берем реальное значение из UF_SROK_DOST
                    'PRICE' => $arResult['PRICES']['BASE']['VALUE'] ?? 0,
                    'SORT' => (int)($store['SORT'] ?? 500)
                ];
            }
        }
    }
    
    // Сортировка складов по полю Сорт. из админки (как в скриншоте 2)
    usort($storeData, function($a, $b) {
        return ($a['SORT'] ?? 500) - ($b['SORT'] ?? 500);
    });
    
    // Сохраняем данные о складах в глобальной переменной для использования в шаблоне
    $GLOBALS['STORE_DATA_FOR_PRODUCT'] = $storeData;
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

    // Перезагружаем цены и склады для текущего оффера (SKU) — цены в Bitrix привязаны к офферу
    if (Loader::includeModule('catalog') && Loader::includeModule('sale')) {
        $priceTypes = \Bitrix\Catalog\GroupTable::getList(['select' => ['ID', 'NAME']])->fetchAll();
        $priceProductId = isset($arResult['SKU']['CURRENT']['ID']) ? $arResult['SKU']['CURRENT']['ID'] : $arResult['ID'];
        if ($priceProductId != $arResult['ID'] || empty($arResult['EXTENDED_PRICES'])) {
            $prices = \Bitrix\Catalog\PriceTable::getList([
            'filter' => ['PRODUCT_ID' => $priceProductId],
            'order' => ['CATALOG_GROUP_ID' => 'ASC', 'QUANTITY_FROM' => 'ASC']
        ])->fetchAll();
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
                'CATALOG_GROUP_ID' => $price['CATALOG_GROUP_ID'],
                'PRICE' => $price['PRICE'],
                'CURRENCY' => $price['CURRENCY'],
                'QUANTITY_FROM' => $price['QUANTITY_FROM'],
                'QUANTITY_TO' => $price['QUANTITY_TO']
            ];
        }
        $storeProducts = \Bitrix\Catalog\StoreProductTable::getList([
            'filter' => ['=PRODUCT_ID' => $priceProductId, '>AMOUNT' => 0],
            'select' => ['ID', 'STORE_ID', 'AMOUNT', 'PRODUCT_ID']
        ]);
        $storeData = [];
        while ($storeProduct = $storeProducts->fetch()) {
            $store = \Bitrix\Catalog\StoreTable::getList([
                'filter' => ['ID' => $storeProduct['STORE_ID']],
                'select' => ['ID', 'TITLE', 'ADDRESS', 'UF_SROK_DOST', 'ACTIVE', 'SORT']
            ])->fetch();
            if ($store && $store['ACTIVE'] == 'Y' && !in_array($store['ID'], [4, 5]) && 
                stripos($store['TITLE'], 'digi-key') === false && stripos($store['TITLE'], 'mouser') === false) {
                $storeData[] = [
                    'ID' => $store['ID'],
                    'NAME' => $store['TITLE'],
                    'ADDRESS' => $store['ADDRESS'],
                    'QUANTITY' => $storeProduct['AMOUNT'],
                    'DELIVERY_TIME' => $store['UF_SROK_DOST'] ?: '4-5 недель',
                    'PRICE' => $arResult['PRICES']['BASE']['VALUE'] ?? 0,
                    'SORT' => (int)($store['SORT'] ?? 500)
                ];
            }
        }
        // Сортировка складов по полю Сорт. из админки (как в скриншоте 2)
        usort($storeData, function($a, $b) {
            return ($a['SORT'] ?? 500) - ($b['SORT'] ?? 500);
        });
        $GLOBALS['STORE_DATA_FOR_PRODUCT'] = $storeData;
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

// Client-side Mouser image probe config: page renders immediately,
// then browser asks API to check/fetch image once per product.
$lvtIsBxAjaxRequest = !empty($_REQUEST['bxajaxid'])
    || (isset($_SERVER['REQUEST_URI']) && stripos((string)$_SERVER['REQUEST_URI'], 'bxajaxid=') !== false)
    || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && mb_strtolower((string)$_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');

$arResult['LVT_MOUSER_IMAGE_PROBE'] = [
    'ENABLED' => false,
    'IBLOCK_ID' => (int)($arResult['IBLOCK_ID'] ?? 0),
    'ELEMENT_ID' => (int)($arResult['ID'] ?? 0),
    'PART_NUMBER' => '',
];

if ((int)($arResult['IBLOCK_ID'] ?? 0) === 11 && !$lvtIsBxAjaxRequest) {
    $normalizeCandidate = static function (string $value): string {
        $value = trim(urldecode($value));
        if ($value === '') {
            return '';
        }
        $value = preg_replace('/-[a-f0-9]{5,}$/i', '', $value) ?? $value;
        $value = preg_replace('/\s+/', '', $value) ?? $value;
        $value = trim((string)$value, " \t\n\r\0\x0B-_");
        if ($value === '') {
            return '';
        }

        return trim((string)(preg_replace('/[^A-Za-z0-9\.\-_\+\/]/', '', $value) ?? ''));
    };

    $requestUri = (string)($_SERVER['REQUEST_URI'] ?? '');
    $requestPath = (string)parse_url($requestUri, PHP_URL_PATH);
    $requestTail = trim((string)basename($requestPath), "/ \t\n\r\0\x0B");

    $partNumber = '';
    $candidateMap = [
        (string)($arResult['PROPERTIES']['MOUSER_PART_NUMBER']['VALUE'] ?? ''),
        (string)($arResult['PROPERTIES']['artikul_proizvoditelya']['VALUE'] ?? ''),
        (string)($arResult['PROPERTIES']['CML2_ARTICLE']['VALUE'] ?? ''),
        (string)($arResult['DISPLAY_PROPERTIES']['CML2_ARTICLE']['VALUE'] ?? ''),
        (string)($arResult['CODE'] ?? ''),
        $requestTail,
    ];
    foreach ($candidateMap as $rawValue) {
        $normalized = $normalizeCandidate((string)$rawValue);
        if ($normalized !== '' && strlen($normalized) >= 5) {
            $partNumber = $normalized;
            break;
        }
    }

    $arResult['LVT_MOUSER_IMAGE_PROBE']['ENABLED'] = true;
    $arResult['LVT_MOUSER_IMAGE_PROBE']['PART_NUMBER'] = $partNumber;
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

if ((int) ($arResult['IBLOCK_ID'] ?? 0) === 41) {
    $galleryHelper = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/LvtIb41ProductGallery.php';
    if (is_file($galleryHelper)) {
        require_once $galleryHelper;
        $liveVideoProp = $arResult['PROPERTIES']['ETM_VIDEO_URLS']['VALUE'] ?? [];
        if (!is_array($liveVideoProp)) {
            $liveVideoProp = [$liveVideoProp];
        }
        $liveVideoProp = array_values(array_filter(array_map('trim', array_map('strval', $liveVideoProp))));
        if ($liveVideoProp === []) {
            $liveHelperFile = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/LvtEtmCatalogLiveHelper.php';
            if (is_file($liveHelperFile)) {
                require_once $liveHelperFile;
                $etmCodeLive = LvtEtmCatalogLiveHelper::resolveEtmCode($arResult);
                if ($etmCodeLive !== '') {
                    $liveVideoUrls = LvtEtmCatalogLiveHelper::fetchGoodsVideoUrls($etmCodeLive);
                    if ($liveVideoUrls !== []) {
                        $liveVideoMarkup = [];
                        foreach ($liveVideoUrls as $videoUrl) {
                            $src = htmlspecialchars($videoUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                            $liveVideoMarkup[] = '<div class="video_from_file ui-card__image"><video class="video-js" preload="metadata" controls="controls"><source src="' . $src . '" type="video/mp4"></video></div>';
                        }
                        $arResult['PROPERTIES']['ETM_VIDEO_URLS']['VALUE'] = $liveVideoMarkup;
                    }
                }
            }
        }
        $arResult['GALLERY'] = LvtIb41ProductGallery::mergeGallery((array) $arResult['GALLERY'], $arResult);
        $arResult['GALLERY_HAS_VIDEO'] = LvtIb41ProductGallery::galleryHasVideo((array) $arResult['GALLERY']);
    }
}

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
            $arBrand['IMAGE'] = CFile::ResizeImageGet($picture, ['width' => 200, 'height' => 100], BX_RESIZE_IMAGE_PROPORTIONAL, true);
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

if (
    (int) ($arResult['IBLOCK_ID'] ?? 0) === 11
    && $docsProp === 'INSTRUCTIONS'
    && empty($arResult['DOCUMENTS'])
    && !empty($arResult['PROPERTIES']['INSTRUCTIONS'])
    && is_array($arResult['PROPERTIES']['INSTRUCTIONS'])
) {
    if (empty($arResult['DISPLAY_PROPERTIES']['INSTRUCTIONS'])) {
        $arResult['DISPLAY_PROPERTIES']['INSTRUCTIONS'] = $arResult['PROPERTIES']['INSTRUCTIONS'];
    }
    $rawIns = $arResult['DISPLAY_PROPERTIES']['INSTRUCTIONS']['VALUE'] ?? [];
    if (!is_array($rawIns)) {
        $rawIns = [$rawIns];
    }
    $clean = [];
    foreach ($rawIns as $v) {
        $id = (int) $v;
        if ($id > 0) {
            $clean[] = $id;
        }
    }
    if ($clean !== []) {
        $arResult['DISPLAY_PROPERTIES']['INSTRUCTIONS']['VALUE'] = array_values(array_unique($clean));
        $arResult['DOCUMENTS'] = $arResult['DISPLAY_PROPERTIES']['INSTRUCTIONS']['VALUE'];
    }
}

// #region agent log
if ((int) ($arResult['IBLOCK_ID'] ?? 0) === 11 && strncasecmp((string) ($arResult['XML_ID'] ?? $arResult['~XML_ID'] ?? ''), 'MOUSER_', 7) === 0) {
    $insProp = $arResult['PROPERTIES']['INSTRUCTIONS']['VALUE'] ?? [];
    if (!is_array($insProp)) {
        $insProp = [$insProp];
    }
    $insDisplay = $arResult['DISPLAY_PROPERTIES']['INSTRUCTIONS']['VALUE'] ?? [];
    if (!is_array($insDisplay)) {
        $insDisplay = [$insDisplay];
    }
    @file_put_contents(
        '/var/www/www-root/data/www/lvtgroup.ru/cursor/.cursor/debug-37eb08.log',
        json_encode([
            'sessionId' => '37eb08',
            'runId' => 'mouser-specs',
            'hypothesisId' => 'J',
            'location' => 'catalog.element/main/result_modifier.php',
            'message' => 'instructions_visibility_pipeline',
            'data' => [
                'elementId' => (int) ($arResult['ID'] ?? 0),
                'docsProp' => (string) $docsProp,
                'propInstructionsCount' => count(array_filter($insProp, static fn ($x) => (int) $x > 0)),
                'displayInstructionsCount' => count(array_filter($insDisplay, static fn ($x) => (int) $x > 0)),
                'documentsCount' => is_array($arResult['DOCUMENTS'] ?? null) ? count($arResult['DOCUMENTS']) : 0,
            ],
            'timestamp' => (int) round(microtime(true) * 1000),
        ], JSON_UNESCAPED_UNICODE) . "\n",
        FILE_APPEND | LOCK_EX
    );
}
// #endregion

if ((int) ($arParams['IBLOCK_ID'] ?? 0) === 41) {
    $ib41PropsPath = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/LvtIb41CatalogElementProps.php';
    if (is_file($ib41PropsPath)) {
        require_once $ib41PropsPath;
        LvtIb41CatalogElementProps::mergeIntoDisplayProperties($arResult, $arParams);
    }

    $arResult['SMART_FILTER_MAP'] = [];
    $filterPropertyCodes = (array) ($arParams['FILTER_PROPERTY_CODE'] ?? []);
    foreach ($filterPropertyCodes as $filterCode) {
        $filterCode = trim((string) $filterCode);
        if ($filterCode === '' || !isset($arResult['PROPERTIES'][$filterCode])) {
            continue;
        }
        $propertyId = (int) ($arResult['PROPERTIES'][$filterCode]['ID'] ?? 0);
        if ($propertyId > 0) {
            $arResult['SMART_FILTER_MAP'][$filterCode] = $propertyId;
        }
    }

    if (!empty($arResult['DISPLAY_PROPERTIES'])) {
        foreach ($arResult['DISPLAY_PROPERTIES'] as $displayCode => $displayProperty) {
            $displayCode = (string) $displayCode;
            $propertyId = (int) ($displayProperty['ID'] ?? 0);
            if ($displayCode === '' || $propertyId <= 0) {
                continue;
            }
            if (!array_key_exists($displayCode, $arResult['SMART_FILTER_MAP'])) {
                $arResult['SMART_FILTER_MAP'][$displayCode] = $propertyId;
            }
        }
    }
}

if ($arResult['DISPLAY_PROPERTIES']) {
    if (is_array($arResult['PROPERTIES']['CML2_ARTICLE']['VALUE']) && $arResult['DISPLAY_PROPERTIES']['CML2_ARTICLE']) {
        $arResult['DISPLAY_PROPERTIES']['CML2_ARTICLE']['VALUE'] = reset($arResult['DISPLAY_PROPERTIES']['CML2_ARTICLE']['VALUE']);
    }

    if (
        (int) ($arResult['IBLOCK_ID'] ?? 0) === 11
        && empty($arResult['DISPLAY_PROPERTIES']['PDF2'])
        && !empty($arResult['PROPERTIES']['PDF2'])
        && is_array($arResult['PROPERTIES']['PDF2'])
    ) {
        $arResult['DISPLAY_PROPERTIES']['PDF2'] = $arResult['PROPERTIES']['PDF2'];
    }

    if ((int) ($arResult['IBLOCK_ID'] ?? 0) === 11 && !empty($arResult['DISPLAY_PROPERTIES']['PDF2']) && is_array($arResult['DISPLAY_PROPERTIES']['PDF2'])) {
        $pdfProp = &$arResult['DISPLAY_PROPERTIES']['PDF2'];
        $raw = $pdfProp['VALUE'] ?? '';
        $url = is_array($raw) ? trim((string) reset($raw)) : trim((string) $raw);
        if ($url !== '' && preg_match('#^https?://#i', $url)) {
            $safe = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $link = '<a class="lvt-pdf2-datasheet" href="' . $safe . '" target="_blank" rel="noopener noreferrer">Скачать</a>';
            $pdfProp['DISPLAY_VALUE'] = $link;
            $pdfProp['~DISPLAY_VALUE'] = $link;
        }
        unset($pdfProp);
    }

    $arResult['CHARACTERISTICS'] = TSolution::PrepareItemProps($arResult['DISPLAY_PROPERTIES']);

    if ((int) ($arResult['IBLOCK_ID'] ?? 0) === 41) {
        $manufacturerValue = trim((string)($arResult['PROPERTIES']['PROIZVODITEL']['VALUE'] ?? ''));
        if ($manufacturerValue === '') {
            $manufacturerValue = trim((string)($arResult['PROPERTIES']['BRAND']['VALUE'] ?? ''));
        }
        if ($manufacturerValue !== '' && empty($arResult['CHARACTERISTICS']['PROIZVODITEL'])) {
            $arResult['CHARACTERISTICS'] = ['PROIZVODITEL' => [
                'ID' => (int)($arResult['PROPERTIES']['PROIZVODITEL']['ID'] ?? 0),
                'CODE' => 'PROIZVODITEL',
                'NAME' => 'Производитель',
                'VALUE' => $manufacturerValue,
                'DISPLAY_VALUE' => $manufacturerValue,
                '~VALUE' => $manufacturerValue,
                '~DISPLAY_VALUE' => $manufacturerValue,
            ]] + (array)$arResult['CHARACTERISTICS'];
        }
    }

    // hide property "Документы (ссылки)" from characteristics
    foreach ((array)$arResult['CHARACTERISTICS'] as $cKey => $cVal) {
        $cName = mb_strtolower(trim((string)($cVal['NAME'] ?? '')));
        $cCode = mb_strtolower(trim((string)($cVal['CODE'] ?? $cKey)));
        $cId = (int)($cVal['ID'] ?? 0);
        if (
            $cId === 1219
            || $cCode === 'documents'
            || $cCode === 'instructions_links'
            || strpos($cName, 'документы (ссылки)') !== false
        ) {
            unset($arResult['CHARACTERISTICS'][$cKey]);
        }
    }

    // IBLOCK 11 (Mouser): не показывать служебные PDF-свойства в таблице характеристик
    if ((int)($arResult['IBLOCK_ID'] ?? 0) === 11) {
        foreach ((array)$arResult['CHARACTERISTICS'] as $cKey => $cVal) {
            $cCode = mb_strtoupper(trim((string)($cVal['CODE'] ?? $cKey)));
            $cName = mb_strtolower(trim((string)($cVal['NAME'] ?? '')));
            if (
                in_array($cCode, ['PDF', 'PDF2'], true)
                || strpos($cName, 'документация (pdf') !== false
            ) {
                unset($arResult['CHARACTERISTICS'][$cKey]);
            }
        }
        unset(
            $arResult['DISPLAY_PROPERTIES']['PDF'],
            $arResult['DISPLAY_PROPERTIES']['PDF2']
        );
    }

    // show only property #500 INSTRUCTIONS (deduplicated)
    $instructionSource = null;
    if (!empty($arResult['PROPERTIES']['INSTRUCTIONS']) && is_array($arResult['PROPERTIES']['INSTRUCTIONS'])) {
        $instructionSource = $arResult['PROPERTIES']['INSTRUCTIONS'];
    } elseif (!empty($arResult['DISPLAY_PROPERTIES']['INSTRUCTIONS']) && is_array($arResult['DISPLAY_PROPERTIES']['INSTRUCTIONS'])) {
        $instructionSource = $arResult['DISPLAY_PROPERTIES']['INSTRUCTIONS'];
    }

    if ($instructionSource) {
        $rawValues = $instructionSource['VALUE'] ?? '';
        if (!is_array($rawValues)) {
            $rawValues = [$rawValues];
        }
        $allUrls = [];
        foreach ($rawValues as $raw) {
            $raw = (string)$raw;
            if ($raw === '') {
                continue;
            }
            if (preg_match_all('#https?://[^\s"<>\']+#iu', $raw, $m) && !empty($m[0])) {
                foreach ($m[0] as $u) {
                    $allUrls[] = trim((string)$u);
                }
            } else {
                $allUrls[] = trim(strip_tags($raw));
            }
        }
        $allUrls = array_values(array_filter($allUrls, static function ($u) {
            return (string)$u !== '';
        }));
        $uniqueUrls = array_values(array_unique($allUrls));
        $instructionValue = implode(', ', $uniqueUrls);

        $arResult['CHARACTERISTICS']['INSTRUCTIONS'] = [
            'ID' => 500,
            'CODE' => 'INSTRUCTIONS',
            'NAME' => (string)($instructionSource['NAME'] ?? 'INSTRUCTIONS'),
            'VALUE' => $instructionValue,
            'DISPLAY_VALUE' => $instructionValue,
            '~VALUE' => $instructionValue,
            '~DISPLAY_VALUE' => $instructionValue,
        ];
    }

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

// IB41: видео только в галерее (ETM_VIDEO_URLS), не дублируем в блок «Видео» внизу карточки.
?>