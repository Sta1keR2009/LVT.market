<?php

namespace Ipol\Catapulto\Bitrix\Controller;

use Ipol\Catapulto\Bitrix\Entity\BasicResponse;
use Ipol\Catapulto\Bitrix\Entity\ErrorCollector;
use Ipol\Catapulto\Bitrix\Tools;
use Ipol\Catapulto\Core\Order\Order;

class ShipmentNp extends AbstractController
{
    protected $iso = 'ru';

    public function __construct()
    {
        parent::__construct(CATAPULTO_DELIVERY, CATAPULTO_DELIVERY_LBL);
    }


    /**
     * Update contact data in catapulto
     * @param int $id
     * @param Order $cOrder
     *
     * @return BasicResponse
     */
    public function create(Order &$cOrder)
    {
        $obReturn = new BasicResponse();


        // update contact
        $obUpdateResponse = $this->application->shipmentNpCreate($cOrder);
        if ($obUpdateResponse->isSuccess()) {
            $obReturn->setData($obUpdateResponse->getResponse());
        } else {
            return new ErrorCollector($obUpdateResponse);
        }

        return $obReturn;
    }

}