<?php

namespace Ipol\Catapulto;


use Ipol\Catapulto\Admin\Logger;
use Ipol\Catapulto\Api\BadResponseException;
use Ipol\Catapulto\Api\Entity\Response\Part\RateId\RateCollection;
use Ipol\Catapulto\Bitrix\Adapter;
use Ipol\Catapulto\Bitrix\Controller\Dadata;
use Ipol\Catapulto\Bitrix\Entity\BasicResponse;
use Ipol\Catapulto\Bitrix\Entity\Cache;
use Ipol\Catapulto\Bitrix\Entity\Encoder;
use Ipol\Catapulto\Bitrix\Entity\Options;
use Ipol\Catapulto\Bitrix\Handler\Deliveries;
use Ipol\Catapulto\Bitrix\Handler\Locations;
use Ipol\Catapulto\Bitrix\Tools;
use Ipol\Catapulto\Catapulto\CatapultoApplication;
use Ipol\Catapulto\Core\Delivery\Cargo;
use Ipol\Catapulto\Bitrix\Adapter\Cargo as CargoAdapter;
use Ipol\Catapulto\Core\Delivery\CargoCollection;
use Ipol\Catapulto\Core\Delivery\CargoItem;
use Ipol\Catapulto\Core\Delivery\Shipment;
use Ipol\Catapulto\Core\Entity\Money;
use Ipol\Catapulto\Core\Order\Address;
use Ipol\Catapulto\Core\Order\Item;
use Ipol\Catapulto\Core\Order\ItemCollection;
use Ipol\Catapulto\Core\Order\Order;
use Ipol\Catapulto\Bitrix\Entity\DefaultGabarites;
use Ipol\Catapulto\Bitrix\Handler\GoodsPicker;
use Ipol\Catapulto\Core\Order\Payment;
use Ipol\Catapulto\Admin\BitrixLoggerController;


class WidgetHandler extends AbstractGeneral
{
    /** @var string */
    public static $city;

    /**
     * ID города Битрикса
     */
    public static $cityCode;

    /** @var string */
    public static $location_type;

    /** @var string */
    public static $location_name;

    /** @var int */
    public static $PAY_SYSTEM_ID = 0;

    /** @var int */
    public static $PERSON_TYPE_ID = 0;

    /** @var int */
    public static $DELIVERY_ID = 0;

    /** @var float */
    private static $BASKET_PRICE = 0;

    /** @var int */
    public static $receiver_contact_id = 0;

    /** @var string */
    public static $rate_result = '';

    /** @var int */
    public static $sender_contact_id = 0;

    /** @var int */
    public static $rate_cost = 0;

    /** @var string */
    public static $rate_term = '';

    /** @var int */
    public static $rate_result_id = 0;

    /** @var Options */
    public static $options = false;

    /** @var string */
    protected static $postField = 'PVZ';

    /** @var array */
    protected static $answer = [];

    /** @var CatapultoApplication */
    protected $catapulto;

    /** @var int */
    protected $timeout;

    /** @var Encoder */
    protected $encoder;

    /** @var Cache */
    protected $cache;

    /** @var string name for file with API-logs */
    protected $loggerName = 'Catapulto_API';

    /** @var Logger */
    protected $logger;

    /** @var array $errors */
    protected $errors;

    /** @var string */
    protected $senderLocalityId;

    /** @var string */
    protected $senderZip;

    /** @var mixed|string */
    private $apikey;

    public function __construct()
    {
        $arDefaultWarehouse = WarehousesHandler::getDefaultWarehouse();

        $this->apikey           = Option::get('apikey');
        $this->timeout          = (int)Option::get('timeout');
        $this->senderLocalityId = $arDefaultWarehouse['CATAPULTO_CITY_ID'];
        $this->senderZip        = $arDefaultWarehouse['CATAPULTO_CITY_INDEX'];

        $this->encoder = new Encoder();

        $this->cache  = new Cache();
        $this->logger = new BitrixLoggerController($this->loggerName);

        $customBaseUrl = '';
        if (Option::get('isTest') === 'Y') $customBaseUrl = Option::get('customApiUrl');
        $this->catapulto = new CatapultoApplication(
            $this->apikey,
            $customBaseUrl,
            $this->timeout,
            $this->encoder,
            $this->cache,
            $this->logger
        );
    }

    /**
     * Контроллер методов виджета
     *
     * @param $params
     *
     * @return array|false
     */
    public static function widget($params)
    {
        $method = explode('_', $params['METHOD']);
        $method = implode(array_map('ucfirst', $method));
        $method = 'widget' . $method;
        if (method_exists(__CLASS__, $method)) {
            $arParams = @json_decode($params['PARAMS'], true);
            /*if (!$arParams) {
                return false;
            }*/

            $widget = new WidgetHandler();
            $result = $widget->{$method}($arParams);

            if (!empty($widget->errors)) {
                if (Tools::isModuleAjaxRequest()) {
                    // TODO прикрутить локализацию для ошибок
                    echo Tools::jsonEncode(
                        [
                            'error' => implode('; ', $widget->errors)
                        ]
                    );
                }
                return $widget->errors;
            }

            if (Tools::isModuleAjaxRequest()) {
                echo Tools::jsonEncode($result ?? []);
            }
            return $result;

        }

        return false;
    }

    /**
     * Возвращает json данные по запрашиваемому через ajax оператору
     *
     * @param $arRequest
     *
     * @return void
     */
    public static function widgetGetOperatorAjax($arRequest)
    {
        $arResult = [];
        if (!empty($arRequest['operator_id'])) {
            $arResult = self::widgetGetOperator(trim($arRequest['operator_id']));
        }

        echo Tools::jsonEncode($arResult);
    }

    /**
     * Возвращает данные по оператору по его ID
     *
     * @param string $sOperatorId
     *
     * @return array
     */
    protected static function widgetGetOperator(string $sOperatorId)
    {
        return OperatorsTable::getByOperatorId($sOperatorId);
    }

    private static function widgetGetAllOperatorsWithMarkup()
    {
        $operators = OperatorsTable::getList();
        $result = [];
        while ($item = $operators->fetch()) {
           $result[] = [
               'id'=>$item['OPERATOR_ID'],
               'mktp' => (Options::fetchOption('markupOperatorsTypes_' . $item['OPERATOR_ID']) == '')?'0':Options::fetchOption('markupOperatorsTypes_' . $item['OPERATOR_ID']),
               'mkvl' => (Options::fetchOption('markupOperatorsVals_' . $item['OPERATOR_ID']) == '')?'0':Options::fetchOption('markupOperatorsVals_' . $item['OPERATOR_ID']),
               //'tarifinv' => (Options::fetchOption('invTarif_' . $item['OPERATOR_ID']) == '')?'0':Options::fetchOption('invTarif_' . $item['OPERATOR_ID']),
           ];
        }
        return $result;
    }

