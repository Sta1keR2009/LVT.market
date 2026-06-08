<?php

/**
 * Определение обходчиков / поисковых ботов, чтобы не тратить внешние лимиты Getchips API.
 */
final class LvtSupplierTrafficHelper
{
    /** Фрагменты User-Agent типичных поисковых и мониторинговых ботов */
    private const UA_BOT_MARKERS = [
        'googlebot', 'google-inspectiontool', 'googleother', 'adsbot-google', 'mediapartners-google',
        'bingbot', 'bingpreview', 'msnbot', 'slurp', 'duckduckbot', 'baiduspider', 'yandexbot', 'yandex.com/bots',
        'mail.ru_bot', 'mailru/', 'sogou', 'exabot', 'facebot', 'facebookexternalhit', 'ia_archiver',
        'ahrefsbot', 'semrushbot', 'dotbot', 'petalbot', 'bytespider', 'archive.org_bot', 'crawler', 'spider',
    ];

    public static function isSearchEngineOrCrawlerBot(?string $userAgent = null): bool
    {
        if (\PHP_SAPI === 'cli') {
            return false;
        }
        if ($userAgent === null) {
            $userAgent = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
        }
        $ua = strtolower($userAgent);
        if ($ua === '') {
            return false;
        }
        foreach (self::UA_BOT_MARKERS as $m) {
            if (strpos($ua, $m) !== false) {
                return true;
            }
        }

        return false;
    }
}
