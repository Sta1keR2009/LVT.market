<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'getchips' => [
        /** Совпадает с GETCHIPS_INTERNAL_API_KEY в Bitrix getchips/.env — заголовок X-Getchips-Internal-Key */
        'internal_api_key' => env('GETCHIPS_INTERNAL_API_KEY', ''),
        'endpoint' => env('GETCHIPS_ENDPOINT', 'https://api.client-service.getchips.ru/client/api/gh/v1/search/partnumber'),
        'token' => env('GETCHIPS_TOKEN', ''),
        /** Ответ API часто 6–15 с; 10 с давало обрывы на проде */
        'timeout' => (int) env('GETCHIPS_TIMEOUT', 25),
        'retries' => (int) env('GETCHIPS_RETRIES', 1),
        /** Кеш сырых rows Getchips (сек.) для fallback при 429/таймаутах. */
        'cache_ttl' => (int) env('GETCHIPS_CACHE_TTL', 604800),
        /** Попытки при 429 от api.client-service.getchips.ru */
        'rate_limit_attempts' => (int) env('GETCHIPS_RATE_LIMIT_ATTEMPTS', 2),
        /** Паузы между попытками (секунды через запятую) */
        'rate_limit_backoff_seconds' => env('GETCHIPS_RATE_LIMIT_BACKOFF_SECONDS', '2,5'),
        /** Base URL for Aspro Lite CSS (main site template), used by search UI v2 */
        'aspro_assets_base' => rtrim(env('GETCHIPS_ASPRO_ASSETS_BASE', 'https://lvt.market/bitrix/templates/aspro-lite'), '/'),
        'pricing' => [
            /** Наценка к цене после нормализации в USD, % */
            'markup_percent' => (float) env('GETCHIPS_MARKUP_PERCENT', -9),
            /** Курс USD → RUB для пересчёта отображения */
            'usd_to_rub' => (float) env('GETCHIPS_USD_TO_RUB', 92.5),
            /** Валюта таблицы по умолчанию: rub | usd */
            'default_currency' => strtolower((string) env('GETCHIPS_DISPLAY_CURRENCY', 'rub')) === 'usd' ? 'usd' : 'rub',
        ],
    ],

    'lvt_market' => [
        'base_url' => env('LVT_MARKET_BASE_URL', 'https://lvt.market'),
        'search_path' => env('LVT_MARKET_SEARCH_PATH', '/api/search/components'),
        'search_create_path' => env('LVT_MARKET_SEARCH_CREATE_PATH', '/api/search/components'),
        'token' => env('LVT_MARKET_TOKEN', ''),
        'timeout' => (int) env('LVT_MARKET_TIMEOUT', 8),
        'retries' => (int) env('LVT_MARKET_RETRIES', 1),
        'stub' => (bool) env('LVT_MARKET_STUB', true),
    ],

    'mouser' => [
        'base_url' => env('MOUSER_BASE_URL', 'https://api.mouser.com/api/v1'),
        'search_part_path' => env('MOUSER_SEARCH_PART_PATH', '/search/partnumber'),
        'api_key' => env('MOUSER_API_KEY', ''),
        'timeout' => (int) env('MOUSER_TIMEOUT', 12),
        'retries' => (int) env('MOUSER_RETRIES', 1),
    ],

];