    public static function loadWidjet()
    {
        if (Deliveries::isActive() && $_REQUEST['is_ajax_post'] != 'Y' && $_REQUEST["AJAX_CALL"] != 'Y' && !$_REQUEST["ORDER_AJAX"]) {

            $pathToWidjet = 'https://widgetcdn.catapulto.ru/assets/js/catapulto-widget/v3/catapulto-widget.js';
            if ((Option::get('use_widget_local') === 'Y') && file_exists($_SERVER['DOCUMENT_ROOT'] . Tools::getJSPath() . 'widjet/catapultowidget.min.js')) $pathToWidjet = Tools::getJSPath() . 'widjet/catapultowidget.min.js';
            $pathToController = Tools::getJSPath() . 'pvzWidjet.js';


            $GOODS     = GoodsPicker::fromBasket();
            $obDefGabs = new DefaultGabarites();
            $obCargo   = new CargoAdapter($obDefGabs);

            // Something wrong with basket items CAN_BUY flag or basket was empty at all
            if (empty($GOODS)) {
                $GOODS = [Tools::makeSimpleGood()];
            }

            $cargos = $obCargo->set($GOODS)->getCargo();
            $dims   = $cargos->getDimensions();

            if (
                file_exists($_SERVER['DOCUMENT_ROOT'] . $pathToController)
            ) {
                $GLOBALS['APPLICATION']->AddHeadString('<script type="module" src="'.$pathToWidjet.'"></script>');
                $GLOBALS['APPLICATION']->AddHeadScript($pathToController);
                $jsScripts = [Tools::getJqPath()];
                if (Option::get('enDadataSuggestions') === 'Y') {
                    $GLOBALS['APPLICATION']->SetAdditionalCSS("https://cdn.jsdelivr.net/npm/suggestions-jquery@21.12.0/dist/css/suggestions.min.css");
                    $jsScripts[] = 'https://cdn.jsdelivr.net/npm/suggestions-jquery@21.12.0/dist/js/jquery.suggestions.min.js';
                    $GLOBALS['APPLICATION']->AddHeadString('<link href="' . Tools::getJSPath() . 'jquery-ui/jquery-ui.min.css"  type="text/css" rel="stylesheet">', true);
                    $GLOBALS['APPLICATION']->AddHeadString('<script src="' . Tools::getJSPath() . 'jquery-ui/jquery-ui.min.js"></script>', true);
                }
                ?>
                <script type="text/javascript">
                    var catapulto_widget_params = <?=\CUtil::PhpToJSObject(self::getWidgetParams())?>;
                    var catapulto_mapping_ps    = <?=\CUtil::PhpToJSObject(Adapter::getPaymentCorresponds())?>;
                    var catapulto_pay_type_np   = '<?=Option::get('payTypeNP')?>';

                    var selectPsTmp = 0;

                    $(function() {
                        if ($('input[name="PAY_SYSTEM_ID"]:checked').length) {
                            let date = new Date(Date.now() + 86400e3);
                            date = date.toUTCString();

                            var selectPS = $('input[name="PAY_SYSTEM_ID"]:checked').val();
                            document.cookie = "catapulto_load_ps=" + selectPS + "; path=/; expires=" + date;
                            document.cookie = "catapulto_reselect=0; path=/; expires=" + date;
                            document.cookie = "catapulto_select_ps=" + selectPS + "; path=/; expires=" + date;

                            setServiceFilter(selectPS, catapulto_mapping_ps);
                        }

                    });

                    // отслеживаем смену платежной системы для переинициализации виджета
                    BX.addCustomEvent('onAjaxSuccess', function() {
                        var selectPS = $('input[name="PAY_SYSTEM_ID"]:checked').val();
                        setServiceFilter(selectPS, window.catapulto_mapping_ps);

                        if (catapulto_delivery_pvzWidjet.widjetController) {

                            let widgetParams = Object.assign(
                                window.catapulto_widget_params,
                                {
                                    onPopupClose: (Widget) => { // обработчик после закрытия кнопкой в режиме попапа
                                        //console.log(Widget.isClosedPopup());
                                    },
                                    onSelectPvzItem: (Item) => { // Событие при выборе ПВЗ варианта
                                        //alert(JSON.stringify(Item.getData(), null, 4));
                                        window.CATAPULTO_DELIVERY_pvzWidjet.selectPvzItem(Item);
                                    },
                                    onSelectCourierItem: (Item) => {// Событие при выборе курьерского варианта
                                        //alert(JSON.stringify(Item.getData(), null, 4));
                                        console.log(<?=self::getMODULELBL()?>PVZWidjet);
                                        //window.CATAPULTO_DELIVERY_pvzWidjet.selectCourierItem(Item, widget);
                                    },
                                    onTariffResponse: function (Tariff) { // Событие получения возможной даты и времени доставки для варианта курьерской доставки с примером получения рейта для него.
                                        let courierItems = catapulto_delivery_pvzWidjet.widjetController.getData().getCourierItems(); // варианты курьерской доставки
                                        let Variant = false;
                                        for (let i in courierItems) {
                                            if (courierItems[i].id === Tariff.id) { // определение какому варианту курьерской доставки принадлежит только что полученный "тариф" (список возможных дат и времени доставки)
                                                Variant = courierItems[i];
                                                break;
                                            }
                                        }
                                    },
                                    onRateResponse: function (Rate, Widget) {
                                        if (typeof(Rate.results) != 'undefined') {
                                            Rate.results.forEach(function(item){
                                                if (typeof(item.markup) == 'undefined') {
                                                    item.price_orig = item.price;
                                                    item.price += (markupMode===0)?markup:( Math.round(markup * item.price_orig)/100 );
                                                    item.markup = true;
                                                }
                                            });
                                        }
                                    }
                                }
                            );

                            if (selectPS !== getCookie('catapulto_select_ps')) {
                                let date = new Date(Date.now() + 86400e3);
                                date = date.toUTCString();
                                document.cookie = "catapulto_select_ps=" + selectPS + "; path=/; expires=" + date;
                                document.cookie = "catapulto_reselect=0; path=/; expires=" + date;

                                var <?=self::getMODULELBL()?>PVZWidjet = new catapulto_delivery_pvzWidjet(
                                    window.catapulto_widget_params,
                                    <?=\CUtil::PhpToJSObject(self::getProfileLink())?>,
                                    catapulto_mapping_ps,
                                    '<?=self::getSavingLink()?>', // where pvz saves via request
                                    '<?=self::getDeliveryTypeSavingLink()?>', // where receiver_contact_id, sender_contact_id, rate_result_id saves via request
                                    '<?=self::getPostField()?>', // contents result of calculations?,
                                    <?=\CUtil::PhpToJSObject(self::getAddressInput())?>, // id props where ID is saved
                                    {
                                        WRONG_PAY: '<?=Tools::getMessage('WIDJET_ERROR_WRONGPAY')?>',
                                        PVZTYPE_postamat: '<?=Tools::getMessage('WIDJET_PVZTYPE_postamat')?>',
                                        PVZTYPE_pickup: '<?=Tools::getMessage('WIDJET_PVZTYPE_pickup')?>',
                                        operator: '<?=Tools::getMessage('WIDJET_OPERATOR')?>',
                                        tariff: '<?=Tools::getMessage('WIDJET_TARIFF')?>',
                                        delivery_date: '<?=Tools::getMessage('WIDJET_DELIVERY_DATE')?>',
                                        pvz: '<?=Tools::getMessage('WIDJET_PVZ')?>',
                                        default_cost: '<?=Options::fetchOption('deliveryDefaultPrice')?>',
                                        default_term: '<?=self::getDefaultTermString()?>',
                                        buttonLabel: '<?=Tools::getMessage('SIGN_CHOOSE_DELIVERY_TYPE')?>',
                                        iPE: '<?=self::$MODULE_LBL?>',
                                        jsPath: '<?=Tools::getJSPath()?>'
                                    },
                                    'BILL',
                                    <?=intval(Option::get('markupType'))?>,
                                    <?=floatval(Option::get('markupValue'))?>,
                                    <?=\CUtil::PhpToJSObject(self::widgetGetAllOperatorsWithMarkup())?>,
                                    <?=(Option::get('enDadataSuggestions') === 'Y')?'true':'false'?>,
                                    <?=(Option::get('runWidgetOnStart') === 'Y')?'true':'false'?>,
                                    <?=\CUtil::PhpToJSObject($jsScripts)?>
                                );
                                //<?=self::getMODULELBL()?>PVZWidjet.onLoad();
                            }

                        } else {
                            // for p2d
                            let date = new Date(Date.now() + 86400e3);
                            date = date.toUTCString();
                            document.cookie = "catapulto_select_ps=" + selectPS + "; path=/; expires=" + date;
                            document.cookie = "catapulto_reselect=0; path=/; expires=" + date;
                        }

                    });


                    function setServiceFilter(selectPS, arPs) {
                        // set services_filter
                        let smsAmount = '';
                        if(window.catapulto_widget_params.services_filter.search('sms_amount') >= 0) {
                            smsAmount = 'sms_amount';
                        }
                        for (var id in arPs) {
                            if (id === selectPS && arPs[id] !== 'BILL') {
                                window.catapulto_widget_params.services_filter = window.catapulto_pay_type_np;
                                if(window.catapulto_widget_params.services_filter !== '') {
                                    window.catapulto_widget_params.services_filter += (smsAmount !== '' ? ',' + smsAmount : '');
                                }
                                else {
                                    window.catapulto_widget_params.services_filter = smsAmount;
                                }
                                
                                if (arPs[id] === 'CASH_CARD') {
                                    window.catapulto_widget_params.filter_cash = true;
                                    window.catapulto_widget_params.filter_card = true;
                                } else {
                                    window.catapulto_widget_params.filter_cash = arPs[id] === 'CASH';
                                    window.catapulto_widget_params.filter_card = arPs[id] === 'CARD';
                                }
                                break;
                            } else {
                                window.catapulto_widget_params.services_filter = smsAmount;
                                window.catapulto_widget_params.filter_cash = false;
                                window.catapulto_widget_params.filter_card = false;
                            }
                        }
                    }

                    function getCookie(name) {
                        function escape(s) { return s.replace(/([.*+?\^$(){}|\[\]\/\\])/g, '\\$1'); }
                        var match = document.cookie.match(RegExp('(?:^|;\\s*)' + escape(name) + '=([^;]*)'));
                        return match ? match[1] : null;
                    }

                    var <?=self::getMODULELBL()?>PVZWidjet = new catapulto_delivery_pvzWidjet(
                        window.catapulto_widget_params,
                        <?=\CUtil::PhpToJSObject(self::getProfileLink())?>,
                        catapulto_mapping_ps,
                        '<?=self::getSavingLink()?>', // where pvz saves via request
                        '<?=self::getDeliveryTypeSavingLink()?>', // where receiver_contact_id, sender_contact_id, rate_result_id saves via request
                        '<?=self::getPostField()?>', // contents result of calculations?,
                        <?=\CUtil::PhpToJSObject(self::getAddressInput())?>, // id props where ID is saved
                        {
                            WRONG_PAY: '<?=Tools::getMessage('WIDJET_ERROR_WRONGPAY')?>',
                            PVZTYPE_postamat: '<?=Tools::getMessage('WIDJET_PVZTYPE_postamat')?>',
                            PVZTYPE_pickup: '<?=Tools::getMessage('WIDJET_PVZTYPE_pickup')?>',
                            operator: '<?=Tools::getMessage('WIDJET_OPERATOR')?>',
                            tariff: '<?=Tools::getMessage('WIDJET_TARIFF')?>',
                            delivery_date: '<?=Tools::getMessage('WIDJET_DELIVERY_DATE')?>',
                            pvz: '<?=Tools::getMessage('WIDJET_PVZ')?>',
                            default_cost: '<?=Options::fetchOption('deliveryDefaultPrice')?>',
                            default_term: '<?=self::getDefaultTermString()?>',
                            buttonLabel: '<?=Tools::getMessage('SIGN_CHOOSE_DELIVERY_TYPE')?>',
                            iPE: '<?=self::$MODULE_LBL?>',
                            jsPath: '<?=Tools::getJSPath()?>'
                        },
                        'BILL',
                        <?=intval(Option::get('markupType'))?>,
                        <?=floatval(Option::get('markupValue'))?>,
                        <?=\CUtil::PhpToJSObject(self::widgetGetAllOperatorsWithMarkup())?>,
                        <?=(Option::get('enDadataSuggestions') === 'Y')?'true':'false'?>,
                        <?=(Option::get('runWidgetOnStart') === 'Y')?'true':'false'?>,
                        <?=\CUtil::PhpToJSObject($jsScripts)?>
                    );
                </script>
                <?
                \Ipol\Catapulto\Bitrix\Tools::getCommonCss();
            }
        }
    }

