<?php
define('NO_KEEP_STATISTIC', true);
define('STOP_STATISTICS', true);
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

header('Content-Type: application/json; charset=UTF-8');

$term = isset($_GET['term']) ? (string)$_GET['term'] : '';

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/SaleLocationCityService.php';

$res = SaleLocationCityService::suggest($term, 25);

echo json_encode($res, JSON_UNESCAPED_UNICODE);
