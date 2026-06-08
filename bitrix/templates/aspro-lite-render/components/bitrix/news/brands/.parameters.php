<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}

use Aspro\Lite\Functions\ExtComponentParameter;
use Bitrix\Iblock;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

if (!Loader::includeModule('iblock')) {
    return;
}

$arSKU = $boolSKU = false;
$arPropertySort = $arPropertySortDefault = $arPropertyDefaultSort = [];
$arPrice = $arProperty = $arProperty_N = $arProperty_X = $arProperty_F = [];
$arSort = CIBlockParameters::GetElementSortFields(
    ['SHOWS', 'SORT', 'TIMESTAMP_X', 'NAME', 'ID', 'ACTIVE_FROM', 'ACTIVE_TO'],
    ['KEY_LOWERCASE' => 'Y']
);
$arPropertySortDefault = ['SORT', 'SHOWS', 'NAME'];
$arPropertySort = [
    'SORT' => GetMessage('SORT_BUTTONS_SORT'),
    'SHOWS' => GetMessage('SORT_BUTTONS_POPULARITY'),
    'NAME' => GetMessage('SORT_BUTTONS_NAME'),
    // "CUSTOM"=>GetMessage("SORT_BUTTONS_CUSTOM")
];

if (Loader::includeModule('catalog')) {
    $arSort = array_merge($arSort, CCatalogIBlockParameters::GetCatalogSortFields(), ['PROPERTY_MINIMUM_PRICE' => GetMessage('SORT_PRICES_MINIMUM_PRICE'), 'PROPERTY_MAXIMUM_PRICE' => GetMessage('SORT_PRICES_MAXIMUM_PRICE'), 'REGION_PRICE' => GetMessage('SORT_PRICES_REGION_PRICE')]);
    if (isset($arSort['CATALOG_AVAILABLE'])) {
        unset($arSort['CATALOG_AVAILABLE']);
    }

    $rsPrice = CCatalogGroup::GetList($v1 = 'sort', $v2 = 'asc');
    while ($arr = $rsPrice->Fetch()) {
        $arPrice[$arr['NAME']] = '['.$arr['NAME'].'] '.$arr['NAME_LANG'];
    }
    if ((isset($arCurrentValues['IBLOCK_ID']) && (int) $arCurrentValues['IBLOCK_ID']) > 0) {
        $arSKU = CCatalogSKU::GetInfoByProductIBlock($arCurrentValues['IBLOCK_ID']);
        $boolSKU = !empty($arSKU) && is_array($arSKU);
    }
    $arPropertySortDefault = array_merge($arPropertySortDefault, ['PRICES', 'QUANTITY']);
    $arPropertySort = array_merge($arPropertySort, [
        'PRICES' => GetMessage('SORT_BUTTONS_PRICE'),
        'QUANTITY' => GetMessage('SORT_BUTTONS_QUANTITY'),
    ]);
} else {
    $arPrice = $arProperty_N;
}

$propertyIterator = Iblock\PropertyTable::getList([
    'select' => ['ID', 'IBLOCK_ID', 'NAME', 'CODE', 'PROPERTY_TYPE', 'MULTIPLE', 'LINK_IBLOCK_ID', 'USER_TYPE', 'SORT'],
    'filter' => [
        '=IBLOCK_ID' => $arCurrentValues['LINK_GOODS_IBLOCK_ID'],
        '=ACTIVE' => 'Y',
    ],
    'order' => [
        'SORT' => 'ASC',
        'NAME' => 'ASC',
    ],
]);
while ($property = $propertyIterator->fetch()) {
    $propertyCode = (string) $property['CODE'];

    if ('' == $propertyCode) {
        $propertyCode = $property['ID'];
    }

    $propertyName = '['.$propertyCode.'] '.$property['NAME'];
    $arPropertySort[$propertyCode] = $propertyName;

    if (Iblock\PropertyTable::TYPE_FILE != $property['PROPERTY_TYPE']) {
        $arProperty[$propertyCode] = $propertyName;

        if ('Y' == $property['MULTIPLE']) {
            $arProperty_X[$propertyCode] = $propertyName;
        } elseif (Iblock\PropertyTable::TYPE_LIST == $property['PROPERTY_TYPE']) {
            $arProperty_X[$propertyCode] = $propertyName;
        } elseif (Iblock\PropertyTable::TYPE_ELEMENT == $property['PROPERTY_TYPE'] && (int) $property['LINK_IBLOCK_ID'] > 0) {
            $arProperty_X[$propertyCode] = $propertyName;
        }
    } else {
        $arProperty_F[$propertyCode] = $propertyName;
    }

    if (Iblock\PropertyTable::TYPE_NUMBER == $property['PROPERTY_TYPE']) {
        $arProperty_N[$propertyCode] = $propertyName;
    }

    if (Iblock\PropertyTable::TYPE_STRING == $property['PROPERTY_TYPE']) {
        $arProperty_S[$propertyCode] = $propertyName;
    }
}

unset($propertyCode, $propertyName, $property, $propertyIterator);

