<?php

use Bitrix\Main\Loader;

/**
 * Режим «Поиск по партномеру»: локальный поиск по артикулу + выдача Getchips при отсутствии в каталоге.
 */
class GetchipsPartnumberSearchHelper
{
    public const MODE_PARAM = 'search_mode';
    public const MODE_VALUE = 'partnumber';
    public const COOKIE_NAME = 'lvt_search_mode';
    public const IMPORT_SECTION_CODE = 'getchips-import';
    public const IMPORT_XML_PREFIX = 'GETCHIPS_PN_';

    public static function isPartnumberMode(): bool
    {
        if (isset($_GET[self::MODE_PARAM])) {
            return (string) $_GET[self::MODE_PARAM] === self::MODE_VALUE;
        }
        if (isset($_POST[self::MODE_PARAM])) {
            return (string) $_POST[self::MODE_PARAM] === self::MODE_VALUE;
        }

        return (string) ($_COOKIE[self::COOKIE_NAME] ?? '') === self::MODE_VALUE;
    }

    /**
     * ID товаров каталога (родительский ИБ), совпавших по партномеру/названию.
     *
     * @return list<int>
     */
    public static function findLocalProductElementIds(int $catalogIblockId, string $query): array
    {
        $query = trim($query);
        if ($query === '' || $catalogIblockId <= 0) {
            return [];
        }
        if (!Loader::includeModule('iblock')) {
            return [];
        }

        require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/GetchipsCatalogOffersHelper.php';
        $norm = GetchipsCatalogOffersHelper::normalizeArticle($query);

        $ids = [];

        $filter = [
            'IBLOCK_ID' => $catalogIblockId,
            'ACTIVE' => 'Y',
            'CHECK_PERMISSIONS' => 'Y',
            'MIN_PERMISSION' => 'R',
                [
                    'LOGIC' => 'OR',
                    ['=NAME' => $query],
                    ['=NAME' => $norm],
                    ['%NAME' => $query],
                    ['PROPERTY_CML2_ARTICLE' => $query],
                    ['PROPERTY_CML2_ARTICLE' => $norm],
                    ['PROPERTY_pr_article' => $query],
                    ['PROPERTY_pr_article' => $norm],
                    ['PROPERTY_MOUSER_PART_NUMBER' => $query],
                    ['PROPERTY_MOUSER_PART_NUMBER' => $norm],
                ],
        ];

        $res = CIBlockElement::GetList(
            ['SORT' => 'ASC', 'ID' => 'ASC'],
            $filter,
            false,
            ['nTopCount' => 50],
            ['ID', 'IBLOCK_ID']
        );
        while ($row = $res->Fetch()) {
            $ids[(int) $row['ID']] = (int) $row['ID'];
        }

        if (Loader::includeModule('catalog')) {
            $sku = CCatalogSKU::GetInfoByProductIBlock($catalogIblockId);
            if (is_array($sku) && !empty($sku['IBLOCK_ID']) && !empty($sku['SKU_PROPERTY_ID'])) {
                $skuIblock = (int) $sku['IBLOCK_ID'];
                $linkProp = 'PROPERTY_' . $sku['SKU_PROPERTY_ID'];
                $skuFilter = [
                    'IBLOCK_ID' => $skuIblock,
                    'ACTIVE' => 'Y',
                    'CHECK_PERMISSIONS' => 'Y',
                    'MIN_PERMISSION' => 'R',
                    [
                        'LOGIC' => 'OR',
                        ['PROPERTY_CML2_ARTICLE' => $query],
                        ['PROPERTY_CML2_ARTICLE' => $norm],
                        ['PROPERTY_pr_article' => $query],
                        ['PROPERTY_pr_article' => $norm],
                        ['%=NAME' => $query],
                    ],
                ];
                $sres = CIBlockElement::GetList(
                    ['ID' => 'ASC'],
                    $skuFilter,
                    false,
                    ['nTopCount' => 50],
                    ['ID', $linkProp]
                );
                while ($srow = $sres->Fetch()) {
                    $pid = (int) ($srow[$linkProp . '_VALUE'] ?? 0);
                    if ($pid > 0) {
                        $ids[$pid] = $pid;
                    }
                }
            }
        }

        return array_values($ids);
    }

    public static function getElementDetailUrl(int $elementId): string
    {
        if ($elementId <= 0 || !Loader::includeModule('iblock')) {
            return '';
        }
        $res = CIBlockElement::GetList(
            [],
            ['ID' => $elementId, 'CHECK_PERMISSIONS' => 'Y', 'MIN_PERMISSION' => 'R'],
            false,
            false,
            ['ID', 'DETAIL_PAGE_URL']
        );
        if ($row = $res->GetNext()) {
            return trim((string) ($row['DETAIL_PAGE_URL'] ?? ''));
        }

        return '';
    }

