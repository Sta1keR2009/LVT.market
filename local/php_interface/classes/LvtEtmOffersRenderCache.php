<?php

use Bitrix\Main\Data\Cache;

/**
 * Кеш JSON-ответа /local/api/lvt_etm_offers_render.php (HTML + цены ETM).
 */
class LvtEtmOffersRenderCache
{
    private const CACHE_DIR = '/lvt/etm_offers_render/';
    private const CACHE_TTL = 172800; // 2 суток
    private const CACHE_VER = 5;

    /**
     * @return array<string, mixed>|null
     */
    public static function get(int $elementId, string $layout): ?array
    {
        if ($elementId <= 0 || $layout === '') {
            return null;
        }
        $cache = Cache::createInstance();
        if ($cache->initCache(self::CACHE_TTL, self::cacheKey($elementId, $layout), self::CACHE_DIR)) {
            $vars = $cache->getVars();
            $payload = $vars['payload'] ?? null;
            if (is_array($payload)) {
                return $payload;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function set(int $elementId, string $layout, array $payload): void
    {
        if ($elementId <= 0 || $layout === '') {
            return;
        }
        $cache = Cache::createInstance();
        $key = self::cacheKey($elementId, $layout);
        if ($cache->initCache(self::CACHE_TTL, $key, self::CACHE_DIR)) {
            return;
        }
        $cache->startDataCache();
        $cache->endDataCache(['payload' => $payload]);
    }

    private static function cacheKey(int $elementId, string $layout): string
    {
        return 'v' . self::CACHE_VER . '_' . $elementId . '_' . $layout;
    }
}
