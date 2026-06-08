<?php
/**
 * Миграция iblock-изображений в WebP (b_file, SUBDIR iblock/%).
 *
 * sudo -u www-root php local/tools/iblock_webp_migrate.php check
 * sudo -u www-root php local/tools/iblock_webp_migrate.php check --benchmark --limit=500
 * sudo -u www-root php local/tools/iblock_webp_migrate.php migrate --limit=500 --last-id=0
 * sudo -u www-root php local/tools/iblock_webp_migrate.php rollback --limit=100
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    die("CLI only\n");
}

$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 2);
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('BX_NO_ACCELERATOR_RESET', true);
define('BX_CRONTAB', true);
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);
define('BX_COMP_MANAGED_CACHE', false);

@ini_set('memory_limit', '1024M');
@set_time_limit(0);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
require __DIR__ . '/iblock_webp_migrate_lib.php';

$mode = 'check';
$config = [
    'limit' => 500,
    'last_id' => 0,
    'quality_max' => 90,
    'quality_min' => 80,
    'target_savings_min' => 0.20,
    'target_savings_max' => 0.35,
    'min_savings' => 0.18,
    'strict_size' => true,
    'force_webp' => false,
    'engine' => 'auto',
    'upload_dir' => \Bitrix\Main\Config\Option::get('main', 'upload_dir', 'upload'),
    'manifest' => $_SERVER['DOCUMENT_ROOT'] . '/upload/_ORIGINALIMG/migrate_manifest.csv',
    'benchmark' => false,
];

foreach (array_slice($argv ?? [], 1) as $arg) {
    if ($arg === 'check' || $arg === 'migrate' || $arg === 'rollback') {
        $mode = $arg;
    } elseif ($arg === '--benchmark') {
        $config['benchmark'] = true;
    } elseif ($arg === '--force-webp') {
        $config['force_webp'] = true;
        $config['strict_size'] = false;
    } elseif ($arg === '--no-strict-size') {
        $config['strict_size'] = false;
    } elseif (preg_match('/^--limit=(\d+)$/', $arg, $m)) {
        $config['limit'] = (int)$m[1];
    } elseif (preg_match('/^--last-id=(\d+)$/', $arg, $m)) {
        $config['last_id'] = (int)$m[1];
    } elseif (preg_match('/^--quality-max=(\d+)$/', $arg, $m)) {
        $config['quality_max'] = (int)$m[1];
    } elseif (preg_match('/^--quality-min=(\d+)$/', $arg, $m)) {
        $config['quality_min'] = (int)$m[1];
    } elseif (preg_match('/^--min-savings=([\d.]+)$/', $arg, $m)) {
        $config['min_savings'] = (float)$m[1];
    } elseif (preg_match('/^--target-savings-min=([\d.]+)$/', $arg, $m)) {
        $config['target_savings_min'] = (float)$m[1];
    } elseif (preg_match('/^--engine=(\w+)$/', $arg, $m)) {
        $config['engine'] = $m[1];
    } elseif (preg_match('/^--log=(.+)$/', $arg, $m)) {
        $log = $m[1];
        $config['manifest'] = $log[0] === '/' ? $log : $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($log, '/');
    }
}

try {
    $migrator = new IblockWebpMigrate($config);
    $migrator->bootstrap();

    $exit = match ($mode) {
        'check' => $migrator->runCheck((bool)$config['benchmark']),
        'migrate' => $migrator->runMigrate(),
        'rollback' => $migrator->runRollback((int)$config['limit']),
        default => 1,
    };
} catch (Throwable $e) {
    fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n");
    $exit = 1;
}

exit($exit ?? 0);
