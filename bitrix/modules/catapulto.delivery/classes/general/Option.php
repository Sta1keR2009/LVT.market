<?php
namespace Ipol\Catapulto;

use Ipol\Catapulto\Bitrix\Adapter;
use Ipol\Catapulto\Bitrix\Entity\BasicResponse;
use Ipol\Catapulto\Bitrix\Handler\PaySystems;
use Ipol\Catapulto\Bitrix\Handler\Statuses;
use Ipol\Catapulto\Bitrix\Tools;

IncludeModuleLangFile(__FILE__);

/**
 * Class option
 * @package Ipol\Catapulto
 * Класс для хранения и работы с опциями модуля
 */
class Option extends AbstractGeneral
{
    // optionsControll
    public static $ABYSS = array();

    public static $collection  = false;

    /**
     * @param $option
     * @return mixed|string
     * Получение значения опции модуля. Лучше обычного Coption, так как берет значение по умолчанию из одного места.
     * Если опция с разными значениями - десериализует его.
     */
    public static function get($option)
    {
        $self = \COption::GetOptionString(self::$MODULE_ID,$option,self::getDefault($option));
        if(
            unserialize($self) &&
            self::checkMultiple($option)
        )
            $self = unserialize($self);
        return $self;
    }

    /**
     * @param $option
     * @param $val
     * @param bool $doSerialise
     * Установка опции. Если надо серилиазовать - собственно, делает это.
     */
    public static function set($option, $val, $doSerialise = false)
    {
        if($doSerialise){
            $val = serialize($val);
        }
        $self = \COption::SetOptionString(self::$MODULE_ID,$option,$val);
    }

    /**
     * @param $option
     * @return bool
     * Получение дефолтного значения опции
     */
    public static function getDefault($option)
    {
        $opt = self::collection();
        if(array_key_exists($option,$opt))
            return $opt[$option]['default'];
        return false;
    }

    /**
     * @param $option
     * @return bool
     * Проверяет, может ли опция быть установлена в несколько значений
     */
    public static function checkMultiple($option)
    {
        $opt = self::collection();
        if(array_key_exists($option,$opt) && array_key_exists('multiple',$opt[$option]))
            return $opt[$option]['multiple'];
        return false;
    }

    /**
     * @param bool $helpMakros
     * @return array
     * Выводит массив для отображения опции в Битриксе. Если есть подсказка - добавляет ее. Нужно для ShowParamsHTMLByArray
     */
    public static function toOptions($helpMakros = false)
    {
        if(!$helpMakros)
            $helpMakros = "<a href='#' class='".self::$MODULE_LBL."PropHint' onclick='return ".self::$MODULE_LBL."setups.popup(\"pop-#CODE#\", this);'></a>";

        $arOptions = array();
        foreach(self::collection() as $optCode => $optVal){
            if(!array_key_exists('group',$optVal) || !$optVal['group'])
                continue;

            if (!array_key_exists($optVal['group'], $arOptions))
                $arOptions[$optVal['group']] = array();

            $name = ($optVal['hasHint'] == 'Y') ? " ".str_replace('#CODE#',$optCode,$helpMakros) : '';

            $optionType = [$optVal['type']];
            $optionType[] = $optVal['size'] ?? '';
            $optionType[] = $optVal['attr'] ?? '';
            $arDescription = array(
                $optCode,
                ($optVal['name'] ?? Tools::getMessage("OPT_{$optCode}")).$name, // dynamic name from array or static from lang file
                $optVal['default'],
                $optionType,
                'N',
                false,
                $optVal['additionalData'] ?? false
            );

            if(array_key_exists('required', $optVal) && $optVal['required']){
                $arDescription []= ' *';
            }

            $arOptions[$optVal['group']][] = $arDescription;
        }

        return $arOptions;
    }

