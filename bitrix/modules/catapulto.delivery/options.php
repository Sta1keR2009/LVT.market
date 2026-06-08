<?php
#################################################
#        Company developer: IPOL
#        Developers: Nikta Egorov
#        Site: http://www.ipol.ru
#        E-mail: om-sv2@mail.ru
#        Copyright (c) 2006-2021 IPOL
#################################################
?>
<?php

use Ipol\Catapulto\Bitrix\Handler\Deliveries;
use Ipol\Catapulto\Bitrix\Tools as Tools;
use Ipol\Catapulto\Option;

IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");

\Bitrix\Main\Loader::includeModule('catapulto.delivery');
\Bitrix\Main\Loader::includeModule('sale');

Tools::jqInclude();
$jsExtensions = ['popup', 'color_picker'];
CJSCore::Init($jsExtensions);

$module_id = Ipol\Catapulto\AbstractGeneral::getMODULEID();
$LABEL     = Ipol\Catapulto\AbstractGeneral::getMODULELBL();

$arAllOptions = Ipol\Catapulto\Option::toOptions();

$authorized = \Ipol\Catapulto\AuthHandler::isAuthorized();

if($authorized){
    $arTabs = array(
        // файл с FAQ
        array(
            "DIV" => "setup_faq",
            "TAB" => Tools::getMessage("TAB_FAQ"),
            "TITLE" => Tools::getMessage("TAB_TITLE_FAQ"),
            "PATH" => Tools::defaultOptionPath() . "FAQ.php"
        ),
        // файл с основными опциями
        array(
            "DIV" => "setup_main",
            "TAB" => GetMessage("MAIN_TAB_SET"),
            "TITLE" => GetMessage("MAIN_TAB_TITLE_SET"),
            "PATH" => Tools::defaultOptionPath() . "setups.php"
        ),
        array(
            "DIV" =>   "setup_warehouses",
            "TAB" =>   Tools::getMessage('TAB_WH_TITLE'),
            "TITLE" => Tools::getMessage('TAB_WH_HDR'),
            "PATH" =>  Tools::defaultOptionPath() . "warehouses.php"
        ),
        // файл с правами на доступ к модулю
        array(
            "DIV" => "setup_rights",
            "TAB" => Tools::getMessage('TAB_RIGHRTS'),
            "TITLE" => Tools::getMessage('TAB_TITLE_RIGHRTS'),
            "PATH" => Tools::defaultOptionPath() . "rights.php"
        )
    );

    if(\Ipol\Catapulto\Option::get('debug') === 'Y'){
        $arTabs []= array(
            "DIV" => "setup_debug",
            "TAB" => Tools::getMessage("TAB_DEBUG"),
            "TITLE" => Tools::getMessage("TAB_TITLE_DEBUG"),
            "PATH" => Tools::defaultOptionPath() . "debug.php"
        );
    } else {
        // \Ipol\Catapulto\OptionsHandler::turnOffLogging();
        // TODO: ограничение логирования отдельных методов (отключение логов для всех методов)
    }

    $_arTabs = array();
    // событие на сотворение табов, чтобы можно было расширять настройками
    foreach(GetModuleEvents($module_id,"onTabsBuild",true) as $arEvent)
        ExecuteModuleEventEx($arEvent,Array(&$_arTabs));

    $divId = count($arTabs);
    if(!empty($_arTabs))
        foreach($_arTabs as $tabName => $path)
            $arTabs[]=array("DIV" => "setup_edit".(++$divId), "TAB" => $tabName, "TITLE" => $tabName, "PATH" => $path);

}else{
    $arTabs = array(
        array(
            "DIV"   => "setup_auth",
            "TAB"   => Tools::getMessage('TAB_AUTH'),
            "TITLE" => Tools::getMessage('TAB_TITLE_AUTH'),
            "PATH"  => Tools::defaultOptionPath() . "auth.php"
        ),
    );
}


