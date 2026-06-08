<?php

use Bitrix\Main\Data\Cache;

require_once __DIR__ . '/MouserPartOffersHelper.php';

/**
 * Поиск Mouser для страницы каталога (пустой индекс), с файловым кешем.
 */
class MouserCatalogSearchBridge
{
    private const CACHE_DIR = '/mouser/catalog_keyword/';
    private const CACHE_TTL = 3600;
    private const MIN_LEN = 3;
    private const MAX_RECORDS = 20;

    private static function debugLog(string $runId, string $hypothesisId, string $location, string $message, array $data = []): void
    {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function searchPartsCached(string $keyword): array
    {
        $runId = 'pre-fix-' . substr(md5('bridge|' . $keyword . '|' . microtime(true)), 0, 10);
        $keyword = trim($keyword);
        self::debugLog($runId, 'H1', 'MouserCatalogSearchBridge.php:44', 'searchPartsCached enter', [
            'keyword' => $keyword,
            'keywordLen' => mb_strlen($keyword),
        ]);
        if (mb_strlen($keyword) < self::MIN_LEN) {
            self::debugLog($runId, 'H1', 'MouserCatalogSearchBridge.php:49', 'searchPartsCached early return', [
                'reason' => 'keyword_too_short',
            ]);
            return [];
        }
        $cache = Cache::createInstance();
        $key = 'kw_' . md5(mb_strtolower($keyword));
        if ($cache->initCache(self::CACHE_TTL, $key, self::CACHE_DIR)) {
            $vars = $cache->getVars();
            self::debugLog($runId, 'H3', 'MouserCatalogSearchBridge.php:58', 'cache hit', [
                'partsCount' => is_array($vars['parts'] ?? null) ? count($vars['parts']) : -1,
            ]);

            return is_array($vars['parts'] ?? null) ? $vars['parts'] : [];
        }
        $cache->startDataCache();
        $mouser = $_SERVER['DOCUMENT_ROOT'] . '/mouser/mouser_client.php';
        if (!is_file($mouser)) {
            $cache->abortDataCache();
            self::debugLog($runId, 'H2', 'MouserCatalogSearchBridge.php:66', 'searchPartsCached abort', [
                'reason' => 'missing_mouser_client',
                'path' => $mouser,
            ]);

            return [];
        }
        require_once $mouser;
        if (!function_exists('mouser_keyword_search')) {
            $cache->abortDataCache();
            self::debugLog($runId, 'H2', 'MouserCatalogSearchBridge.php:74', 'searchPartsCached abort', [
                'reason' => 'missing_function_mouser_keyword_search',
            ]);

            return [];
        }
        $res = mouser_keyword_search($keyword, self::MAX_RECORDS, 0);
        if (empty($res['ok']) || !is_array($res['data'] ?? null)) {
            $cache->abortDataCache();
            self::debugLog($runId, 'H3', 'MouserCatalogSearchBridge.php:83', 'mouser_keyword_search failed', [
                'ok' => !empty($res['ok']),
            ]);

            return [];
        }
        $parts = self::extractParts($res['data']);
        self::debugLog($runId, 'H3', 'MouserCatalogSearchBridge.php:90', 'mouser_keyword_search extracted parts', [
            'partsCount' => count($parts),
        ]);
        $cache->endDataCache(['parts' => $parts]);

        return $parts;
    }

    /**
     * @param array<string, mixed> $data
     * @return list<array<string, mixed>>
     */
    private static function extractParts(array $data): array
    {
        $sr = $data['SearchResults'] ?? null;
        if (!is_array($sr)) {
            return [];
        }
        $raw = $sr['Parts'] ?? null;
        if (!is_array($raw)) {
            return [];
        }
        $out = [];
        foreach ($raw as $p) {
            if (!is_array($p)) {
                continue;
            }
            $mpn = trim((string) ($p['MouserPartNumber'] ?? ''));
            if ($mpn === '') {
                continue;
            }
            $out[] = [
                'MouserPartNumber' => $mpn,
                'ManufacturerPartNumber' => trim((string) ($p['ManufacturerPartNumber'] ?? '')),
                'Manufacturer' => trim((string) ($p['Manufacturer'] ?? '')),
                'Description' => trim((string) ($p['Description'] ?? '')),
                'ImagePath' => trim((string) ($p['ImagePath'] ?? '')),
                'Category' => MouserPartOffersHelper::categoryTextFromPart($p),
            ];
        }

        return $out;
    }
}
