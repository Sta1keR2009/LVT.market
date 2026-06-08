<?php
$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

// Clear all managed cache
BXClearCache(true, '/');

// Clear opcache if available
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache reset OK\n";
}

echo "Full cache cleared OK\n";
