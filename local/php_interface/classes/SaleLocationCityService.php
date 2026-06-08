<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Service\GeoIp\Manager as GeoIpManager;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Sale\Location\Admin\LocationHelper;
use Bitrix\Sale\Location\LocationTable;
use Bitrix\Sale\Location\Search\Finder;
use Bitrix\Sale\Location\SiteLocationTable;

/**
 * Подсказки городов по базе Sale Locations (РФ + др.) и привязка к cookie Aspro (current_region).
 */
class SaleLocationCityService
{
    public const DEFAULT_CITY = 'Москва';
    private const COOKIE_SALE_CODE = 'lvt_sale_location_code';
    private const COOKIE_GEO_DONE = 'lvt_geo_city_done';
    /** Подпись города в шапке/меню, если Sale-локация не совпала с NAME элемента региональности */
    private const COOKIE_DISPLAY = 'lvt_display_city';
    private const SYPEX_KEY = 'v7Q9z';

    public static function resolveCityForDelivery(?string $inputCity = null): array
    {
        $city = trim((string)$inputCity);
        if ($city !== '') {
            return ['city' => $city, 'source' => 'request'];
        }

        $display = trim(rawurldecode((string)($_COOKIE[self::COOKIE_DISPLAY] ?? '')));
        if ($display !== '') {
            return ['city' => $display, 'source' => 'display_cookie'];
        }

        $saleCode = trim((string)($_COOKIE[self::COOKIE_SALE_CODE] ?? ''));
        if ($saleCode !== '') {
            $saleCity = self::getCityBySaleLocationCode($saleCode);
            if ($saleCity !== '') {
                return ['city' => $saleCity, 'source' => 'sale_cookie'];
            }
        }

        $ip = self::getRealIp();
        if ($ip !== '' && $ip !== '127.0.0.1') {
            $geoMeta = self::fetchCityByGeoProviders($ip);
            $geoCity = (string)($geoMeta['city'] ?? '');
            $provider = (string)($geoMeta['provider'] ?? '');
            if ($geoCity !== '') {
                return ['city' => $geoCity, 'source' => 'ip_geo', 'ip' => $ip, 'provider' => $provider];
            }
        }

        $currentRegionCity = self::getCityNameFromCurrentRegionCookie();
        if ($currentRegionCity !== '') {
            return ['city' => $currentRegionCity, 'source' => 'current_region'];
        }

        return ['city' => self::DEFAULT_CITY, 'source' => 'default'];
    }

    public static function ensureLvtCookiesFromCurrentRegion(): array
    {
        $hasDisplay = !empty($_COOKIE[self::COOKIE_DISPLAY]);
        $hasSale = !empty($_COOKIE[self::COOKIE_SALE_CODE]);
        if ($hasDisplay && $hasSale) {
            return ['ok' => false, 'skip' => 'already_synced'];
        }

        $regionCity = '';
        if ($hasDisplay) {
            $regionCity = trim(rawurldecode((string)$_COOKIE[self::COOKIE_DISPLAY]));
        } else {
            $regionCity = self::getCityNameFromCurrentRegionCookie();
        }
        if ($regionCity === '') {
            return ['ok' => false, 'skip' => 'no_current_region_city'];
        }

        $saleCode = trim((string)($_COOKIE[self::COOKIE_SALE_CODE] ?? ''));
        if ($saleCode === '') {
            $hits = self::suggest($regionCity, 1);
            if (!empty($hits[0]['code'])) {
                $saleCode = (string)$hits[0]['code'];
            }
        }

        $opt = self::cookieOptions();
        $changed = false;
        if (!$hasDisplay) {
            $encCity = rawurlencode($regionCity);
            setcookie(self::COOKIE_DISPLAY, $encCity, $opt);
            $_COOKIE[self::COOKIE_DISPLAY] = $encCity;
            $changed = true;
        }

        if (!$hasSale && $saleCode !== '') {
            setcookie(self::COOKIE_SALE_CODE, $saleCode, $opt);
            $_COOKIE[self::COOKIE_SALE_CODE] = $saleCode;
            $changed = true;
        }

        if (!$changed) {
            return ['ok' => false, 'skip' => 'nothing_to_sync'];
        }

        return [
            'ok' => true,
            'city' => $regionCity,
            'saleCode' => $saleCode,
            'source' => 'current_region_sync',
        ];
    }