    private static function getDefaultTermString():string
    {
        $descriptionText = '<p class="' . CATAPULTO_DELIVERY_LBL . 'terms">' . Tools::getMessage('DELIVERY_HANDLER_TERMS') . '</p><p class="' . CATAPULTO_DELIVERY_LBL . 'terms_hint">' . Tools::getMessage('DELIVERY_HANDLER_TERMS_HINT') . '</p>';
        $defaultWidgetText = Option::get('ctptCustomDefaultWidgetText');
        if (!empty($defaultWidgetText)) $descriptionText = '<p class="' . CATAPULTO_DELIVERY_LBL . 'terms">' . $defaultWidgetText . '</p>';
        return $descriptionText;
    }

    /**
     * Возвращает массив настроек для подключения виджета Catapulto
     *
     * @return array
     */
    protected static function getWidgetParams()
    {
        $arLocation = [
            'address' => self::$city
        ];

        switch (self::$location_type) {
            case Locations::TYPE_CITY:
                $arLocation['city'] = self::$location_name;
                break;

            case Locations::TYPE_VILLAGE:
                $arLocation['settlement'] = self::$location_name;
                break;

            default:
                break;
        }

        $arParams = array_merge(
            self::getBaseWidgetParams(),
            [
                'location'              => $arLocation,
                'cargo'                 => self::getCargoParams(),
                'need_insurance'        => true, //(Option::get('mindEnsurance') === 'Y'),
                'insured_value'         => self::getBasketFinalPrice(),
            ]
        );

        return $arParams;
    }

    /**
     * Возвращает массив настроек для подключения виджета Catapulto на странице просмотра товара
     * Для выбора ПВЗ при отправлении заявки (создание заказа из админки)
     *
     * @return array
     */
    public static function getWidgetAdminParams($orderId, $mode)
    {
        $order = Adapter::getOrderData($orderId, $mode);

        $arParams = array_merge(
            self::getBaseWidgetParams(),
            [
                'location' => [
                    'address' => trim(implode(',', [$order->getAddressTo()->getCity(), $order->getAddressTo()->getAddress()])),
                    'city'    => $order->getAddressTo()->getCity(),
                ],
                'cargo'    => self::getCargoParams($orderId),
            ]
        );

        return $arParams;
    }

    private static function getCp1251WidgetLang(): array
    {
        return [
            'ui_text'=>[
                'currency'=> [ 'RUB' => [ 'short' => Tools::getMessage('1251_RUB') ] ],
                'delivery_variant_day' => [
                    Tools::getMessage('1251_delivery_variant_day_0'),
                    Tools::getMessage('1251_delivery_variant_day_1'),
                    Tools::getMessage('1251_delivery_variant_day_2')
                ],
                'delivery_variant_days_from' => [
                    Tools::getMessage('1251_delivery_variant_days_from_0'),
                    Tools::getMessage('1251_delivery_variant_days_from_1')
                ],
                'panel_info_type_1' => Tools::getMessage('1251_panel_info_type_1'),
                'panel_info_type_2' => Tools::getMessage('1251_panel_info_type_2'),
                'delivery_variant_item_time_title' => Tools::getMessage('1251_delivery_variant_item_time_title'),
                'delivery_variant_item_time_title_date' => Tools::getMessage('1251_delivery_variant_item_time_title_date'),
                'variant_day_date' => Tools::getMessage('1251_variant_day_date'),
                'pvz_item_time_from' => Tools::getMessage('1251_pvz_item_time_from'),

                'reload' => Tools::getMessage('1251_reload'),
                'api_error_code' => Tools::getMessage('1251_api_error_code'),
                'api_error_text' => Tools::getMessage('1251_api_error_text'),
                'api_error_text_r400' => Tools::getMessage('1251_api_error_text_r400'),
                'api_error_text_r401' => Tools::getMessage('1251_api_error_text_r401'),
                'api_error_text_r404' => Tools::getMessage('1251_api_error_text_r404'),
                'api_error_text_r500' => Tools::getMessage('1251_api_error_text_r500'),
                'api_error_text_r429' => Tools::getMessage('1251_api_error_text_r429'),
                'yandex_warn_text' => Tools::getMessage('1251_yandex_warn_text'),

                'address' => Tools::getMessage('1251_address'),
                'find' => Tools::getMessage('1251_find'),
                'select' => Tools::getMessage('1251_select'),
                'quality' => Tools::getMessage('1251_quality'),
                'loading' => Tools::getMessage('1251_loading'),
                'btn_mode_courier' => Tools::getMessage('1251_btn_mode_courier'),
                'btn_mode_map' => Tools::getMessage('1251_btn_mode_map'),
                'select_tarif' => Tools::getMessage('1251_select_tarif'),
                'today' => Tools::getMessage('1251_today'),
                'back' => Tools::getMessage('1251_back'),

                'persent_ontime' => Tools::getMessage('1251_persent_ontime'),

                'sort_byratio' => Tools::getMessage('1251_sort_byratio'),
                'sort_bydate' => Tools::getMessage('1251_sort_bydate'),
                'sort_byqual' => Tools::getMessage('1251_sort_byqual'),
                'sort_bycost' => Tools::getMessage('1251_sort_bycost'),

                'payfilter_any' => Tools::getMessage('1251_payfilter_any'),
                'payfilter_card' => Tools::getMessage('1251_payfilter_card'),
                'payfilter_cash' => Tools::getMessage('1251_payfilter_cash'),

                'pvzfilter_all' => Tools::getMessage('1251_pvzfilter_all'),
                'pvzfilter_pvz' => Tools::getMessage('1251_pvzfilter_pvz'),
                'pvzfilter_postamat' => Tools::getMessage('1251_pvzfilter_postamat'),

                'pvz_address' => Tools::getMessage('1251_pvz_address'),
                'pvz_worktime' => Tools::getMessage('1251_pvz_worktime'),
                'pvz_phones' => Tools::getMessage('1251_pvz_phones'),
                'pvz_howto' => Tools::getMessage('1251_pvz_howto'),

                'delivery_date' => Tools::getMessage('1251_delivery_date'),
                'map_mobile_hint' => Tools::getMessage('1251_map_mobile_hint'),
                'search_address_hint' => Tools::getMessage('1251_search_address_hint'),
                'select_date_and_time' => Tools::getMessage('1251_select_date_and_time'),
                'tarif_description' => Tools::getMessage('1251_tarif_description'),
                'courier_companies' => Tools::getMessage('1251_courier_companies'),
            ]
        ];
    }

