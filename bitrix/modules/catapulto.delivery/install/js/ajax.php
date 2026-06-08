<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$module_id = "catapulto.delivery";
\Bitrix\Main\Loader::includeModule($module_id);

\Ipol\Catapulto\SubscribeHandler::getAjaxAction($_REQUEST['CATAPULTO_DELIVERY_action']);