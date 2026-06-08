<?php

use Bitrix\Main\Data\Cache;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\HttpClient;

/**
 * Серверный слой: кеш 7 суток, HTTP к Laravel /api/catalog-offers, пересчёт ₽ через модуль currency (USD/EUR из админки).
 */
class GetchipsCatalogOffersHelper
{
    private const CACHE_DIR = '/getchips/catalog_offers/';
    private const CACHE_TTL = 604800;
    private const FILE_CACHE_DIR = '/upload/getchips_offers_cache';
    private const FILE_CACHE_STALE_TTL = 1209600; // 14 days
    private const CBR_CACHE_DIR = '/getchips/cbr_rates/';
    private const CBR_CACHE_TTL = 3600;
    private const CBR_USD_FALLBACK = 74.8806;

    /** Инфоблок «Бренды» (aspro_lite_content), привязка логотипа и ссылки /brands/... */
    private const BRANDS_IBLOCK_ID = 6;

    /**
     * local/brands.csv: Название;ID элемента ИБ брендов;URL картинки.
     * ID — элемент инфоблока брендов (ссылка на /brands/...); картинка может переопределять превью элемента.
     *
     * @return array<string, array{logo: string, element_id: int}>
     */
    private static function loadBrandCsvByName(): array
    {
        static $map = null;
        if ($map !== null) {
            return $map;
        }
        $map = [];
        $root = (string) ($_SERVER['DOCUMENT_ROOT'] ?? '');
        $path = $root !== '' ? $root . '/local/brands.csv' : '';
        if ($path === '' || !is_readable($path)) {
            return $map;
        }
        $raw = @file_get_contents($path);
        if ($raw === false || $raw === '') {
            return $map;
        }
        if (strncmp($raw, "\xEF\xBB\xBF", 3) === 0) {
            $raw = substr($raw, 3);
        }
        foreach (explode("\n", $raw) as $i => $line) {
            $line = trim($line);
            if ($line === '' || $i === 0) {
                continue;
            }
            $parts = str_getcsv($line, ';', '"');
            if (count($parts) < 3) {
                continue;
            }
            $name = trim((string) $parts[0]);
            $idRaw = trim((string) $parts[1]);
            $url = trim((string) $parts[2]);
            if ($name === '') {
                continue;
            }
            $elementId = ctype_digit($idRaw) ? (int) $idRaw : 0;
            $logo = '';
            if ($url !== '' && preg_match('#^https?://#i', $url)) {
                $logo = $url;
            }
            if ($elementId <= 0 && $logo === '') {
                continue;
            }
            $map[mb_strtoupper($name)] = [
                'logo' => $logo,
                'element_id' => $elementId,
            ];
        }

        return $map;
    }

    public static function normalizeArticle(string $s): string
    {
        return strtoupper(trim($s));
    }

    public static function resolvePrArticle(array $arResult): string
    {
        $fromProp = self::propertySingleValue($arResult['PROPERTIES']['pr_article'] ?? null);
        if ($fromProp !== '') {
            return self::normalizeArticle($fromProp);
        }
        $fromDisplay = self::propertySingleValue($arResult['DISPLAY_PROPERTIES']['pr_article'] ?? null);
        if ($fromDisplay !== '') {
            return self::normalizeArticle($fromDisplay);
        }
        $offers = $arResult['OFFERS'] ?? [];
        $idx = (int) ($arResult['OFFERS_SELECTED'] ?? 0);
        if (isset($offers[$idx]['PROPERTIES']['pr_article'])) {
            $v = self::propertySingleValue($offers[$idx]['PROPERTIES']['pr_article']);
            if ($v !== '') {
                return self::normalizeArticle($v);
            }
        }
        foreach ($offers as $offer) {
            $v = self::propertySingleValue($offer['PROPERTIES']['pr_article'] ?? null);
            if ($v !== '') {
                return self::normalizeArticle($v);
            }
        }
        $iblockId = (int) ($arResult['IBLOCK_ID'] ?? 0);
        $elementId = (int) ($arResult['ID'] ?? 0);
        if ($elementId > 0 && $iblockId > 0) {
            $fromDb = self::loadPropertyValueByCode($iblockId, $elementId, 'pr_article');
            if ($fromDb !== '') {
                return self::normalizeArticle($fromDb);
            }
        }
        foreach ($offers as $offer) {
            $oid = (int) ($offer['ID'] ?? 0);
            $oIblock = (int) ($offer['IBLOCK_ID'] ?? 0);
            if ($oid > 0 && $oIblock > 0) {
                $fromDb = self::loadPropertyValueByCode($oIblock, $oid, 'pr_article');
                if ($fromDb !== '') {
                    return self::normalizeArticle($fromDb);
                }
            }
        }

        $fallbackCode = 'CML2_ARTICLE';
        $v = self::propertySingleValue($arResult['PROPERTIES'][$fallbackCode] ?? null);
        if ($v !== '') {
            return self::normalizeArticle($v);
        }
        $v = self::propertySingleValue($arResult['DISPLAY_PROPERTIES'][$fallbackCode] ?? null);
        if ($v !== '') {
            return self::normalizeArticle($v);
        }
        if ($elementId > 0 && $iblockId > 0) {
            $fromDb = self::loadPropertyValueByCode($iblockId, $elementId, $fallbackCode);
            if ($fromDb !== '') {
                return self::normalizeArticle($fromDb);
            }
        }
        foreach ($offers as $offer) {
            $v = self::propertySingleValue($offer['PROPERTIES'][$fallbackCode] ?? null);
            if ($v !== '') {
                return self::normalizeArticle($v);
            }
            $v = self::propertySingleValue($offer['DISPLAY_PROPERTIES'][$fallbackCode] ?? null);
            if ($v !== '') {
                return self::normalizeArticle($v);
            }
            $oid = (int) ($offer['ID'] ?? 0);
            $oIblock = (int) ($offer['IBLOCK_ID'] ?? 0);
            if ($oid > 0 && $oIblock > 0) {
                $fromDb = self::loadPropertyValueByCode($oIblock, $oid, $fallbackCode);
                if ($fromDb !== '') {
                    return self::normalizeArticle($fromDb);
                }
            }
        }

        return '';
    }

