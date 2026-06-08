<?php

declare(strict_types=1);

/**
 * Живые цена и остаток ETM для карточки IB 41 (/katalog/).
 */
class LvtEtmCatalogLiveHelper
{
    private const FILE_CACHE_DIR = '/upload/etm_live_cache';
    private const FILE_CACHE_TTL = 172800;

    /** Логировать каждый резолв кода (иначе — только slug_override, db, empty). */
    private const LOG_ALL_CODE_RESOLVES = false;

    public static function ensureBootstrap(): void
    {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/api_etm_ai/config_ib40.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/api_etm_ai/classes/EtmApiClient.php';
        require_once $_SERVER['DOCUMENT_ROOT'] . '/api_etm_ai/includes/etm_element_code.php';
    }

    public static function resolveEtmCode(array $arResult): string
    {
        return (string) (self::resolveEtmCodeMeta($arResult)['code'] ?? '');
    }

    /**
     * @return array{
     *   element_id: int,
     *   code: string,
     *   source: string,
     *   property_code?: string,
     *   primary?: string,
     *   slug?: string
     * }
     */
    public static function resolveEtmCodeMeta(array $arResult): array
    {
        self::ensureBootstrap();

        $elementId = (int) ($arResult['ID'] ?? 0);
        $meta = [
            'element_id' => $elementId,
            'code' => '',
            'source' => 'empty',
        ];

        $propertyPriority = ['ETMCODE'];
        if (defined('API_ETM_PROP_ETM_CODE')) {
            $propertyPriority[] = (string) API_ETM_PROP_ETM_CODE;
        }
        $propertyPriority[] = 'kod_tovara_';
        if (defined('API_ETM_PROP_ETM_CODE_LEGACY')) {
            $propertyPriority[] = (string) API_ETM_PROP_ETM_CODE_LEGACY;
        }
        $propertyPriority[] = 'ID_ELEMENTA';

        $primary = '';
        $primaryProperty = '';
        $ordered = [];
        $properties = is_array($arResult['PROPERTIES'] ?? null) ? $arResult['PROPERTIES'] : [];

        foreach (array_values(array_unique($propertyPriority)) as $propCode) {
            if ($propCode === '') {
                continue;
            }
            $raw = $properties[$propCode]['VALUE'] ?? '';
            if (is_array($raw)) {
                $raw = reset($raw);
            }
            $candidate = trim((string) $raw);
            if ($candidate === '') {
                continue;
            }
            if ($primary === '') {
                $primary = $candidate;
                $primaryProperty = $propCode;
            }
            $ordered[] = ['code' => $candidate, 'source' => 'property:' . $propCode];
        }

        $slugFromCode = '';
        $elementCode = trim((string) ($arResult['CODE'] ?? ''));
        if ($elementCode !== '' && preg_match('/^etm_(.+)$/i', $elementCode, $m)) {
            $slugFromCode = trim((string) $m[1]);
            if ($slugFromCode !== '') {
                $ordered[] = ['code' => $slugFromCode, 'source' => 'slug:element_code'];
            }
        }

        $slugFromUrl = '';
        if ($slugFromCode === '') {
            $detailUrl = trim((string) ($arResult['DETAIL_PAGE_URL'] ?? ''));
            if ($detailUrl !== '' && preg_match('~/etm_([^/?#]+)/?~i', $detailUrl, $m)) {
                $slugFromUrl = trim((string) $m[1]);
                if ($slugFromUrl !== '') {
                    $ordered[] = ['code' => $slugFromUrl, 'source' => 'slug:detail_url'];
                }
            }
        }

        $slugCandidate = $slugFromCode !== '' ? $slugFromCode : $slugFromUrl;

        // Для части карточек IB41 в kod_tovara_ лежит внутренний короткий ID.
        // Если в URL/символьном коде есть полноценный ETM-код (длиннее), используем его.
        if (
            $primary !== '' &&
            ctype_digit($primary) &&
            strlen($primary) < 6 &&
            $slugCandidate !== '' &&
            ctype_digit($slugCandidate) &&
            strlen($slugCandidate) >= 6
        ) {
            $meta['code'] = $slugCandidate;
            $meta['source'] = 'slug_override';
            $meta['primary'] = $primary;
            $meta['property_code'] = $primaryProperty;
            $meta['slug'] = $slugCandidate;
            self::logEtmCodeResolve($meta);

            return $meta;
        }

        foreach ($ordered as $item) {
            if ($item['code'] !== '') {
                $meta['code'] = $item['code'];
                $meta['source'] = $item['source'];
                if ($primary !== '' && $primary !== $item['code']) {
                    $meta['primary'] = $primary;
                    $meta['property_code'] = $primaryProperty;
                }
                self::logEtmCodeResolve($meta);

                return $meta;
            }
        }

        $iblockId = (int) ($arResult['IBLOCK_ID'] ?? API_ETM_IBLOCK_ID);
        if ($iblockId > 0 && $elementId > 0) {
            $dbCode = trim(etmGetElementEtmCode($iblockId, $elementId));
            if ($dbCode !== '') {
                $meta['code'] = $dbCode;
                $meta['source'] = 'db';
                self::logEtmCodeResolve($meta);

                return $meta;
            }
        }

        self::logEtmCodeResolve($meta);

        return $meta;
    }

