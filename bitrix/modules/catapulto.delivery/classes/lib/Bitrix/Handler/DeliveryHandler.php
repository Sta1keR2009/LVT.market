<?php

namespace Ipol\Catapulto\Bitrix\Handler;

use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Error;
use \Bitrix\Sale\Shipment;
use \Bitrix\Sale\Delivery\CalculationResult;
use \Bitrix\Main\Result;
use \Ipol\Catapulto\Bitrix\Tools;
use \Ipol\Catapulto\Bitrix\Entity\Options;
use Ipol\Catapulto\Option;
use Ipol\Catapulto\WidgetHandler;

Loc::loadMessages(__FILE__);

/**
 * Class DeliveryHandler
 *
 * @package namespace Ipol\Catapulto\Bitrix\Handler
 */
class DeliveryHandler extends \Bitrix\Sale\Delivery\Services\Base
{
    // Default additional option variant means "get it from module options"
    const CONFIG_DEFAULT = 'DEFAULT';

    // Extra charge types
    const EXTRA_CHARGE_TYPE_PERCENT = '%';
    const EXTRA_CHARGE_TYPE_FIXED   = 'F';

    /**
     * Calculate price immediately
     *
     * @var bool
     */
    protected static $isCalculatePriceImmediately = true;

    /**
     * Can has profiles
     *
     * @var bool
     */
    protected static $canHasProfiles = false;

    /**
     * Uses extra services
     *
     * @var bool
     */
    protected static $whetherAdminExtraServicesShow = false;

    /**
     * Tracking class
     *
     * @var string
     */
    protected $trackingClass = '\Ipol\Catapulto\Bitrix\Handler\Tracking';


    /**
     * @param array $initParams
     *
     * @throws \Bitrix\Main\ArgumentTypeException
     */
    public function __construct(array $initParams)
    {
        parent::__construct($initParams);
    }

    /**
     * @return string Class title
     */
    public static function getClassTitle()
    {
        return Tools::getMessage("DELIVERY_NAME");
    }

    /**
     * @return string Class, service description
     */
    public static function getClassDescription()
    {
        return Tools::getMessage("DELIVERY_DESCRIPTION");
    }

    /**
     * @return bool
     */
    public static function canHasProfiles()
    {
        return self::$canHasProfiles;
    }

    /**
     * @return array Class names for profiles
     */
    public static function getChildrenClassNames()
    {
        return [];
    }

    /**
     * @return bool
     */
    public static function whetherAdminExtraServicesShow()
    {
        return self::$whetherAdminExtraServicesShow;
    }

    /**
     * Add delivery handler profiles after parent handler entity was added
     *
     * @param int   $handlerId
     * @param array $fields
     *
     * @return Result
     */
    public static function onBeforeAdd(array &$fields = []): Result
    {
        if (empty($fields["LOGOTIP"])) {
            $fields["LOGOTIP"] = self::makeLogotip();
        }

        return new Result();
    }

    /**
     * Make logo file and return it's Bitrix id if success
     *
     * @return int|false
     */
    public static function makeLogotip()
    {
        $path = implode(DIRECTORY_SEPARATOR, [$_SERVER['DOCUMENT_ROOT'], 'bitrix', 'images', CATAPULTO_DELIVERY, 'catapulto_delivery.png']);
        if (file_exists($path)) {
            $content = file_get_contents($path);
            if ($content !== false && $content <> '') {
                $fileName = \Bitrix\Main\Security\Random::getString(32);
                $fileName = \CTempFile::GetFileName($fileName);
                if (\CheckDirPath($fileName)) {
                    if (file_put_contents($fileName, $content) !== false) {
                        $file              = \CFile::MakeFileArray($fileName);
                        $file['MODULE_ID'] = CATAPULTO_DELIVERY;
                        return \CFile::SaveFile($file, implode(DIRECTORY_SEPARATOR, ['sale', 'delivery', 'logotip']));
                    }
                }
            }
        }

        return false;
    }

    /**
     * @return array Profiles list
     */
    public function getProfilesList()
    {
        return [];
    }

