<?php
/**
 * Снять привязку всех элементов IB=41 к разделам (основную и дополнительные).
 *
 *   sudo -u www-root php api_etm_ai/cron/unlink_ib41_sections.php --dry-run
 *   sudo -u www-root php api_etm_ai/cron/unlink_ib41_sections.php --apply
 *
 * Перед массовой операцией рекомендуется снимок:
 *   sudo -u www-root php api_etm_ai/cron/snapshot_ib41_before_rebuild.php
 */

declare(strict_types=1);

set_time_limit(3600);
ini_set('memory_limit', '512M');

define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);

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

require_once $docRoot . '/bitrix/modules/main/include/prolog_before.php';
require_once dirname(__DIR__) . '/config_ib40.php';

use Bitrix\Main\Application;

$apply = in_array('--apply', $argv ?? [], true);
$dryRun = in_array('--dry-run', $argv ?? [], true) || !$apply;

if (!$dryRun && !$apply) {
    fwrite(STDERR, "Specify --dry-run or --apply\n");
    exit(1);
}

$iblockId = (int)API_ETM_IBLOCK_ID;
$connection = Application::getConnection();
$sqlHelper = $connection->getSqlHelper();

$countElements = (int)$connection->queryScalar(
    "SELECT COUNT(*) FROM b_iblock_element WHERE IBLOCK_ID = {$iblockId}"
);
$countMain = (int)$connection->queryScalar(
    "SELECT COUNT(*) FROM b_iblock_element
     WHERE IBLOCK_ID = {$iblockId}
       AND IBLOCK_SECTION_ID IS NOT NULL
       AND IBLOCK_SECTION_ID > 0"
);
$countLinks = (int)$connection->queryScalar(
    "SELECT COUNT(*)
     FROM b_iblock_section_element se
     INNER JOIN b_iblock_element e ON e.ID = se.IBLOCK_ELEMENT_ID
     WHERE e.IBLOCK_ID = {$iblockId}"
);

echo "IBLOCK_ID={$iblockId}\n";
echo "elements_total={$countElements}\n";
echo "with_main_section={$countMain}\n";
echo "section_links={$countLinks}\n";
echo "mode=" . ($dryRun ? 'dry-run' : 'apply') . "\n";

if ($dryRun) {
    echo "Nothing changed. Run with --apply to unlink.\n";
    exit(0);
}

$connection->startTransaction();
try {
    $connection->queryExecute(
        "UPDATE b_iblock_element
         SET IBLOCK_SECTION_ID = NULL
         WHERE IBLOCK_ID = {$iblockId}
           AND IBLOCK_SECTION_ID IS NOT NULL
           AND IBLOCK_SECTION_ID > 0"
    );
    $updatedMain = $connection->getAffectedRowsCount();

    $connection->queryExecute(
        "DELETE se
         FROM b_iblock_section_element se
         INNER JOIN b_iblock_element e ON e.ID = se.IBLOCK_ELEMENT_ID
         WHERE e.IBLOCK_ID = {$iblockId}"
    );
    $deletedLinks = $connection->getAffectedRowsCount();

    $connection->commitTransaction();
} catch (Throwable $e) {
    $connection->rollbackTransaction();
    fwrite(STDERR, 'Error: ' . $e->getMessage() . "\n");
    exit(1);
}

if (CModule::IncludeModule('iblock')) {
    CIBlock::clearIblockTagCache($iblockId);
    if (class_exists('\Bitrix\Iblock\PropertyIndex\Manager')) {
        \Bitrix\Iblock\PropertyIndex\Manager::markAsInvalid($iblockId);
    }
}

$countMainAfter = (int)$connection->queryScalar(
    "SELECT COUNT(*) FROM b_iblock_element
     WHERE IBLOCK_ID = {$iblockId}
       AND IBLOCK_SECTION_ID IS NOT NULL
       AND IBLOCK_SECTION_ID > 0"
);
$countLinksAfter = (int)$connection->queryScalar(
    "SELECT COUNT(*)
     FROM b_iblock_section_element se
     INNER JOIN b_iblock_element e ON e.ID = se.IBLOCK_ELEMENT_ID
     WHERE e.IBLOCK_ID = {$iblockId}"
);

echo "updated_main_section_rows={$updatedMain}\n";
echo "deleted_section_links={$deletedLinks}\n";
echo "after_with_main_section={$countMainAfter}\n";
echo "after_section_links={$countLinksAfter}\n";
echo "Done.\n";