//Restore defaults
if ($USER->IsAdmin() && $_SERVER["REQUEST_METHOD"]=="GET" && strlen($RestoreDefaults)>0 && check_bitrix_sessid()) {
    COption::RemoveOption($module_id);
    \Ipol\Catapulto\OptionsHandler::clearCache(true); // всегда очищаем кэш, на всякий
}

//Save options
if($REQUEST_METHOD=="POST" && strlen($Update.$Apply)>0 && check_bitrix_sessid()){
    
    \Ipol\Catapulto\OptionsHandler::clearCache(true); // всегда очищаем кэш, на всякий

    $arErrors = array();

    foreach($arAllOptions as $aOptGroup){
        foreach($aOptGroup as $option){
            $validate = \Ipol\Catapulto\Option::validate($option[0], $_REQUEST[$option[0]]);

            if($validate->isSuccess()){
                if(\Ipol\Catapulto\Option::checkMultiple($option[0]))
                    $_REQUEST[$option[0]] = serialize($_REQUEST[$option[0]]);
                __AdmSettingsSaveOption($module_id, $option);
            } else {
                $arErrors[] = Tools::getMessage('OPT_'.$option[0]).': '.$validate->getErrorText();
            }
        }
    }
    
    //Save Warehouses
    $result = \Ipol\Catapulto\WarehousesTable::saveItems($_POST);
    
    if(!$result->isSuccess()) {
        $arErrors = array_unique(array_merge($arErrors, $result->getErrorMessages()));
    }
    unset($result);

    if(count($arErrors)){
        ?><table><?php
        Tools::placeErrorLabel(implode('<br>',$arErrors),Tools::getMessage('ERROR_OPTSAVE_TITLE'));
        ?></table><?php
    }
    

    if($_REQUEST["back_url_settings"] <> "" && $_REQUEST["Apply"] == "")
        echo '<script type="text/javascript">window.location="'.CUtil::addslashes($_REQUEST["back_url_settings"]).'";</script>';
}

function ShowParamsHTMLByArray($arParams,$isHidden = false){
    global $module_id;
    global $LABEL;
    if($isHidden){
        ob_start();
    }
    
    if(!Tools::getMarkupAvailable()) {
        $optionMarkup['attrs']       = 'disabled="disabled"';
        $optionMarkup['USE_DEFAULT'] = true;
    }

    foreach($arParams as $Option){
        $sup_text = array_key_exists(7, $Option) ? '<span class="required"><sup> *</sup></span>' : '';
        $Option['1'] .= $sup_text;
        if(
            $Option[3][0] != 'selectbox' &&
            $Option[3][0] != 'textbox'   &&
            $Option[3][0] != 'special'
        ){
            //if isset attr in option
            if($Option[3][2]) {
                Tools::placeOptionRow($Option[1],'<input '.$Option[3][2].' type="text" maxlength="255" name="'.$Option['0'].'" id="'.$Option['0'].'" value="'.Ipol\Catapulto\Option::get($Option['0']).'">');
            }
            else {
                switch ($Option['0']) {
                    case 'deliveryPvzMarkupValue' :
                    case 'deliveryCourierMarkupValue' :
                        Tools::placeDoubleOptionRowSecond($Option, $optionMarkup);
                        break;
                    
                    default:
                        __AdmSettingsDrawRow($module_id, $Option);
                        break;
                } // если для текста, чекбокса, итп есть особые правила отображения - фигачим их тут
            }
        }
        elseif($Option[3][0]=='selectbox'){
            
            if(in_array($Option[0], ['deliveryPvzMarkupType', 'deliveryCourierMarkupType'], false)) {
                Tools::placeDoubleOptionRowFirst($Option, $optionMarkup);
            }
            else {
                // вывод селекта
                $optVal     = Ipol\Catapulto\Option::get($Option[0]);
                $selectVals = Ipol\Catapulto\Option::getSelectVals($Option[0]);
                $attrs      = '';
                $solo       = false;
                
                // если надо как-то по-особому выводить это дело
                switch ($Option['0']) {
                    case 'payNal'  :
                    case 'payCard' :
                        $attrs = "multiple='multiple' size='5'";
                        break;
                    case 'paySystemsNal' :
                        $attrs = "multiple='multiple' size='5'";
                        if (array_key_exists('paySystemsDefaults', Ipol\Catapulto\Option::$ABYSS)) {
                            $optVal = Ipol\Catapulto\Option::$ABYSS['paySystemsDefaults'];
                        }
                        break;
                }
                
                if ($solo) {
                    Tools::placeOptionRow(false, (($selectVals) ? Tools::makeSelect($Option['0'], $selectVals, $optVal, $attrs) : $optVal));
                }
                else {
                    Tools::placeOptionRow($Option['1'], (($selectVals) ? Tools::makeSelect($Option['0'], $selectVals, $optVal, $attrs) : $optVal));
                }
            }
        }
        elseif($Option[3][0]=='textbox'){
            // текстарея
            Tools::placeOptionRow($Option[1],"<textarea name='".$Option['0']."' id='".$Option['0']."'>".Ipol\Catapulto\Option::get($Option['0'])."</textarea>");
        }else{
            // специальный вывод для опций с типом special
        }

        if(
            $Option['0'] == 'flat' ||
            $Option['0'] == 'STATUS_REFUSED' ||
            $Option['0'] == 'STATUS_UNDELIVERED'
        ){
            echo '<tr><td colspan="2"><hr></td></tr>';
        }
    }

    if($isHidden){
        // если опция скрыта - не покажем ее, пока не кликнут по кой-чаму
        $DATAS = ob_get_contents();
        ob_end_clean();
        echo str_replace('<tr',"<tr class='{$GLOBALS['LABEL']}hidden'",$DATAS);
    }
}

