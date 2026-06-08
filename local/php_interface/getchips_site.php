<?php

/**
 * Getchips ↔ Bitrix.
 *
 * Ключ API: getchips/.env → GETCHIPS_INTERNAL_API_KEY (этот файл читает .env с DOCUMENT_ROOT/getchips/.env).
 *
 * Товар-заглушка для корзины (обязательно для кнопки «В корзину»):
 * 1) В каталоге создайте простой товар (не SKU-родитель), например «Поставка Getchips».
 * 2) Включите доступность к покупке; при необходимости «можно купить при отсутствии на складе».
 * 3) Укажите ID в getchips/.env → GETCHIPS_STUB_PRODUCT_ID (приоритет) или в константе ниже.
 * Важно: заглушка не должна быть обычной карточкой каталога (например, был ошибочный ID 286524 = PAC5225QM: лимит 54 шт.).
 * Нужен отдельный тех. товар: учёт остатка отключён, «можно купить при отсутствии».
 * Свойства корзины с кодами GETCHIPS_* создаются автоматически при добавлении; при желании заранее
 * заведите их в Настройки → Настройки продукта → Свойства корзины для единообразия в админке.
 */
if (!defined('GETCHIPS_LARAVEL_BASE')) {
    define('GETCHIPS_LARAVEL_BASE', 'https://lvt.market/getchips');
}

/** На проде с валидным TLS оставляем проверку включённой. При необходимости переопределите в php.ini/константе. */
if (!defined('GETCHIPS_HTTP_DISABLE_SSL_VERIFY')) {
    define('GETCHIPS_HTTP_DISABLE_SSL_VERIFY', false);
}

/** Читает getchips/.env (строки KEY=value). */
$GLOBALS['__getchips_env_cache'] = $GLOBALS['__getchips_env_cache'] ?? null;
if ($GLOBALS['__getchips_env_cache'] === null) {
    $GLOBALS['__getchips_env_cache'] = [];
    $envPath = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/') . '/getchips/.env';
    if (is_readable($envPath)) {
        foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if ($line !== '' && $line[0] === '#') {
                continue;
            }
            if (preg_match('/^([A-Za-z0-9_]+)=(.*)$/', $line, $m)) {
                $GLOBALS['__getchips_env_cache'][$m[1]] = trim($m[2], " \t\"'");
            }
        }
    }
}

if (!defined('GETCHIPS_INTERNAL_API_KEY')) {
    $key = (string) ($GLOBALS['__getchips_env_cache']['GETCHIPS_INTERNAL_API_KEY'] ?? '');
    define('GETCHIPS_INTERNAL_API_KEY', $key);
}

if (!defined('GETCHIPS_STUB_PRODUCT_ID')) {
    /** Тех. товар каталога: без учёта остатка, покупка при нуле (не SKU реального артикула). */
    $stubFromEnv = (int) ($GLOBALS['__getchips_env_cache']['GETCHIPS_STUB_PRODUCT_ID'] ?? 0);
    define('GETCHIPS_STUB_PRODUCT_ID', $stubFromEnv > 0 ? $stubFromEnv : 523417);
}

/** Публичный origin каталога (карточка + /upload): для корзины/Getchips при отображении с другого домена (sharebasket и т.д.). */
if (!defined('GETCHIPS_PUBLIC_SITE_URL')) {
    $pub = (string) ($GLOBALS['__getchips_env_cache']['GETCHIPS_PUBLIC_SITE_URL'] ?? '');
    define('GETCHIPS_PUBLIC_SITE_URL', $pub !== '' ? $pub : 'https://lvt.market');
}
