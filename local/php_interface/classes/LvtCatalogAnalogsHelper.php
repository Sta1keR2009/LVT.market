<?php

/**
 * Поиск и обогащение карточек аналогов для IB 11 (promelec) и IB 41 (element ID / kod_tovara_).
 */
class LvtCatalogAnalogsHelper
{
    private const IBLOCK_ETM = 41;

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getAnalogItems(
        int $iblockId,
        int $currentElementId,
        string $fullAnalogCodes,
        string $interchangeableCodes
    ): array {
        $allAnalogs = [];

        if ($fullAnalogCodes !== '') {
            $fullAnalogs = self::resolveAnalogs($iblockId, $currentElementId, $fullAnalogCodes, 'full');
            $allAnalogs = array_merge($allAnalogs, $fullAnalogs);
        }

        if ($interchangeableCodes !== '') {
            $interchangeableAnalogs = self::resolveAnalogs($iblockId, $currentElementId, $interchangeableCodes, 'interchangeable');
            $allAnalogs = self::mergeUniqueAnalogs($allAnalogs, $interchangeableAnalogs);
        }

        return $allAnalogs;
    }

    /**
     * @param array<int, array<string, mixed>> $existing
     * @param array<int, array<string, mixed>> $incoming
     * @return array<int, array<string, mixed>>
     */
    private static function mergeUniqueAnalogs(array $existing, array $incoming): array
    {
        $seenIds = [];
        foreach ($existing as $item) {
            $seenIds[(int) ($item['ID'] ?? 0)] = true;
        }

        foreach ($incoming as $item) {
            $id = (int) ($item['ID'] ?? 0);
            if ($id <= 0 || isset($seenIds[$id])) {
                continue;
            }
            $existing[] = $item;
            $seenIds[$id] = true;
        }

        return $existing;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function resolveAnalogs(int $iblockId, int $currentId, string $codes, string $type): array
    {
        $codesArray = self::parseCodesString($codes);
        if ($codesArray === []) {
            return [];
        }

        if ($iblockId === self::IBLOCK_ETM) {
            return self::resolveEtmAnalogs($iblockId, $currentId, $codesArray, $type);
        }

        return self::resolvePromelecAnalogs($iblockId, $currentId, $codesArray, $type);
    }

    /**
     * @param list<string> $codesArray
     * @return array<int, array<string, mixed>>
     */
    private static function resolvePromelecAnalogs(int $iblockId, int $currentId, array $codesArray, string $type): array
    {
        $filter = [
            'IBLOCK_ID' => $iblockId,
            'ACTIVE' => 'Y',
            '!ID' => $currentId,
            'PROPERTY_promelec' => $codesArray,
        ];

        return self::fetchAndEnrichItems($filter, $type, 10);
    }

    /**
     * @param list<string> $codesArray
     * @return array<int, array<string, mixed>>
     */
    private static function resolveEtmAnalogs(int $iblockId, int $currentId, array $codesArray, string $type): array
    {
        self::ensureEtmElementCodeHelper();

        $elementIds = [];
        foreach ($codesArray as $token) {
            $elementId = self::resolveEtmTokenToElementId($iblockId, $token);
            if ($elementId > 0 && $elementId !== $currentId) {
                $elementIds[] = $elementId;
            }
        }

        $elementIds = array_values(array_unique($elementIds));
        if ($elementIds === []) {
            return [];
        }

        $filter = [
            'IBLOCK_ID' => $iblockId,
            'ACTIVE' => 'Y',
            '!ID' => $currentId,
            'ID' => array_slice($elementIds, 0, 10),
        ];

        return self::fetchAndEnrichItems($filter, $type, 10);
    }

    private static function resolveEtmTokenToElementId(int $iblockId, string $token): int
    {
        $token = trim($token);
        if ($token === '') {
            return 0;
        }

        // IB 41: сначала kod_tovara_ (код товара ETM), затем ID элемента
        $byEtmCode = etmFindElementIdByEtmCode($iblockId, $token);
        if ($byEtmCode > 0) {
            return $byEtmCode;
        }

        if (ctype_digit($token)) {
            $id = (int) $token;
            $row = CIBlockElement::GetList(
                [],
                ['IBLOCK_ID' => $iblockId, 'ID' => $id, 'ACTIVE' => 'Y'],
                false,
                ['nTopCount' => 1],
                ['ID']
            )->Fetch();
            if ($row) {
                return (int) $row['ID'];
            }
        }

        return 0;
    }

    /**
     * @param array<string, mixed> $filter
     * @return array<int, array<string, mixed>>
     */
    private static function fetchAndEnrichItems(array $filter, string $type, int $limit): array
    {
        if (!CModule::IncludeModule('iblock')) {
            return [];
        }

        $select = [
            'ID',
            'NAME',
            'DETAIL_PAGE_URL',
            'PREVIEW_PICTURE',
            'DETAIL_PICTURE',
            'CATALOG_QUANTITY',
            'CATALOG_AVAILABLE',
            'PROPERTY_promelec',
            'PROPERTY_ARTICLE',
            'PROPERTY_MANUFACTURER',
            'PROPERTY_BRAND',
            'PROPERTY_PROIZVODITEL',
        ];

        $res = CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            $filter,
            false,
            ['nTopCount' => $limit],
            $select
        );

        $items = [];
        while ($obElement = $res->GetNextElement()) {
            $item = $obElement->GetFields();
            $props = $obElement->GetProperties();
            $items[] = self::enrichItem($item, $props, $type);
        }

        return $items;
    }

