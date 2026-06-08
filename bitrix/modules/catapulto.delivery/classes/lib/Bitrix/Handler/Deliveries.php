<?php

namespace Ipol\Catapulto\Bitrix\Handler;

use \Ipol\Catapulto\Bitrix\Entity\Options;

use \Bitrix\Main\Loader;
use \Bitrix\Main\SystemException;
use \Bitrix\Sale\Shipment;
use \Bitrix\Sale\Delivery\Services\Table;

/**
 * Class Deliveries
 * @package namespace Ipol\Catapulto\Bitrix\Handler
 */
class Deliveries
{
    
    /**
     * Get profile code by delivery handler id
     *
     * @param int $id delivery handler id
     * @return false|string profile code
     */
    public static function defineDelivery($id)
    {
        return Table::getList(array('filter' => array('ID' => $id, '%CLASS_NAME' => DeliveryHandler::class)))->fetch();
    }
    
    /**
     *
     * @return int
     */
    public static function getCatapultoDeliveryId(): int
    {
        $arDelivery = Table::getList(['filter' => ['%CLASS_NAME' => DeliveryHandler::class], 'select' => ['ID']])->fetch();
        return $arDelivery['ID'] ?? 0;
    }
    
    /**
     * Checks if at least one active delivery profile exists
     *
     * @return bool
     */
    public static function isActive()
    {
        return (!empty(self::getActualProfiles(true)));
    }

    /**
     * Checks is any shipment in order with module delivery handler used
     *
     * @param $orderId
     * @return bool
     */
    public static function isCatapultoDelivery($orderId)
    {
        $order = Order::getOrderById($orderId);
        /** @var Shipment $shipment */
        foreach ($order->getShipmentCollection() as $shipment) {
            if ($shipment->isSystem())
                continue;

            if (self::defineDelivery($shipment->getDeliveryId()))
                return true;
        }

        return false;
    }

    /**
     * Get actual delivery profiles data for all existing module delivery handlers
     *
     * @param bool $onlyActive get only active profiles
     * @return array of delivery profiles data
     */
    public static function getActualProfiles($onlyActive = true)
    {
        $result = array();

        if (!Loader::includeModule('sale'))
            return $result;

        $filter = array('%CLASS_NAME' => DeliveryHandler::class);
        if ($onlyActive)
            $filter['ACTIVE'] = 'Y';

        $handlerDB = Table::getList(array(
            'filter' => $filter,
            'select' => array('ID', 'CODE', 'ACTIVE', 'NAME', 'CLASS_NAME'),
        ));
        while ($tmp = $handlerDB->fetch())
            $result[$tmp['ID']] = $tmp;

        return $result;
    }

    /**
     * Get VAT rate by delivery handler id
     *
     * @param int $id delivery handler id
     * @return false|string VAT rate - false can be if no VAT rate stored in b_sale_delivery_srv for this handler or not existed handler id set
     */
    public static function getVatRateByDeliveryId($id)
    {
        try {
            $handler = Table::getList(array('filter' => array('ID' => (int)$id), 'select' => array('VAT_ID')))->fetch();

            if (is_array($handler) && isset($handler['VAT_ID']) && Loader::includeModule('catalog')) {
                $possibleVat = \Bitrix\Catalog\VatTable::getList(['filter' => ['ID' => $handler['VAT_ID']], 'select' => ['ID', 'NAME', 'RATE']])->fetch();
                if (is_array($possibleVat)) {
                    return $possibleVat['RATE'];
                }
            }
        } catch (SystemException $e) {
            // Catch unknown field definition `VAT_ID` on 17-
        }

        return false;
    }
}