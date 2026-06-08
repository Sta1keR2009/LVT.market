<?php
/**
 * Прокси для запросов к api.telegram.org (санкции / блокировки РФ).
 * TELEGRAM_PROXY_URL=socks5://user:pass@host:port  или  http://user:pass@host:port
 */
class LvtTelegramProxy
{
    public static function applyToCurl($ch): void
    {
        $url = getenv('TELEGRAM_PROXY_URL') ?: '';
        if ($url === '') {
            return;
        }

        $p = parse_url($url);
        if (!$p || empty($p['host'])) {
            return;
        }

        $scheme = strtolower((string)($p['scheme'] ?? 'socks5'));
        $host = $p['host'];
        $port = (int)($p['port'] ?? ($scheme === 'http' || $scheme === 'https' ? 8080 : 1080));
        $proxy = $host . ':' . $port;

        if ($scheme === 'http' || $scheme === 'https') {
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        } else {
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        }
        curl_setopt($ch, CURLOPT_PROXY, $proxy);

        if (!empty($p['user'])) {
            curl_setopt(
                $ch,
                CURLOPT_PROXYUSERPWD,
                $p['user'] . ':' . ($p['pass'] ?? '')
            );
        }
    }
}
