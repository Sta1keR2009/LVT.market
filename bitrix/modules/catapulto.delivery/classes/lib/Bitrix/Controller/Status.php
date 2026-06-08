<?php

namespace Ipol\Catapulto\Bitrix\Controller;


class Status extends AbstractController
{
    public function __construct()
    {
        parent::__construct(CATAPULTO_DELIVERY, CATAPULTO_DELIVERY_LBL);
    }
    
    /**
     * @param $sKey - KEY of checking order
     *
     * @return \Ipol\Catapulto\Catapulto\Entity\ShipmentReadResult
     */
    public function checkStatus($sKey)
    {
        return $this->application->shipmentRead($sKey);
    }
}