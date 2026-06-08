<?php
define('STOP_STATISTICS', true);
define('NO_KEEP_STATISTIC', true);
define('DisableEventsCheck', true);
$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

// Clear ALL bitrix managed cache
BXClearCache(true, '/');

// Reset opcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache: OK\n";
}

// Try to clear compiled templates
$cacheDir = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/cache/';
$cleared = 0;
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($cacheDir, FilesystemIterator::SKIP_DOTS));
foreach ($it as $file) {
    if ($file->isFile()) {
        @unlink($file->getPathname());
        $cleared++;
    }
}
echo "Files cleared from bitrix/cache/: $cleared\n";

// Also clear managed cache via Bitrix API
\Bitrix\Main\Application::getInstance()->getManagedCache()->cleanAll();
echo "Managed cache: OK\n";
echo "Done\n";
