<?php

use \Ipol\Catapulto\Bitrix\Tools;
use \Bitrix\Main\Localization\Loc;
use Ipol\Catapulto\Option;

define("ADMIN_MODULE_NAME", "catapulto.delivery");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin.php");
global $APPLICATION, $USER;

Loc::loadMessages(__FILE__);

if (!CModule::IncludeModule(ADMIN_MODULE_NAME)) {
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

$APPLICATION->SetTitle(Tools::getMessage('ADMIN_SYNC_DATA_TITLE'));

?>
<form id="catapulto_delivery_sync_data_form" action="<?= $APPLICATION->GetCurPageParam() ?>" method="POST">
    <input type="hidden" name="run" value="Y"><?php

if (isset($_REQUEST['run'])) {
    $sync   = new \Ipol\Catapulto\Bitrix\Controller\SyncOperators();
    $result = $sync->refreshOperators();
    
    $errors      = $result->isSuccess() ? '' : implode('<br>', $result->getErrorMessages());
    $continueBtn = $errors ? '' : '<br>' . Tools::getMessage('SYNC_CONTINUE', ['ON_CLICK' => 'javascript:document.getElementById(\'catapulto_delivery_sync_data_form\').submit()']);
    
    CAdminMessage::ShowMessage(
        [
            'MESSAGE' => Tools::getMessage('SYNC_FINISH_TITLE'),
            'DETAILS' => Tools::getMessage('SYNC_FINISH_DESCR') . '<br><br><hr><p>' . Tools::getMessage('SYNC_STATISTIC_TITLE') . '</p><ul><li>' . Tools::getMessage('STATISTIC_OPERATORS_LOADED') . $result->getData()['TOTAL_OPERATORS_FOUND'] . '</li></ul>',
            'TYPE'    => 'OK',
            'HTML'    => true,
        ]
    );
    unset($_REQUEST['run']);
    
    if ($errors) {
        CAdminMessage::ShowMessage(
            [
                "MESSAGE" => Tools::getMessage('SYNC_ERRORS'),
                "DETAILS" => $errors . '<br><br>' . Tools::getMessage('SYNC_CONTINUE_AFTER_ERRORS', ['ON_CLICK' => 'javascript:document.getElementById(\'catapulto_delivery_sync_data_form\').submit()']),
                'TYPE'    => 'PROGRESS',
                "HTML"    => true,
            ]
        );
    }
    else {
        Option::set('sync_data_completed', 'Y');
    }
    
}
else {
    CAdminMessage::ShowMessage(
        [
            "MESSAGE" => Tools::getMessage('SYNC_START_TITLE'),
            "DETAILS" => Tools::getMessage('SYNC_START_DESCR'),
            'TYPE'    => 'PROGRESS',
            "HTML"    => true,
        ]
    );
    ?><input type="submit" value="<?= Tools::getMessage('SYNC_START_BTN') ?>" class="adm-btn-save"><?php
}
?></form><?php

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");