    private static function getBaseWidgetParams(): array
    {
        $arDefaultWarehouse = WarehousesHandler::getDefaultWarehouse();

        //without cargo && location
        $arParams = [
            'popup_mode'            => true,
            'service_path'          => '/bitrix/js/catapulto.delivery/ajax.php?' . self::$MODULE_LBL . 'action=widget',
            'isMultiWarehouse'      => true,
            'sender_contact_params' => [
                'locality_id' => $arDefaultWarehouse['CATAPULTO_CITY_ID'],
                'zip'         => $arDefaultWarehouse['CATAPULTO_CITY_INDEX'],
                'cityFrom'    => $arDefaultWarehouse['BX_LOC_NAME'],
            ],
            'dadata_token'          => Option::get('dadataApikey'),
            'widget_yandex_key'     => Option::get('widgetYandexKey') ?: '',
            'only_delivery_type'    => self::getParamDeliveryType(),
            'services_filter'       => self::getParamPaymentType(),
            'startTabMap'           => (Option::get('enMapOpenMode') === 'map'),
            'day_shift'             => (int)Option::get('termIncrease'),
            'require_full_address'  => (Option::get('requireFullAddress') === 'Y'),
            'search_button_color' => [
                    'background' => Option::get('search_button_background') ?? '',
                    'border' => Option::get('search_button_border') ?? '',
                    'text' => Option::get('search_button_text') ?? '',
                    'hover' => Option::get('search_button_hover') ?? '',
            ],
            'primary_widget_color' => Option::get('primary_widget_color') ?? '',
            'cluster_color' => Option::get('cluster_color') ?? '',
        ];

        if ((Option::get('isTest') === 'Y') && (Option::get('customWSSUrl') !== '')) {
            $arParams['ws_api_domain'] = Option::get('customWSSUrl');
        }
        if ((Option::get('ctptGeoEmptyMessage') !== '')) {
            $arParams['geo_data_empty_message'] = Option::get('ctptGeoEmptyMessage');
        }
        if (Option::get('isFitting') === 'Y') {
            $arParams['is_fitting'] = true;
        }
        if (Option::get('fittingDefaultEnabled') === 'Y') {
            $arParams['fitting_default'] = true;
        }
        if (Option::get('partialRedemptionEnabled') === 'Y') {
            $arParams['is_partial_redemption'] = true;
        }
        if (defined("LANG_CHARSET") && (LANG_CHARSET == 'windows-1251' || LANG_CHARSET == 'cp1251')) {
            $arParams['lang'] = self::getCp1251WidgetLang();
        }
        
        //markup delivery
        if (Tools::getMarkupAvailable()) {
            $deliveryPvzMarkupType      = Option::get('deliveryPvzMarkupType');
            $deliveryPvzMarkupValue     = Option::get('deliveryPvzMarkupValue');
            $deliveryCourierMarkupType  = Option::get('deliveryCourierMarkupType');
            $deliveryCourierMarkupValue = Option::get('deliveryCourierMarkupValue');
            
            $arParams['extraPrice'] = [
                'pvz'     => ['value' => 0, 'type' => 'rub'],
                'courier' => ['value' => 0, 'type' => 'rub']
            ];
            
            if (($deliveryPvzMarkupType && $deliveryPvzMarkupType !== 'N' && $deliveryPvzMarkupValue > 0)) {
                $arParams['extraPrice']['pvz']['value'] = $deliveryPvzMarkupValue;
                $arParams['extraPrice']['pvz']['type']  = ($deliveryPvzMarkupType === 'P' ? 'percent' : 'rub');
            }
            if (($deliveryCourierMarkupType && $deliveryCourierMarkupType !== 'N' && $deliveryCourierMarkupValue > 0)) {
                $arParams['extraPrice']['courier']['value'] = $deliveryCourierMarkupValue;
                $arParams['extraPrice']['courier']['type']  = ($deliveryCourierMarkupType === 'P' ? 'percent' : 'rub');
            }
        }
        
        return $arParams;
    }

    /**
     * Параметры отправления для виджета
     *
     * @param int $orderId
     *
     * @return array
     */
    protected static function getCargoParams($orderId = 0): array
    {
        if(!$orderId) {
            $GOODS = GoodsPicker::fromBasket();
        }
        else {
            $GOODS = GoodsPicker::fromOrder($orderId);
        }

        $obDefGabs = new DefaultGabarites();
        $obCargo   = new CargoAdapter($obDefGabs);

        // Something wrong with basket items CAN_BUY flag or basket was empty at all
        if (empty($GOODS)) {
            $GOODS = [Tools::makeSimpleGood()];
        }

        $cargoComment = '';
        foreach ($GOODS as $item) {
            $cargoComment .= $item['NAME'].'('.$item['QUANTITY'].');';
        }

        $cargos = $obCargo->set($GOODS)->getCargo();
        $dims   = $cargos->getDimensions();

        return [
            'length'   => $dims['L'],
            'width'    => $dims['W'],
            'height'   => $dims['H'],
            'quantity' => $cargos->getQuantity(),
            'weight'   => $cargos->getWeight(),
            'cargo_comment'  => $cargoComment
        ];
    }

    /**
     * @param $orderId
     *
     * @return float
     */
    public static function getOrderCost($orderId = 0): float
    {
        if(!$orderId) {
            $cost = GoodsPicker::fromBasket();
        }
        else {
            $cost = GoodsPicker::fromOrder($orderId);
        }

        return array_sum(array_column($cost,'PRICE')) ?? 0;
    }

    /**
     * Получение финальной цены корзины с учетом скидок
     *
     * @return float
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentTypeException
     * @throws \Bitrix\Main\InvalidOperationException
     * @throws \Bitrix\Main\NotImplementedException
     */
    public static function getBasketFinalPrice(): float
    {
        $basket = \Bitrix\Sale\Basket::loadItemsForFUser(
            \Bitrix\Sale\Fuser::getId(),
            \Bitrix\Main\Context::getCurrent()->getSite()
        );

        $fuser = new \Bitrix\Sale\Discount\Context\Fuser($basket->getFUserId(true));
        $discounts = \Bitrix\Sale\Discount::buildFromBasket($basket, $fuser);
        $discounts->calculate();

        $result = $discounts->getApplyResult(true);

        foreach ($result['PRICES']['BASKET'] as $key => &$value ) {
            $bItem = $basket->getItemByBasketCode($key);
            $qty = $bItem->getQuantity();
            $value['PRICE'] = $value['PRICE'] * $qty;
        }
        unset($value);

        return array_sum(array_column($result['PRICES']['BASKET'],'PRICE')) ?? 0; // цена товаров с учетом скидки

    }

    protected static function getParamDeliveryType()
    {
        $sValue = Option::get('widgetDeliveryTypes');
        return ($sValue == 'All' ? '' : $sValue);
    }

    protected static function getParamPaymentType()
    {
        $arResult = [];
        if (Option::get('smsAmount') === 'Y') {
            $arResult[] = 'sms_amount';
        }
        
        return implode(',', $arResult);
    }

    /**
     * @return array of places for linking with widjet
     */
    public static function getProfileLink()
    {
        $arProfiles  = Deliveries::getActualProfiles(true);
        $objProfiles = [];

        $activeProfileIds = [];
        foreach ($arProfiles as $id => $arProfile) {
            if ($arProfile['ACTIVE'] == 'Y') $activeProfileIds[] = $id;
        }

        foreach ($arProfiles as $arProfile) {
            $objProfiles['catapulto'] = [
                'tag'   => false,
                'price' => false,
                'self'  => true,
                'link'  => $activeProfileIds,
                'type'  => $arProfile['CODE'] ? : 'pickup'
            ];
        }

        return $objProfiles;
    }

    /**
     * In what key of request will be the id of chosen PVZ
     *
     * @return string
     */
    public static function getSavingLink()
    {
        return 'POINT_GUID';
    }

    /**
     * In what key of request will be the id of chosen delivery type
     *
     * @return string
     */
    public static function getDeliveryTypeSavingLink()
    {
        return 'DELIVERY_VARIANT_ID';
    }

    /**
     * In which key of ajax answer put the widjet data
     *
     * @return string
     */
    public static function getPostField()
    {
        return self::$MODULE_LBL . self::$postField;
    }

    public static function getAddressInput()
    {
        $arInputs = [];
        if (\cmodule::includeModule('sale')) {
            $orderProp = self::getOptions()->fetchPvzPicker();
            $dbProps   = \CSaleOrderProps::GetList([], ['CODE' => $orderProp]);

            while ($arProp = $dbProps->Fetch()) {
                $arInputs [] = $arProp['ID'];
            }
        }

        return $arInputs;
    }

    /**
     * @return Options
     */
    protected static function getOptions()
    {
        if (!self::$options) {
            self::$options = new Options();
        }

        return self::$options;
    }

    // WORKOUT

    /**
     * Добавляет данные по услугам в тело страницы
     *
     * @param $content
     *
     */
    public static function addWidjetData(&$content)
    {
        if (Deliveries::isActive()) {
            $noJson = self::no_json($content);
            $deliveryType = Deliveries::defineDelivery(self::$DELIVERY_ID);

            // convert to win1251
            if (defined("LANG_CHARSET") && (LANG_CHARSET == 'windows-1251' || LANG_CHARSET == 'cp1251') && function_exists("mb_convert_encoding")) {
                array_walk_recursive($deliveryType, function(&$item) { $item = mb_convert_encoding($item, 'UTF-8','Windows-1251'); });
            }

            if ((($_REQUEST['is_ajax_post'] ?? '') == 'Y' || ($_REQUEST["AJAX_CALL"] ?? '') == 'Y' || isset($_REQUEST["ORDER_AJAX"])) && $noJson) {
                $content
                    .= '<input type="hidden"
                                id="' . self::getPostField() . '"
                                name="' . self::getPostField() . '"
                                value=\'' . \Ipol\Catapulto\Bitrix\Tools::jsonEncode(
                        [
                            'city'                => self::$city,
                            'location_name'       => self::$location_name,
                            'location_type'       => self::$location_type,
                            'paysys'              => DeliveryHandler::definePaysystem(),
                            'PAY_SYSTEM_ID'       => self::$PAY_SYSTEM_ID,
                            'PERSON_TYPE_ID'      => self::$PERSON_TYPE_ID,
                            'DELIVERY_ID'         => self::$DELIVERY_ID,
                            'receiver_contact_id' => self::$receiver_contact_id,
                            'rate_result'         => self::$rate_result,
                            'sender_contact_id'   => self::$sender_contact_id,
                            'rate_result_id'      => self::$rate_result_id,
                            'rate_cost'           => self::$rate_cost,
                            'rate_term'           => self::$rate_term,
                            'DELIVERY_TYPE'       => $deliveryType
                        ]
                    ) . '\' />
                        ';
            }
            elseif (
                (
                    ($_REQUEST['action'] ?? '') == 'refreshOrderAjax' || ($_REQUEST['soa-action'] ?? '') == 'refreshOrderAjax'
                )
                && !$noJson
            ) {
                if (function_exists('mb_substr') && defined('BX_UTF'))
                    $content = mb_substr($content,0,mb_strlen($content)-1);
                else $content = substr($content,0,strlen($content)-1);
                $content.=',"'.self::getPostField().'":'.Tools::jsonEncode([
                        'city'                => self::$city,
                        'location_name'       => self::$location_name,
                        'location_type'       => self::$location_type,
                        'paysys'              => DeliveryHandler::definePaysystem(),
                        'PAY_SYSTEM_ID'       => self::$PAY_SYSTEM_ID,
                        'PERSON_TYPE_ID'      => self::$PERSON_TYPE_ID,
                        'DELIVERY_ID'         => self::$DELIVERY_ID,
                        'receiver_contact_id' => self::$receiver_contact_id,
                        'rate_result'         => self::$rate_result,
                        'sender_contact_id'   => self::$sender_contact_id,
                        'rate_result_id'      => self::$rate_result_id,
                        'rate_cost'           => self::$rate_cost,
                        'rate_term'           => self::$rate_term,
                        'DELIVERY_TYPE'       => $deliveryType
                    ]).'}';
            }
        }
    }

