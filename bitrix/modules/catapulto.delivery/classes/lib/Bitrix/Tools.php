<?php
namespace Ipol\Catapulto\Bitrix;
use Ipol\Catapulto\Admin\Logger;
use Ipol\Catapulto\Bitrix\Entity\Table;
use Ipol\Catapulto\Option;


/**
 * Class Tools
 * @package Ipol\Catapulto\Bitrix
 * Общие методы, упрощающие работу с html и прочими фишками модуля
 */
class Tools
{
	private static $MODULE_ID  = CATAPULTO_DELIVERY;
	private static $MODULE_LBL = CATAPULTO_DELIVERY_LBL;

    // RIGHTS

    protected static $skipAdminCheck = false;

    /**
     * @param string $min - минимальные права
     * @return bool
     * Проверка на админа
     */
    public static function isAdmin($min = 'W'){
        if(self::$skipAdminCheck) return true;
        $rights = \CMain::GetUserRight(self::$MODULE_ID);
        $DEPTH = array('D'=>1,'R'=>2,'W'=>3);
        return($DEPTH[$min] <= $DEPTH[$rights]);
    }

    /**
     * @return bool
     * Пропускать ли проверку на админа
     */
    public static function isSkipAdminCheck()
    {
        return self::$skipAdminCheck;
    }

    /**
     * @param bool $skipAdminCheck
     */
    public static function setSkipAdminCheck($skipAdminCheck)
    {
        self::$skipAdminCheck = $skipAdminCheck;
    }

    // COMMON

    /**
     * @param $code
     * @return string
     * Получение лэнговой строки
     */
    static function getMessage($code)
    {
        return GetMessage('CATAPULTO_DELIVERY_'.$code);
    }

    /**
     * @return string
     * Путь к файлам js-а
     */
    static function getJSPath()
    {
        return '/bitrix/js/'.self::$MODULE_ID.'/';
    }

    /**
     * @return string
     * Путь к файлам с картинками
     */
    static function getImagePath()
    {
        return '/bitrix/images/'.self::$MODULE_ID.'/';
    }

    /**
     * @param $array
     * @return string
     * По факту то же, что и CUtil::PhpToJSObject, только хуже. Лучше не юзать
     */
    static function arrToJs($array){
        if(!is_array($array))
            return $array;
        else{
            $ret = '{';
            foreach($array as $key => $value){
                $ret .= $key.' : "'.self::arrToJs($value).'",';
            }
            $ret .= '}';
            return $ret;
        }
    }