    /**
     * @return array
     * Список всех опций модуля в формате
     * group - в какой группе располагается опция (для ShowParamsHTMLByArray($arAllOptions["группа"]);?
     * hasHint - Y/N , есть ли у опции подсказка (надо выводить значок вопроса)
     * default - дефолтное значение. Чтобы не вспоминать, какое же оно было когда-то объявлено, сразу подставляется в метод get
     * type - тип опции (примерно по аналогии с Битриксом): text, checkbox, selectbox, textbox
     * multiple - принимает несколько значений
     * required - опция обязательна для заполнения, если true
     *
     * Если опция формата selectbox - см. функцию getSelectVals
     */
    public static function collection()
    {
        if(self::$collection){
            $arOptions = self::$collection;
        } else {
            // name - always CATAPULTO_DELIVERY_OPT_<code>
            $arOptions = [
                // сразу группируем по группам и пишем группу
                // auth
                // опция с кодом key в группе auth без подсказки, по дефолту - пустая
                'apikey' => [
                    'group' => 'auth',
                    'hasHint' => 'N',
                    'default' => '',
                    'type' => 'text',
                    'required' => true
                ],

                // common
                // опция с кодом showInOrders в группе common с подсказкой, по дефолту - Y. В getSelectVals стоят значения для селекта.
                'showInOrders' => [
                    'group' => 'common',
                    'hasHint' => 'Y',
                    'default' => 'N',
                    'type' => 'selectbox'
                ],
                'dadataApikey' => [
                    'group' => 'common',
                    'hasHint' => 'Y',
                    'type' => 'text',
                    'required' => true
                ],
                'widgetYandexKey' => [
                    'group' => 'common',
                    'hasHint' => 'Y',
                    'type' => 'text',
                    'required' => \Bitrix\Main\Config\Option::get(self::$MODULE_ID, 'system_widget_yandex_key_required', '') === 'Y'
                ],


                // тип опции - special. Это означает, что выводится она не просто так: в options.php в ShowParamsHTMLByArray показано, как именно

                // defaultCargo
                // Габариты по умолчанию
                'defMode' => array(
                    'group'   => 'defaultCargo',
                    'hasHint' => 'N',
                    'default' => 'O',
                    'type'    => 'selectbox'
                ),
                'defaultWidth' => [
                    'group' => 'defaultCargo',
                    'hasHint' => 'N',
                    'default' => '200',
                    'type' => 'text'
                ],
                'defaultHeight' => [
                    'group' => 'defaultCargo',
                    'hasHint' => 'N',
                    'default' => '300',
                    'type' => 'text'
                ],
                'defaultLength' => [
                    'group' => 'defaultCargo',
                    'hasHint' => 'N',
                    'default' => '100',
                    'type' => 'text'
                ],
                'defaultWeight' => [
                    'group' => 'defaultCargo',
                    'hasHint' => 'N',
                    'default' => '500',
                    'type' => 'text'
                ],

                // delivery
                'deliveryDefaultPrice' => array(
                    'group' => 'delivery',
                    'hasHint' => 'Y',
                    'default' => '99',
                    'type' => "text"
                ),
                'termIncrease' => array(
                    'group' => 'delivery',
                    'hasHint' => 'Y',
                    'default' => '0',
                    'type' => "text"
                ),/*
                'mindEnsurance' => array(
                    'group'   => 'delivery',
                    'hasHint' => 'Y',
                    'default' => 'N',
                    'type'    => 'checkbox'
                ),*/
                'smsAmount' => array(
                    'group'   => 'delivery',
                    'hasHint' => 'Y',
                    'default' => 'N',
                    'type'    => 'checkbox'
                ),
                'noPVZnoOrder' => array(
                    'group'   => 'delivery',
                    'hasHint' => 'Y',
                    'default' => 'Y',
                    'type'    => 'checkbox'
                ),
                'needReselect' => array(
                    'group'   => 'delivery',
                    'hasHint' => 'Y',
                    'default' => 'N',
                    'type'    => 'checkbox',
                ),
                'isFitting' => [
                    'group'   => 'delivery',
                    'hasHint' => 'Y',
                    'default' => 'N',
                    'type'    => 'checkbox',
                ],
                'fittingDefaultEnabled' => [
                    'group'   => 'delivery',
                    'hasHint' => 'Y',
                    'default' => 'N',
                    'type'    => 'checkbox',
                ],
                'partialRedemptionEnabled' => [
                    'group'   => 'delivery',
                    'hasHint' => 'Y',
                    'default' => 'N',
                    'type'    => 'checkbox',
                ],
                //order's shipment and payment update after select new delivery variant
                'updateOrderDelivery' => array(
                    'group'   => 'delivery',
                    'hasHint' => 'Y',
                    'default' => 'Y',
                    'type'    => 'checkbox'
                ),
                
                'deliveryCourierMarkupType' => [
                    'group'   => 'delivery',
                    'hasHint' => 'Y',
                    'default' => 'N',
                    'type'    => 'selectbox',
                    'required' => false
                ],
                'deliveryCourierMarkupValue' => [
                    'group'   => 'delivery',
                    'hasHint' => 'Y',
                    'default' => '',
                    'type'    => 'text',
                    'required' => false
                ],
                'deliveryPvzMarkupType' => [
                    'group'   => 'delivery',
                    'hasHint' => 'Y',
                    'default' => 'N',
                    'type'    => 'selectbox',
                    'required' => false
                ],
                'deliveryPvzMarkupValue' => [
                    'group'   => 'delivery',
                    'hasHint' => 'Y',
                    'default' => '',
                    'type'    => 'text',
                    'required' => false
                ],

                //MarkUp
                'markupValue' => [
                    'group'   => 'markupOperatorsValsStatic',
                    'hasHint' => 'N',
                    'default' => '0',
                    'type'    => 'text'
                ],
                'markupType' => [
                    'group'   => 'markupOperatorsValsStatic',
                    'hasHint' => 'N',
                    'default' => '0',
                    'type'    => 'selectbox'
                ],

                // statuses
                'status_courier_take' => array(
                    'group'   => 'statuses',
                    'hasHint' => 'N',
                    'default' => '',
                    'type'    => 'selectbox'
                ),
                'status_on_road' => array(
                    'group'   => 'statuses',
                    'hasHint' => 'N',
                    'default' => '',
                    'type'    => 'selectbox'
                ),
                'status_delivery' => array(
                    'group'   => 'statuses',
                    'hasHint' => 'N',
                    'default' => '',
                    'type'    => 'selectbox'
                ),
                'status_delivery_problem' => array(
                    'group'   => 'statuses',
                    'hasHint' => 'N',
                    'default' => '',
                    'type'    => 'selectbox'
                ),
                'status_reject' => array(
                    'group'   => 'statuses',
                    'hasHint' => 'N',
                    'default' => '',
                    'type'    => 'selectbox'
                ),
                'status_are_cleared' => array(
                    'group'   => 'statuses',
                    'hasHint' => 'N',
                    'default' => '',
                    'type'    => 'selectbox'
                ),
                'status_created' => array(
                    'group'   => 'statuses',
                    'hasHint' => 'N',
                    'default' => '',
                    'type'    => 'selectbox'
                ),
                'status_forwarding' => array(
                    'group'   => 'statuses',
                    'hasHint' => 'N',
                    'default' => '',
                    'type'    => 'selectbox'
                ),
                'status_return_to_sender' => array(
                    'group'   => 'statuses',
                    'hasHint' => 'N',
                    'default' => '',
                    'type'    => 'selectbox'
                ),
                'status_completed' => array(
                    'group'   => 'statuses',
                    'hasHint' => 'N',
                    'default' => '',
                    'type'    => 'selectbox'
                ),
                'status_return_doc' => array(
                    'group'   => 'statuses',
                    'hasHint' => 'N',
                    'default' => '',
                    'type'    => 'selectbox'
                ),
                'status_ready_to_pickup' => array(
                    'group'   => 'statuses',
                    'hasHint' => 'N',
                    'default' => '',
                    'type'    => 'selectbox'
                ),
                'addTracking' => array(
                    'group'   => 'statuses',
                    'hasHint' => 'N',
                    'default' => '',
                    'type'    => 'checkbox'
                ),
                'markPayed' => array(
                    'group'   => 'statuses',
                    'hasHint' => 'Y',
                    'default' => '',
                    'type'    => 'checkbox'
                ),
                'useTrackingStatuses' => array(
                    'group'   => 'statuses',
                    'hasHint' => 'Y',
                    'default' => 'N',
                    'type'    => 'checkbox'
                ),
                'blockingStatus' => array(
                    'group'   => 'statuses',
                    'hasHint' => 'Y',
                    'default' => '',
                    'type'    => 'selectbox'
                ),

                // orderProps
                'firstName' => array(
                    'group'   => 'orderProps',
                    'hasHint' => 'N',
                    'default' => 'FIO',
                    'type'    => 'text'
                ),
                'company' => array(
                    'group'   => 'orderProps',
                    'hasHint' => 'N',
                    'default' => 'COMPANY',
                    'type'    => 'text'
                ),
                'email' => array(
                    'group'   => 'orderProps',
                    'hasHint' => 'N',
                    'default' => 'EMAIL',
                    'type'    => 'text'
                ),
                'phone' => array(
                    'group'   => 'orderProps',
                    'hasHint' => 'N',
                    'default' => 'PHONE',
                    'type'    => 'text'
                ),
                'line' => array(
                    'group'   => 'orderProps',
                    'hasHint' => 'N',
                    'default' => 'ADDRESS',
                    'type'    => 'text'
                ),

                // widget
                'widgetDeliveryTypes' => [
                    'group' => 'widget',
                    'hasHint' => 'Y',
                    'default' => 'All',
                    'type' => 'selectbox'
                ],
                'pvzPicker' => array(
                    'group' => 'widget',
                    'hasHint' => 'Y',
                    'default' => 'ADDRESS',
                    'type'    => 'text',
                ),
                'requireFullAddress' => [
                    'group' => 'widget',
                    'hasHint' => 'Y',
                    'default' => 'N',
                    'type'    => 'checkbox',
                ],
                'enDadataSuggestions' => [
                    'group' => 'widget',
                    'hasHint' => 'Y',
                    'default' => 'N',
                    'type'    => 'checkbox',
                ],
                'enMapOpenMode' => [
                    'group' => 'widget',
                    'hasHint' => 'Y',
                    'default' => 'courier',
                    'multiple' => false,
                    'type'    => 'selectbox',
                ],
                'runWidgetOnStart' => [
                    'group' => 'widget',
                    'hasHint' => 'Y',
                    'default' => 'Y',
                    'type'    => 'checkbox',
                ],
                'ctptGeoEmptyMessage' => [
                    'group' => 'widget',
                    'hasHint' => 'Y',
                    'default' => '',
                    'type'    => 'textarea',
                    'size'    => 5,
                ],
                'ctptCustomDefaultWidgetText' => [
                    'group' => 'widget',
                    'hasHint' => 'Y',
                    'default' => Tools::getMessage('DELIVERY_HANDLER_TERMS') . '     ' .  Tools::getMessage('DELIVERY_HANDLER_TERMS_HINT'),
                    'type'    => 'textarea',
                    'size'    => 5,
                ],

                // payments
                'payNal' => array(
                    'group'   => 'payments',
                    'hasHint' => 'N',
                    'default' => 'N',
                    'multiple' => true,
                    'type'    => 'selectbox',
                ),
                'payCard' => array(
                    'group'   => 'payments',
                    'hasHint' => 'N',
                    'default' => 'N',
                    'multiple' => true,
                    'type'    => 'selectbox',
                ),
                'checkPayed' => array(
                    'group'   => 'payments',
                    'hasHint' => 'Y',
                    'default' => 'N',
                    'type'    => 'checkbox',
                ),
                'payTypeNP' => array(
                    'group'   => 'payments',
                    'hasHint' => 'Y',
                    'default' => 'N',
                    'multiple' => false,
                    'type'    => 'selectbox',
                ),
                
                // colors
                'search_button_background' => array(
                    'group'   => 'colors',
                    'hasHint' => 'Y',
                    'default' => '',
                    'multiple' => false,
                    'type'    => 'text',
                    'attr'    => 'class="color-picker-box"',
                ),
                'search_button_border' => array(
                    'group'   => 'colors',
                    'hasHint' => 'Y',
                    'default' => '',
                    'multiple' => false,
                    'type'    => 'text',
                    'attr'    => 'class="color-picker-box"',
                ),
                'search_button_text' => array(
                    'group'   => 'colors',
                    'hasHint' => 'Y',
                    'default' => '',
                    'multiple' => false,
                    'type'    => 'text',
                    'attr'    => 'class="color-picker-box"',
                ),
                'search_button_hover' => array(
                    'group'   => 'colors',
                    'hasHint' => 'Y',
                    'default' => '',
                    'multiple' => false,
                    'type'    => 'text',
                    'attr'    => 'class="color-picker-box"',
                ),
                'primary_widget_color' => array(
                    'group'   => 'colors',
                    'hasHint' => 'Y',
                    'default' => '',
                    'multiple' => false,
                    'type'    => 'text',
                    'attr'    => 'class="color-picker-box"',
                ),
                'cluster_color' => array(
                    'group'   => 'colors',
                    'hasHint' => 'Y',
                    'default' => '',
                    'multiple' => false,
                    'type'    => 'text',
                    'attr'    => 'class="color-picker-box"',
                ),

                // service
                'isTest' => [
                    'group' => 'service',
                    'hasHint' => 'Y',
                    'default' => 'N',
                    'type' => 'checkbox'
                ],
                'customApiUrl'=>[
                    'group' => 'service',
                    'hasHint' => 'Y',
                    'default' => '',
                    'type' => 'text'
                ],
                'customWSSUrl'=>[
                    'group' => 'service',
                    'hasHint' => 'Y',
                    'default' => '',
                    'type' => 'text'
                ],
                'timeout' => [
                    'group' => 'service',
                    'hasHint' => 'Y',
                    'default' => '60',
                    'type' => 'text'
                ],
                'debug' => [
                    'group' => 'service',
                    'hasHint' => 'Y',
                    'default' => 'N',
                    'type' => 'checkbox'
                ],
                'sync_data_completed' => array(
                    'group' => 'service',
                    'hasHint' => 'N',
                    'default' => 'N',
                    'type' => 'checkbox'
                ),
                'client_notify' => [
                    'group' => 'service',
                    'hasHint' => 'Y',
                    'default' => 'Y',
                    'type' => 'checkbox'
                ],
                'use_widget_local' => [
                    'group' => 'service',
                    'hasHint' => 'N',
                    'default' => 'N',
                    'type' => 'checkbox'
                ],
                'DEFAULT_WAREHOUSE_ID' => [
                    'group' => 'warehouses',
                    'hasHint' => 'N',
                    'default' => '',
                    'type' => 'selectbox'
                ],
            ];

            // динамически подгружаем операторов для маппинга
            $arTerminalMap = Adapter::getOperatorsForOptions(['defaultSenderTerminal', 'markupOperatorsTypes','markupOperatorsVals']);
            $arInvOperatorTarifs = Adapter::getOperatorsForOptions(['termSendType'],'selectbox','door');
            $arFreeDeliveryForOperators = Adapter::getOperatorsForOptions(['opFreeDelivery'],'checkbox','N');
            $arOptions = array_merge($arOptions,$arTerminalMap,$arInvOperatorTarifs,$arFreeDeliveryForOperators);

            self::$collection = $arOptions;

        }

        return $arOptions;
    }

