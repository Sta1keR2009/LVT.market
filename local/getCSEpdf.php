<?
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
// подключим все необходимые файлы:
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php"); // первый общий пролог
Loader::includeModule("courierserviceexpress.moduledost");

if ($_REQUEST['numb']) {
    
    //Данные пользователя
    $KCELogin = Option::get("courierserviceexpress.moduledost", "login");
    $KCEPass = Option::get("courierserviceexpress.moduledost", "pass");
    $PrintForm = Option::get("courierserviceexpress.moduledost", "PrintFormName");
    $WaybID = $_REQUEST['numb'];
    $DocType = $_REQUEST['type'];
    $getPDF = cKCE::GetWayBillsPrint($KCELogin,$KCEPass,$WaybID,$PrintForm,$DocType);
    header('Content-Transfer-Encoding: binary');
    header("Content-type: application/pdf");
    header("Cache-Control: private, must-revalidate, post-check=0, pre-check=0, public");
    header("Pragma: public");
    header("Accept-Ranges: bytes");
    echo $getPDF;
}

?>