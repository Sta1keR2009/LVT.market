<?php
#################################################
#        Company developer: IPOL
#        Developer: Nikta Egorov
#        Site: http://www.ipol.com
#        E-mail: om-sv2@mail.ru
#        Copyright (c) 2006-2017 IPOL
#################################################
?>
<?php
IncludeModuleLangFile(__FILE__);

if (class_exists("catapulto_delivery")) {
    return;
}

class catapulto_delivery extends CModule
{
    var $MODULE_ID           = "catapulto.delivery";
    var $MODULE_NAME;
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;
    var $MODULE_GROUP_RIGHTS = "N";
    var $errors;
    
    function __construct()
    {
        $arModuleVersion = [];
        
        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php"));
        include($path . "/version.php");
        
        $this->MODULE_VERSION      = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        
        $this->MODULE_NAME        = GetMessage("CATAPULTO_DELIVERY_INSTALL_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("CATAPULTO_DELIVERY_INSTALL_DESCRIPTION");
        
        $this->PARTNER_NAME = "Ipol";
        $this->PARTNER_URI  = "https://ipol.ru";
    }
    
    /**
     * @return array
     * список таблиц для установки вида название таблицы -> sql+код (имя файла - sql+код.php)
     */
    protected function getDB()
    {
        return [
            'catapulto_delivery_orders'               => 'Orders',
            'catapulto_delivery_operators'            => 'Operators',
            'catapulto_delivery_orders_props'         => 'OrderProps',
            'catapulto_delivery_warehouses'           => 'Warehouses',
            'catapulto_delivery_warehouses_operators' => 'WarehousesOperators',
        ];
    }
    
    function InstallDB()
    {
        global $DB, $DBType, $APPLICATION;
        $this->errors = false;
        
        $arDB = $this->getDB();
        
        foreach ($arDB as $name => $path) {
            if (!$DB->Query("SELECT 'x' FROM " . $name, true)) {
                $this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/" . $this->MODULE_ID . "/install/db/mysql/install" . $path . ".sql");
                if ($this->errors !== false) {
                    $APPLICATION->ThrowException(implode("", $this->errors));
                    return false;
                }
                else {
                    if ($name == 'catapulto_delivery_warehouses') {
                        //todo сделать создание 1-го склада из старых настроек
                        //сделано через updater
                    }
                }
            }
        }
        
        return true;
    }
    
    function UnInstallDB()
    {
        global $DB, $DBType, $APPLICATION;
        $this->errors = false;
        
        $arDB = $this->getDB();
        
        foreach ($arDB as $name => $path) {
            $batchPath = $_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/" . $this->MODULE_ID . "/install/db/mysql/uninstall" . $path . ".sql";
            if(file_exists($batchPath)) {
                $this->errors = $DB->RunSQLBatch($batchPath);
                if (!empty($this->errors)) {
                    $APPLICATION->ThrowException(implode("", $this->errors));
                    return false;
                }
            }
        }
        
        return true;
    }
    
    function InstallFiles()
    {
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/images/", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/images/" . $this->MODULE_ID, true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/js/", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/js/" . $this->MODULE_ID, true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/admin/", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin", true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/themes/", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/themes", true, true);
        //CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/tools/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools/".$this->MODULE_ID, true, true);
        //CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/components/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/", true, true);
        return true;
    }
    
    function UnInstallFiles()
    {
        DeleteDirFilesEx("/bitrix/js/" . $this->MODULE_ID);
        if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/bitrix/tools/' . $this->MODULE_ID)) {
            DeleteDirFilesEx("/bitrix/tools/" . $this->MODULE_ID);
        }
        DeleteDirFilesEx("/bitrix/images/" . $this->MODULE_ID);
        