    /**
     * @return array Profiles params
     */
    public function getProfilesDefaultParams()
    {
        return [];
    }

    /**
     * Compatibility check
     *
     * @param \Bitrix\Sale\Shipment|null $shipment
     *
     * @return bool
     */
    public function isCompatible(Shipment $shipment)
    {
        /*
        // PaymentCollection empty on D2P SOA, but seems useful on P2D
        //
        $order = $shipment->getCollection()->getOrder();
        $payments = $order->getPaymentCollection();
        */

        $result = $this->checkRequiredData($shipment);
        if (!$result->isSuccess()) {
            return false;
        }

        return true;
    }

    /**
     * Check minimum required data used for delivery calculation, also checks module auth
     *
     * @param \Bitrix\Sale\Shipment|null $shipment
     *
     * @return CalculationResult
     */
    protected function checkRequiredData(Shipment $shipment)
    {
        $result = new CalculationResult;

        if (!(\Ipol\Catapulto\authHandler::isAuthorized())) {
            $result->addError(new Error(Tools::getMessage('DELIVERY_CALC_ERROR_NO_AUTH'), 'DELIVERY_CALCULATION'));
            return $result;
        }
        
        $requiredWidgetYandexKey = \Ipol\Catapulto\Option::get('system_widget_yandex_key_required') === 'Y';
        $widgetYandexKey         = \Ipol\Catapulto\Option::get('widgetYandexKey');
        
        if ($requiredWidgetYandexKey && empty($widgetYandexKey)) {
            $result->addError(new Error(Tools::getMessage('DELIVERY_NEED_YANDEX_API_KEY'), 'DELIVERY_CALCULATION'));
            return $result;
        }

        $order = $shipment->getCollection()->getOrder();

        if (!$props = $order->getPropertyCollection()) {
            $result->addError(new Error(Tools::getMessage('DELIVERY_CALC_ERROR_NO_PROPS'), 'DELIVERY_CALCULATION'));
            return $result;
        }

        /*
        if (!$locationProp = $props->getDeliveryLocation()) {
            $result->addError(new Error(Tools::getMessage('DELIVERY_CALC_ERROR_NO_LOCATION_PROP'), 'DELIVERY_CALCULATION'));
            return $result;
        }

        if (!$locationCode = $locationProp->getValue()) {
            $result->addError(new Error(Tools::getMessage('DELIVERY_CALC_ERROR_NO_LOCATION_CODE'), 'DELIVERY_CALCULATION'));
            return $result;
        }*/

        return $result;
    }

    /**
     * Show message on delivery service edit page
     *
     * @return array
     * @see \CAdminMessage::CAdminMessage
     */
    public function getAdminMessage()
    {
        $options = new Options();

        if (!(\Ipol\Catapulto\authHandler::isAuthorized())) {
            return [
                "MESSAGE" => Tools::getMessage('DELIVERY_HANDLER_ERROR_NO_AUTH_TITLE'),
                "DETAILS" => Tools::getMessage('DELIVERY_HANDLER_ERROR_NO_AUTH_DESCR'),
                "TYPE"    => "ERROR",
                "HTML"    => true
            ];
        }

        if ($options->fetchSync_data_completed() !== 'Y') {
            return [
                "MESSAGE" => Tools::getMessage('DELIVERY_HANDLER_ERROR_NO_SYNC_TITLE'),
                "DETAILS" => Tools::getMessage('DELIVERY_HANDLER_ERROR_NO_SYNC_DESCR'),
                "TYPE"    => "ERROR",
                "HTML"    => true
            ];
        }

        return [];
    }

    /**
     * @return bool
     */
    public function isCalculatePriceImmediately()
    {
        return self::$isCalculatePriceImmediately;
    }

