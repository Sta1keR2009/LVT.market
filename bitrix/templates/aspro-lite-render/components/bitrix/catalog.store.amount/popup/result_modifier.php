<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}

use Bitrix\Main\Type\Collection;

if ($arParams['FIELDS']) {
    $arResult['EMPTY_FIELDS'] = true;

    foreach ($arParams['FIELDS'] as $key => $field) {
        if (!$field) {
            unset($arParams['FIELDS'][$key]);
        } elseif (!($field == 'PHONE' && isset($arParams['USE_STORE_PHONE']) && $arParams['USE_STORE_PHONE'] == 'Y')) {
            $arResult['EMPTY_FIELDS'] = false;
        }
    }
}

$arSelect = [
    'ADDRESS',
    'ELEMENT_ID',
    'EMAIL',
    'GPS_N',
    'GPS_S',
    'ID',
    'PRODUCT_AMOUNT',
    'SCHEDULE',
    'SORT',
    'TITLE',
    'UF_METRO',
    'UF_PHONES',
];

if ($arParams['SHOW_GENERAL_STORE_INFORMATION'] != 'Y') {
    $arStoresIDs = $arStoresByID = [];

    foreach ($arResult['STORES'] as $key => $arStore) {
        $arStoresIDs[] = $arStore['ID'];
        $arStoresByID[$arStore['ID']] = &$arResult['STORES'][$key];
    }

    if ($arStoresIDs) {
        $dbRes = CCatalogStore::GetList(
            [
                'TITLE' => 'ASC',
                'ID' => 'ASC',
            ],
            [
                'ACTIVE' => 'Y',
                'PRODUCT_ID' => $arParams['ELEMENT_ID'],
                'ID' => $arStoresIDs,
            ],
            false,
            false,
            $arSelect
        );
        while ($store = $dbRes->Fetch()) {
            if ($arStoresByID[$store['ID']]) {
                $arStore = &$arStoresByID[$store['ID']];

                $arStore['NUM_AMOUNT'] = $store['PRODUCT_AMOUNT'];
                $arStore['SORT'] = $store['SORT'];
                $arStore['TITLE'] = $store['TITLE'];
                $arStore['ADDRESS'] = $store['ADDRESS'];
                $arStore['EMAIL'] = (!$arResult['EMPTY_FIELDS'] && in_array('EMAIL', $arParams['FIELDS']) || $arResult['EMPTY_FIELDS'] ? $store['EMAIL'] : '');
                $arStore['SCHEDULE'] = (!$arResult['EMPTY_FIELDS'] && in_array('SCHEDULE', $arParams['FIELDS']) || $arResult['EMPTY_FIELDS'] ? $store['SCHEDULE'] : '');
                $arStore['GPS_N'] = $store['GPS_N'];
                $arStore['GPS_S'] = $store['GPS_S'];
                $arStore['METRO'] = TSolution::unserialize($store['UF_METRO']);
                $store['UF_PHONES'] = TSolution::unserialize($store['UF_PHONES']);
                $arStore['PHONE'] = $store['UF_PHONES'] ? array_unique(array_merge((array) $arStore['PHONE'], (array) $store['UF_PHONES'])) : $arStore['PHONE'];

                unset($arStore);
            }
        }
    }
} else {
    unset($arResult['STORES']);

    $item = CCatalogProduct::GetByID($arParams['ELEMENT_ID']);
    $arResult['STORES'][0]['NUM_AMOUNT'] = $arResult['STORES'][0]['AMOUNT'] = TSolution\Product\Quantity::getTotalCount([
        'ITEM' => [
            'ID' => $item['ID'],
            'PRODUCT' => [
                'TYPE' => $item['TYPE'],
            ]
        ],
        'PARAMS' => array_merge(['USE_REGION' => 'Y'], $arParams),
    ]);
}

$order = ($arParams['STORES_FILTER_ORDER'] == 'SORT_ASC' ? SORT_ASC : SORT_DESC);
if ($arParams['STORES_FILTER'] == 'TITLE') {
    Collection::sortByColumn($arResult['STORES'], [$arParams['STORES_FILTER'] => $order]);
} else {
    Collection::sortByColumn($arResult['STORES'], [$arParams['STORES_FILTER'] => [SORT_NUMERIC, $order], 'TITLE' => $order]);
}