if ($arCurrentValues['SORT_PROP']) {
    foreach ($arCurrentValues['SORT_PROP'] as $code) {
        $arPropertyDefaultSort[$code] = $arPropertySort[$code];
    }
} else {
    foreach ($arPropertySortDefault as $code) {
        $arPropertyDefaultSort[$code] = $arPropertySort[$code];
    }
}

$arIBlocks = [];
$rsIBlock = CIBlock::GetList(
    [
        'ID' => 'ASC',
    ],
    [
        // 'TYPE' => $arCurrentValues['IBLOCK_TYPE'],
        'ACTIVE' => 'Y',
    ]
);
while ($arIBlock = $rsIBlock->Fetch()) {
    $arIBlocks[$arIBlock['ID']] = "[{$arIBlock['ID']}] {$arIBlock['NAME']}";
}

if ($arCurrentValues['SORT_PROP']) {
    foreach ($arCurrentValues['SORT_PROP'] as $code) {
        $arPropertyDefaultSort[$code] = $arPropertySort[$code];
    }
} else {
    foreach ($arPropertySortDefault as $code) {
        $arPropertyDefaultSort[$code] = $arPropertySort[$code];
    }
}

$arAscDesc = [
    'asc' => GetMessage('IBLOCK_SORT_ASC'),
    'desc' => GetMessage('IBLOCK_SORT_DESC'),
];

$arRegionPrice = $arPrice;
if (Loader::includeModule('catalog')) {
    $arPriceSort = array_merge(['MINIMUM_PRICE' => GetMessage('SORT_PRICES_MINIMUM_PRICE'), 'MAXIMUM_PRICE' => GetMessage('SORT_PRICES_MAXIMUM_PRICE'), 'REGION_PRICE' => GetMessage('SORT_PRICES_REGION_PRICE')], $arPrice);
}

ExtComponentParameter::init(__DIR__, $arCurrentValues);

ExtComponentParameter::addBaseParameters([
    [
        ['SECTION' => 'SECTION', 'OPTION' => 'BRANDS_PAGE'],
        'SECTION_ELEMENTS_TYPE_VIEW',
    ],
    [
        ['SECTION' => 'SECTION', 'OPTION' => 'BRANDS_DETAIL_PAGE'],
        'ELEMENT_TYPE_VIEW',
    ],
]);

ExtComponentParameter::addRelationBlockParameters([
    ExtComponentParameter::RELATION_BLOCK_DOCS,
    ExtComponentParameter::RELATION_BLOCK_LINK_GOODS,
    ExtComponentParameter::RELATION_BLOCK_LINK_SECTIONS,
    ExtComponentParameter::RELATION_BLOCK_COMMENTS,
]);

ExtComponentParameter::addTextParameter('DEPTH_LEVEL_BRAND', [
    'NAME' => GetMessage('T_DEPTH_LEVEL_BRAND'),
    'DEFAULT' => 2,
]);

ExtComponentParameter::addSelectParameter('SECTION_LIST_DISPLAY_TYPE', [
    'VALUES' => [
        3 => GetMessage('V_SECTION_LIST_DISPLAY_TYPE_BIG'),
        4 => GetMessage('V_SECTION_LIST_DISPLAY_TYPE_SMALL'),
    ],
    'NAME' => GetMessage('T_SECTION_LIST_DISPLAY_TYPE'),
    'DEFAULT' => 3,
]);

ExtComponentParameter::appendTo($arTemplateParameters);

$arTemplateParameters['SHOW_DETAIL_LINK'] = [
    'PARENT' => ExtComponentParameter::PARENT_GROUP_LIST,
    'NAME' => Loc::getMessage('SHOW_DETAIL_LINK'),
    'TYPE' => 'CHECKBOX',
    'DEFAULT' => 'Y',
];

$arTemplateParameters['USE_SHARE'] = [
    'PARENT' => ExtComponentParameter::PARENT_GROUP_LIST,
    'NAME' => Loc::getMessage('USE_SHARE'),
    'TYPE' => 'CHECKBOX',
    'DEFAULT' => 'Y',
];

$arTemplateParameters['SORT_PROP'] = [
    'PARENT' => ExtComponentParameter::PARENT_GROUP_DETAIL,
    'NAME' => GetMessage('T_SORT_PROP'),
    'TYPE' => 'LIST',
    'VALUES' => array_merge([/* "CUSTOM"=>GetMessage("SORT_BUTTONS_CUSTOM") */], $arPropertySort),
    'DEFAULT' => $arPropertySortDefault,
    'SIZE' => 5,
    'MULTIPLE' => 'Y',
    'REFRESH' => 'Y',
];

$arTemplateParameters['SORT_PROP_DEFAULT'] = [
    'PARENT' => ExtComponentParameter::PARENT_GROUP_DETAIL,
    'NAME' => GetMessage('T_SORT_PROP_DEFAULT'),
    'TYPE' => 'LIST',
    'VALUES' => $arPropertyDefaultSort,
];