    /**
     * Карточка в списке каталога: pr_article, затем CML2_ARTICLE (как на детальной).
     */
    public static function resolveArticleForCatalogSectionItem(array $arItem): string
    {
        $pseudo = [
            'ID' => (int) ($arItem['ID'] ?? 0),
            'IBLOCK_ID' => (int) ($arItem['IBLOCK_ID'] ?? 0),
            'PROPERTIES' => is_array($arItem['PROPERTIES'] ?? null) ? $arItem['PROPERTIES'] : [],
            'DISPLAY_PROPERTIES' => is_array($arItem['DISPLAY_PROPERTIES'] ?? null) ? $arItem['DISPLAY_PROPERTIES'] : [],
            'OFFERS' => [],
            'OFFERS_SELECTED' => 0,
        ];
        if (!empty($arItem['SKU']['CURRENT']) && is_array($arItem['SKU']['CURRENT'])) {
            $pseudo['OFFERS'] = [$arItem['SKU']['CURRENT']];
        } elseif (!empty($arItem['OFFERS']) && is_array($arItem['OFFERS'])) {
            $pseudo['OFFERS'] = $arItem['OFFERS'];
        }

        return self::resolvePrArticle($pseudo);
    }

    /**
     * Картинка товара для корзины Getchips: текущее ТП, иначе элемент (карточка или позиция в списке).
     *
     * @param array<string, mixed> $row $arResult карточки или $arItem списка после result_modifier
     */
    public static function getProductPreviewSrcForGetchipsCartRow(array $row): string
    {
        $sku = $row['SKU']['CURRENT'] ?? null;
        if (is_array($sku)) {
            if (!empty($sku['DETAIL_PICTURE']['SRC'])) {
                return (string) $sku['DETAIL_PICTURE']['SRC'];
            }
            if (!empty($sku['PREVIEW_PICTURE']['SRC'])) {
                return (string) $sku['PREVIEW_PICTURE']['SRC'];
            }
            $fromSkuGallery = self::firstGallerySrcFromRow($sku);
            if ($fromSkuGallery !== '') {
                return $fromSkuGallery;
            }
        }
        if (!empty($row['DETAIL_PICTURE']['SRC'])) {
            return (string) $row['DETAIL_PICTURE']['SRC'];
        }
        if (!empty($row['PREVIEW_PICTURE']['SRC'])) {
            return (string) $row['PREVIEW_PICTURE']['SRC'];
        }
        $fromGallery = self::firstGallerySrcFromRow($row);
        if ($fromGallery !== '') {
            return $fromGallery;
        }

        return '';
    }

    /**
     * Первый SRC из GALLERY (Aspro getSliderForItem) или из MORE_PHOTO у ТП.
     *
     * @param array<string, mixed> $item
     */
    private static function firstGallerySrcFromRow(array $item): string
    {
        if (!empty($item['GALLERY']) && is_array($item['GALLERY'])) {
            foreach ($item['GALLERY'] as $one) {
                if (is_array($one) && !empty($one['SRC'])) {
                    return (string) $one['SRC'];
                }
            }
        }
        if (!empty($item['MORE_PHOTO']) && is_array($item['MORE_PHOTO'])) {
            foreach ($item['MORE_PHOTO'] as $photo) {
                if (is_array($photo) && !empty($photo['SRC'])) {
                    return (string) $photo['SRC'];
                }
            }
        }

        return '';
    }