    /**
     * @param array<string, mixed> $meta
     */
    private static function logEtmCodeResolve(array $meta): void
    {
        $source = (string) ($meta['source'] ?? '');
        $interesting = in_array($source, ['slug_override', 'db', 'empty'], true)
            || (($meta['primary'] ?? '') !== '' && ($meta['primary'] ?? '') !== ($meta['code'] ?? ''));

        if (!self::LOG_ALL_CODE_RESOLVES && !$interesting) {
            return;
        }

        if (!defined('API_ETM_LOGS_DIR')) {
            return;
        }

        static $logFile = null;
        if ($logFile === null) {
            $dir = API_ETM_LOGS_DIR;
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            $logFile = $dir . '/etm_live_code_resolve_' . date('Y-m-d') . '.log';
        }

        $payload = [
            'element_id' => (int) ($meta['element_id'] ?? 0),
            'source' => $source,
            'code' => (string) ($meta['code'] ?? ''),
        ];
        if (!empty($meta['property_code'])) {
            $payload['property'] = (string) $meta['property_code'];
        }
        if (!empty($meta['primary']) && ($meta['primary'] ?? '') !== ($meta['code'] ?? '')) {
            $payload['primary'] = (string) $meta['primary'];
        }
        if (!empty($meta['slug']) && ($meta['slug'] ?? '') !== ($meta['code'] ?? '')) {
            $payload['slug'] = (string) $meta['slug'];
        }

        $line = '[' . date('H:i:s') . '] ' . json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
        @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * @return array{
     *   ok: bool,
     *   error?: string,
     *   etm_code?: string,
     *   price_rub?: float,
     *   quantity?: int,
     *   store_id?: int,
     *   store_name?: string,
     *   delivery_time?: string,
     *   extended_prices?: list<array<string, mixed>>,
     *   store_data?: list<array<string, mixed>>,
     *   min_order_quantity?: int,
     *   cached?: bool
     * }
     */
    /**
     * @return list<string>
     */
    public static function fetchGoodsVideoUrls(string $etmCode, bool $skipCache = false): array
    {
        $etmCode = trim($etmCode);
        if ($etmCode === '') {
            return [];
        }

        $cacheFile = self::cacheFilePath($etmCode . '_videos');
        if (!$skipCache) {
            $cached = self::readCache($cacheFile);
            if ($cached !== null && isset($cached['video_urls']) && is_array($cached['video_urls'])) {
                return array_values(array_filter(array_map('strval', $cached['video_urls'])));
            }
        }

        self::ensureBootstrap();
        require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/LvtIb41ProductGallery.php';

        $client = new ApiEtmClient(ETM_API_URL, ETM_LOGIN, ETM_PASSWORD);
        if (!$client->ensureAuth()) {
            $stale = self::readCache($cacheFile, true);
            if ($stale !== null && isset($stale['video_urls']) && is_array($stale['video_urls'])) {
                return array_values(array_filter(array_map('strval', $stale['video_urls'])));
            }
            return [];
        }

        $goods = $client->getGoods($etmCode, 'etm');
        $urls = [];
        foreach ((array) ($goods['gdsVideos'] ?? $goods['data']['gdsVideos'] ?? []) as $video) {
            if (!is_array($video)) {
                continue;
            }
            $url = LvtIb41ProductGallery::encodeMediaUrl((string) ($video['gdsVidSrc'] ?? ''));
            if ($url !== '') {
                $urls[] = $url;
            }
        }
        $urls = array_values(array_unique($urls));

        self::writeCache($cacheFile, [
            'ok' => true,
            'video_urls' => $urls,
        ]);

        return $urls;
    }

    public static function fetchLiveData(string $etmCode, bool $skipCache = false): array
    {
        $etmCode = trim($etmCode);
        if ($etmCode === '') {
            return ['ok' => false, 'error' => 'empty etm code'];
        }

        $cacheFile = self::cacheFilePath($etmCode);
        if (!$skipCache) {
            $cached = self::readCache($cacheFile);
            if ($cached !== null) {
                $cached['cached'] = true;
                return $cached;
            }
        }

        self::ensureBootstrap();

        $client = new ApiEtmClient(ETM_API_URL, ETM_LOGIN, ETM_PASSWORD);
        if (!$client->ensureAuth()) {
            $stale = self::readCache($cacheFile, true);
            if ($stale !== null) {
                $stale['cached'] = true;
                $stale['stale'] = true;
                return $stale;
            }
            return ['ok' => false, 'error' => 'etm auth failed', 'etm_code' => $etmCode];
        }

        $priceRub = 0.0;
        $priceRows = $client->getGoodsPrice([$etmCode], 'etm');
        if (is_array($priceRows)) {
            foreach ($priceRows as $row) {
                if ((string) ($row['gdscode'] ?? '') !== $etmCode) {
                    continue;
                }
                $priceRub = (float) ($row['pricewnds'] ?? $row['price'] ?? 0);
                break;
            }
        }

        $qty = 0;
        $remains = $client->getGoodsRemains($etmCode, 'etm');
        if (is_array($remains)) {
            $stores = $remains['data']['InfoStores'] ?? ($remains['InfoStores'] ?? []);
            foreach ((array) $stores as $store) {
                $type = (string) ($store['StoreType'] ?? '');
                if ($type === 'all' || $type === '') {
                    $qty = max($qty, (int) ($store['StoreQuantRem'] ?? 0));
                }
            }
            if ($qty === 0) {
                foreach ((array) $stores as $store) {
                    if (($store['StoreType'] ?? '') === 'reg') {
                        $qty += (int) ($store['StoreQuantRem'] ?? 0);
                    }
                }
            }
        }

        $storeMeta = self::resolveStoreMeta((int) API_ETM_STORE_ID);
        $minOrder = 1;
        $extendedPrices = [[
            'TYPE' => 'BASE',
            'CATALOG_GROUP_ID' => (int) API_ETM_PRICE_TYPE_ID,
            'PRICE' => $priceRub,
            'CURRENCY' => 'RUB',
            'QUANTITY_FROM' => $minOrder,
            'QUANTITY_TO' => 0,
        ]];

        $storeData = [[
            'ID' => (int) ($storeMeta['ID'] ?? API_ETM_STORE_ID),
            'NAME' => (string) ($storeMeta['TITLE'] ?? 'ETM'),
            'ADDRESS' => (string) ($storeMeta['ADDRESS'] ?? ''),
            'QUANTITY' => $qty,
            'DELIVERY_TIME' => (string) ($storeMeta['DELIVERY_TIME'] ?? '4-5 недель'),
            'SORT' => (int) ($storeMeta['SORT'] ?? 500),
        ]];

        $payload = [
            'ok' => true,
            'etm_code' => $etmCode,
            'price_rub' => $priceRub,
            'quantity' => $qty,
            'store_id' => (int) ($storeMeta['ID'] ?? API_ETM_STORE_ID),
            'store_name' => (string) ($storeMeta['TITLE'] ?? 'ETM'),
            'delivery_time' => (string) ($storeMeta['DELIVERY_TIME'] ?? '4-5 недель'),
            'extended_prices' => $extendedPrices,
            'store_data' => $storeData,
            'min_order_quantity' => $minOrder,
            'cached' => false,
        ];

        if ($priceRub <= 0 && $qty <= 0) {
            $payload['ok'] = false;
            $payload['error'] = 'empty etm response';
        } elseif ($payload['ok']) {
            self::writeCache($cacheFile, $payload);
        }

        return $payload;
    }

    /**
     * @return array{ID: int, TITLE: string, ADDRESS: string, DELIVERY_TIME: string, SORT: int}
     */
    private static function resolveStoreMeta(int $storeId): array
    {
        $fallback = [
            'ID' => $storeId,
            'TITLE' => 'ETM',
            'ADDRESS' => '',
            'DELIVERY_TIME' => '4-5 недель',
            'SORT' => 500,
        ];
        if ($storeId <= 0 || !\Bitrix\Main\Loader::includeModule('catalog')) {
            return $fallback;
        }

        $store = \Bitrix\Catalog\StoreTable::getList([
            'filter' => ['=ID' => $storeId],
            'select' => ['ID', 'TITLE', 'ADDRESS', 'UF_SROK_DOST', 'SORT'],
            'limit' => 1,
        ])->fetch();

        if (!$store) {
            return $fallback;
        }

        return [
            'ID' => (int) $store['ID'],
            'TITLE' => (string) ($store['TITLE'] ?: 'ETM'),
            'ADDRESS' => (string) ($store['ADDRESS'] ?? ''),
            'DELIVERY_TIME' => trim((string) ($store['UF_SROK_DOST'] ?? '')) ?: '4-5 недель',
            'SORT' => (int) ($store['SORT'] ?? 500),
        ];
    }

    private static function cacheFilePath(string $etmCode): string
    {
        return rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/') . self::FILE_CACHE_DIR . '/' . md5($etmCode) . '.json';
    }

    private static function readCache(string $path, bool $allowStale = false): ?array
    {
        if (!is_readable($path)) {
            return null;
        }
        $raw = @file_get_contents($path);
        if ($raw === false || $raw === '') {
            return null;
        }
        $data = json_decode($raw, true);
        if (!is_array($data) || empty($data['ok'])) {
            return null;
        }
        $mtime = @filemtime($path);
        if (!$allowStale && $mtime !== false && (time() - $mtime) > self::FILE_CACHE_TTL) {
            return null;
        }

        return $data;
    }

    private static function writeCache(string $path, array $payload): void
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        @file_put_contents($path, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
