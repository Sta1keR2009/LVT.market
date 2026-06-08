<?php

namespace Ipol\Catapulto\Bitrix\Adapter;


use Ipol\Catapulto\Bitrix\Handler\Deliveries;

class AddressFrom extends Address
{
    public function fromDefaults($orderDelivery)
    {
//        $warehouseAddress = $this->getCoreAddress()->setCode($this->options->fetchFromPlaceId());;
//        if($orderDelivery){
//            $warehouseAddress = Deliveries::getWarehouseByDeliveryId($orderDelivery);
//        }
//
//        $this->getCoreAddress()->setCode($warehouseAddress);
    }
}