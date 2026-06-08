<?php
/**
 * Общий bootstrap для cron IB41 (цены, остатки, обогащение).
 */

declare(strict_types=1);

set_time_limit(7200);
ini_set('memory_limit', '512M');

define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);

$docRoot = '';
if (php_sapi_name() === 'cli') {
    foreach (['/var/www/www-root/data/www/lvtgroup.ru', dirname(dirname(dirname(__DIR__)))] as $r) {
        if (is_dir($r . '/bitrix')) {
            $docRoot = $r;
            break;
        }
    }
} else {
    $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/');
}
if ($docRoot === '') {
    die("Cannot find document root\n");
}
$_SERVER['DOCUMENT_ROOT'] = $docRoot;

require_once $docRoot . '/bitrix/modules/main/include/prolog_before.php';
require_once dirname(__DIR__) . '/config_ib40.php';
require_once dirname(__DIR__) . '/classes/EtmApiClient.php';

if (!CModule::IncludeModule('iblock')) {
    die("iblock not loaded\n");
}
if (!CModule::IncludeModule('catalog')) {
    die("catalog not loaded\n");
}

if (!is_dir(API_ETM_LOGS_DIR)) {
    @mkdir(API_ETM_LOGS_DIR, 0755, true);
}

function etmCronLog(string $msg, string $logBasename = 'cron_ib40'): void {
    static $logFile = null;
    if ($logFile === null) {
        $logFile = API_ETM_LOGS_DIR . '/' . $logBasename . '_' . date('Y-m-d') . '.log';
    }
    $line = '[' . date('H:i:s') . '] ' . $msg . "\n";
    echo $line;
    @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

function cpLog(string $msg): void {
    etmCronLog($msg, 'cron_prices_ib40');
}

function rmLog(string $msg): void {
    etmCronLog($msg, 'cron_remains_ib40');
}

function ecLog(string $msg, string $level = 'INFO'): void {
    etmCronLog('[' . $level . '] ' . $msg, 'cron_enrich');
}