    /**
     * Служебный раздел и карточка-заготовка для партномера из Getchips (канонический URL в каталоге).
     *
     * @return int 0 при ошибке
     */
    public static function upsertImportPlaceholderElement(int $catalogIblockId, string $article): int
    {
        $article = trim($article);
        if ($article === '' || $catalogIblockId <= 0 || !Loader::includeModule('iblock')) {
            return 0;
        }
        require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/GetchipsCatalogOffersHelper.php';
        $norm = GetchipsCatalogOffersHelper::normalizeArticle($article);
        if ($norm === '' || mb_strlen($norm) < 3) {
            return 0;
        }

        require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/MouserCatalogElementHelper.php';
        $mouserExisting = MouserCatalogElementHelper::findElementIdByPartnerQueries($catalogIblockId, $article, $norm);
        if ($mouserExisting > 0) {
            GetchipsCatalogOffersHelper::syncCatalogQuantityFromSupplierOffers($mouserExisting, $norm);
            require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/LvtSupplierBasePriceHelper.php';
            LvtSupplierBasePriceHelper::syncForProduct($mouserExisting, null, $norm);

            return $mouserExisting;
        }
        $mouserNew = MouserCatalogElementHelper::upsertFromMouserPartNumber($catalogIblockId, $article);
        // Второй вызов API Mouser только если нормализованный артикул отличается от ввода (иначе дубликат ~1 с).
        if ($mouserNew <= 0 && $norm !== $article) {
            $mouserNew = MouserCatalogElementHelper::upsertFromMouserPartNumber($catalogIblockId, $norm);
        }
        if ($mouserNew > 0) {
            GetchipsCatalogOffersHelper::syncCatalogQuantityFromSupplierOffers($mouserNew, $norm);

            return $mouserNew;
        }

        $xmlId = self::IMPORT_XML_PREFIX . md5($norm);
        $exist = CIBlockElement::GetList(
            [],
            ['IBLOCK_ID' => $catalogIblockId, 'XML_ID' => $xmlId],
            false,
            false,
            ['ID']
        );
        if ($ex = $exist->Fetch()) {
            $eid = (int) $ex['ID'];
            GetchipsCatalogOffersHelper::syncCatalogQuantityFromSupplierOffers($eid, $norm);
            require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/LvtSupplierBasePriceHelper.php';
            LvtSupplierBasePriceHelper::syncForProduct($eid, null, $norm);

            return $eid;
        }

        $sectionId = self::ensureImportSectionId($catalogIblockId);
        if ($sectionId <= 0) {
            return 0;
        }

        $code = \CUtil::translit($norm, 'en', ['replace_space' => '-', 'replace_other' => '-']);
        $code = preg_replace('/-+/', '-', (string) $code);
        $code = trim((string) $code, '-');
        if ($code === '') {
            $code = 'part-' . substr(md5($norm), 0, 10);
        }
        $code .= '-' . substr(md5($norm), 0, 6);

        $el = new CIBlockElement();
        $name = $norm;
        $fields = [
            'IBLOCK_ID' => $catalogIblockId,
            'IBLOCK_SECTION_ID' => $sectionId,
            'NAME' => $name,
            'ACTIVE' => 'Y',
            'XML_ID' => $xmlId,
            'CODE' => $code,
            'PREVIEW_TEXT' => 'Карточка импорта Getchips по запросу поиска по партномеру.',
            'PREVIEW_TEXT_TYPE' => 'text',
        ];

        $propVals = [];
        if (self::resolvePropertyIdByCode($catalogIblockId, 'pr_article')) {
            $propVals['pr_article'] = $norm;
        }
        if (self::resolvePropertyIdByCode($catalogIblockId, 'CML2_ARTICLE')) {
            $propVals['CML2_ARTICLE'] = $norm;
        }
        if ($propVals !== []) {
            $fields['PROPERTY_VALUES'] = $propVals;
        }

        $newId = (int) $el->Add($fields);
        if ($newId <= 0) {
            self::logLine('upsert Add failed: ' . $el->LAST_ERROR);

            return 0;
        }

        if (Loader::includeModule('catalog')) {
            $typeProduct = class_exists(\Bitrix\Catalog\ProductTable::class)
                ? (int) \Bitrix\Catalog\ProductTable::TYPE_PRODUCT
                : 1;
            CCatalogProduct::Add([
                'ID' => $newId,
                'TYPE' => $typeProduct,
                'QUANTITY' => 0,
                'QUANTITY_TRACE' => 'N',
                'AVAILABLE' => 'Y',
                'CAN_BUY_ZERO' => 'Y',
            ]);
        }
        GetchipsCatalogOffersHelper::syncCatalogQuantityFromSupplierOffers($newId, $norm);
        require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/LvtSupplierBasePriceHelper.php';
        LvtSupplierBasePriceHelper::syncForProduct($newId, null, $norm);

        return $newId;
    }

    private static function ensureImportSectionId(int $catalogIblockId): int
    {
        $db = CIBlockSection::GetList(
            [],
            ['IBLOCK_ID' => $catalogIblockId, 'CODE' => self::IMPORT_SECTION_CODE],
            false,
            ['ID']
        );
        if ($s = $db->Fetch()) {
            return (int) $s['ID'];
        }

        $bs = new CIBlockSection();
        $sid = (int) $bs->Add([
            'IBLOCK_ID' => $catalogIblockId,
            'NAME' => 'Импорт Getchips (поиск по партномеру)',
            'CODE' => self::IMPORT_SECTION_CODE,
            'ACTIVE' => 'Y',
            'DESCRIPTION' => 'Технические карточки, создаваемые при поиске по партномеру.',
            'DESCRIPTION_TYPE' => 'text',
        ]);
        if ($sid <= 0) {
            self::logLine('section Add failed: ' . $bs->LAST_ERROR);
        }

        return $sid;
    }

    private static function resolvePropertyIdByCode(int $iblockId, string $code): int
    {
        $code = trim($code);
        if ($code === '') {
            return 0;
        }
        $db = CIBlockProperty::GetList([], ['IBLOCK_ID' => $iblockId, 'CODE' => $code]);
        if ($p = $db->Fetch()) {
            return (int) $p['ID'];
        }

        return 0;
    }

    public static function logLine(string $msg): void
    {
        $dir = $_SERVER['DOCUMENT_ROOT'] . '/local/logs';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $file = $dir . '/partnumber_search.log';
        $line = date('c') . ' ' . $msg . "\n";
        @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }
}
