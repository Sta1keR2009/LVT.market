<?php
/**
 * Общий bootstrap для скриптов API ETM.
 */

$docRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/');
if ($docRoot === '' || !is_dir($docRoot . '/bitrix')) {
    $docRoot = dirname(__DIR__);
}
$_SERVER['DOCUMENT_ROOT'] = $docRoot;

require_once $docRoot . '/bitrix/modules/main/include/prolog_before.php';

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/classes/EtmApiClient.php';
require_once __DIR__ . '/classes/PropertyMapper.php';
require_once __DIR__ . '/classes/DataMapper.php';
require_once __DIR__ . '/Logger.php';
