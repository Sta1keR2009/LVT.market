<?php
/**
 * Запуск агентов и рассылок из cron (по инструкции 1C-Битрикс).
 * https://dev.1c-bitrix.ru/learning/course/?COURSE_ID=43&LESSON_ID=2943
 *
 * В crontab пользователя www-root:
 * * * * * /opt/php84/bin/php -f /var/www/www-root/data/www/lvtgroup.ru/bitrix/php_interface/cron_events.php >/dev/null 2>&1
 */
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__) . "/../..");
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

// Prevent overlapping runs: cron can trigger every minute while previous pass is still active.
$lockFilePath = $_SERVER["DOCUMENT_ROOT"] . '/bitrix/tmp/cron_events.lock';
$lockDir = dirname($lockFilePath);
if (!is_dir($lockDir)) {
	$lockFilePath = sys_get_temp_dir() . '/lvtgroup_bitrix_cron_events.lock';
}
$lockHandle = fopen($lockFilePath, 'c');
if ($lockHandle === false) {
	exit(0);
}
if (!flock($lockHandle, LOCK_EX | LOCK_NB)) {
	fclose($lockHandle);
	exit(0);
}

register_shutdown_function(static function () use ($lockHandle): void {
	flock($lockHandle, LOCK_UN);
	fclose($lockHandle);
});

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define('BX_NO_ACCELERATOR_RESET', true);
define('CHK_EVENT', true);
define('BX_WITH_ON_AFTER_EPILOG', true);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

@set_time_limit(0);
@ignore_user_abort(true);

// Запуск агентов (вместо выполнения на хите)
CAgent::CheckAgents();

define("BX_CRONTAB_SUPPORT", true);
define("BX_CRONTAB", true);

if (CModule::IncludeModule('sender')) {
	\Bitrix\Sender\MailingManager::checkPeriod(false);
	\Bitrix\Sender\MailingManager::checkSend();
}

require($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/tools/backup.php");
CMain::FinalActions();
