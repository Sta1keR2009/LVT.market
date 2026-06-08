<?php
/**
 * ITNULL Updater - Protected
 * DO NOT MODIFY!
 */

// ITNULL Protection - DO NOT MODIFY
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) { die(); }
if (function_exists('xdebug_is_enabled') && xdebug_is_enabled()) { die(); }if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
die();
}
use Itnull\Updater\IntegrityChecker;
$_UYR0AyagaI_ = IntegrityChecker::verify(true);
if (!$_UYR0AyagaI_['valid']) {
\Itnull\Updater\IntegrityChecker::handleIntegrityViolation($_UYR0AyagaI_['errors']);
if (defined('ADMIN_SECTION') && ADMIN_SECTION === true) {
$_oc8zIMkJTy_ = $_oUAnnFFMd1_['REQUEST_URI'] ?? '';
if (strpos($_oc8zIMkJTy_, 'itnull') !== false) {
\Itnull\Updater\IntegrityChecker::showBlockPage();
}
}
}
\CModule::IncludeModule('itnull.updater');
\Bitrix\Main\Loader::registerAutoLoadClasses(
'itnull.updater',
[
'Itnull\Updater\IntegrityChecker' => 'lib/integritychecker.php',
'Itnull\Updater\DemoManager' => 'lib/demomanager.php',
'Itnull\Updater\ModuleList' => 'lib/modulelist.php',
'Itnull\Updater\BitrixAPI' => 'lib/bitrixapi.php',
'Itnull\Updater\UpdateManager' => 'lib/updatemanager.php',
'Itnull\Updater\Patcher' => 'lib/patcher.php',
'Itnull\Updater\SessionManager' => 'lib/sessionmanager.php',
]
);