$arTemplateParameters['SORT_DIRECTION'] = [
    'PARENT' => ExtComponentParameter::PARENT_GROUP_DETAIL,
    'NAME' => GetMessage('T_SORT_DIRECTION'),
    'TYPE' => 'LIST',
    'VALUES' => $arAscDesc,
];

if (is_array($arCurrentValues['SORT_PROP'])) {
    if (in_array('PRICES', $arCurrentValues['SORT_PROP'])) {
        $arTemplateParameters['SORT_PRICES'] = [
            'SORT' => 200,
            'NAME' => GetMessage('SORT_PRICES'),
            'TYPE' => 'LIST',
            'VALUES' => $arPriceSort,
            'DEFAULT' => ['MINIMUM_PRICE'],
            'PARENT' => 'DETAIL_SETTINGS',
            'MULTIPLE' => 'N',
        ];
        $arTemplateParameters['SORT_REGION_PRICE'] = [
            'SORT' => 200,
            'NAME' => GetMessage('SORT_REGION_PRICE'),
            'TYPE' => 'LIST',
            'VALUES' => $arRegionPrice,
            'DEFAULT' => ['BASE'],
            'PARENT' => 'DETAIL_SETTINGS',
            'MULTIPLE' => 'N',
        ];
    }
}

$arTemplateParameters['VIEW_TYPE'] = [
    'PARENT' => ExtComponentParameter::PARENT_GROUP_DETAIL,
    'NAME' => GetMessage('DEFAULT_LIST_TEMPLATE'),
    'TYPE' => 'LIST',
    'VALUES' => [
        'table' => GetMessage('DEFAULT_LIST_TEMPLATE_BLOCK'),
        'list' => GetMessage('DEFAULT_LIST_TEMPLATE_LIST'),
        'price' => GetMessage('DEFAULT_LIST_TEMPLATE_TABLE')],
    'DEFAULT' => 'table',
];

$arTemplateParameters['PRICE_CODE'] = [
    'PARENT' => 'DETAIL_SETTINGS',
    'NAME' => GetMessage('PRICE_CODE_TITLE'),
    'TYPE' => 'LIST',
    'MULTIPLE' => 'Y',
    'VALUES' => $arPrice,
    'ADDITIONAL_VALUES' => 'Y',
];

$arTemplateParameters['PRICE_VAT_INCLUDE'] = [
    'PARENT' => 'DETAIL_SETTINGS',
    'NAME' => GetMessage('IBLOCK_PRICE_VAT_INCLUDE'),
    'TYPE' => 'CHECKBOX',
    'REFRESH' => 'N',
    'DEFAULT' => 'Y',
];

/* check for custom option */
$siteID = SITE_ID;
if (isset($_REQUEST['src_site'])) {
    $siteID = $_REQUEST['src_site'];
}
$viewTemplate = $arCurrentValues['SECTION_ELEMENTS_TYPE_VIEW'];

if ('FROM_MODULE' === $viewTemplate) {
    if (isset($_SESSION)
        && isset($_SESSION['THEME'])
        && isset($_SESSION['THEME'][$siteID])
        && isset($_SESSION['THEME'][$siteID]['BRANDS_PAGE'])
    ) {
        $viewTemplate = $_SESSION['THEME'][$siteID]['BRANDS_PAGE'];
    } else {
        $viewTemplate = Bitrix\Main\Config\Option::get(CLite::moduleID, 'BRANDS_PAGE', '', $siteID);
    }
}
if (false !== strpos($viewTemplate, 'with_group')) {
    $arTemplateParameters['USE_AGENT'] = [
        'NAME' => GetMessage('T_USE_AGENT'),
        'TYPE' => 'CHECKBOX',
        'DEFAULT' => 'N',
        'PARENT' => 'LIST_SETTINGS',
    ];
}

$arTemplateParameters['USE_FILTER_PRICE'] = [
    'NAME' => GetMessage('USE_FILTER_PRICE_TITLE'),
    'TYPE' => 'CHECKBOX',
    'DEFAULT' => 'N',
    'REFRESH' => 'Y',
    'PARENT' => 'DETAIL_SETTINGS',
    'SORT' => 600,
];
$arTemplateParameters['FILTER_PRICE_CODE'] = [
    'PARENT' => 'DETAIL_SETTINGS',
    'NAME' => GetMessage('FILTER_PRICE_CODE_TITLE'),
    'TYPE' => 'LIST',
    'MULTIPLE' => 'Y',
    'VALUES' => $arPrice,
    'SORT' => 601,
    'HIDDEN' => (isset($arCurrentValues['USE_FILTER_PRICE']) && 'Y' == $arCurrentValues['USE_FILTER_PRICE'] ? 'N' : 'Y'),
];

$arTemplateParameters['USE_SMARTFILTER_ON_DETAIL_PAGE'] = [
    'NAME' => GetMessage('T_USE_SMARTFILTER_ON_DETAIL_PAGE'),
    'TYPE' => 'CHECKBOX',
    'DEFAULT' => 'Y',
    'REFRESH' => 'N',
    'PARENT' => 'DETAIL_SETTINGS',
    'SORT' => 602,
];
