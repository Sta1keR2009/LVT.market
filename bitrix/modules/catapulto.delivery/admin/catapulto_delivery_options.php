<?php
use \Ipol\Catapulto\Bitrix\Tools;

use \Bitrix\Main\Localization\Loc;

define("ADMIN_MODULE_NAME", "catapulto.delivery");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin.php");
global $APPLICATION, $USER;

Loc::loadMessages(__FILE__);

if (!CModule::IncludeModule(ADMIN_MODULE_NAME))
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$APPLICATION->SetTitle(Tools::getMessage('ADMIN_OPTIONS_TITLE'));

?>
    <style>
        .ipol_adminButtonPanel {
            text-align: left;
            padding: 13px;
            opacity: 1;
            background: white;
            margin-bottom: 10px;
        }
    </style>
<?php
if (class_exists('\Bitrix\Main\UI\Extension'))
    \Bitrix\Main\UI\Extension::load("ui.buttons");
else
    \CJSCore::Init("ui.buttons");

$buttonPanel = '<div class="ipol_adminButtonPanel">';
$buttonPanel .= '<button onclick=\'location.assign("/bitrix/admin/settings.php?lang='.LANGUAGE_ID.'&mid='.ADMIN_MODULE_NAME.'&mid_menu=1");\' class="ui-btn ui-btn-success">'.Tools::getMessage('TO_OPTIONS_BTN').'</button>';
$buttonPanel .= '<button onclick=\'location.assign("/bitrix/admin/sale_delivery_service_list.php?lang='.LANGUAGE_ID.'&filter_group=0");\' class="ui-btn ui-btn-secondary">'.Tools::getMessage('TO_DELIVERIES_BTN').'</button>';
$buttonPanel .= '</div>';

echo $buttonPanel;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");