$tabControl = new \CAdminTabControl("tabControl", $arTabs);
?>
<script type="text/javascript" src="<?=Tools::getJSPath()?>adminInterface.js"></script>
<script>
    // инициализируем объект для работы с опциями
    var <?=$LABEL?>setups = new catapulto_delivery_adminInterface({
        'ajaxPath' : '<?=Tools::getJSPath()?>ajax.php',
        'label'    : '<?=$module_id?>',
        'logging'  : true
    });
    $(document).ready(<?=$LABEL?>setups.init);
    
    $('body').on('change', '#payNal, #payCard', function() {
        let payNalVal = $('#payNal').val();
        let payCardVal = $('#payCard').val();
        if(payNalVal.length || payCardVal.length) {
            $('#deliveryPvzMarkupType').attr('disabled', true).val('N');
            $('#deliveryPvzMarkupValue').attr('disabled', true).val('');
            $('#deliveryCourierMarkupType').attr('disabled', true).val('N');
            $('#deliveryCourierMarkupValue').attr('disabled', true).val('');
        }
        else {
            $('#deliveryPvzMarkupType').attr('disabled', false).val('<?=(Option::get('deliveryPvzMarkupType'))?>');
            $('#deliveryPvzMarkupValue').attr('disabled', false).val('<?=(Option::get('deliveryPvzMarkupValue'))?>');
            $('#deliveryCourierMarkupType').attr('disabled', false).val('<?=(Option::get('deliveryCourierMarkupType'))?>');
            $('#deliveryCourierMarkupValue').attr('disabled', false).val('<?=(Option::get('deliveryCourierMarkupValue'))?>');
        }
    });
    