    /**
     * @param $wat
     * @return string
     * Преобразовать данные в json (энкод + кодировка)
     */
    public static function jsonEncode($wat)
    {
        return json_encode(self::encodeToUTF8($wat), JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param $handle
     * @return array
     * Преобразует данные из кодировки сайта в utf-8
     */
    public static function encodeToUTF8($handle){
        if(LANG_CHARSET !== 'UTF-8') {
            if (is_array($handle)) {
                foreach ($handle as $key => $val) {
                    unset($handle[$key]);
                    $key          = self::encodeToUTF8($key);
                    $handle[$key] = self::encodeToUTF8($val);
                }
            } elseif (is_object($handle)){
                $arCorresponds = array(); // why = because
                foreach($handle as $key => $val){
                    $arCorresponds[$key] = ['utf_key' => self::encodeToUTF8($key), 'utf_val' => self::encodeToUTF8($val)];
                }
                foreach($arCorresponds as $key => $new)
                {
                    unset($handle->$key);
                    $utf_key = $new['utf_key'];
                    $handle->$utf_key = $new['utf_val'];
                }
            }else {
                $handle = $GLOBALS['APPLICATION']->ConvertCharset($handle, LANG_CHARSET, 'UTF-8');
            }
        }
        return $handle;
    }

    /**
     * @param $handle
     * @return array
     * Преобразует данные из utf-8 в кодировку сайта
     */
    public static function encodeFromUTF8($handle){
        if(LANG_CHARSET !== 'UTF-8'){
            if(is_array($handle)) {
                foreach ($handle as $key => $val) {
                    unset($handle[$key]);
                    $key          = self::encodeFromUTF8($key);
                    $handle[$key] = self::encodeFromUTF8($val);
                }
            } elseif (is_object($handle)){
                $arCorresponds = array();
                foreach($handle as $key => $val){
                    $arCorresponds[$key] = ['site_encode_key' => self::encodeFromUTF8($key), 'site_encode_val' => self::encodeFromUTF8($val)];
                }
                foreach($arCorresponds as $key => $new)
                {
                    unset($handle->$key);
                    $site_encode_key = $new['site_encode_key'];
                    $handle->$site_encode_key = $new['site_encode_val'];
                }
            } else {
                $handle = $GLOBALS['APPLICATION']->ConvertCharset($handle, 'UTF-8', LANG_CHARSET);
            }
        }
        return $handle;
    }

    /**
     * @return bool
     * Проверяет, есть ли на хите запрос по аяксу от модуля
     */
    public static function isModuleAjaxRequest(){
        return (array_key_exists(self::$MODULE_LBL.'action',$_REQUEST)&& $_REQUEST[self::$MODULE_LBL.'action']);
    }

    /**
     * Подключение общих стилей модуля (опции, админки, итп).
     * Это попапы дял хинтов, стили оповещений и ошибок
     */
    static function getCommonCss(){?>
        <style>
            .<?=self::$MODULE_LBL?>errInput{
                background-color: #ffb3b3 !important;
            }
            .<?=self::$MODULE_LBL?>PropHint, .<?=self::$MODULE_LBL?>PropHint:hover{
                background: url("/bitrix/images/<?=self::$MODULE_ID?>/hint.gif") no-repeat transparent !important;
                text-decoration: none !important;
                display: inline-block;
                height: 12px;
                position: relative;
                width: 12px;
            }
            .<?=self::$MODULE_LBL?>b-popup {
                background-color: #FEFEFE;
                border: 1px solid #9A9B9B;
                box-shadow: 0px 0px 10px #B9B9B9;
                display: none;
                font-size: 12px;
                padding: 19px 13px 15px;
                position: absolute;
                top: 38px;
                width: 300px;
                z-index: 50;
                text-align: initial;
            }
            .<?=self::$MODULE_LBL?>b-popup .<?=self::$MODULE_LBL?>pop-text {
                margin-bottom: 10px;
                color:#000;
            }
            .<?=self::$MODULE_LBL?>pop-text i {color:#AC12B1;}
            .<?=self::$MODULE_LBL?>b-popup .<?=self::$MODULE_LBL?>close {
                background: url("/bitrix/images/<?=self::$MODULE_ID?>/popup_close.gif") no-repeat transparent;
                cursor: pointer;
                height: 10px;
                position: absolute;
                right: 4px;
                top: 4px;
                width: 10px;
            }
            .<?=self::$MODULE_LBL?>warning{
                color:red !important;
            }
            .<?=self::$MODULE_LBL?>hidden {
                display:none !important;
            }
            .<?=self::$MODULE_LBL?>popup-modal {
                height: calc(100% - 40px);
                max-width: 1050px;
                background-color: #fff;
                position: fixed;
                display: none;
                transition: all 300ms ease-in-out;
                z-index: 1150;
                left: calc(50% - 481px);
                top: 20px;
            }
            .ctpt_popup_mode.ctpt-widget.second-step {
                height: 100%;
            }
            .ctpt-widget {
                height: 100% !important;
            }
            @media screen and (max-width: 1090px){
                div.<?=self::$MODULE_LBL?>popup-modal {
                    left: 2vw;
                    width: 96vw;
                }
            }
            @media screen and (max-width: 767px){
                div.<?=self::$MODULE_LBL?>popup-modal {
                    left: 5px;
                    height: calc(100% - 30px);
                    padding: 0;
                    width: calc(100% - 10px);
                }
            }
            .<?=self::$MODULE_LBL?>popup-modal.is--visible {
                display: block;
                pointer-events: auto;
            }
            .<?=self::$MODULE_LBL?>popup-modal__close {
                position: absolute;
                right: 5px;
                top: 5px;
                cursor: pointer;
                font-style: initial;
                width: 36px;
                height: 36px;
                padding: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                border: none;
                background: transparent;
                transition: all 0.15s;
            }
            .<?=self::$MODULE_LBL?>popup-modal__close svg path {
                transition: all 0.15s;
            }
            .<?=self::$MODULE_LBL?>popup-modal__close:hover svg path {
                fill: #000000;
                opacity: 1;
            }
            .<?=self::$MODULE_LBL?>popup-modal__close span {
                font-size: 2rem;
                transform: rotate(45deg);
            }
            .<?=self::$MODULE_LBL?>pvzAddr {
                border: 1px solid #63aa28;
                border-radius: 4px;
                padding: 3px 6px;
                width: 100%;
                display: block;
                margin: 4px 0;
            }
            .<?=self::$MODULE_LBL?>body-blackout {
                position: fixed;
                z-index: 1010;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, .65);
                display: none;
            }
            .<?=self::$MODULE_LBL?>body-blackout.is-blacked-out {
                display: block;
            }
            .<?=self::$MODULE_LBL?>opIcon {
                margin-left: 10px;
                max-height: 20px;
            }

            .<?=self::$MODULE_LBL?>flx {
                width: 100%;
                display: flex;
                gap: 10px;
            }

            .<?=self::$MODULE_LBL?>operatorstable p {
                margin: 0;
            }
            p.ctpt_title {
                text-align: center;
                width: 100%;
                font-weight: 700;
            }
            .ctpt_op_code_wr {
                vertical-align: top;
            }

            .<?=self::$MODULE_LBL?>operatorstable .ctpt_op_stype {
                width: 120px;
                min-width: 120px;
            }


            .ctpt_opterm_code {
                width: 70% !important;
            }

            @media only screen and (max-width: 767px) {
                .<?=self::$MODULE_LBL?>popup-modal {
                    height: 100vh;
                    width: 100vw;
                    display: flex;
                    align-items: center;
                    padding: 45px 0 0;
                }
            }
        </style>
    <?}

    // OPTIONS
    /**
     * @param $code
     * Помещает FAQ с кодом code (то есть, лэновая приписка должна выглядеть как <общий лэнг модуля>_FAQ_$code_TITLE и _DESCR соответственно
     */
    static function placeFAQ($code){?>
        <a class="ipol_header" onclick="$(this).next().toggle(); return false;"><?=self::getMessage('FAQ_'.$code.'_TITLE')?></a>
        <div class="ipol_inst"><?=self::getMessage('FAQ_'.$code.'_DESCR')?></div>
    <?}

    /**
     * @param $code
     * Установка блока с подсказкой
     */
    static function placeHint($code){?>
        <div id="pop-<?=$code?>" class="<?=self::$MODULE_LBL?>b-popup" style="display: none; ">
            <div class="<?=self::$MODULE_LBL?>pop-text"><?=self::getMessage("HELPER_".$code)?></div>
            <div class="<?=self::$MODULE_LBL?>close" onclick="$(this).closest('.<?=self::$MODULE_LBL?>b-popup').hide();"></div>
        </div>
    <?}
    
    static function placeHintMultiple($id, $messCode): void
    {?>
        <div id="pop-<?=$id?>" class="<?=self::$MODULE_LBL?>b-popup" style="display: none; ">
            <div class="<?=self::$MODULE_LBL?>pop-text"><?=self::getMessage($messCode)?></div>
            <div class="<?=self::$MODULE_LBL?>close" onclick="$(this).closest('.<?=self::$MODULE_LBL?>b-popup').hide();"></div>
        </div>
    <?}

    /**
     * @param $id - id селекта
     * @param $vals - значения вида код => текст
     * @param bool $def - код значения по умолчанию
     * @param string $atrs - атрибуты селекта
     * @return string
     * Делает селект из предоставленных значений
     */
    static function makeSelect($id, $vals, $def=false, $atrs=''){
        $select = "<select ".(($id) ? "name='".((strpos($atrs,'multiple')===false)?$id:$id.'[]')."' id='{$id}' " : '' )." {$atrs}>";
			if(is_array($vals)){
				foreach($vals as $val => $sign)
					$select .= "<option value='{$val}' ".(((is_array($def) && in_array($val,$def)) || $def == $val )?'selected':'').">{$sign}</option>";
			}
        $select .= "</select>";

        return $select;
    }

    /**
     * @param $id - id и name радиокнопок
     * @param $vals - значения вида код => текст
     * @param bool $def - код значения по умолчанию
     * @param string $atrs - атрибуты радиокнопок
     * @return string
     *
     * Делает блок радиокнопок
     */
    static function makeRadio($id, $vals, $def=false, $atrs=''){
        $radio = "";
        if(is_array($vals)){
            foreach ($vals as $val => $sign){
                $checked = ($val == $def) ? 'checked' : '';
                $radio .= "<input type='radio' {$atrs} {$checked} name='{$id}' id='".$id.'_'.$val."' value='{$val}'>&nbsp;<label for='".$id.'_'.$val."'>{$sign}</label><br>";
            }
        }

        return $radio;
    }

    /**
     * @param $code
     * makes da heading, FAQ und send command to establish included options
     */
    static function placeOptionBlock($code,$isHidden=false)
    {
        global $arAllOptions;
        ?>
        <tr class="heading"><td colspan="2" valign="top" align="center" <?=($isHidden) ? "class='".self::$MODULE_LBL."headerLink' onclick='".self::$MODULE_LBL."setups.getPage(\"main\").showHidden($(this))'" : ''?>><?=self::getMessage("HDR_".$code)?></td></tr>
        <?if(self::getMessage('FAQ_'.$code.'_TITLE')){?>
            <tr><td colspan="2"><?self::placeFAQ($code)?></td></tr>
        <?}
        if(Logger::getLogInfo($code)){
            self::placeWarningLabel(Logger::toOptions($code),self::getMessage("WARNING_".$code),150,array('name'=>Tools::getMessage('LBL_CLEAR'),'action'=>'CATAPULTO_DELIVERY_setups.getPage("main").clearLog("'.$code.'")','id'=>'clear'.$code));
        }
        if(array_key_exists($code,$arAllOptions)) {
            ShowParamsHTMLByArray($arAllOptions[$code], $isHidden);

            $collection = Option::collection();
            foreach ($arAllOptions[$code] as $arOption){
                if(
                    array_key_exists($arOption[0],$collection) &&
                    $collection[$arOption[0]]['hasHint'] == 'Y'
                ){
                    \Ipol\Catapulto\Bitrix\Tools::placeHint($arOption[0]);
                }
            }
        }
    }
    
    public static function getMarkupAvailable(): bool
    {
        $payNal  = Option::get('payNal');
        $payCard = Option::get('payCard');
        
        return ((empty($payNal) && empty($payCard)) || ($payNal === 'N;' && $payCard === 'N;'));
    }

    public static function placeOptionTerminalMarkups()
    {
        global $arAllOptions;
        if (!isset($arAllOptions['markupOperatorsVals'])) return '';

        ?>
        <tr class="heading"><td colspan="2" valign="top" align="center"><?=self::getMessage("HDR_markups")?></td></tr>
        <?php if(self::getMessage('FAQ_markups_TITLE')){?>
            <tr><td colspan="2"><?self::placeFAQ('markups')?></td></tr>
        <?php }

        foreach ($arAllOptions['markupOperatorsValsStatic'] as $staticOption) {
            if ($staticOption[3][0] == 'selectbox') {
                $optVal     = Option::get($staticOption[0]);
                $selectVals = Option::getSelectVals($staticOption[0]);
                self::placeOptionRow($staticOption[1],(($selectVals) ? self::makeSelect($staticOption['0'],$selectVals,$optVal,'') : $optVal));
            } else __AdmSettingsDrawRow('catapulto.delivery', $staticOption);
        }

        $optionsNotEmpty = false;
        foreach ($arAllOptions['markupOperatorsVals'] as &$terminal) {
            $terminal[1000] = Option::get('markupOperatorsVals_' . $terminal[6]['id']);
            $terminal[1001] = Option::get('markupOperatorsTypes_' . $terminal[6]['id']);
            if (!empty($terminal[1000])) $optionsNotEmpty = true;
        }
        unset($terminal);
        ?>
            <tr class="<?=self::$MODULE_LBL?>markupTitles<?=($optionsNotEmpty)?' sh':''?>"><td colspan="2"><hr></td></tr>
            <tr class="<?=self::$MODULE_LBL?>markupTitles<?=($optionsNotEmpty)?' sh':''?>"><td colspan="2" align="center"><p class="h"><?=Tools::getMessage("MK_manageMK")?>:</p></td></tr>
            <tr class="<?=self::$MODULE_LBL?>markupTitles<?=($optionsNotEmpty)?' sh':''?>">
                <td width="50%" class="adm-detail-content-cell-l"></td>
                <td width="50%" class="adm-detail-content-cell-r">
                    <div class="<?=self::$MODULE_LBL?>tbl">
                        <p class="mkVal"><?=Tools::getMessage("MK_LBL_value")?></p>
                        <p class="mkType"><?=Tools::getMessage("MK_LBL_type")?></p>
                        <p class="mkDel"><?=Tools::getMessage("MK_LBL_delete")?></p>
                    </div>
                </td>
            </tr>
        <?php

        $operatorCodes = [];
        foreach ($arAllOptions['markupOperatorsVals'] as $terminal) {
            $operatorCodes[] = '{i:"'.$terminal[6]['id'].'",n:"'.$terminal[1].'"}';

            $currentVal = $terminal[1000];
            $currentType = $terminal[1001];

            if (empty($currentVal)) {
            ?>
                <input type="hidden" id="CATAPULTO_DELIVERY_<?=$terminal[0]?>" name="<?=$terminal[0]?>" value="" />
            <?php
            } else {
            ?>
                <tr class="<?=self::$MODULE_LBL?>markupOperators" id="<?=self::$MODULE_LBL?>markupOperatorsTypes_<?=$terminal[6]['id']?>_wr">
                    <td width="50%" class="adm-detail-content-cell-l"><?=$terminal[1]?></td>
                    <td width="50%" class="adm-detail-content-cell-r">
                        <div class="<?=self::$MODULE_LBL?>tbl">
                            <input type="text" name="markupOperatorsVals_<?=$terminal[6]['id']?>" id="<?=self::$MODULE_LBL?>markupOperatorsVals_<?=$terminal[6]['id']?>" value="<?=$currentVal?>" onkeyup="<?=self::$MODULE_LBL?>setups.getPage('main').onMarkupKeyup(this)" onblur="<?=self::$MODULE_LBL?>setups.getPage('main').onMarkupBlur(this)" />
                            <select name="markupOperatorsTypes_<?=$terminal[6]['id']?>" id="<?=self::$MODULE_LBL?>markupOperatorsTypes_<?=$terminal[6]['id']?>"><option value="0"<?=($currentType=='0')?' selected':''?>><?=Tools::getMessage("MK_LBL_fixed")?></option><option value="1"<?=($currentType=='1')?' selected':''?>><?=Tools::getMessage("MK_LBL_persent")?></option></select>
                            <span class="delete" onclick="<?=self::$MODULE_LBL?>setups.getPage('main').onDeleteMarkupOption(this)"><span></span><span></span></span>
                        </div>
                    </td>
                </tr>
            <?php
            }
        }
        ?>
        <tr><td colspan="2"><hr></td></tr>
        <tr>
            <td colspan="2"><input type="button" id="catapulto_markup_addoperatorbtn" onclick="<?=self::$MODULE_LBL?>setups.getPage('main').onMarkupAdd()" value="<?=Tools::getMessage("MK_addOperator")?>" /></td>
        </tr>
        <script>window.catapulto_operators = [<?=implode(",",$operatorCodes)?>];</script>
        <?php
    }

    /**
     * @param $name
     * @param $val
     * Draws tr-td. That's all. Bwahahahaha.
     */
    static function placeOptionRow($name, $val){
        if($name){?>
            <tr>
                <td width='50%' class='adm-detail-content-cell-l'><?=$name?></td>
                <td width='50%' class='adm-detail-content-cell-r'><?=$val?></td>
            </tr>
        <?}else{?>
            <tr><td colspan = '2' style='text-align: center'><?=$val?></td></tr>
        <?}?>
    <?}

    static function placeDoubleOptionRowFirst($Option, $params = [])
    {
        $attrs = $params['attrs'] ?? '';
        $value = $params['USE_DEFAULT'] ? Option::getDefault($Option[0]) : Option::get($Option[0]);
        if ($Option[3][0] == 'selectbox') {
            $selectVals = Option::getSelectVals($Option[0]);
            $val        = (($selectVals) ? self::makeSelect($Option['0'], $selectVals, $value, $attrs) : $value);
        }
        else {
            $val = "<input " . $attrs . " type=\"text\" name='" . $Option['0'] . "' id='" . $Option['0'] . "' value='" . $value . "'>";
        }
        ?>
        <tr>
            <td width='50%' class='adm-detail-content-cell-l'><?= $Option[1] ?></td>
            <td width='50%' class='adm-detail-content-cell-r'>
                <div class="flex-box"><?= $val ?>
    <?
    }
    
    static function placeDoubleOptionRowSecond($Option, $params = [])
    {
        $attrs = $params['attrs'] ?? '';
        $value = $params['USE_DEFAULT'] ? Option::getDefault($Option[0]) : Option::get($Option[0]);
        if ($Option[3][0] == 'selectbox') {
            $selectVals = Option::getSelectVals($Option[0]);
            $val        = (($selectVals) ? self::makeSelect($Option['0'], $selectVals, $value, $attrs) : $value);
        }
        else {
            $val = "<input " . $attrs . " type=\"text\" name='" . $Option['0'] . "' id='" . $Option['0'] . "' value='" . $value . "'>";
        }
        ?>
                   <div class="flex-box">
                        <div class="flex-item"><?=$Option[1]?></div>
                        <div class="flex-item"><?=$val?></div>
                   </div>
                </div>
            </td>
        </tr>
    <?}

    static function defaultOptionPath()
    {
        return "/bitrix/modules/".self::$MODULE_ID."/optionsInclude/";
    }

    /**
     * @param Table $obTable
     */
    static function placeOptionTable($obTable)
    {
        ?>
        <tr><td colspan="2">
            <?$obTable->placeHTML()?>
        </td></tr>
        <?
    }

    // SEND ORDER
    // ВНИМАНИЕ! Данные методы приведены для ПРИМЕРА, их можно удалить. Тут представлены методы для работы с формой отправки заказа

    static function placeSOHeaderRow($code,$link=false,$headerClass='')
    {?>
        <tr class="heading <?=(($headerClass) ? self::$MODULE_LBL.$headerClass : '')?>">
            <td colspan="2">
                <?=($link)?'<a href="javascript:void(0)" onclick="'.$link.'">':''?><?=self::getMessage('HDR_'.$code)?><?=($link)?'</a>':''?>
                <?if(self::getMessage('HELPER_'.$code)){?> <a href='#' class='<?=self::$MODULE_LBL?>PropHint' onclick='return <?=self::$MODULE_LBL?>export.popup("pop-<?=$code?>", this,"#<?=self::$MODULE_LBL?>wndOrder");'></a><?self::placeHint($code);}?>
            </td>
        </tr>
    <?}

    static function placeSORow($code,$type,$def=false,$vals=false,$attrs=false,$trClass = false){
        if($type !== 'select' && $type !== 'radio'){
            $attrs = "id='".self::$MODULE_LBL.$code."' name='".self::$MODULE_LBL.$code."' ".$attrs;
        }
        $class = '';
        if($trClass){
            $class = 'class="';
            if(is_array($trClass)){
                foreach($trClass as $className){
                    $class .= self::$MODULE_LBL.$className.' ';
                }
            }
            else{
                $class .= self::$MODULE_LBL.$trClass;
            }
            $class .= '"';
        }
        ?>
        <tr <?=$class?>>
            <td>
                <label for="<?=self::$MODULE_LBL?><?=$code?>"><?=self::getMessage('LBL_'.$code)?></label>
                <?if($hint = Tools::getMessage('HELPER_'.$code)){?>
                    <a href='#' class='<?=self::$MODULE_LBL?>PropHint' onclick='return <?=self::$MODULE_LBL?>export.popup("pop-<?=$code?>", this,"#<?=self::$MODULE_LBL?>wndOrder");'></a>
                <?  self::placeHint($code);
                }?>
            </td><td>
        <?
        switch($type){
            case 'text'     : ?><input type="text" <?=$attrs?> value="<?=htmlspecialchars($def)?>"/><? break;
            case 'radio'    : echo self::makeRadio(self::$MODULE_LBL.$code,$vals,$def,$attrs);break;
            case 'select'   : echo self::makeSelect(self::$MODULE_LBL.$code,$vals,$def,$attrs); break;
            case 'sign'     : echo $def; break;
            case 'checkbox' : ?><input type="checkbox" <?=$attrs?> value="Y" <?=($def)?'checked':''?>/><?break;
            case 'textbox'  : ?><textarea <?=$attrs?>><?=$def?></textarea><? break;
            case 'hidden'   : ?><input type="hidden" <?=$attrs?>  value="<?=$def?>"/><span id="<?=self::$MODULE_LBL?>hidLabel_<?=$code?>"><?=$def?></span><? break;
        }
        ?></td></tr><?
    }

    // == END методы работы с заказом

    /**
     * @param $content
     * @param bool $header
     *
     * Вывод окна с ошибкой
     */
    static function placeErrorLabel($content, $header=false)
    {?>
        <tr><td colspan='2'>
            <div class="adm-info-message-wrap adm-info-message-red">
                <div class="adm-info-message">
                    <?if($header){?><div class="adm-info-message-title"><?=$header?></div><?}?>
                    <?=$content?>
                    <div class="adm-info-message-icon"></div>
                </div>
            </div>
        </td></tr>
    <?}

    /**
     * @param $content
     * @param bool $header
     * @param bool $heghtLimit
     * @param bool $click
     *
     * Вывод окна с предупреждением
     */
    static function placeWarningLabel($content, $header=false, $heghtLimit=false, $click=false)
    {?>
        <tr><td colspan='2'>
            <div class="adm-info-message-wrap">
                <div class="adm-info-message" style='color: #000000'>
                    <?if($header){?><div class="adm-info-message-title"><?=$header?></div><?}?>
                    <?if($click){?><input type="button" <?=($click['id'] ? 'id="'.self::$MODULE_LBL.$click['id'].'"' : '')?> onclick='<?=$click['action']?>' value="<?=$click['name']?>"/><?}?>
                    <div <?if($heghtLimit){?>style="max-height: <?=$heghtLimit?>px; overflow: auto;"<?}?>>
                        <?=$content?>
                    </div>
                </div>
            </div>
        </td></tr>
    <?}

    // STUFF

    // Как правило, солянка для работы с СД

    static public function getB24URLs()
    {
        return array (
            'ORDER' => '/shop/orders/details/',
            'SHIPMENT' => '/shop/orders/shipment/details/',
        );
    }

    public static function getDeliveryIdHref($deliveryId){
        return "/bitrix/admin/sale_delivery_service_edit.php?PARENT_ID=0&ID={$deliveryId}";
    }

    public static function getProfileIdHref($profile_id,$deliveryId){
        return "/bitrix/admin/sale_delivery_service_edit.php?PARENT_ID={$deliveryId}&ID={$profile_id}";
    }

    public static function getOrderLink($id){
        return "/bitrix/admin/sale_order_view.php?ID={$id}";
    }

    public static function getShipmentLink($shipmentId,$orderId){
        return "/bitrix/admin/sale_order_shipment_edit.php?order_id={$orderId}&shipment_id={$shipmentId}";
    }

    public static function isConverted()
    {
        return (\COption::GetOptionString("main","~sale_converted_15",'N') == 'Y');
    }

    public static function isAdminSection(){
        if (class_exists('\\Bitrix\\Main\\Request') && method_exists('\\Bitrix\\Main\\Request','isAdminSection'))
        {
            $request = \Bitrix\Main\Context::getCurrent()->getRequest();
            $result = $request->isAdminSection();
        }
        else
            $result = defined('ADMIN_SECTION') && ADMIN_SECTION === true;

        return ($result || self::isB24Section());
    }

    public static function isB24Section()
    {
        return (defined('SITE_TEMPLATE_ID') && SITE_TEMPLATE_ID === "bitrix24");
    }

    public static function formatCurrency($val, $currency = 'RUB', $template=true)
    {
        try {
            \Bitrix\Main\Loader::includeModule('sale');
            return \CCurrencyLang::CurrencyFormat($val, $currency, $template);
        }catch(\Exception $e){
            return $val;
        }
    }

    public static function makeSimpleGood($params = array())
    {
        $arGood = array(
            "MODULE"     => self::$MODULE_ID.'Delivery',
            "NAME"       => 'testGood',
            "CAN_BUY"    => 'Y',
            "DELAY"      => 'N',
            "SUBSCRIBE"  => 'N',
            "RESERVED"   => 'N',
            "QUANTITY"   => (array_key_exists("QUANTITY", $params)) ? $params["QUANTITY"] : 1,
            "LID"        => (array_key_exists("LID", $params)) ? $params["LID"] : SITE_ID,
            "CURRENCY"   => (array_key_exists("CURRENCY", $params)) ? $params['CURRENCY'] : 'RUB',
            "DIMENSIONS" => array(
                "WIDTH"  => (array_key_exists("WIDTH", $params))  ? $params["WIDTH"]  : 0,
                "HEIGHT" => (array_key_exists("HEIGHT", $params)) ? $params["HEIGHT"] : 0,
                "LENGTH" => (array_key_exists("LENGTH", $params)) ? $params["LENGTH"] : 0
            )
        );

        foreach(array('ID','PRODUCT_ID','SET_PARENT_ID','PRICE','WEIGHT','BASE_PRICE') as $key)
            $arGood[$key] = (array_key_exists($key, $params)) ? $params[$key] : 0;

        return $arGood;
    }

    public static function getDayEnd($day)
    {
        if(strpos($day,'-') !== false){
            $check = explode('-',$day);
            $day = intval(trim($check[1]));
        }
        if(($day > 4 && $day < 21) || $day == 0)
            $label = Tools::getMessage('DELIV_DAYS');
        else{
            $lst = $day % 10;
            if($lst == 1)
                $label = Tools::getMessage('DELIV_DAY');
            elseif($lst < 5)
                $label = Tools::getMessage('DELIV_DAYA');
            else
                $label = Tools::getMessage('DELIV_DAYS');
        }

        return $label;
    }

    // service
    public static function getArrVal($key,$arr)
    {
        return (array_key_exists($key,$arr)) ? $arr[$key] : false;
    }

    //include jquery
    public static function jqInclude()
    {
        $jsExtensions = [\CJSCore::IsExtRegistered('jquery3') ? 'jquery3' : 'jquery'];
        \CJSCore::Init($jsExtensions);
    }

    //get jquery path
    public static function getJqPath()
    {
        $jsExtensionsInfo = \CJSCore::getExtInfo(\CJSCore::IsExtRegistered('jquery3') ? 'jquery3' : 'jquery');
        return $jsExtensionsInfo['js'] ?? '';
    }


}
