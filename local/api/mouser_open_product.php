<?php
declare(strict_types=1);

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/lvt_mouser_integration.php';

if (!lvt_is_mouser_api_enabled()) {
    LocalRedirect('/catalog/', true, '302 Found');
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/GetchipsPartnumberSearchHelper.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/MouserCatalogElementHelper.php';

$mouserPn = trim((string) ($_GET['mouser'] ?? ''));
$catalogIblockId = (int) ($_GET['catalog_iblock'] ?? 11);

if ($mouserPn === '' || $catalogIblockId <= 0) {
    LocalRedirect('/catalog/', true, '302 Found');
}

$elementId = MouserCatalogElementHelper::upsertFromMouserPartNumber($catalogIblockId, $mouserPn);
$url = $elementId > 0 ? GetchipsPartnumberSearchHelper::getElementDetailUrl($elementId) : '';

if ($url !== '') {
    LocalRedirect($url, true, '302 Found');
}

LocalRedirect('/catalog/', true, '302 Found');