        if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/admin/")) {
            $adminFiles = scandir($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/admin/");
            foreach ($adminFiles as $file) {
                if (strlen($file) > 2 && strpos($file, '.')) {
                    if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin/" . $file)) {
                        unlink($_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin/" . $file);
                    }
                }
            }
        }
        if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/themes/")) {
            $adminFiles = scandir($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/themes/.default/");
            foreach ($adminFiles as $file) {
                if (strlen($file) > 2 && strpos($file, '.')) {
                    if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/bitrix/themes/.default/" . $file)) {
                        unlink($_SERVER["DOCUMENT_ROOT"] . "/bitrix/themes/.default/" . $file);
                    }
                }
            }
        }
        
        
        //        DeleteDirFilesEx(\COption::GetOptionString('sale','delivery_handles_custom_path','/bitrix/php_interface/include/sale_delivery')."delivery_catapulto_delivery.php");
        //DeleteDirFilesEx("/bitrix/components/ipol/catapulto.deliveryPickup");
        DeleteDirFilesEx("/upload/" . $this->MODULE_ID);
        /*$arrayOfFiles=scandir($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/ipol');
        $flagForDelete=true;
        foreach($arrayOfFiles as $element){
            if(strlen($element)>2)
                $flagForDelete=false;
        }
        if($flagForDelete)
            DeleteDirFilesEx("/bitrix/components/ipol");
        */
        return true;
    }
    
    function DoInstall()
    {
        global $DB, $APPLICATION, $step;
        $this->errors = false;
        
        // если надо не давать устанавливать модуль - вот тут этим делом занимаемся
        if (!CheckVersion($this->getSaleVersion(), '16.0.0')) {
            $GLOBALS['CATAPULTO_DELIVERY_LBL_INSTALL_ERROR'] = GetMessage('CATAPULTO_DELIVERY_BADSALEVERSION') . " " . $this->getSaleVersion() . ".";
        }
        
        if ($GLOBALS['CATAPULTO_DELIVERY_LBL_INSTALL_ERROR']) {
            $GLOBALS['APPLICATION']->IncludeAdminFile(GetMessage('CATAPULTO_DELIVERY_INSTALL_ERROR_TITLE'), __DIR__ . '/error.php');
            
            return;
        }
        
        $this->InstallDB();
        $this->InstallFiles();
        
        RegisterModule($this->MODULE_ID);
        
        $APPLICATION->IncludeAdminFile(GetMessage("CATAPULTO_DELIVERY_INSTALL"), $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/step1.php");
    }
    
    function DoUninstall()
    {
        global $DB, $APPLICATION, $step;
        $this->errors = false;
        
        if ($_REQUEST['step'] < 2) {
            $this->ShowDataSaveForm();
        }
        else {
            \Bitrix\Main\Loader::includeModule($this->MODULE_ID);
            
            // если надо сохранить таблицы
            if (!array_key_exists('CATAPULTO_DELIVERY_savedata', $_REQUEST) || $_REQUEST['CATAPULTO_DELIVERY_savedata'] != 'Y') {
                $this->UnInstallDB();
            }
            
            $this->UnInstallFiles();
            
            \Ipol\Catapulto\AuthHandler::delogin();
            
            UnRegisterModule($this->MODULE_ID);
            $APPLICATION->IncludeAdminFile(GetMessage("CATAPULTO_DELIVERY_DEL"), $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/" . $this->MODULE_ID . "/install/unstep1.php");
        }
    }
    
    /**
     * сохранение таблиц модуля
     */
    private function ShowDataSaveForm()
    {
        $keys = array_keys($GLOBALS);
        for ($i = 0; $i < count($keys); $i++) {
            if ($keys[$i] != 'i' && $keys[$i] != 'GLOBALS' && $keys[$i] != 'strTitle' && $keys[$i] != 'filepath') {
                global ${$keys[$i]};
            }
        }
        
        $APPLICATION->SetTitle(GetMessage('CATAPULTO_DELIVERY_DEL'));
        include($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php');
        ?>
        <form action="<?= $APPLICATION->GetCurPage() ?>" method="get">
            <?= bitrix_sessid_post(); ?>
            <input type="hidden" name="lang" value="<?= LANG ?>"/>
            <input type="hidden" name="id" value="<?= $this->MODULE_ID ?>"/>
            <input type="hidden" name="uninstall" value="Y"/>
            <input type="hidden" name="step" value="2"/>
            <?
            \CAdminMessage::ShowMessage(GetMessage('CATAPULTO_DELIVERY_PRESERVE_TABLES')) ?>
            <p><? echo GetMessage('MOD_UNINST_SAVE') ?></p>
            <p><input type="checkbox" name="CATAPULTO_DELIVERY_savedata" id="CATAPULTO_DELIVERY_savedata" value="Y" checked="checked"/><label for="savedata"><? echo GetMessage('MOD_UNINST_SAVE_TABLES') ?></label><br></p>
            <input type="submit" name="inst" value="<? echo GetMessage('MOD_UNINST_DEL'); ?>"/>
        </form>
        <?
        include($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');
        die();
    }
    
    private function getSaleVersion()
    {
        include($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/sale/install/version.php');
        return $arModuleVersion['VERSION'];
    }
}

?>
