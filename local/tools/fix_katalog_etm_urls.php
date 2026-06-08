<?php
/**
 * Синхронизация ЧПУ каталога ETM (iblock 41): раздел etm + шаблон DETAIL_PAGE_URL.
 * Запуск: sudo -u www-root php local/tools/fix_katalog_etm_urls.php
 */
$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 2);
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

if (!CModule::IncludeModule('iblock')) {
    fwrite(STDERR, "iblock module not loaded\n");
    exit(1);
}

global $DB;

const IBLOCK_ID = 41;
const ETM_SECTION_ID = 8000;
const DETAIL_TEMPLATE = '/katalog/#SECTION_CODE_PATH#/#ELEMENT_CODE#/';

$ib = new CIBlock();
if (!$ib->Update(IBLOCK_ID, ['DETAIL_PAGE_URL' => DETAIL_TEMPLATE])) {
    fwrite(STDERR, "Failed to update iblock: " . $ib->LAST_ERROR . "\n");
    exit(1);
}
echo "Iblock DETAIL_PAGE_URL updated\n";

$fixedCode = (int) $DB->Query(
    "UPDATE b_iblock_element SET CODE = CONCAT('etm_', ID)
     WHERE IBLOCK_ID = " . IBLOCK_ID . " AND (CODE IS NULL OR CODE = '')"
)->AffectedRowsCount();
echo "Fixed empty CODE: $fixedCode\n";

$assignedSection = (int) $DB->Query(
    "UPDATE b_iblock_element SET IBLOCK_SECTION_ID = " . ETM_SECTION_ID . "
     WHERE IBLOCK_ID = " . IBLOCK_ID . " AND (IBLOCK_SECTION_ID IS NULL OR IBLOCK_SECTION_ID = 0)"
)->AffectedRowsCount();
echo "Assigned section etm: $assignedSection\n";

CIBlock::clearIblockTagCache(IBLOCK_ID);
echo "DETAIL_PAGE_URL will be built from iblock template on read.\n";

if (class_exists(\Bitrix\Main\Data\Cache::class)) {
    \Bitrix\Main\Data\Cache::clearCache(true);
}

echo "Done.\n";
