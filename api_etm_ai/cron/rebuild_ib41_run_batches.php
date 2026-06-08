<?php
/**
 * Запускает rebuild_ib41_sections_from_etm.php несколько батчей подряд (один процесс — удобно для cron-окна).
 * Один батч = один вызов основного скрипта с текущим offset из state-файла.
 *
 *   sudo -u www-root php api_etm_ai/cron/rebuild_ib41_run_batches.php
 *   sudo -u www-root php ... --batches=40 --max=3600
 *
 * Рекомендуемый порядок (см. шапку rebuild_ib41_sections_from_etm.php):
 *   1) snapshot_ib41_before_rebuild.php
 *   2) этот скрипт (или ручной цикл)
 *   3) prune-empty-under-etm --dry-run → --apply
 *   4) при необходимости prune-outside-etm
 */

declare(strict_types=1);

set_time_limit(0);
ini_set('memory_limit', '128M');

$docRoot = '';
if (php_sapi_name() === 'cli') {
    $tryRoots = [
        '/var/www/www-root/data/www/lvtgroup.ru',
        dirname(dirname(dirname(__DIR__))),
    ];
    foreach ($tryRoots as $r) {
        if (is_dir($r . '/bitrix')) {
            $docRoot = $r;
            break;
        }
    }
} else {
    $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/');
}
if ($docRoot === '') {
    fwrite(STDERR, "Cannot find document root\n");
    exit(1);
}
$_SERVER['DOCUMENT_ROOT'] = $docRoot;

define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);

require_once $docRoot . '/bitrix/modules/main/include/prolog_before.php';
require_once dirname(__DIR__) . '/config_ib40.php';

if (!CModule::IncludeModule('iblock')) {
    fwrite(STDERR, "iblock not loaded\n");
    exit(1);
}

$batches = 45;
$maxPer = 3600;
$dryExtra = '';
foreach ($argv ?? [] as $arg) {
    if (preg_match('/^--batches=(\d+)$/', $arg, $m)) {
        $batches = max(1, (int)$m[1]);
    }
    if (preg_match('/^--max=(\d+)$/', $arg, $m)) {
        $maxPer = max(1, (int)$m[1]);
    }
    if ($arg === '--dry-run' || $arg === '--dry') {
        $dryExtra = ' --dry-run';
    }
}

$iblockId = (int)API_ETM_IBLOCK_ID;
$propEtm = API_ETM_PROP_ETM_CODE;
$total = (int)CIBlockElement::GetList(
    [],
    ['IBLOCK_ID' => $iblockId, '!PROPERTY_' . $propEtm => false],
    false,
    false,
    ['ID']
)->SelectedRowsCount();

$minBatches = (int)ceil($total / $maxPer) + 3;
if ($batches < $minBatches) {
    $batches = $minBatches;
}

$php = PHP_BINARY && is_executable(PHP_BINARY) ? PHP_BINARY : 'php';
$target = __DIR__ . '/rebuild_ib41_sections_from_etm.php';

echo "[rebuild_ib41_run_batches] total_elements_with_etm={$total}, max={$maxPer}, batches={$batches}{$dryExtra}\n";

for ($i = 1; $i <= $batches; $i++) {
    $cmd = escapeshellcmd($php) . ' ' . escapeshellarg($target) . ' --max=' . $maxPer . $dryExtra;
    echo "--- batch {$i}/{$batches}: {$cmd}\n";
    passthru($cmd, $ret);
    if ($ret !== 0) {
        fwrite(STDERR, "Batch {$i} exited with code {$ret}, stopping.\n");
        exit($ret);
    }
}

echo "[rebuild_ib41_run_batches] done.\n";
