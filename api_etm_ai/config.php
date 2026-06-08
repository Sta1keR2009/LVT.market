<?php
/**
 * API ETM — базовая конфигурация (legacy IB11).
 * Для IB41 подключать config_ib40.php (константы IB41 задаются до require config.php).
 */

$docRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__), '/');
if (!defined('API_ETM_ROOT')) {
    define('API_ETM_ROOT', __DIR__);
}

define('ETM_API_URL', 'https://ipro.etm.ru/api/v1');
define('ETM_LOGIN', '330252858fad');
define('ETM_PASSWORD', 'Lvt160916!');

if (!defined('API_ETM_IBLOCK_ID')) {
    define('API_ETM_IBLOCK_ID', 11);
}
if (!defined('API_ETM_ROOT_SECTION_ID')) {
    define('API_ETM_ROOT_SECTION_ID', 4064);
}
if (!defined('API_ETM_PRICE_TYPE_ID')) {
    define('API_ETM_PRICE_TYPE_ID', 1);
}
if (!defined('API_ETM_STORE_ID')) {
    define('API_ETM_STORE_ID', 1);
}
if (!defined('API_ETM_PROP_ETM_CODE')) {
    define('API_ETM_PROP_ETM_CODE', 'promelec');
}

define('API_ETM_COLUMNS_CSV', API_ETM_ROOT . '/columns.csv');
define('API_ETM_NEW_PROPERTIES_CSV', API_ETM_ROOT . '/new_properties.csv');
define('API_ETM_NEW_PROPERTIES_LOG', API_ETM_ROOT . '/logs/new_properties_log.txt');
define('API_ETM_LOGS_DIR', API_ETM_ROOT . '/logs');
define('API_ETM_STATE_FILE', API_ETM_LOGS_DIR . '/api_etm_state.json');
define('API_ETM_UPLOAD_DIR', $docRoot . '/upload/etm');

define('API_ETM_LOGIN_INTERVAL_SEC', 120);
define('API_ETM_SESSION_TTL_SEC', 7200);
define('API_ETM_JOB_PROCEDURE', '40029846');
define('API_ETM_REPORT_FILE', API_ETM_ROOT . '/upload/goods_report.json');
define('API_ETM_GOODS_PRICE_REMAINS_INTERVAL_SEC', 1);
define('API_ETM_PRICE_BATCH_SIZE', 50);

$configLocal = __DIR__ . '/config_local.php';
if (is_readable($configLocal)) {
    require_once $configLocal;
}