    public static function getColOption($code){
        $arCol = self::collection();

        if(array_key_exists($code,$arCol)){
            return $arCol[$code];
        } else {
            return false;
        }
    }

    public static function validate($code,$val){
        $result = new BasicResponse();

        $checker = self::getValidator($code);

        if($checker){
            $result = $checker($val);
        }

        $optDescr = self::getColOption($code);

        if(array_key_exists('required',$optDescr) && $optDescr['required'] && !$val){
            $result->setSuccess(false)->setErrorText(\Ipol\Catapulto\Bitrix\Tools::getMessage('ERROR_OPTSAVE_UNGIVEN'));
        }

        return $result;
    }

    public static function getValidator($code)
    {
        $checker = false;
        switch ($code) {
            case 'key' :
                $checker = function ($val) {
                    $result = new BasicResponse();
                    if (strlen($val) < 5) {
                        $result->setSuccess(false)->setErrorText('Too short emae');
                    }
                    return $result;
                };
                break;
            case 'deliveryDefaultPrice' :
                $checker = function ($val) {
                    $result = new BasicResponse();
                    if (!is_numeric($val)) {
                        $result->setSuccess(false)->setErrorText('Please. only integer or float');
                    }
                    return $result;
                };
                break;
            case 'search_button_background' :
            case 'search_button_border' :
            case 'search_button_text' :
            case 'search_button_hover' :
            case 'primary_widget_color' :
            case 'cluster_color' :
                $checker = function ($val) {
                    $result = new BasicResponse();
                    if (!empty($val) && !preg_match('/^#[a-fA-F0-9]{6}$/', $val)) {
                        $result->setSuccess(false)->setErrorText('Please input correct HEX color (format #000000)');
                    }
                    return $result;
                };
                break;
        }

        return $checker;
    }