    /**
     * Add additional tab with module statistic
     *
     * @return array
     */
    public function getAdminAdditionalTabs()
    {
        $options = new Options();

        $content = '';
        $content .= self::makeStatusTableRow(Tools::getMessage('DELIVERY_HANDLER_STATUS_TAB_APIKEY'), ($options->fetchApikey() ? : '-'));

        $syncData = ($options->fetchSync_data_completed() === 'Y')
            ? Tools::getMessage('DELIVERY_HANDLER_STATUS_TAB_SYNC_DATA_Y')
            :
            Tools::getMessage('DELIVERY_HANDLER_STATUS_TAB_SYNC_DATA_N');

        $content .= self::makeStatusTableRow(Tools::getMessage('DELIVERY_HANDLER_STATUS_TAB_SYNC_DATA'), $syncData);

        return [
            [
                "TAB"     => Tools::getMessage('DELIVERY_HANDLER_STATUS_TAB_TITLE'),
                "TITLE"   => Tools::getMessage('DELIVERY_HANDLER_STATUS_TAB_DESCR'),
                "CONTENT" => $content
            ]
        ];
    }

    /**
     * Make one row for table on status tab
     *
     * @param $name  string param name
     * @param $value string param value
     *
     * @return string
     */
    public static function makeStatusTableRow($name, $value)
    {
        return '<tr><td width="40%" class="adm-detail-valign-top adm-detail-content-cell-l">' . $name . '</td><td width="60%" class="adm-detail-valign-top adm-detail-content-cell-r">' . $value . '</td></tr>';
    }

    /**
     * Get delivery extra charge based on profile config
     *
     * @param float $price
     *
     * @return float
     */
    protected function getExtraCharge($price)
    {
        $extraChargeType = (is_array($this->config["MAIN"]) && array_key_exists('EXTRA_CHARGE_TYPE', $this->config["MAIN"])) ?
            $this->config["MAIN"]["EXTRA_CHARGE_TYPE"] : self::EXTRA_CHARGE_TYPE_PERCENT;

        $extraChargeValue = (is_array($this->config["MAIN"]) && array_key_exists('EXTRA_CHARGE_VALUE', $this->config["MAIN"])) ?
            floatval($this->config["MAIN"]["EXTRA_CHARGE_VALUE"]) : 0;

        return (($extraChargeType == self::EXTRA_CHARGE_TYPE_PERCENT) ? $price * $extraChargeValue / 100 : $extraChargeValue);
    }

    /**
     * @param \Bitrix\Sale\Shipment|null $shipment
     *
     * @return CalculationResult
     */
    protected function calculateConcrete(Shipment $shipment = null)
    {
        $result = new CalculationResult;

        $check = $this->checkRequiredData($shipment);
        if (!$check->isSuccess()) {
            return $check;
        }
        else {
            // TODO: make check against BX Order basket items

            // Case: make order copy in admin interface, calculateConcrete called before isCompatible and no shipped items given at first calls
            // Ask Bitrix about this shit logic
            if (!$this->checkShipmentItems($shipment)) {
                // No shipped items = zero values returned
                $result->setDeliveryPrice(0);
                $result->setPeriodDescription('');
                $result->setPeriodFrom(0);
                $result->setPeriodTo(0);
                return $result;
            }
        }

        if (isset($_REQUEST['order'][WidgetHandler::getDeliveryTypeSavingLink()]) || Tools::getArrVal(WidgetHandler::getDeliveryTypeSavingLink(), $_REQUEST)) {
            $arVal = [];
            $sVal  = $_REQUEST['order'][WidgetHandler::getDeliveryTypeSavingLink()] ? : $_REQUEST[WidgetHandler::getDeliveryTypeSavingLink()];
            if (!empty($sVal)) {
                $arVal = json_decode($sVal, 1);
            }

            if (isset($arVal['rate_cost'])) {
                $result->setDeliveryPrice($this->roundPrice($arVal['rate_cost']));
            }

            if (isset($arVal['rate_term'])) {
                if (!empty($arVal['rate_result']) && $customer_date = json_decode($arVal['rate_result'],true)['customer_date']) {
                    $customer_date = array_reverse(explode('-', $customer_date));
                    $result->setPeriodDescription(implode('.', $customer_date));
                } else {
                    $result->setPeriodDescription($this->roundPrice($arVal['rate_term']));
                }
            }
        }
        elseif (isset($_SESSION[CATAPULTO_DELIVERY_LBL . 'WIDGET_CALC_RESULT'])) {
            $result->setDeliveryPrice($this->roundPrice($_SESSION[CATAPULTO_DELIVERY_LBL . 'WIDGET_CALC_RESULT']['PRICE']));
            $result->setPeriodDescription($_SESSION[CATAPULTO_DELIVERY_LBL . 'WIDGET_CALC_RESULT']['DELIVERY_DATE']);
        }
        else {
            if($shipment && $shipment->getField('ORDER_ID')) {
                //only for confirmed orders
                if (\Ipol\Catapulto\Option::get('updateOrderDelivery') === 'Y') {
                    $result->setDeliveryPrice($this->roundPrice(Options::fetchOption('deliveryDefaultPrice')));
                }
                else{
                    // get current delivery price
                    $order = $shipment->getCollection()->getOrder();
                    $result->setDeliveryPrice($order->getDeliveryPrice() ? : 0);
                }
            }
            else {
                $result->setDeliveryPrice($this->roundPrice(Options::fetchOption('deliveryDefaultPrice')));
            }
            $result->setPeriodDescription(Option::get('ctptCustomDefaultWidgetText'));
            $result->setPeriodFrom(1);
            $result->setPeriodTo(7);
        }

        return $result;
    }