    /**
     * @param array<string, mixed> $item
     * @param array<string, mixed> $props
     * @return array<string, mixed>
     */
    private static function enrichItem(array $item, array $props, string $type): array
    {
        $imageSrc = '';
        if ($item['PREVIEW_PICTURE']) {
            $imageFile = CFile::GetFileArray($item['PREVIEW_PICTURE']);
            if ($imageFile && isset($imageFile['SRC'])) {
                $imageSrc = $imageFile['SRC'];
            }
        }
        if ($imageSrc === '' && $item['DETAIL_PICTURE']) {
            $imageFile = CFile::GetFileArray($item['DETAIL_PICTURE']);
            if ($imageFile && isset($imageFile['SRC'])) {
                $imageSrc = $imageFile['SRC'];
            }
        }
        if ($imageSrc === '' && $item['PREVIEW_PICTURE']) {
            $imageSrc = CFile::GetPath($item['PREVIEW_PICTURE']);
        }
        if ($imageSrc === '' && $item['DETAIL_PICTURE']) {
            $imageSrc = CFile::GetPath($item['DETAIL_PICTURE']);
        }
        $item['IMAGE_SRC'] = $imageSrc;

        if (\Bitrix\Main\Loader::includeModule('catalog')) {
            $priceInfo = CCatalogProduct::GetOptimalPrice($item['ID']);
            if ($priceInfo && isset($priceInfo['RESULT_PRICE'])) {
                $item['PRICE'] = $priceInfo['RESULT_PRICE']['DISCOUNT_PRICE'];
                $item['BASE_PRICE'] = $priceInfo['RESULT_PRICE']['BASE_PRICE'];
                $item['DISCOUNT'] = $item['BASE_PRICE'] - $item['PRICE'];
            }

            $storeList = [];
            $stores = CCatalogStore::GetList([], ['ACTIVE' => 'Y'], false, false, ['ID', 'TITLE']);
            while ($store = $stores->Fetch()) {
                $storeQuantity = CCatalogStoreProduct::GetList(
                    [],
                    ['PRODUCT_ID' => $item['ID'], 'STORE_ID' => $store['ID']],
                    false,
                    false,
                    ['AMOUNT']
                )->Fetch();

                if ($storeQuantity) {
                    $storeList[] = [
                        'NAME' => $store['TITLE'],
                        'QUANTITY' => $storeQuantity['AMOUNT'],
                    ];
                }
            }
            $item['STORE_QUANTITY'] = $storeList;

            $totalQuantity = 0;
            foreach ($storeList as $store) {
                $totalQuantity += (int) $store['QUANTITY'];
            }
            $item['TOTAL_QUANTITY'] = $totalQuantity;
        }

        $manufacturer = 'Не указан';
        if (isset($props['MANUFACTURER']['VALUE']) && $props['MANUFACTURER']['VALUE'] !== '') {
            $manufacturer = $props['MANUFACTURER']['VALUE'];
        } elseif (isset($props['PROIZVODITEL']['VALUE']) && $props['PROIZVODITEL']['VALUE'] !== '') {
            $manufacturer = $props['PROIZVODITEL']['VALUE'];
        } elseif (isset($props['BRAND']['VALUE']) && $props['BRAND']['VALUE'] !== '') {
            if (is_numeric($props['BRAND']['VALUE'])) {
                $brandElement = CIBlockElement::GetByID($props['BRAND']['VALUE'])->Fetch();
                if ($brandElement && !empty($brandElement['NAME'])) {
                    $manufacturer = $brandElement['NAME'];
                }
            } else {
                $manufacturer = $props['BRAND']['VALUE'];
            }
        }

        $item['MANUFACTURER'] = $manufacturer;
        $item['ANALOG_TYPE'] = $type;

        return $item;
    }

    /**
     * @return list<string>
     */
    private static function parseCodesString(string $codes): array
    {
        if ($codes === '') {
            return [];
        }

        $codesArray = array_map('trim', explode(',', $codes));
        $codesArray = array_filter($codesArray, static fn ($c) => $c !== '');

        return array_values($codesArray);
    }

    private static function ensureEtmElementCodeHelper(): void
    {
        $path = $_SERVER['DOCUMENT_ROOT'] . '/api_etm_ai/includes/etm_element_code.php';
        if (is_file($path) && !function_exists('etmFindElementIdByEtmCode')) {
            require_once $path;
        }
    }
}