    public static function no_json($wat)
    {
        return is_null(json_decode($wat, true));
    }

    /**
     * @param \Bitrix\Sale\Order $entity
     * @param                    $values
     *
     * @return \Bitrix\Main\EventResult|bool
     */
    public static function checkDeliveryTypeProp($entity, $values)
    {
        $options    = self::getOptions();
        $bActive    = Deliveries::isActive();
        $bCatapulto = false;

        if($bActive) {
            $shipmentCollection = $entity->getShipmentCollection();
            $shipmentCollection->rewind();
            /** @var \Bitrix\Sale\Shipment $obShipment */
            while ($obShipment = $shipmentCollection->next()) {
                if ($obShipment->isSystem()) {
                    continue;
                }

                if (Deliveries::defineDelivery($obShipment->getField('DELIVERY_ID'))) {
                    $bCatapulto = true;
                }
            }
            $shipmentCollection->rewind();
        }

        if($bActive && $bCatapulto) {
            if (
                !\Ipol\Catapulto\Bitrix\Tools::isAdminSection()
                && $options->fetchNoPVZnoOrder() == 'Y'
                && !self::getRequestDeliveryType()
            ) {
                return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::ERROR, new \Bitrix\Sale\ResultError(Tools::getMessage('ERROR_NOPVZ'), 'code'), 'sale');
            }


            if (
                    !empty($_COOKIE['catapulto_load_ps']) &&
                    !empty($_COOKIE['catapulto_select_ps']) &&
                    !\Ipol\Catapulto\Bitrix\Tools::isAdminSection()
            ) {
                if (Option::get('needReselect') === 'Y') {
                    if ($_COOKIE['catapulto_load_ps'] !== $_COOKIE['catapulto_select_ps'] && $_COOKIE['catapulto_reselect'] === '0') {
                        return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::ERROR, new \Bitrix\Sale\ResultError(Tools::getMessage('ERROR_RESELECT'), 'code'), 'sale');
                    }
                }
            }