</script>
<?php Tools::getCommonCss();?>
<style>
    .ipol_header {
        font-size: 16px;
        cursor: pointer;
        display:block;
        color:#2E569C;
    }
    .ipol_inst {
        display:none;
        margin-left:10px;
        margin-top: 10px;
        margin-bottom: 10px;
        color: #555;
    }
    .ipol_smallHeader{
        cursor: pointer;
        display:block;
        color:#2E569C;
    }
    .ipol_subFaq{
        margin-bottom:10px;
    }
    .<?=$LABEL?>subHeading td{
        padding: 8px 70px 10px !important;
        background-color: #EDF7F9;
        border-top: 11px solid #F5F9F9;
        border-bottom: 11px solid #F5F9F9;
        color: #4B6267;
        font-size: 14px;
        font-weight: bold;
        text-align: center !important;
        text-shadow: 0px 1px #FFF;
    }
    .ipol_borderBottom {
        border-bottom: 1px dotted black;
    }

    .<?=$LABEL?>headerLink{
        cursor: pointer;
        text-decoration: underline;
    }

    /*img{border: 1px dotted black;}*/

    .ipol_adminButtonPanel {
        text-align: left;
        padding: 13px;
        opacity: 1;
        background: white;
        margin-bottom: 10px;
    }
    .flex-box {
        display: flex;
        align-items: baseline;
    }
    .flex-item {
        margin-left: 14px;
    }
    .flex-item:first-child {
        margin-left: 24px;
    }
</style>

<?php
// место для вывода глобальных ошибок в работе модуля
if($authorized) {

    if (class_exists('\Bitrix\Main\UI\Extension')) {
        \Bitrix\Main\UI\Extension::load("ui.buttons");
    }
    else {
        \CJSCore::Init("ui.buttons");
    }

    $isSyncCompleted = (\Ipol\Catapulto\Option::get('sync_data_completed') == 'Y');

    $buttonPanel = '<div class="ipol_adminButtonPanel">';

    if ($isSyncCompleted)
        $buttonPanel .= '<button onclick=\'window.open("/bitrix/admin/catapulto_delivery_orders.php?lang='.LANGUAGE_ID.'");\' class="ui-btn ui-btn-success">'.Tools::getMessage('LBL_toOrders').'</button>';

    $btnSyncClass = $isSyncCompleted ? 'ui-btn-secondary' : 'ui-btn-danger';
    $buttonPanel .= '<button onclick=\'window.open("/bitrix/admin/catapulto_delivery_sync_data.php?lang='.LANGUAGE_ID.'");\' class="ui-btn '.$btnSyncClass.'">'.Tools::getMessage('LBL_toSunc').'</button>';
    
    $buttonPanel .= '<button onclick=\'window.open("/bitrix/admin/catapulto_delivery_orders_mass.php?lang='.LANGUAGE_ID.'");\' class="ui-btn ui-btn-secondary">'.Tools::getMessage('LBL_toMassOrders').'</button>';
    
    $buttonPanel .= '</div>';

    echo $buttonPanel;

    if (!$isSyncCompleted)
    {
        ?><table><?php
        Tools::placeErrorLabel(Tools::getMessage('ERROR_SYNC_DATA_REQUIRED_DESCR'), Tools::getMessage('ERROR_SYNC_DATA_REQUIRED_TITLE'));
        ?></table><?php
    }

    if (!Deliveries::isActive()) {
        ?><table><?php
        Tools::placeErrorLabel(Tools::getMessage('ERROR_NODELIVERY_DESCR'), Tools::getMessage('ERROR_NODELIVERY_TITLE'));
        ?></table><?php
    }
}
?>

<form method="post" action="<?php echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialchars($mid)?>&amp;lang=<?php echo LANG?>">
    <?php
    // подключаем табы
    $tabControl->Begin();
    foreach($arTabs as $arTab){
        $tabControl->BeginNextTab();
        include_once($_SERVER['DOCUMENT_ROOT'].$arTab["PATH"]);
    }

    $tabControl->Buttons();

    // сохранение - только если авторизован
    if($authorized) {?>
        <div align="left">
            <input type="hidden" name="Update" value="Y">
            <input type="submit" <?php if(!$USER->IsAdmin())echo " disabled ";?> name="Update" value="<?php echo GetMessage("MAIN_SAVE")?>">
        </div>
    <?php }?>
    <?php $tabControl->End();?>
    <div style='text-align: right'>
        <?=Tools::getMessage('LBL_COPYRIGHT')?> <a href='https://ipol.ru/' target='_blank'><img style="border:none" src='<?=Tools::getImagePath()?>ipol.png'></a>
        <div>
            <?=bitrix_sessid_post();?>
</form>
