<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Loader;

/**
 * Скрывает в меню каталога только разделы без активных товаров.
 * Сами страницы разделов остаются доступными по прямой ссылке.
 */
class LvtCatalogMenuFilter
{
    private const SECTION_ELEMENTS_CACHE_TTL = 86400;
    private const SECTION_ELEMENTS_CACHE_DIR = '/lvt/menu_section_elements';

    /** @var array<string, bool> */
    private static $sectionHasElementsCache = [];
    /** @var array<string, array<string, int>> */
    private static $sectionMetaCache = [];

    public static function isEnabled(): bool
    {
        $host = strtolower(trim((string) ($_SERVER['HTTP_HOST'] ?? '')));
        $host = (string) preg_replace('/:\d+$/', '', $host);

        return in_array($host, ['lvt.market', 'www.lvt.market'], true);
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    public static function filterEmptySections(array $items): array
    {
        $result = [];

        foreach ($items as $item) {
            if (!empty($item['CHILD']) && is_array($item['CHILD'])) {
                $item['CHILD'] = self::filterEmptySections($item['CHILD']);
            }

            if (self::isCatalogSection($item)) {
                $item = self::resolveSectionMeta($item);

                $sectionId = (int) ($item['PARAMS']['ID'] ?? 0);
                $iblockId = (int) ($item['PARAMS']['IBLOCK_ID'] ?? 0);
                $hasVisibleChildren = !empty($item['CHILD']);

                if (!$hasVisibleChildren && $sectionId > 0 && !self::sectionHasElements($sectionId, $iblockId)) {
                    continue;
                }
            }

            $result[] = $item;
        }

        return array_values($result);
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function isCatalogSection(array $item): bool
    {
        if (!empty($item['PARAMS']['IS_ITEM'])) {
            return false;
        }

        if (!empty($item['PARAMS']['FROM_IBLOCK'])) {
            return true;
        }

        $path = self::normalizeMenuLinkPath((string) ($item['LINK'] ?? ''));

        return (bool) preg_match('#^/(catalog|katalog)(/|$)#', $path);
    }

    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    private static function resolveSectionMeta(array $item): array
    {
        $sectionId = (int) ($item['PARAMS']['ID'] ?? 0);
        $iblockId = (int) ($item['PARAMS']['IBLOCK_ID'] ?? 0);
        if ($sectionId > 0 && $iblockId > 0) {
            return $item;
        }

        $link = (string) ($item['LINK'] ?? '');
        $path = self::normalizeMenuLinkPath($link);
        if ($path === '') {
            return $item;
        }

        $catalogIblockId = self::getCatalogIblockId();
        if ($catalogIblockId <= 0) {
            return $item;
        }

        $cacheKey = $catalogIblockId . ':' . $path;
        if (!isset(self::$sectionMetaCache[$cacheKey])) {
            self::$sectionMetaCache[$cacheKey] = ['ID' => 0, 'IBLOCK_ID' => $catalogIblockId];

            if (Loader::includeModule('iblock')) {
                if ($arSection = self::findSectionByLink($catalogIblockId, $path)) {
                    self::$sectionMetaCache[$cacheKey] = [
                        'ID' => (int) $arSection['ID'],
                        'IBLOCK_ID' => (int) $arSection['IBLOCK_ID'],
                    ];
                }
            }
        }

        if (!empty(self::$sectionMetaCache[$cacheKey]['ID'])) {
            $item['PARAMS']['ID'] = self::$sectionMetaCache[$cacheKey]['ID'];
            $item['PARAMS']['IBLOCK_ID'] = self::$sectionMetaCache[$cacheKey]['IBLOCK_ID'];
        }

        return $item;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function normalizeMenuLinkPath(string $link): string
    {
        if ($link === '' || $link === 'javascript:;') {
            return '';
        }

        $path = (string) parse_url($link, PHP_URL_PATH);
        if ($path === '' || $path[0] !== '/') {
            $path = '/' . ltrim(strtok($link, '?#') ?: $link, '/');
        }

        return $path;
    }

    private static function findSectionByLink(int $iblockId, string $path): ?array
    {
        $path = trim($path, '/');
        if ($path === '') {
            return null;
        }

        $segments = explode('/', $path);
        $catalogIndex = array_search('catalog', $segments, true);
        if ($catalogIndex === false) {
            $catalogIndex = array_search('katalog', $segments, true);
        }
        if ($catalogIndex === false) {
            return null;
        }

        $codePath = array_slice($segments, $catalogIndex + 1);
        if (!$codePath) {
            return null;
        }

        $parentSectionId = false;
        $foundSection = null;

        foreach ($codePath as $sectionCode) {
            if ($sectionCode === '') {
                return null;
            }

            $filter = [
                'IBLOCK_ID' => $iblockId,
                '=CODE' => $sectionCode,
                'ACTIVE' => 'Y',
                'GLOBAL_ACTIVE' => 'Y',
            ];
            if ($parentSectionId === false) {
                $filter['SECTION_ID'] = false;
            } else {
                $filter['SECTION_ID'] = $parentSectionId;
            }

            $rsSection = CIBlockSection::GetList(
                [],
                $filter,
                false,
                ['ID', 'IBLOCK_ID', 'NAME', 'CODE', 'IBLOCK_SECTION_ID']
            );

            $foundSection = $rsSection->Fetch();
            if (!$foundSection) {
                return null;
            }

            $parentSectionId = (int) $foundSection['ID'];
        }

        return $foundSection ?: null;
    }

    private static function getCatalogIblockId(): int
    {
        static $catalogIblockId;

        if ($catalogIblockId !== null) {
            return $catalogIblockId;
        }

        $moduleId = defined('VENDOR_MODULE_ID') ? VENDOR_MODULE_ID : 'aspro.lite';
        $catalogIblockId = (int) Option::get($moduleId, 'CATALOG_IBLOCK_ID', 0);

        return $catalogIblockId;
    }

    private static function sectionHasElements(int $sectionId, int $iblockId = 0): bool
    {
        if ($sectionId <= 0) {
            return false;
        }

        $cacheKey = $iblockId . ':' . $sectionId;
        if (array_key_exists($cacheKey, self::$sectionHasElementsCache)) {
            return self::$sectionHasElementsCache[$cacheKey];
        }

        $bitrixCache = Cache::createInstance();
        $cacheId = 'sec_el_' . $cacheKey;
        if ($bitrixCache->initCache(self::SECTION_ELEMENTS_CACHE_TTL, $cacheId, self::SECTION_ELEMENTS_CACHE_DIR)) {
            $has = (bool) $bitrixCache->getVars();

            return self::$sectionHasElementsCache[$cacheKey] = $has;
        }

        if (!Loader::includeModule('iblock')) {
            return self::$sectionHasElementsCache[$cacheKey] = true;
        }

        $has = false;
        if ($bitrixCache->startDataCache()) {
            $filter = [
                'ACTIVE' => 'Y',
                'ACTIVE_DATE' => 'Y',
                'SECTION_ID' => $sectionId,
                'INCLUDE_SUBSECTIONS' => 'Y',
                'CHECK_PERMISSIONS' => 'Y',
                'MIN_PERMISSION' => 'R',
            ];
            if ($iblockId > 0) {
                $filter['IBLOCK_ID'] = $iblockId;
            }

            $rsElements = CIBlockElement::GetList(
                ['ID' => 'ASC'],
                $filter,
                false,
                ['nTopCount' => 1],
                ['ID']
            );

            $has = (bool) $rsElements->Fetch();
            $bitrixCache->endDataCache($has);
        }

        return self::$sectionHasElementsCache[$cacheKey] = $has;
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    public static function containsCatalogSections(array $items): bool
    {
        foreach ($items as $item) {
            if (self::isCatalogSection($item)) {
                return true;
            }

            if (!empty($item['CHILD']) && is_array($item['CHILD']) && self::containsCatalogSections($item['CHILD'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    public static function applyIfNeeded(array $items): array
    {
        if (!self::isEnabled() || !$items || !self::containsCatalogSections($items)) {
            return $items;
        }

        return self::filterEmptySections($items);
    }

    /**
     * Фильтрация плоского массива пунктов меню (формат Bitrix / Aspro getMenuChildsExt).
     * Не зависит от result_modifier.php в шаблоне.
     *
     * @param array<int, array<string, mixed>> $links
     * @return array<int, array<string, mixed>>
     */
    public static function filterFlatMenuLinks(array $links): array
    {
        if (!self::isEnabled() || !$links) {
            return $links;
        }

        $normalized = self::normalizeFlatLinksForGetChilds2($links);
        if (!self::containsCatalogSections($normalized)) {
            return $links;
        }

        if (!Loader::includeModule('aspro.lite')) {
            return $links;
        }

        $tree = CLite::getChilds2($normalized);
        $tree = self::filterEmptySections($tree);

        return self::flattenTreeToFlatLinks($tree);
    }

    /**
     * @param array<int, array<string, mixed>> $links
     * @return array<int, array<string, mixed>>
     */
    private static function normalizeFlatLinksForGetChilds2(array $links): array
    {
        $result = [];

        foreach ($links as $link) {
            if (isset($link['LINK']) || isset($link['PARAMS'])) {
                $params = is_array($link['PARAMS'] ?? null) ? $link['PARAMS'] : [];
                $result[] = [
                    'TEXT' => (string) ($link['TEXT'] ?? ''),
                    'LINK' => (string) ($link['LINK'] ?? ''),
                    'DEPTH_LEVEL' => (int) ($link['DEPTH_LEVEL'] ?? $params['DEPTH_LEVEL'] ?? 1),
                    'IS_PARENT' => (int) ($link['IS_PARENT'] ?? $params['IS_PARENT'] ?? 0),
                    'PARAMS' => $params,
                ];
                continue;
            }

            $params = is_array($link[3] ?? null) ? $link[3] : [];
            $result[] = [
                'TEXT' => (string) ($link[0] ?? ''),
                'LINK' => (string) ($link[1] ?? ''),
                'DEPTH_LEVEL' => (int) ($params['DEPTH_LEVEL'] ?? 1),
                'IS_PARENT' => (int) ($params['IS_PARENT'] ?? 0),
                'PARAMS' => $params,
            ];
        }

        return $result;
    }

    /**
     * @param array<int, array<string, mixed>> $tree
     * @return array<int, array<int|string, mixed>>
     */
    private static function flattenTreeToFlatLinks(array $tree): array
    {
        $result = [];
        self::flattenTreeToFlatLinksRecursive($tree, $result);

        return $result;
    }

    /**
     * @param array<int, array<string, mixed>> $tree
     * @param array<int, array<int|string, mixed>> $result
     */
    private static function flattenTreeToFlatLinksRecursive(array $tree, array &$result): void
    {
        foreach ($tree as $item) {
            $params = is_array($item['PARAMS'] ?? null) ? $item['PARAMS'] : [];
            $depthLevel = (int) ($item['DEPTH_LEVEL'] ?? $params['DEPTH_LEVEL'] ?? 1);
            $children = is_array($item['CHILD'] ?? null) ? $item['CHILD'] : [];

            $params['DEPTH_LEVEL'] = $depthLevel;
            $params['IS_PARENT'] = $children ? 1 : 0;

            $result[] = [
                (string) ($item['TEXT'] ?? ''),
                (string) ($item['LINK'] ?? ''),
                [],
                $params,
            ];

            if ($children) {
                self::flattenTreeToFlatLinksRecursive($children, $result);
            }
        }
    }
}