    public static function suggest(string $phrase, int $limit = 25): array
    {
        $phrase = trim($phrase);
        if (mb_strlen($phrase) < 2) {
            return [];
        }

        if (!Loader::includeModule('sale')) {
            return [];
        }

        $filter = [
            '=PHRASE' => $phrase,
            '=NAME.LANGUAGE_ID' => LANGUAGE_ID,
        ];
        if (SiteLocationTable::checkLinkUsageAny(SITE_ID)) {
            $filter['=SITE_ID'] = SITE_ID;
        }

        $params = [
            'filter' => $filter,
            'select' => [
                'ID',
                'CODE',
                'NAME_NAME' => 'NAME.NAME',
            ],
            'limit' => max(1, min(50, $limit)),
        ];

        $result = Finder::find($params, ['USE_INDEX' => true, 'USE_ORM' => true, 'FALLBACK_TO_NOINDEX_ON_NOTFOUND' => true]);
        $out = [];
        while ($row = $result->fetch()) {
            $id = (int)($row['ID'] ?? 0);
            $code = (string)($row['CODE'] ?? '');
            if ($id <= 0 || $code === '') {
                continue;
            }
            $label = LocationHelper::getLocationStringById($id, [
                'LANGUAGE_ID' => LANGUAGE_ID,
                'DELIMITER' => ', ',
                'INVERSE' => false,
            ]);
            if ($label === '') {
                $label = (string)($row['NAME_NAME'] ?? $code);
            }
            $out[] = [
                'label' => $label,
                'code' => $code,
                'id' => $id,
                'saleCode' => $code,
            ];
        }

        return $out;
    }

    public static function applyByLocationCode(string $code): array
    {
        $code = trim($code);
        if ($code === '' || !Loader::includeModule('sale')) {
            return ['ok' => false, 'error' => 'empty_code'];
        }

        if (!Loader::includeModule('aspro.lite')) {
            return ['ok' => false, 'error' => 'no_aspro'];
        }

        include_once $_SERVER['DOCUMENT_ROOT'] . SITE_TEMPLATE_PATH . '/vendor/php/solution.php';

        $row = LocationTable::getList([
            'filter' => ['=CODE' => $code],
            'select' => ['ID'],
            'limit' => 1,
        ])->fetch();

        if (empty($row['ID'])) {
            return ['ok' => false, 'error' => 'location_not_found'];
        }

        $locId = (int)$row['ID'];
        $label = LocationHelper::getLocationStringById($locId, [
            'LANGUAGE_ID' => LANGUAGE_ID,
            'DELIMITER' => ', ',
            'INVERSE' => true,
        ]);
        $cityName = self::extractCityNameFromPath($label);

        $regions = \TSolution\Regionality::getRegions();
        $regionId = 0;
        if (is_array($regions) && $regions) {
            $regionId = self::matchAsproRegionId($cityName, $regions);
            if ($regionId <= 0) {
                $regionId = self::defaultAsproRegionId($regions);
            }
        }

        $display = $cityName !== '' ? $cityName : self::extractCityNameFromPath($label);
        self::writeCookies($regionId > 0 ? (string)$regionId : '', $code, $display);

        return [
            'ok' => true,
            'regionId' => $regionId,
            'label' => $label,
            'city' => $cityName,
            'displayCity' => $display,
        ];
    }

    public static function tryApplyFromIp(): array
    {
        if (($_COOKIE['lvt_city_confirmed'] ?? '') === '1') {
            return ['ok' => false, 'skip' => 'city_confirmed'];
        }
        if (!empty($_COOKIE[self::COOKIE_GEO_DONE]) && !empty($_COOKIE[self::COOKIE_DISPLAY])) {
            return ['ok' => false, 'skip' => 'geo_done'];
        }

        $ip = self::getRealIp();
        if ($ip === '' || $ip === '127.0.0.1') {
            return ['ok' => false, 'skip' => 'no_ip'];
        }

        $geoMeta = self::fetchCityByGeoProviders($ip);
        $city = (string)($geoMeta['city'] ?? '');
        if ($city === '') {
            return ['ok' => false, 'skip' => 'no_geo_city', 'ip' => $ip];
        }

        $hits = self::suggest($city, 10);
        if (!$hits) {
            return ['ok' => false, 'skip' => 'no_finder_hit', 'city' => $city, 'ip' => $ip];
        }

        $best = self::pickBestSuggestHit($city, $hits);
        if (!$best) {
            return ['ok' => false, 'skip' => 'no_finder_hit', 'city' => $city, 'ip' => $ip];
        }

        $result = self::applyByLocationCode((string)$best['code']);
        if (!empty($result['ok'])) {
            $result['source'] = 'ip_geo';
            $result['provider'] = (string)($geoMeta['provider'] ?? '');
            $result['ip'] = $ip;
        }

        return $result;
    }