            if ($arVal = self::getRequestDeliveryTypeData()) {
                $ps = DeliveryHandler::definePaysystem();
                $sErrorPaySystem = '';
                if (isset($arVal['rate_result']['terminal_cash']) || isset($arVal['rate_result']['terminal_card'])) {
                    if ($ps == DeliveryHandler::PAYSYSTEM_CASH) {
                        if (!$arVal['rate_result']['terminal_cash']) {
                            $sErrorPaySystem = Tools::getMessage('ERROR_PAY_TYPE');
                        }
                    }
                    elseif ($ps == DeliveryHandler::PAYSYSTEM_CARD) {
                        if (!$arVal['rate_result']['terminal_card']) {
                            $sErrorPaySystem = Tools::getMessage('ERROR_PAY_TYPE');
                        }
                    }
                }

                if (!empty($sErrorPaySystem)) {
                    return new \Bitrix\Main\EventResult(\Bitrix\Main\EventResult::ERROR, new \Bitrix\Sale\ResultError($sErrorPaySystem, 'code'), 'sale');
                }
            }
        }

        return true;
    }

    /**
     * Gets delivery type from request after making order
     *
     * @return bool|string of id
     */
    protected static function getRequestDeliveryType()
    {
        $arVal = self::getRequestDeliveryTypeData();
        return !empty($arVal['rate_result_id']);
    }

    /**
     * @return array|mixed
     */
    protected static function getRequestDeliveryTypeData()
    {
        $sVal = (
            !array_key_exists(self::getDeliveryTypeSavingLink(), $_REQUEST) || !$_REQUEST[self::getDeliveryTypeSavingLink()] || $_REQUEST[self::getDeliveryTypeSavingLink()] == 'false'
        ) ? false : $_REQUEST[self::getDeliveryTypeSavingLink()];

        if ($sVal) {
            $arVal = json_decode($sVal, 1);
            if ($arVal['rate_result']) {
                $arVal['rate_result'] = json_decode($arVal['rate_result'], 1);
            }

            return $arVal;
        }

        return [];
    }

    public static function prepareData($arResult, $arUserResult)
    {
        if (!Deliveries::isActive()) {
            return false;
        }
        if ($arUserResult['DELIVERY_LOCATION']) {
            $locationCode = $arUserResult['DELIVERY_LOCATION'];
        }
        else {
            $locationProp = \CSaleOrderProps::GetList([], [
                'PERSON_TYPE_ID' => $arUserResult['PERSON_TYPE_ID'],
                'ACTIVE'         => 'Y',
                'IS_LOCATION'    => 'Y'
            ])->Fetch();
            if ($arUserResult['ORDER_PROP'][$locationProp['ID']]) {
                $locationCode = $arUserResult['ORDER_PROP'][$locationProp['ID']];
            }
            else {
                $locationCode = $_REQUEST['order']['ORDER_PROP_' . $locationProp['ID']];
            }
        }
        
        if(!$locationCode) {
            //default location from Bitrix settings
            $locationCode = \COption::GetOptionString('sale','location');
        }

        if ($locationCode) {
            $location = \Ipol\Catapulto\Bitrix\Adapter::getCmsLocation($locationCode);

            if ($location) {
                self::$cityCode      = $locationCode;
                self::$location_type = $location->getField('type') ?? null;

                // convert to win1251
                if (defined("LANG_CHARSET") && (LANG_CHARSET == 'windows-1251' || LANG_CHARSET == 'cp1251') && function_exists("mb_convert_encoding")) {
                    self::$city = mb_convert_encoding($location->getField('line') ?? $location->getName(), 'UTF-8', 'windows-1251');
                    self::$location_name = mb_convert_encoding(Adapter\Location::getNormalizeSettlement($location->getName()) ?? null, 'UTF-8', 'windows-1251');
                } else {
                    self::$city = $location->getField('line') ?? $location->getName();
                    self::$location_name = Adapter\Location::getNormalizeSettlement($location->getName()) ?? null;
                }
            }
        }

        if ($arUserResult['PAY_SYSTEM_ID']) {
            self::$PAY_SYSTEM_ID = $arUserResult['PAY_SYSTEM_ID'];
        }
        if ($arUserResult['PERSON_TYPE_ID']) {
            self::$PERSON_TYPE_ID = $arUserResult['PERSON_TYPE_ID'];
        }
        if ($arUserResult['DELIVERY_ID']) {
            self::$DELIVERY_ID = $arUserResult['DELIVERY_ID'];
        }
        
        if(!self::$city) {
            //default city
            self::$city = Tools::getMessage('WIDGET_MOSCOW');
        }

        return true;
    }

    /**
     * Gets PVZ code from request after making order
     *
     * @return bool|string of id
     */
    protected static function getRequestPVZ()
    {
        $check = (
            !array_key_exists(self::getSavingLink(), $_REQUEST) || !$_REQUEST[self::getSavingLink()] || $_REQUEST[self::getSavingLink()] == 'false'
        ) ? false : $_REQUEST[self::getSavingLink()];

        return $check;
    }

    protected static function toAnswer($wat)
    {
        $stucked = ['error'];
        if (!is_array($wat)) {
            $wat = ['info' => $wat];
        }
        if (!is_array(self::$answer)) {
            self::$answer = [];
        }
        foreach ($wat as $key => $sign) {
            if (in_array($key, $stucked)) {
                if (!array_key_exists($key, self::$answer)) {
                    self::$answer[$key] = [];
                }
                self::$answer[$key] [] = $sign;
            }
            else {
                self::$answer[$key] = $sign;
            }
        }
    }

    protected static function printAnswer()
    {
        echo Tools::jsonEncode(self::$answer);
    }
    
    public function widgetDadataFindById(array $arParams = [])
    {
        $dadata = new Dadata();
        
        $dataParams = array_merge(($arParams['data_params'] ?? []), [
            'from_bound' => ['value' => 'city'],
            'to_bound'   => ['value' => 'house-flat'],
            'locations'  => []
        ]);
        
        if (empty($arParams['query'])) {
            $this->errors[] = 'EMPTY_QUERY';
            return [];
        }
        
        try {
            $suggest = $dadata->findById('address', $arParams['query'], ($arParams['count'] ?? Dadata::SUGGESTION_COUNT), $dataParams);
        } catch (Api\ApiLevelException|BadResponseException $e) {
            $suggest = [];
        }
        
        return $suggest ?? [];
    }
    
    public function widgetDadataSuggest(array $arParams = [])
    {
        $dadata = new Dadata();
        
        $dataParams = array_merge(($arParams['data_params'] ?? []), [
            'from_bound' => ['value' => 'city'],
            'to_bound'   => ['value' => 'house-flat'],
            'locations'  => []
        ]);
        
        if (empty($arParams['query'])) {
            $this->errors[] = 'EMPTY_QUERY';
            return [];
        }
        
        try {
            $suggest = $dadata->suggest('address', $arParams['query'], ($arParams['count'] ?? Dadata::SUGGESTION_COUNT), $dataParams);
        } catch (Api\ApiLevelException|BadResponseException $e) {
            $suggest = [];
        }
        
        return $suggest ?? [];
    }

    /**
     * Creating rate action
     *
     * @param array $data
     *
     * @return array|false
     */
    public function widgetCreateRate($data)
    {
        //Ближайший склад
        if ($data['warehouseId']) {
            $arNearestWarehouse = WarehousesTable::getWarehouses(['=ID' => $data['warehouseId']]);
            $arNearestWarehouse = array_shift($arNearestWarehouse);
            $arNearestWarehouse['CUSTOM'] = true;
        }
        else {
            $arNearestWarehouse = WarehousesHandler::getNearestWarehouse(
                [
                    'lat' => $data['dadata_selected_choice']['geo_lat'] ?? '',
                    'lon' => $data['dadata_selected_choice']['geo_lon'] ?? ''
                ]
            );
        }

        /*if (empty($data['location']['term']) || empty($data['location']['iso'])) {
            $this->errors[] = 'GEO_DATA_EMPTY';
        }*/

        // контакт отправителя
        $arNearestWarehouse['CATAPULTO_CONTACT_ID'] = (int)$arNearestWarehouse['CATAPULTO_CONTACT_ID'];
        if ($arNearestWarehouse['CATAPULTO_CONTACT_ID'] === 0) {
            $this->errors[] = 'EMPTY_SENDER_ID';
            return false;
        }

        $this->senderLocalityId = $arNearestWarehouse['CATAPULTO_CITY_ID'];
        $this->senderZip        = $arNearestWarehouse['CATAPULTO_CITY_INDEX'];

        $cOrder = new Order();

        // отправитель из настроек модуля
        $sender = new Address();
        $sender->setZip($this->senderZip)
            ->setField('cityFrom', $data['sender_contact_data']['cityFrom'])
            ->setField('locality_id', $this->senderLocalityId);

        $cOrder->setAddressFrom($sender);

        if (empty($this->senderLocalityId) || empty($sender->getZip())) {
            $this->errors[] = 'WRONG_SENDER_CONTACT_DATA';
            return false;
        }

        // получаем информацию о городе получателя
        // task 142055 - проверяем наличие данных dadata или параметра receiver_locality_id
        $receiverLocalityId = 0;
        $zip = null;
        if (isset($data['dadata_selected_choice']) && !empty($data['dadata_selected_choice'])) {
            // task 138121 - мы более не ожидаем от битрикс данных адреса а добываем их самостоятельно от ответа дадата, при этом на этом этапе адрес должен существовать как минимум на уровне населенного пункта и быть полным.
            $ignoredSettlementTypes = [
                Tools::getMessage('DADATAEXCL_0'),
                Tools::getMessage('DADATAEXCL_1'),
                Tools::getMessage('DADATAEXCL_2'),
                Tools::getMessage('DADATAEXCL_3'),
                Tools::getMessage('DADATAEXCL_4'),
                Tools::getMessage('DADATAEXCL_5'),
                Tools::getMessage('DADATAEXCL_6'),
            ];
            $fiasLevel = intval($data['dadata_selected_choice']['fias_level']);
            $receiverCity = $data['dadata_selected_choice']['settlement'] ?? '';
            if (
                empty($receiverCity)
                || ($fiasLevel > 64)
                || (in_array($data['dadata_selected_choice']['settlement_type'], $ignoredSettlementTypes))
            ) $receiverCity = $data['dadata_selected_choice']['city'] ?? '';

            $zip = $data['dadata_selected_choice']['postal_code'] ?? '';

            $fiasLevel = $data['dadata_selected_choice']['fias_level'];
            $cityFiasId = $data['dadata_selected_choice']['city_fias_id'];
            $settlementType = $data['dadata_selected_choice']['settlement_type'];
            $settlementFiasId = $data['dadata_selected_choice']['settlement_fias_id'];
            $countryIso = $data['dadata_selected_choice']['country_iso_code'] ?? 'ru';
            $countryIso = mb_strtolower($countryIso);

            if (empty($receiverCity)) {
                $this->errors[] = 'GEO_DATA_EMPTY';
                return false;
            }
            
            $geoData = $this->catapulto->geo($zip, $receiverCity, $countryIso, 1, $cityFiasId, $settlementFiasId, $fiasLevel, $settlementType);
            if ($geoData->isError() || $geoData->getResponse()->getGeo()->getQuantity() === 0) {
                $this->errors[] = 'NOT_EXISTING_ADDRESS_FROM_GEO';
                if ($geoData->getError()) {
                    if ($geoData->getError() instanceof BadResponseException) {
                        $this->errors[] = '[' . $geoData->getError()->getCode() . '] ' . $geoData->getError()->getMessage();
                    }
                    else {
                        $this->errors[] = 'R' . $geoData->getError()->getCode() . ': ' . $geoData->getError()->getMessage();
                    }
                }
                else {
                    $this->errors[] = 'R: ' . $geoData->getResponse()->getOrigin();
                }
                return false;
            }

            $zip = $geoData->getResponse()->getGeo()->getFirst()->getZip();
            $receiverLocalityId = (int)$geoData->getResponse()->getGeo()->getFirst()->getId();
        }
        if (!empty($data['receiver_locality_id']))
            $receiverLocalityId = (int)$data['receiver_locality_id'];

        if ($receiverLocalityId === 0) {
            $this->errors[] = 'NOT_EXISTING_ADDRESS';
            return false;
        }

        // получатель
        $receiver = new Address();
        $receiver->setZip($zip)
            ->setField('cityFrom', $data['location']['term'])
            ->setField('locality_id', $receiverLocalityId);

        $cOrder->setAddressTo($receiver);

        $cargoSubDataNames     = ['cargo_comment', 'height', 'length', 'width', 'quantity', 'weight'];
        $cargoRequestArrayData = $data['cargo_data'];
        $isSingle = false;
        foreach ($cargoSubDataNames as $name) if (isset($cargoRequestArrayData[$name])) $isSingle = true;
        $cargoIds = [];
        if (
            is_array($cargoRequestArrayData)
            && (count($cargoRequestArrayData) > 0)
            && !$isSingle
        ) {
            //Если это существующий заказ - смотрим - есть ли данные по сохраненным грузоместам, т.к. если это многоместный заказ - скорее всего есть.
            $bxOrderId = (int)$cargoRequestArrayData[0]['ord'];
            $orderProps = OrderPropsTable::getByBitrixId($bxOrderId);
            $customValues = json_decode($orderProps['OTHER'], true);
            if (!$customValues) $customValues = [];
            $savedCargoData = [];
            if (isset($customValues['customCargo'])) $savedCargoData = json_decode($customValues['customCargo'], true);
            if (!$savedCargoData) $savedCargoData = [];

            foreach ($cargoRequestArrayData as $cCata) {
                $cargoId = $this->createCatapultoCargo($cCata, $data['delivery_type'] ?? 'parcel');
                if (!$cargoId && !empty($this->errors)) return false;
                if ($cargoId !== false) $cargoIds[] = $cargoId;

                //save to custom cargo data...
                foreach ($savedCargoData['cargoes'] as $key => $crg) {
                    if ($crg['id'] == $cCata['crg_id']) {
                        $savedCargoData['cargoes'][$key]['ccargo_id'] = $cargoId;
                        break;
                    }
                }
            }
            /*
            //Добавляем данные о складе
            unset($orderProps['warehouseCustom']);
            if ($arNearestWarehouse['CUSTOM']) {
                $orderProps['warehouseCustom'] = true;
                $orderProps['warehouseId']     = $arNearestWarehouse['ID'];
            }*/

            //save info
            $customValues['customCargo'] = json_encode($savedCargoData);
            $orderProps['OTHER'] = json_encode($customValues);
            OrderPropsTable::saveProps($bxOrderId, $orderProps);

        } else {
            $cargoId = $this->createCatapultoCargo($cargoRequestArrayData, $data['delivery_type'] ?? 'parcel');
            if (!$cargoId && !empty($this->errors)) return false;
            $cargoIds = [$cargoId];
        }

        $cOrder->setField('cargoes', $cargoIds);

        // получаем иконки и доставки
        $iconData     = [];
        $iconResponse = $this->catapulto->companyIcon();

        while ($item = $iconResponse->getResponse()->getCompanies()->getNext()) {
            $iconData[$item->getOperatorId()] = $item->getAllFields();
        }

        $cOrder->setField('dadata_variant',$data['dadata_selected_choice']);
        $cOrder->setField('sender_contact_id', $arNearestWarehouse['CATAPULTO_CONTACT_ID']);
        /*
        if ($arNearestWarehouse['CUSTOM']) {
            $cOrder->setField('warehouseCustom', true);
            $cOrder->setField('warehouseId', $arNearestWarehouse['ID']);
        }*/

        if (isset($data['pickup_days_shift']))
            $cOrder->setField('pickup_days_shift', $data['pickup_days_shift']);

        // создаем расчет доставки
        $rateData = $this->catapulto->rateCreate($cOrder);
        
        if ($rateData->getError() || empty($rateData->getResponse()->getKey())) {
            $this->errors[] = 'CANNOT_CREATE_RATES';
            if ($rateData->getError()) {
                if ($rateData->getError() instanceof BadResponseException) {
                    $this->errors[] = '[' . $rateData->getError()->getCode() . '] ' . $rateData->getError()->getMessage();
                }
                else {
                    $this->errors[] = 'R' . $rateData->getError()->getCode() . ': ' . $rateData->getError()->getMessage();
                }
            }
            else {
                $this->errors[] = 'R: ' . $rateData->getResponse()->getOrigin();
            }
            return false;
        }

        $result = [
            'key'       => $rateData->getResponse()->getKey(),
            'locations' => [
                'sender'  => [
                    'locality_id'  => $this->senderLocalityId,
                    'contact_id'   => $arNearestWarehouse['CATAPULTO_CONTACT_ID'],
                    'zip'          => $this->senderZip,
                    'warehouse_id' => $arNearestWarehouse['ID'],
                ],
                'contact' => [
                    'locality_id' => $cOrder->getAddressTo()->getField('locality_id'),
                    'zip'         => $cOrder->getAddressTo()->getZip()
                ],
            ],
            'multiWarehouse' => $this->getMultiWarehouseParams($arNearestWarehouse),
            'params'    => [
                'sender_locality_id'   => $this->senderLocalityId,
                'sender_warehouse_id'  => $arNearestWarehouse['ID'],
                'sender_contact_id'    => $arNearestWarehouse['CATAPULTO_CONTACT_ID'],
                'receiver_locality_id' => $cOrder->getAddressTo()->getField('locality_id'),
                'cargoes'              => $cargoIds,  //[$cargoData->getResponse()->getId()]
            ],
            'icons'     => $iconData
        ];

        $_SESSION['IPOL_CATAPULTO_DELIVERY']['RATE_WAREHOUSE'] = $arNearestWarehouse;

        return $result;
    }

    /**
     * Возвращает массив для ключа multiWarehouse в параметрах ответа метода widgetCreateRate, содержащий настройки виджета по ближайшему складу.
     *
     * @param array $arWarehouseSettings
     *
     * @return array
     */
    protected function getMultiWarehouseParams(array $arWarehouseSettings): array
    {
        //Все настройки склада
        $arResult = [];

        //Настройки операторов
        $arResult['courierSettings'] = [];

        foreach ($arWarehouseSettings['OPERATORS_SETUP'] as $arOperatorSetup) {
            $arResult['courierSettings'][$arOperatorSetup['OPERATOR_ID']] = [
                'free_delivery'         => $arOperatorSetup['FREE'] === 'Y',
                'inverse_delivery_type' => $arOperatorSetup['DELIVERY_FROM'] !== $arWarehouseSettings['DELIVERY_FROM'],
                'isPvz'                 => in_array('pvz', $arOperatorSetup['DELIVERY_TYPE'], false),
                'isPostamat'            => in_array('postamat', $arOperatorSetup['DELIVERY_TYPE'], false),
                'isCourier'             => in_array('courier', $arOperatorSetup['DELIVERY_TYPE'], false),
            ];
        }

        foreach ($arWarehouseSettings['FREE_DELIVERY_SETUP'] as $freeDeliverySetup) {
            $arLocation = \Ipol\Catapulto\WarehousesFreeDeliveryTable::getLocationDataByCode($freeDeliverySetup['BX_LOC']);

            $arResult['free_delivery'][] = [
                    'city'           => (string)$arLocation['NAME'],
                    'region'         => (string)$arLocation['REGION'],
                    'city_fias_id'   => (string)$freeDeliverySetup['CITY_FIAS_ID'],
                    'region_fias_id' => (string)$freeDeliverySetup['REGION_FIAS_ID'],
                    'min_courier'    => (int)$freeDeliverySetup['FREE_COURIER_FROM'],
                    'min_pvz'        => (int)$freeDeliverySetup['FREE_PICKUP_FROM'],
            ];
        }

        $arResult['delivery_type']             = $arWarehouseSettings['DELIVERY_FROM'];
        $arResult['free_delivery_min_courier'] = $arWarehouseSettings['FREE_COURIER_FROM'] === '' ? null : (int)$arWarehouseSettings['FREE_COURIER_FROM'];
        $arResult['free_delivery_min_pvz']     = $arWarehouseSettings['FREE_PICKUP_FROM'] === '' ? null : (int)$arWarehouseSettings['FREE_PICKUP_FROM'];
        $arResult['warehouseCustom']           = $arWarehouseSettings['CUSTOM'] === true; //todo возможно уже не нужен здесь

        return $arResult;
    }

    private function createCatapultoCargo($cargoData, $deliveryType = 'parcel') {
        $cargoSubDataNames = ['cargo_comment', 'height', 'length', 'width', 'quantity', 'weight'];
        foreach ($cargoSubDataNames as $name) {
            if (!isset($cargoData[$name])) return false; //not valid!
            if (empty($cargoData[$name])) return false;
        }
        $cargoItem = (new Item())
            ->setWeight((int)$cargoData['weight']) //граммы
            ->setWidth((int)$cargoData['width']) //мм
            ->setLength((int)$cargoData['length']) //мм
            ->setHeight((int)$cargoData['height']) //мм
            ->setQuantity((int)$cargoData['quantity']) //мм
            ->setField('comment', $cargoData['cargo_comment'] ?? 'empty')
            ->setField('type', $deliveryType)// тип отправления ['docs', 'parcel']
        ;

        $cargoResult = $this->catapulto->cargoCreateByItem($cargoItem);
        if ($cargoResult->isError() || empty($cargoResult->getResponse()->getId())) {
            $this->errors[] = 'CANNOT_CREATE_CARGO';
            if ($cargoResult->getError()) {
                if ($cargoResult->getError() instanceof BadResponseException) {
                    $this->errors[] = '['.$cargoResult->getError()->getCode().'] '.$cargoResult->getError()->getMessage();
                } else $this->errors[] = 'R' . $cargoResult->getError()->getCode() . ': ' . $cargoResult->getError()->getMessage();
            } else $this->errors[] = 'R' . $cargoResult->getError()->getCode() . ': ' . $cargoResult->getResponse()->getOrigin();
            return false;
        }

        return $cargoResult->getResponse()->getId();
    }

    /**
     * Get existing rate action
     *
     * @param $data
     *
     * @return array|false
     */
    public function widgetGetRate($data)
    {
        session_write_close();
        if (empty($data['rate_id']) || !is_string($data['rate_id'])) {
            $this->errors[] = 'RATE_ID_IS_EMPTY';
            return false;
        }

        $rateId = $data['rate_id'];
        $filter = [
            'shipping_type_filter' => $data['shipping_type_filter'] ? : 'd2d',
            'pickup_days_shift'    => 0
        ];

        if ($data['pickup_days_shift'] > 0 && $data['pickup_days_shift'] <= 365) {
            $filter['pickup_days_shift'] = (int)$data['pickup_days_shift'];
        }
        
        if($data['services_filter']) {
            $arAllowedServices = ['NP', 'COD', 'sms_amount'];
            $arDataFilter = explode(',', $data['services_filter']);
            foreach ($arDataFilter as $key => $value) {
                //Удаляем из фильтра услугу sms_amount, т.к. по условию задачи должны быть показаны все способы, даже без этой услуги
                if($value === 'sms_amount' || !in_array($value, $arAllowedServices, false)) {
                    unset($arDataFilter[$key]);
                }
            }
            
            $data['services_filter']   = implode(',', $arDataFilter);
            $filter['services_filter'] = $data['services_filter'];
        }

        $rate = $this->catapulto->rateRead(
            $rateId,
            $filter['pickup_days_shift'],
            [$filter['shipping_type_filter']],
            ($filter['services_filter']) ? [$filter['services_filter']] : [],
            ($data['need_insurance'] === true),
            $data['insured_value'] ?? 0
        );

        if ($rate->isError()) {
            $this->errors[] = 'CANNOT_GET_RATE';
            if ($rate->getError()) {
                if ($rate->getError() instanceof BadResponseException) {
                    $this->errors[] = '['.$rate->getError()->getCode().'] '.$rate->getError()->getMessage();
                } else $this->errors[] = 'R' . $rate->getError()->getCode() . ': ' . $rate->getError()->getMessage();
            } else $this->errors[] = 'R' . $rate->getError()->getCode() . ': ' . $rate->getResponse()->getOrigin();
            return false;
        }

        $result = [
            'count'          => $rate->getResponse()->getCount(),
            'rate_completed' => $rate->getResponse()->isRateCompleted(),
            'results'        => []
        ];

        while ($item = $rate->getResponse()->getResults()->getNext()) {
            $result['results'][] = $item->getAllFields();
        }

        return $result;
    }

    /**
     * @param $data
     *
     * @return array|false
     */
    public function widgetGetTerminals($data)
    {
        $terminalRequestData = $data['terminal_request_data'];

        if (empty($terminalRequestData['sender_locality_id']) || empty($terminalRequestData['receiver_locality_id'])) {
            $this->errors[] = 'EMPTY_TERMINAL_DATA';
            return false;
        }

        $cOrder = new Order();

        // sender
        $addressFrom = new \Ipol\Catapulto\Core\Order\Address();
        $addressFrom->setField('locality_id', (int)$terminalRequestData['sender_locality_id']);
        $cOrder->setAddressFrom($addressFrom);

        // receiver
        $addressTo = new \Ipol\Catapulto\Core\Order\Address();
        $addressTo->setField('locality_id', (int)$terminalRequestData['receiver_locality_id']);
        $cOrder->setAddressTo($addressTo);

        // add fields
        $cOrder->setField('limit', 300)
            ->setField('page', (int)$data['page'] ? : 1);


        if ($terminalRequestData['company']) {
            $cOrder->setField('company', $terminalRequestData['company']);
        }

        if ($data['services_filter']) {
            $cOrder->setField('services_filter',$data['services_filter']);
        }

        if (isset($terminalRequestData['cargoes']) && is_array($terminalRequestData['cargoes']) && !empty($terminalRequestData['cargoes']))
            $cOrder->setField('cargoes',$terminalRequestData['cargoes']);

        if (isset($terminalRequestData['lat']) && floatval($terminalRequestData['lat']) > 0)
            $cOrder->setField('lat', floatval($terminalRequestData['lat']));

        if (isset($terminalRequestData['lon']) && floatval($terminalRequestData['lon']) > 0)
            $cOrder->setField('lon', floatval($terminalRequestData['lon']));

        if (isset($terminalRequestData['radius_km']) && floatval($terminalRequestData['radius_km']) > 0)
            $cOrder->setField('radius_km', floatval($terminalRequestData['radius_km']));

        $terminals = $this->catapulto->terminalList($cOrder);

        if (!$terminals->isSuccess() || $terminals->isError() || $terminals->getResponse()->getStatus() !== 'ok') {
            $this->errors[] = 'CANNOT_GET_TARIFF';
            if ($terminals->getError()) {
                if ($terminals->getError() instanceof BadResponseException) {
                    $this->errors[] = '['.$terminals->getError()->getCode().'] '.$terminals->getError()->getMessage();
                } else $this->errors[] = 'R' . $terminals->getError()->getCode() . ': (' . $terminals->getError()->getMessage() . ') ' . $terminals->getResponse()->getOrigin();
            } else $this->errors[] = 'R500 unknown terminal error';
            return false;
        }

        $result = [
            'data'   => $terminals->getResponse()->getData()->getAllFields(),
            'status' => $terminals->getResponse()->getStatus()
        ];

        //Получим настройки выбранного в createRate склада
        if (isset($_SESSION['IPOL_CATAPULTO_DELIVERY']['RATE_WAREHOUSE'])) {
            $arWHSettings = $this->getMultiWarehouseParams($_SESSION['IPOL_CATAPULTO_DELIVERY']['RATE_WAREHOUSE']);
            //Фильтруем терминалы по настройкам операторов склада
            if (!empty($arWHSettings['courierSettings'])) {
                foreach ($result['data']['data'] as $k => $point) {
                    if (isset($arWHSettings['courierSettings'][$point['operator']])) {
                        /**
                         * Тип терминала (1 — постамат, 2 — ПВЗ, 3 — склад, 0 — не определено),
                         */
                        $terminalType   = (int)$point['point_type'];
                        $arAccessPoints = [];
                        if ($arWHSettings['courierSettings'][$point['operator']]['isPvz']) {
                            $arAccessPoints[] = 2;
                        }
                        if ($arWHSettings['courierSettings'][$point['operator']]['isPostamat']) {
                            $arAccessPoints[] = 1;
                        }

                        if (empty(array_intersect($arAccessPoints, [$terminalType]))) {
                            unset($result['data']['data'][$k]);
                            $result['data']['count']--;
                        }
                    }
                }
            }

            $result['data']['data'] = array_values($result['data']['data']);
        }

        if ($result['data']['count'] <= 0) {
            $this->errors[] = 'EMPTY_TERMINAL_DATA';
            return false;
        }

        foreach ($result['data']['data'] as &$point) {
            $point['point-type'] = $point['point_type'];
            unset($point['point_type']);
        }
        unset($point);

        return $result;
    }

    /**
     * @param $data
     *
     * @return array|false
     */
    public function widgetGetTerminal($data)
    {
        if (empty($data['terminal_id'])) {
            $this->errors[] = 'TERMINAL_ID_IS_EMPTY';
            return false;
        }

        $terminal = $this->catapulto->terminalRead((int)$data['terminal_id']);
        if ($terminal->isError()) {
            $this->errors[] = 'CANNOT_GET_TERMINAL';
            if ($terminal->getError()) {
                if ($terminal->getError() instanceof BadResponseException) {
                    $this->errors[] = '['.$terminal->getError()->getCode().'] '.$terminal->getError()->getMessage();
                } else $this->errors[] = 'R' . $terminal->getError()->getCode() . ': ' . $terminal->getError()->getMessage();
            } else $this->errors[] = 'R' . $terminal->getError()->getCode() . ': ' . $terminal->getResponse()->getOrigin();
            return false;
        }

        $result = [
            'status' => $terminal->getResponse()->getStatus() ?? null,
            'data'   => []
        ];

        while ($item = $terminal->getResponse()->getData()->getNext()) {
            $point = $item->getAllFields();
            $point['point-type'] = $point['point_type'];
            unset($point['point_type']);
            $result['data'][] = $point;
        }

        return $result;
    }

    /**
     * @param $data
     *
     * @return array|false
     */
    public function widgetGetTariff($data)
    {
        if (empty($data['tariff_id'])) {
            $this->errors[] = 'TARIFF_ID_IS_EMPTY';
            return false;
        }

        $tariffId = $data['tariff_id'];

        $filter = [
            'pickup_days_shift' => 0,
        ];

        if ($data['pickup_days_shift'] > 0 && $data['pickup_days_shift'] <= 365) {
            $filter['pickup_days_shift'] = (int)$data['pickup_days_shift'];
        }
        $tariff = $this->catapulto->tariffRead($tariffId, $filter['pickup_days_shift']);

        if ($tariff->isError()) {
            $this->errors[] = 'CANNOT_GET_TARIFF';
            if ($tariff->getError()) {
                if ($tariff->getError() instanceof BadResponseException) {
                    $this->errors[] = '['.$tariff->getError()->getCode().'] '.$tariff->getError()->getMessage();
                } else $this->errors[] = 'R' . $tariff->getError()->getCode() . ': ' . $tariff->getError()->getMessage();
            } else $this->errors[] = 'R' . $tariff->getError()->getCode() . ': ' . $tariff->getResponse()->getOrigin();
            return false;
        }

        $result = [
            [
                'id'         => $tariff->getResponse()->getId(),
                'time-slots' => $tariff->getResponse()->getTimeSlots()
            ]
        ];

        return $result;
    }

    public function widgetGetGeo($data)
    {
        if (empty($data['term'])) {
            $this->errors[] = 'EMPTY_REQUEST_DATA';
            return false;
        }

        $geo = $this->catapulto->geo($data['term']);
        if ($geo->isError()) {
            $this->errors[] = 'CANNOT_GET_GEO_DATA';
            if ($geo->getError()) {
                if ($geo->getError() instanceof BadResponseException) {
                    $this->errors[] = '['.$geo->getError()->getCode().'] '.$geo->getError()->getMessage();
                } else $this->errors[] = 'R' . $geo->getError()->getCode() . ': ' . $geo->getError()->getMessage();
            } else $this->errors[] = 'R' . $geo->getError()->getCode() . ': ' . $geo->getResponse()->getOrigin();
            return false;
        }

        $result = $geo->getResponse()->getGeo()->getFirst()->getFields();

        return $result ?? [];
    }

    public function widgetGetWs($data) {
        $token = $this->catapulto->getWSToken();
        if ($token->isError()) {
            $this->errors[] = 'CANNOT_GET_WS_TOKEN';
            if ($token->getError()) {
                if ($token->getError() instanceof BadResponseException) {
                    $this->errors[] = '['.$token->getError()->getCode().'] '.$token->getError()->getMessage();
                } else $this->errors[] = 'R' . $token->getError()->getCode() . ': ' . $token->getError()->getMessage();
            } else $this->errors[] = 'R' . $token->getError()->getCode() . ': ' . $token->getResponse()->getOrigin();
            return false;
        }

        $token = $token->getResponse()->getWsToken();
        return ['ws_token'=>$token] ?? [];
    }

}
