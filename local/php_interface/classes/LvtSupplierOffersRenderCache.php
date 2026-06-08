<?php

use Bitrix\Main\Data\Cache;

/**
 * Кеш JSON-ответа /local/api/lvt_supplier_offers_render.php (HTML + диагностика).
 */
class LvtSupplierOffersRenderCache
{
    private const CACHE_DIR = '/lvt/supplier_offers_render/';
    private const CACHE_TTL = 300; // 2 суток
    private const CACHE_VER = 7;

    /**
     * @return array<string, mixed>|null
     */
    public static function get(int $elementId, string $source): ?array
    {
        if ($elementId <= 0 || $source === '') {
            return null;
        }
        $cache = Cache::createInstance();
        if ($cache->initCache(self::CACHE_TTL, self::cacheKey($elementId, $source), self::CACHE_DIR)) {
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
    public static function set(int $elementId, string $source, array $payload): void
    {
        if ($elementId <= 0 || $source === '') {
            return;
        }
        $cache = Cache::createInstance();
        $key = self::cacheKey($elementId, $source);
        if ($cache->initCache(self::CACHE_TTL, $key, self::CACHE_DIR)) {
            return;
        }
        $cache->startDataCache();
        $cache->endDataCache(['payload' => $payload]);
    }

    private static function cacheKey(int $elementId, string $source): string
    {
        return 'v' . self::CACHE_VER . '_' . $elementId . '_' . $source;
    }
}