    /**
     * @param $code
     * @return array|bool
     * Класс для вывода значений селектов. Почему бы не вписать его сразу в collection?
     * Да потому что collection подключается постоянно, когда идет обращение к опциям. Зачем нам, например, каждый раз получать
     * из БД имеющиеся статусы в Битриксе, когда надо просто узнать дефолтный вес из настроек? Поэтому грузим только когда надо.
     */
    public static function getSelectVals($code)
    {
        $arVals = false;

        switch($code){
            case 'showInOrders':
                $arVals = ["Y" => Tools::getMessage("LBL_ALWAYS"), "N" => Tools::getMessage("LBL_ONLYMODULE")];
                break;
            case 'defMode'     :
                $arVals = array("O" => Tools::getMessage("LBL_defModeO"),"G" => Tools::getMessage("LBL_defModeG"));
                break;

            case 'enMapOpenMode':
                $arVals = array("courier" => Tools::getMessage("LBL_STARTWID_COURIER"),"map" => Tools::getMessage("LBL_STARTWID_MAP"));
                break;

            case 'widgetDeliveryTypes':
                $arVals = [
                    "All" => Tools::getMessage("LBL_ALL"),
                    "Pvz" => Tools::getMessage("LBL_PVZ"),
                    "Courier" => Tools::getMessage("LBL_COURIER")
                ];
                break;
            
            case 'deliveryPvzMarkupType':
            case 'deliveryCourierMarkupType':
                $arVals = [
                    "N" => Tools::getMessage("LBL_DELIVERY_MARKUP_N"),
                    "R" => Tools::getMessage("LBL_DELIVERY_MARKUP_R"),
                    "P" => Tools::getMessage("LBL_DELIVERY_MARKUP_P")
                ];
                break;


            case 'status_courier_take':
            case 'status_on_road':
            case 'status_delivery':
            case 'status_delivery_problem':
            case 'status_reject':
            case 'status_are_cleared':
            case 'status_created':
            case 'status_forwarding':
            case 'status_return_to_sender':
            case 'status_completed':
            case 'status_return_doc':
            case 'status_ready_to_pickup':
            case 'blockingStatus' :
                if(array_key_exists('statuses',self::$ABYSS)){
                    $arVals = self::$ABYSS['statuses'];
                } else {
                    $arVals = array(0 => '');
                    $arVals = array_merge($arVals,Statuses::getOrderStatuses());
                    self::$ABYSS['statuses'] = $arVals;
                }
                break;
            case 'payNal'  :
            case 'payCard' :
                if(array_key_exists('paysystems',self::$ABYSS)){
                    $arVals = self::$ABYSS['paysystems'];
                } else {
                    $arVals = PaySystems::getAll();
                    self::$ABYSS['paysystems'] = $arVals;
                }
                break;
            case 'payTypeNP' :
                $arVals = PaySystems::getTypesNP();
                break;
            case 'ndsDefault' :
                $arVals = array('0' => Tools::getMessage('LBL_NONDS'), '10' => '10%', '20' => '20%');
                break;

            case 'markupType':
                $arVals = [
                    '0'=>Tools::getMessage('LBL_MARKUP_T0'),
                    '1'=>Tools::getMessage('LBL_MARKUP_T1'),
                ];
                break;
        }

        return $arVals;
    }
}
