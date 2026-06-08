<?php

namespace Ipol\Catapulto;

use \Ipol\Catapulto\Bitrix\Entity\Options;

/**
 * Class DeliveryHandler
 *
 * @package Ipol\Catapulto
 */
class DeliveryHandler extends AbstractGeneral
{
    /**
     * Available paysystem types
     */
    const PAYSYSTEM_CASH = 'CASH';
    const PAYSYSTEM_CARD = 'CARD';
    const PAYSYSTEM_BILL = 'BILL';

    public static $chosenPaysystem = false;

    /**
     * Get paysystem with which order created
     */
    public static function getOrderCreatePaysystem($arUserResult, $obOrder, $arParams)
    {
        if ($arUserResult['PAY_SYSTEM_ID']) {
            self::$chosenPaysystem = $arUserResult['PAY_SYSTEM_ID'];
        }
    }

    /**
     * Define paysystem type based on module options
     *
     * @return string @see DeliveryHandler::PAYSYSTEM_* constants
     */
    public static function definePaysystem()
    {
        $options = new Options();
        $ps      = false;

        $paySystemsCash = ($options->fetchPayNal()!='N;')?$options->fetchPayNal():[];
        $paySystemsCard = ($options->fetchPayCard()!='N;')?$options->fetchPayCard():[];

        if (self::$chosenPaysystem) {
            if (in_array(self::$chosenPaysystem, $paySystemsCash)) {
                $ps = self::PAYSYSTEM_CASH;
            }
            elseif (in_array(self::$chosenPaysystem, $paySystemsCard)) {
                $ps = self::PAYSYSTEM_CARD;
            }
            else {
                $ps = self::PAYSYSTEM_BILL;
            }
        }

        if (self::$chosenPaysystem === false || !$ps) {
            // Get default variant from Module options
            $ps = self::PAYSYSTEM_BILL;
        }

        return $ps;
    }
}