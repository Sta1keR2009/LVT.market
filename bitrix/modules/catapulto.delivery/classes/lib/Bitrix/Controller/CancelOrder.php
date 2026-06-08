<?php

namespace Ipol\Catapulto\Bitrix\Controller;

class CancelOrder extends AbstractController
{

    public function __construct()
    {
        parent::__construct(CATAPULTO_DELIVERY, CATAPULTO_DELIVERY_LBL);
    }

    /**
     * @param $sKey - KEY of checking order
     *
     * @return \Ipol\Catapulto\Catapulto\Entity\CancelOrderResult
     */
    public function cancelOrder($sKey)
    {
        return $this->application->cancelOrder($sKey);
    }

}