    public static function toAbsoluteSiteUrl(string $src): string
    {
        $src = trim($src);
        if ($src === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $src)) {
            return $src;
        }
        if (strncmp($src, '//', 2) === 0) {
            $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

            return ($https ? 'https:' : 'http:') . $src;
        }
        $host = (string) ($_SERVER['HTTP_HOST'] ?? '');
        if ($host === '') {
            return $src;
        }
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $scheme = $https ? 'https' : 'http';
        if (($src[0] ?? '') !== '/') {
            $src = '/' . $src;
        }

        return $scheme . '://' . $host . $src;
    }

    /**
     * Абсолютный URL для ссылок и картинок Getchips в корзине: всегда от каталога (GETCHIPS_PUBLIC_SITE_URL), не от текущего HTTP_HOST.
     */
    public static function toAbsoluteCatalogPublicUrl(string $src): string
    {
        $src = trim($src);
        if ($src === '') {
            return '';
        }
        if (preg_match('#^https?://#i', $src)) {
            return $src;
        }
        if (strncmp($src, '//', 2) === 0) {
            return 'https:' . $src;
        }
        $base = defined('GETCHIPS_PUBLIC_SITE_URL') ? rtrim((string) constant('GETCHIPS_PUBLIC_SITE_URL'), '/') : '';
        if ($base === '') {
            return self::toAbsoluteSiteUrl($src);
        }
        if (($src[0] ?? '') !== '/') {
            $src = '/' . $src;
        }

        return $base . $src;
    }

    private static function loadPropertyValueByCode(int $iblockId, int $elementId, string $code): string
    {
        if ($iblockId <= 0 || $elementId <= 0 || $code === '') {
            return '';
        }
        if (!Loader::includeModule('iblock')) {
            return '';
        }
        $res = \CIBlockElement::GetProperty($iblockId, $elementId, [], ['CODE' => $code]);
        while ($row = $res->Fetch()) {
            $chunk = self::propertySingleValue([
                'VALUE' => $row['VALUE'] ?? '',
                '~VALUE' => $row['~VALUE'] ?? ($row['VALUE'] ?? ''),
            ]);
            if ($chunk !== '') {
                return $chunk;
            }
        }

        return '';
    }

    /**
     * @param array<string, mixed>|null $prop
     */
    private static function propertySingleValue(?array $prop): string
    {
        if (empty($prop) || !is_array($prop)) {
            return '';
        }
        if (!empty($prop['~VALUE'])) {
            $v = $prop['~VALUE'];

            return is_array($v) ? (string) reset($v) : (string) $v;
        }
        if (!empty($prop['VALUE'])) {
            $v = $prop['VALUE'];

            return is_array($v) ? (string) reset($v) : (string) $v;
        }

        return '';
    }

    /**
     * Убрать строку агрегатора lvt.market (дублирует ваш каталог на карточке).
     *
     * @param list<array<string, mixed>> $offers
     * @return list<array<string, mixed>>
     */
    public static function filterOffersExcludeLvtMarket(array $offers): array
    {
        $out = [];
        foreach ($offers as $o) {
            if (!is_array($o)) {
                continue;
            }
            if (strtolower(trim((string) ($o['provider'] ?? ''))) === 'lvt.market') {
                continue;
            }
            $out[] = $o;
        }

        return $out;
    }

    /**
     * Ссылка «Источник» ведёт на ту же страницу детального просмотра (не показывать).
     */
    public static function isOfferUrlSameDetailPath(string $offerUrl, string $detailPageUrl): bool
    {
        if ($offerUrl === '' || $detailPageUrl === '') {
            return false;
        }
        $pOffer = parse_url($offerUrl, PHP_URL_PATH);
        $pDetail = parse_url($detailPageUrl, PHP_URL_PATH);
        if (!is_string($pOffer) || !is_string($pDetail) || $pOffer === '' || $pDetail === '') {
            return false;
        }
        $pOffer = rtrim($pOffer, '/');
        $pDetail = rtrim($pDetail, '/');

        return $pOffer === $pDetail;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function fetchOffersCached(string $article, int $amount = 1): array
    {
        if (mb_strlen($article) < 3) {
            return [];
        }
        $amount = max(1, $amount);
        // Быстрый файловый кэш: отдаём сразу, чтобы не ждать внешний API на каждом рендере.
        $fileFresh = self::readOffersFromFileCache($article, $amount, self::CACHE_TTL);
        if ($fileFresh !== null) {
            return $fileFresh;
        }
        $fileStale = self::readOffersFromFileCache($article, $amount, self::FILE_CACHE_STALE_TTL);
        if ($fileStale !== null) {
            return $fileStale;
        }

        $cache = Cache::createInstance();
        $cacheId = md5($article . '|' . $amount . '|offers_v3');
        if ($cache->initCache(self::CACHE_TTL, $cacheId, self::CACHE_DIR)) {
            $vars = $cache->getVars();
            $cached = is_array($vars['offers'] ?? null) ? $vars['offers'] : [];
            if ($cached !== []) {
                self::writeOffersToFileCache($article, $amount, $cached);
            }
            return $cached;
        }
        if (!$cache->startDataCache()) {
            return [];
        }
        $payload = self::httpFetch($article, $amount);
        $cache->endDataCache(['offers' => $payload]);
        if ($payload !== []) {
            self::writeOffersToFileCache($article, $amount, $payload);
        }

        return $payload;
    }

    private static function offersFileCachePath(string $article, int $amount): string
    {
        $root = rtrim((string)($_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
        if ($root === '') {
            return '';
        }
        $dir = $root . self::FILE_CACHE_DIR;
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        if (!is_dir($dir) || !is_writable($dir)) {
            return '';
        }
        return $dir . '/' . md5($article . '|' . $amount) . '.json';
    }

    /**
     * @return list<array<string, mixed>>|null
     */
    private static function readOffersFromFileCache(string $article, int $amount, int $ttl): ?array
    {
        $path = self::offersFileCachePath($article, $amount);
        if ($path === '' || !is_file($path)) {
            return null;
        }
        $mtime = @filemtime($path);
        if (!is_int($mtime) || $mtime <= 0 || (time() - $mtime) > $ttl) {
            return null;
        }
        $raw = @file_get_contents($path);
        if (!is_string($raw) || $raw === '') {
            return null;
        }
        $json = json_decode($raw, true);
        if (!is_array($json) || !is_array($json['offers'] ?? null)) {
            return null;
        }
        return $json['offers'];
    }

    /**
     * @param list<array<string, mixed>> $offers
     */
    private static function writeOffersToFileCache(string $article, int $amount, array $offers): void
    {
        if ($offers === []) {
            return;
        }
        $path = self::offersFileCachePath($article, $amount);
        if ($path === '') {
            return;
        }
        @file_put_contents($path, json_encode(['offers' => $offers], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function httpFetch(string $article, int $amount): array
    {
        $key = defined('GETCHIPS_INTERNAL_API_KEY') ? (string) GETCHIPS_INTERNAL_API_KEY : '';
        $base = defined('GETCHIPS_LARAVEL_BASE') ? rtrim((string) GETCHIPS_LARAVEL_BASE, '/') : '';
        if ($base === '') {
            return [];
        }
        $url = $base . '/api/catalog-offers?' . http_build_query([
            'componentNum' => $article,
            'amount' => $amount,
        ]);
        // На части окружений встроенный HttpClient может зависать на TLS к локальному Getchips.
        // Предпочитаем cURL, а HttpClient оставляем резервом.
        $curlPayload = self::httpFetchByCurlFallback($url, $key);
        if ($curlPayload !== []) {
            return $curlPayload;
        }
        $httpOptions = [
            'socketTimeout' => 40,
            'streamTimeout' => 40,
        ];
        if (defined('GETCHIPS_HTTP_DISABLE_SSL_VERIFY') && GETCHIPS_HTTP_DISABLE_SSL_VERIFY) {
            $httpOptions['disableSslVerification'] = true;
        }
        $http = new HttpClient($httpOptions);
        if ($key !== '') {
            $http->setHeader('X-Getchips-Internal-Key', $key);
        }
        $body = $http->get($url);
        if ($body === false || (int) $http->getStatus() !== 200) {
            return [];
        }
        $json = json_decode($body, true);
        if (!is_array($json) || empty($json['ok']) || !isset($json['offers']) || !is_array($json['offers'])) {
            return [];
        }

        return $json['offers'];
    }

    /**
     * Fallback для окружений, где Bitrix HttpClient периодически зависает на HTTPS к локальному getchips API.
     *
     * @return list<array<string, mixed>>
     */
    private static function httpFetchByCurlFallback(string $url, string $key): array
    {
        if (!function_exists('curl_init')) {
            return [];
        }
        $ch = curl_init($url);
        if ($ch === false) {
            return [];
        }
        $headers = [];
        if ($key !== '') {
            $headers[] = 'X-Getchips-Internal-Key: ' . $key;
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if (defined('GETCHIPS_HTTP_DISABLE_SSL_VERIFY') && GETCHIPS_HTTP_DISABLE_SSL_VERIFY) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        }
        $body = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (!is_string($body) || $body === '' || $code !== 200) {
            return [];
        }
        $json = json_decode($body, true);
        if (!is_array($json) || empty($json['ok']) || !isset($json['offers']) || !is_array($json['offers'])) {
            return [];
        }

        return $json['offers'];
    }

    /**
     * @param list<array<string, mixed>> $offers
     * @return list<array<string, mixed>>
     */
    public static function enrichOffersForDisplay(array $offers): array
    {
        foreach ($offers as &$o) {
            $unit = isset($o['unit_price']) && $o['unit_price'] !== null && $o['unit_price'] !== ''
                ? (float) $o['unit_price']
                : null;
            $cur = strtoupper(trim((string) ($o['currency'] ?? 'USD')));
            if ($unit === null) {
                $o['unit_price_rub'] = null;
                $o['display_currency'] = $cur !== '' ? $cur : 'USD';

                continue;
            }
            $o['unit_price_rub'] = self::convertToRubByCbr($unit, $cur);
            $o['display_currency'] = 'RUB';
        }
        unset($o);

        return $offers;
    }

    /** Текст цены в ₽ без HTML-сущностей (для вывода в таблице). */
    public static function formatRub(?float $v): string
    {
        if ($v === null) {
            return '—';
        }

        return number_format($v, 2, ',', ' ') . ' ₽';
    }

    /**
     * Кеш-обогащение + ступени цен (priceBreak), недели, карточка бренда из ИБ №6.
     *
     * @param list<array<string, mixed>> $offers
     * @return list<array<string, mixed>>
     */
    public static function enrichOffersForGetchipsTable(array $offers): array
    {
        $offers = self::enrichOffersForDisplay($offers);
        foreach ($offers as &$o) {
            $raw = isset($o['raw']) && is_array($o['raw']) ? $o['raw'] : [];
            $unit = isset($o['unit_price']) && is_numeric($o['unit_price']) ? (float) $o['unit_price'] : null;
            $tiersSrc = self::buildPriceTiersFromRaw($raw, $unit);
            $cur = strtoupper(trim((string) ($o['currency'] ?? 'USD')));
            if ($cur === '' || $cur === '1') {
                $cur = 'USD';
            }
            $o['price_tiers_source'] = $tiersSrc;
            $o['price_tiers_rub'] = self::convertPriceTiersToRub($tiersSrc, $cur);
            $days = isset($o['lead_time_days']) && $o['lead_time_days'] !== '' && $o['lead_time_days'] !== null
                ? (int) $o['lead_time_days']
                : null;
            $o['lead_weeks_label'] = self::formatLeadWeeksLabel($days);
            $o['lead_sort_value'] = $days !== null ? (float) $days : 999999.0;
            $o['brand_card'] = self::resolveBrandFromIblock6((string) ($o['brand'] ?? ''));
            $o['min_order'] = isset($raw['minq']) && is_numeric($raw['minq']) ? max(1, (int) $raw['minq']) : 1;
            $o['order_step'] = isset($raw['eQuantity']) && is_numeric($raw['eQuantity']) ? max(1, (int) $raw['eQuantity']) : 1;
            $o['pack_norm'] = isset($raw['sPack']) && is_numeric($raw['sPack']) ? max(1, (int) $raw['sPack']) : 1;
            $firstRub = null;
            if (!empty($o['price_tiers_rub'])) {
                $sorted = $o['price_tiers_rub'];
                usort($sorted, static fn ($a, $b) => ((int) ($a['qty'] ?? 0)) <=> ((int) ($b['qty'] ?? 0)));
                $firstRub = (float) ($sorted[0]['rub'] ?? 0);
            }
            if ($firstRub === null || $firstRub <= 0) {
                $firstRub = isset($o['unit_price_rub']) && is_numeric($o['unit_price_rub']) ? (float) $o['unit_price_rub'] : 0.0;
            }
            $o['price_sort_value'] = $firstRub;
        }
        unset($o);

        return $offers;
    }

    /**
     * @return array{NAME: string, URL: string, IMG_SRC: string, ID: int}
     */
    public static function resolveBrandFromIblock6(string $brandName): array
    {
        $empty = ['NAME' => '', 'URL' => '', 'IMG_SRC' => '', 'ID' => 0];
        $brandName = trim($brandName);
        if ($brandName === '' || !Loader::includeModule('iblock')) {
            return $empty;
        }

        static $memo = [];
        $key = mb_strtoupper($brandName);
        if (isset($memo[$key])) {
            return $memo[$key];
        }

        $iblockId = self::BRANDS_IBLOCK_ID;
        $codeGuess = self::guessBrandElementCode($brandName);

        $select = ['ID', 'NAME', 'DETAIL_PAGE_URL', 'CODE', 'PREVIEW_PICTURE'];
        $el = null;

        if ($codeGuess !== '') {
            $r = \CIBlockElement::GetList(
                ['SORT' => 'ASC', 'ID' => 'ASC'],
                ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y', '=CODE' => $codeGuess],
                false,
                ['nTopCount' => 1],
                $select
            );
            $el = $r->GetNext();
        }

        if (!$el) {
            $r = \CIBlockElement::GetList(
                ['SORT' => 'ASC', 'ID' => 'ASC'],
                [
                    'IBLOCK_ID' => $iblockId,
                    'ACTIVE' => 'Y',
                    '=NAME' => $brandName,
                ],
                false,
                ['nTopCount' => 1],
                $select
            );
            $el = $r->GetNext();
        }

        if (!$el) {
            $r = \CIBlockElement::GetList(
                ['SORT' => 'ASC', 'ID' => 'ASC'],
                [
                    'IBLOCK_ID' => $iblockId,
                    'ACTIVE' => 'Y',
                    'NAME' => $brandName,
                ],
                false,
                ['nTopCount' => 1],
                $select
            );
            $el = $r->GetNext();
        }

        $csvRows = self::loadBrandCsvByName();
        $csvKey = mb_strtoupper($brandName);
        $csvRow = $csvRows[$csvKey] ?? null;

        if (!$el && is_array($csvRow)) {
            $csvElId = (int) ($csvRow['element_id'] ?? 0);
            if ($csvElId > 0) {
                $r = \CIBlockElement::GetList(
                    ['SORT' => 'ASC', 'ID' => 'ASC'],
                    ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y', 'ID' => $csvElId],
                    false,
                    ['nTopCount' => 1],
                    $select
                );
                $el = $r->GetNext();
            }
        }

        if (!$el) {
            if (is_array($csvRow)) {
                $csvLogo = (string) ($csvRow['logo'] ?? '');
                if ($csvLogo !== '') {
                    $fromCsv = [
                        'NAME' => $brandName,
                        'URL' => '',
                        'IMG_SRC' => $csvLogo,
                        'ID' => 0,
                    ];
                    $memo[$key] = $fromCsv;

                    return $fromCsv;
                }
            }
            $memo[$key] = $empty;

            return $empty;
        }

        $imgSrc = '';
        if (!empty($el['PREVIEW_PICTURE'])) {
            $path = \CFile::GetPath((int) $el['PREVIEW_PICTURE']);
            if (is_string($path) && $path !== '') {
                $imgSrc = $path;
            }
        }

        $out = [
            'NAME' => (string) ($el['NAME'] ?? $brandName),
            'URL' => (string) ($el['DETAIL_PAGE_URL'] ?? ''),
            'IMG_SRC' => $imgSrc,
            'ID' => (int) ($el['ID'] ?? 0),
        ];

        if (is_array($csvRow) && ($csvRow['logo'] ?? '') !== '') {
            $out['IMG_SRC'] = (string) $csvRow['logo'];
        }

        $memo[$key] = $out;

        return $out;
    }

    private static function guessBrandElementCode(string $brandName): string
    {
        $brandName = trim($brandName);
        if ($brandName === '') {
            return '';
        }
        if (class_exists('\CUtil')) {
            $code = (string) \CUtil::translit($brandName, 'ru', [
                'max_len' => 100,
                'change_case' => 'L',
                'replace_space' => '-',
            ]);
            $code = preg_replace('/[^a-z0-9\-]+/', '-', $code);
            $code = trim((string) $code, '-');

            return $code;
        }

        return strtolower(preg_replace('/[^a-z0-9]+/i', '-', $brandName));
    }

    /**
     * @return list<array{qty: int, price: float}>
     */
    private static function buildPriceTiersFromRaw(array $raw, ?float $fallbackUnit): array
    {
        $breaks = $raw['priceBreak'] ?? $raw['price_break'] ?? null;
        $out = [];
        if (is_array($breaks)) {
            foreach ($breaks as $b) {
                if (!is_array($b)) {
                    continue;
                }
                $q = (int) ($b['quantity'] ?? $b['qty'] ?? $b['Quantity'] ?? 0);
                $p = null;
                if (isset($b['price']) && is_numeric($b['price'])) {
                    $p = (float) $b['price'];
                } elseif (isset($b['Price']) && is_numeric($b['Price'])) {
                    $p = (float) $b['Price'];
                } elseif (isset($b['summ']) && is_numeric($b['summ']) && $q > 0) {
                    $p = (float) $b['summ'] / $q;
                }
                if ($q > 0 && $p !== null && $p > 0) {
                    $out[] = ['qty' => $q, 'price' => $p];
                }
            }
        }
        usort($out, static fn ($a, $b) => $a['qty'] <=> $b['qty']);
        if ($out === [] && $fallbackUnit !== null && $fallbackUnit > 0) {
            $out[] = ['qty' => 1, 'price' => $fallbackUnit];
        }

        return $out;
    }

    /**
     * @param list<array{qty: int, price: float}> $tiers
     * @return list<array{qty: int, rub: float}>
     */
    private static function convertPriceTiersToRub(array $tiers, string $cur): array
    {
        $cur = strtoupper($cur);
        $result = [];
        foreach ($tiers as $t) {
            $p = (float) $t['price'];
            $rub = self::convertToRubByCbr($p, $cur);
            $result[] = ['qty' => (int) $t['qty'], 'rub' => $rub];
        }

        return $result;
    }

    /**
     * @return array<string, float|string>
     */
    private static function getCbrRatesToRub(): array
    {
        static $memo = null;
        if (is_array($memo)) {
            return $memo;
        }

        $cache = Cache::createInstance();
        $cacheId = 'daily_json_rates_v3_' . date('Y-m-d');
        if ($cache->initCache(self::CBR_CACHE_TTL, $cacheId, self::CBR_CACHE_DIR)) {
            $vars = $cache->getVars();
            if (is_array($vars['rates'] ?? null)) {
                $memo = $vars['rates'];
                return $memo;
            }
        }

        $rates = ['RUB' => 1.0, '__DATE' => ''];
        $http = new HttpClient([
            'socketTimeout' => 8,
            'streamTimeout' => 8,
        ]);

        $xml = $http->get('https://www.cbr.ru/scripts/XML_daily.asp');
        if ($xml !== false && (int)$http->getStatus() === 200) {
            try {
                $sx = @simplexml_load_string($xml);
                if ($sx !== false) {
                    $dateAttr = (string)($sx['Date'] ?? '');
                    if ($dateAttr !== '') {
                        $rates['__DATE'] = $dateAttr;
                    }
                    foreach ($sx->Valute as $valute) {
                        $code = strtoupper(trim((string)($valute->CharCode ?? '')));
                        if (!in_array($code, ['USD', 'EUR'], true)) {
                            continue;
                        }
                        $valueRaw = trim((string)($valute->Value ?? ''));
                        $nominalRaw = trim((string)($valute->Nominal ?? '1'));
                        $value = (float)str_replace(',', '.', $valueRaw);
                        $nominal = (float)str_replace(',', '.', $nominalRaw);
                        if ($value > 0 && $nominal > 0) {
                            $rates[$code] = $value / $nominal;
                        }
                    }
                }
            } catch (\Throwable $e) {
            }
        }

        if ((float)($rates['USD'] ?? 0) <= 0) {
            $raw = $http->get('https://www.cbr-xml-daily.ru/daily_json.js');
            if ($raw !== false && (int)$http->getStatus() === 200) {
                $json = json_decode($raw, true);
                if (is_array($json['Valute'] ?? null)) {
                    $isoDate = (string)($json['Date'] ?? '');
                    if ($isoDate !== '') {
                        $ts = strtotime($isoDate);
                        if ($ts !== false) {
                            $rates['__DATE'] = date('d.m.Y', $ts);
                        }
                    }
                    foreach (['USD', 'EUR'] as $code) {
                        $value = $json['Valute'][$code]['Value'] ?? null;
                        if (is_numeric($value) && (float)$value > 0) {
                            $rates[$code] = (float)$value;
                        }
                    }
                }
            }
        }

        if ($cache->startDataCache()) {
            $cache->endDataCache(['rates' => $rates]);
        }

        $memo = $rates;
        return $memo;
    }

    public static function getUsdToRubByCbr(): float
    {
        $rates = self::getCbrRatesToRub();
        $usd = (float)($rates['USD'] ?? 0);
        return $usd > 0 ? $usd : self::CBR_USD_FALLBACK;
    }

    public static function getCbrRateDate(): string
    {
        $rates = self::getCbrRatesToRub();
        return trim((string)($rates['__DATE'] ?? ''));
    }

    public static function convertToRubByCbr(float $amount, string $currency): float
    {
        $cur = strtoupper(trim($currency));
        if ($cur === '' || $cur === 'RUB' || $cur === 'RUR') {
            return $amount;
        }

        $rates = self::getCbrRatesToRub();
        if (isset($rates[$cur]) && (float)$rates[$cur] > 0) {
            return $amount * (float)$rates[$cur];
        }
        if ($cur === 'USD') {
            return $amount * self::CBR_USD_FALLBACK;
        }
        return $amount; // Не уходим в курс Битрикс
    }

    public static function formatLeadWeeksLabel(?int $days): string
    {
        if ($days === null || $days <= 0) {
            return '—';
        }
        $low = max(1, (int) floor($days / 7));
        $high = max(1, (int) ceil($days / 7));
        if ($low === $high) {
            return $low === 1 ? '1 неделя' : $low . ' нед.';
        }

        return $low . '–' . $high . ' нед.';
    }

    /**
     * HTML блока цен: первая ступень (мин. кол-во) — жирная.
     *
     * @param list<array{qty: int, rub: float}> $tiers
     */
    /**
     * Ступени в валюте поставщика (как на getchips.ru: 105.8 x 1).
     *
     * @param list<array{qty: int, price: float}> $tiers
     */
    public static function formatPriceTiersSourceHtml(array $tiers, string $currency = 'USD'): string
    {
        if ($tiers === []) {
            return '—';
        }
        $cur = strtoupper(trim($currency));
        if ($cur === '' || $cur === '1') {
            $cur = 'USD';
        }
        $sorted = $tiers;
        usort($sorted, static fn ($a, $b) => ((int) ($a['qty'] ?? 0)) <=> ((int) ($b['qty'] ?? 0)));
        $minQty = (int) $sorted[0]['qty'];
        $lines = [];
        foreach ($sorted as $t) {
            $q = (int) $t['qty'];
            $srcPrice = (float) $t['price'];
            if ($q <= 0 || $srcPrice <= 0) {
                continue;
            }
            $rub = self::convertToRubByCbr($srcPrice, $cur);
            $priceStr = rtrim(rtrim(number_format($srcPrice, 4, '.', ''), '0'), '.');
            $isPrimary = ($q === $minQty);
            $line = $priceStr . ' x ' . $q;
            $inner = $isPrimary
                ? '<strong>' . htmlspecialchars($line, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</strong>'
                : htmlspecialchars($line, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $lines[] = '<span class="getchips-offers__price-tier js-getchips-price-tier" data-tier-qty="' . $q
                . '" data-price-rub="' . htmlspecialchars((string) $rub, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . '" data-price-src="' . htmlspecialchars((string) $srcPrice, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . '" data-src-currency="' . htmlspecialchars($cur, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . '">' . $inner . '</span>';
        }

        return $lines !== [] ? implode('<br>', $lines) : '—';
    }

    public static function formatPriceTiersHtml(array $tiers): string
    {
        if ($tiers === []) {
            return '—';
        }
        $sorted = $tiers;
        usort($sorted, static fn ($a, $b) => ((int) ($a['qty'] ?? 0)) <=> ((int) ($b['qty'] ?? 0)));
        $minQty = (int) $sorted[0]['qty'];
        $lines = [];
        foreach ($sorted as $t) {
            $q = (int) $t['qty'];
            $rub = (float) $t['rub'];
            $priceStr = self::formatRub($rub);
            $isPrimary = ($q === $minQty);
            $line = '× ' . $q . ' — ' . $priceStr;
            $inner = $isPrimary ? '<strong>' . htmlspecialchars($line, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</strong>' : htmlspecialchars($line, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $lines[] = '<span class="getchips-offers__price-tier js-getchips-price-tier" data-tier-qty="' . $q
                . '" data-price-rub="' . htmlspecialchars((string) $rub, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . '">' . $inner . '</span>';
        }

        return implode('<br>', $lines);
    }

    /** Цена за единицу в ₽ для заказа qty по ступеням (max ступень с breakpoint ≤ qty). */
    public static function unitRubForOrderQty(array $tiersRub, int $qty): ?float
    {
        if ($tiersRub === []) {
            return null;
        }
        $sorted = $tiersRub;
        usort($sorted, static fn ($a, $b) => ((int) ($a['qty'] ?? 0)) <=> ((int) ($b['qty'] ?? 0)));
        $best = null;
        foreach ($sorted as $t) {
            $q = (int) ($t['qty'] ?? 0);
            if ($q <= $qty) {
                $best = (float) ($t['rub'] ?? 0);
            }
        }
        if ($best === null) {
            $best = (float) ($sorted[0]['rub'] ?? 0);
        }

        return $best > 0 ? $best : null;
    }

    /**
     * @return list<array{qty: int, rub: float}>
     */
    public static function normalizeTiersRubFromJson(string $json): array
    {
        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return [];
        }
        $out = [];
        foreach ($decoded as $row) {
            if (!is_array($row)) {
                continue;
            }
            $q = isset($row['qty']) ? (int) $row['qty'] : 0;
            $r = isset($row['rub']) && is_numeric($row['rub']) ? (float) $row['rub'] : null;
            if ($q > 0 && $r !== null && $r > 0) {
                $out[] = ['qty' => $q, 'rub' => $r];
            }
        }

        return $out;
    }

    /**
     * Выставить количество товара в каталоге по максимальному stock из ответа Getchips (для отображения «В наличии» без складов ИБ).
     */
    public static function syncCatalogQuantityFromSupplierOffers(int $productId, string $article): void
    {
        if ($productId <= 0 || !Loader::includeModule('catalog')) {
            return;
        }
        $article = self::normalizeArticle($article);
        if ($article === '' || mb_strlen($article) < 3) {
            return;
        }
        $offers = self::fetchOffersCached($article, 1);
        if ($offers === []) {
            return;
        }
        $offers = self::filterOffersExcludeLvtMarket($offers);
        $max = 0;
        foreach ($offers as $o) {
            if (!is_array($o)) {
                continue;
            }
            $s = isset($o['stock']) ? (int) $o['stock'] : 0;
            if ($s > $max) {
                $max = $s;
            }
        }
        if ($max <= 0) {
            return;
        }
        $max = min($max, 999999);
        $fields = [
            'QUANTITY' => $max,
            'QUANTITY_TRACE' => 'N',
            'AVAILABLE' => 'Y',
            'CAN_BUY_ZERO' => 'Y',
        ];
        $hasRow = false;
        $db = \CCatalogProduct::GetList([], ['ID' => $productId], false, ['nTopCount' => 1], ['ID']);
        if ($db && ($db->Fetch())) {
            $hasRow = true;
        }
        if ($hasRow) {
            \CCatalogProduct::Update($productId, $fields);

            return;
        }
        $typeProduct = class_exists(\Bitrix\Catalog\ProductTable::class)
            ? (int) \Bitrix\Catalog\ProductTable::TYPE_PRODUCT
            : 1;
        \CCatalogProduct::Add(array_merge([
            'ID' => $productId,
            'TYPE' => $typeProduct,
        ], $fields));
    }
}
