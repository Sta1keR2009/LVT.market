<?php

namespace Ipol\Catapulto;


/**
 * Class abstractGeneral
 * @package Ipol\Catapulto
 * Из этого класса наследуются все Главные классы (по факту - просто добавляет лейбл и код модуля
 */
class AbstractGeneral
{

    public const CTPT_SERVICE_POD = 'pod_amount'; //Наложенный платеж (NP)
    public const CTPT_SERVICE_COD = 'cod_amount'; //Оплата получателем за доставку (COD)
    public const CTPT_SERVICE_SMS = 'sms_amount'; //Оплата получателем за sms-информирование
    public const CTPT_SERVICE_FITTING = 'fitting_amount'; //Название услуги "Примерка"
    public const CTPT_SERVICE_PR = 'partial_redemption_amount'; //Название услуги "Частичный выкуп"
    public const CTPT_SERVICE_PR_RETURN = 'partial_redemption_return_amount'; //Название услуги "Частичный возврат товаров"

    protected static $MODULE_LBL = CATAPULTO_DELIVERY_LBL;
    protected static $MODULE_ID  = CATAPULTO_DELIVERY;

    /**
     * @return string
     */
    public static function getMODULELBL()
    {
        return self::$MODULE_LBL;
    }

    /**
     * @return string
     */
    public static function getMODULEID()
    {
        return self::$MODULE_ID;
    }
}