    public static function markGeoDoneCookie(): void
    {
        $opt = self::cookieOptions();
        setcookie(self::COOKIE_GEO_DONE, '1', $opt);
        $_COOKIE[self::COOKIE_GEO_DONE] = '1';
    }

    private static function writeCookies(string $regionId, string $saleCode, string $displayCity = ''): void
    {
        $opt = self::cookieOptions();
        if ($regionId !== '') {
            setcookie('current_region', $regionId, $opt);
            $_COOKIE['current_region'] = $regionId;
        }

        setcookie(self::COOKIE_SALE_CODE, $saleCode, $opt);
        $_COOKIE[self::COOKIE_SALE_CODE] = $saleCode;

        if ($displayCity !== '') {
            $enc = rawurlencode($displayCity);
            setcookie(self::COOKIE_DISPLAY, $enc, $opt);
            $_COOKIE[self::COOKIE_DISPLAY] = $enc;
        }
    }

    private static function cookieOptions(): array
    {
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        return [
            'expires' => time() + 86400 * 365,
            'path' => '/',
            'secure' => $https,
            'httponly' => false,
            'samesite' => 'Lax',
        ];
    }

    private static function extractCityNameFromPath(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '';
        }
        $parts = array_map('trim', explode(',', $path));
        return (string)($parts[0] ?? '');
    }

    private static function extractCityNameFromSuggestLabel(string $label): string
    {
        $label = trim($label);
        if ($label === '') {
            return '';
        }
        $parts = array_map('trim', explode(',', $label));
        return (string)end($parts);
    }

    private static function pickBestSuggestHit(string $geoCity, array $hits): ?array
    {
        if (!$hits) {
            return null;
        }

        $geoNorm = mb_strtolower(trim($geoCity));
        if ($geoNorm === '') {
            return $hits[0];
        }

        foreach ($hits as $hit) {
            $name = mb_strtolower(self::extractCityNameFromSuggestLabel((string)($hit['label'] ?? '')));
            if ($name === $geoNorm) {
                return $hit;
            }
        }

        foreach ($hits as $hit) {
            $name = self::extractCityNameFromSuggestLabel((string)($hit['label'] ?? ''));
            $nameNorm = mb_strtolower($name);
            if ($nameNorm === '' || $nameNorm === 'россия') {
                continue;
            }
            if (mb_stripos($name, $geoCity) !== false || mb_stripos($geoCity, $name) !== false) {
                return $hit;
            }
        }

        return $hits[0];
    }

    private static function matchAsproRegionId(string $city, array $regions): int
    {
        $city = mb_strtolower(trim($city));
        if ($city === '') {
            return 0;
        }
        foreach ($regions as $r) {
            $n = mb_strtolower(trim((string)($r['NAME'] ?? '')));
            if ($n !== '' && $n === $city) {
                return (int)$r['ID'];
            }
        }
        foreach ($regions as $r) {
            $name = (string)($r['NAME'] ?? '');
            if ($name !== '' && mb_stripos($name, $city) !== false) {
                return (int)$r['ID'];
            }
        }
        return 0;
    }

    private static function defaultAsproRegionId(array $regions): int
    {
        foreach ($regions as $r) {
            if (!empty($r['PROPERTY_DEFAULT_VALUE']) && $r['PROPERTY_DEFAULT_VALUE'] === 'Y') {
                return (int)$r['ID'];
            }
        }
        $first = reset($regions);
        return $first ? (int)$first['ID'] : 0;
    }

    private static function getRealIp(): string
    {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_TRUE_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'] as $key) {
            $val = trim((string)($_SERVER[$key] ?? ''));
            if ($val !== '') {
                $parts = explode(',', $val);
                return trim($parts[0]);
            }
        }
        return '';
    }

    private static function fetchCityByGeoProviders(string $ip): array
    {
        $key = self::getSypexKey();
        $dadataCity = self::fetchCityByDadata($ip);
        if ($dadataCity !== '') {
            return ['city' => $dadataCity, 'provider' => 'dadata'];
        }

        $sypexCity = '';
        $bitrixCity = '';
        if ($key !== '') {
            $url = 'https://api.sypexgeo.net/' . $key . '/json/' . rawurlencode($ip);
            $json = @file_get_contents($url);
            if ($json) {
                $data = json_decode($json, true);
                $sypexCity = self::normalizeGeoCityName(trim((string)($data['city']['name_ru'] ?? '')));
            }
        }
        if ($sypexCity !== '') {
            return ['city' => $sypexCity, 'provider' => 'sypex'];
        }

        $bitrixCity = self::fetchCityByBitrixGeo($ip);
        if ($bitrixCity !== '') {
            return ['city' => $bitrixCity, 'provider' => 'bitrix_geo'];
        }

        return ['city' => '', 'provider' => 'none'];
    }

    private static function getCityBySaleLocationCode(string $code): string
    {
        if ($code === '' || !Loader::includeModule('sale')) {
            return '';
        }

        $path = (string)LocationHelper::getLocationStringByCode($code, [
            'LANGUAGE_ID' => defined('LANGUAGE_ID') ? LANGUAGE_ID : 'ru',
            'DELIMITER' => ', ',
            'INVERSE' => true,
        ]);
        $path = trim($path);
        if ($path === '') {
            return '';
        }

        return self::extractCityNameFromPath($path);
    }

    private static function getCityNameFromCurrentRegionCookie(): string
    {
        $regionId = (int)($_COOKIE['current_region'] ?? 0);
        if ($regionId <= 0 || !Loader::includeModule('aspro.lite')) {
            return '';
        }

        $regions = \TSolution\Regionality::getRegions();
        if (!is_array($regions) || !$regions) {
            return '';
        }

        foreach ($regions as $region) {
            if ((int)($region['ID'] ?? 0) === $regionId) {
                return trim((string)($region['NAME'] ?? ''));
            }
        }

        return '';
    }

    private static function getSypexKey(): string
    {
        $key = trim((string)Option::get('aspro.lite', 'SYPEX_GEO_API_KEY', ''));
        if ($key === '') {
            $key = trim((string)Option::get('main', 'sypex_geo_api_key', ''));
        }

        return $key !== '' ? $key : self::SYPEX_KEY;
    }

    private static function fetchCityByDadata(string $ip): string
    {
        $token = self::getDadataToken();
        if ($token === '') {
            return '';
        }

        $secret = self::getDadataSecret();
        $client = new HttpClient(['socketTimeout' => 3, 'streamTimeout' => 3]);
        $client->setHeader('Content-Type', 'application/json', true);
        $client->setHeader('Accept', 'application/json', true);
        $client->setHeader('Authorization', 'Token ' . $token, true);
        if ($secret !== '') {
            $client->setHeader('X-Secret', $secret, true);
        }

        $json = $client->post(
            'https://suggestions.dadata.ru/suggestions/api/4_1/rs/iplocate/address',
            json_encode(['ip' => $ip], JSON_UNESCAPED_UNICODE)
        );
        if (!$json) {
            return '';
        }

        $data = json_decode($json, true);
        return self::normalizeGeoCityName(trim((string)($data['location']['data']['city'] ?? '')));
    }

    private static function getDadataToken(): string
    {
        $key = trim((string)Option::get('catapulto.delivery', 'dadataapikey', ''));
        if ($key === '') {
            $key = trim((string)Option::get('main', 'dadata_api_key', ''));
        }
        return $key;
    }

    private static function getDadataSecret(): string
    {
        $secret = trim((string)Option::get('catapulto.delivery', 'dadatasecret', ''));
        if ($secret === '') {
            $secret = trim((string)Option::get('main', 'dadata_secret', ''));
        }
        return $secret;
    }

    private static function fetchCityByBitrixGeo(string $ip): string
    {
        try {
            $result = GeoIpManager::getDataResult($ip, defined('LANGUAGE_ID') ? LANGUAGE_ID : 'ru');
            $data = $result ? $result->getGeoData() : null;
            $city = '';
            if (is_object($data) && isset($data->cityName)) {
                $city = self::normalizeGeoCityName(trim((string)$data->cityName));
            } elseif (is_array($data)) {
                $city = self::normalizeGeoCityName(trim((string)($data['cityName'] ?? '')));
            }
            return $city;
        } catch (\Throwable $e) {
            return '';
        }
    }

    private static function normalizeGeoCityName(string $city): string
    {
        $city = trim($city);
        if ($city === '') {
            return '';
        }

        $map = [
            'saint petersburg' => 'Санкт-Петербург',
            'st. petersburg' => 'Санкт-Петербург',
            'st petersburg' => 'Санкт-Петербург',
            'moscow' => 'Москва',
            'nizhny novgorod' => 'Нижний Новгород',
            'yekaterinburg' => 'Екатеринбург',
            'ekaterinburg' => 'Екатеринбург',
            'novosibirsk' => 'Новосибирск',
        ];

        $key = mb_strtolower($city);
        return $map[$key] ?? $city;
    }

}