    /**
     * Check if shipment has some data about shipped items. Cause in some cases there are no items in shipment.
     *
     * @param \Bitrix\Sale\Shipment $shipment
     *
     * @return bool
     */
    public static function checkShipmentItems(Shipment $shipment)
    {
        return (is_object($shipment) && is_object($shipment->getShipmentItemCollection()) && !$shipment->isEmpty());
    }

    /**
     * Do round da given price based on profile config
     *
     * @param float $price
     *
     * @return float
     */
    protected function roundPrice($price)
    {
        $roundTo = (is_array($this->config["MAIN"]) && array_key_exists('ROUND_TO', $this->config["MAIN"])) ?
            intval($this->config["MAIN"]["ROUND_TO"]) : 0;

        return (($roundTo > 0) ? ceil($price / $roundTo) * $roundTo : $price);
    }

    /**
     * Get delivery handler config structure
     *
     * @return array
     */
    protected function getConfigStructure()
    {
        $result = [
            "MAIN" => [
                "TITLE"       => Tools::getMessage('DELIVERY_HANDLER_MAIN_TAB_TITLE'),
                "DESCRIPTION" => Tools::getMessage('DELIVERY_HANDLER_MAIN_TAB_DESCR'),
                "ITEMS"       => [
                    "EXTRA_CHARGE_TYPE"  => [
                        "TYPE"    => "ENUM",
                        "NAME"    => Tools::getMessage("DELIVERY_HANDLER_PROFILE_MAIN_TAB_EXTRA_CHARGE_TYPE"),
                        "DEFAULT" => self::EXTRA_CHARGE_TYPE_PERCENT,
                        "OPTIONS" => self::getExtraChargeTypeVariants(),
                    ],
                    "EXTRA_CHARGE_VALUE" => [
                        "TYPE"    => "STRING",
                        "NAME"    => Tools::getMessage("DELIVERY_HANDLER_PROFILE_MAIN_TAB_EXTRA_CHARGE_VALUE"),
                        "DEFAULT" => 0,
                    ],
                    "ROUND_TO"           => [
                        "TYPE"    => "STRING",
                        "NAME"    => Tools::getMessage("DELIVERY_HANDLER_PROFILE_MAIN_TAB_ROUND_TO"),
                        "DEFAULT" => 0,
                    ],
                ]
            ]
        ];

        return $result;
    }

    /**
     * Get extra charge type variants
     *
     * @return array
     */
    public static function getExtraChargeTypeVariants()
    {
        return [
            self::EXTRA_CHARGE_TYPE_PERCENT => Tools::getMessage('DELIVERY_HANDLER_PROFILE_MAIN_TAB_EXTRA_CHARGE_TYPE_PERCENT'),
            self::EXTRA_CHARGE_TYPE_FIXED   => Tools::getMessage('DELIVERY_HANDLER_PROFILE_MAIN_TAB_EXTRA_CHARGE_TYPE_FIXED')
        ];
    }
}
