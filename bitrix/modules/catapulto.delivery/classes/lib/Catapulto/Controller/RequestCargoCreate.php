<?php

namespace Ipol\Catapulto\Catapulto\Controller;

use Ipol\Catapulto\Api\Entity\Request\Cargo as RequestObj;
use Ipol\Catapulto\Catapulto\Entity\CargoCreateResult as ResultObj;
use Ipol\Catapulto\Core\Order\Order;


class RequestCargoCreate extends AutomatedCommonRequest
{

    /** @var Order  */
    protected $coreOrder;

    /**
     * @param ResultObj $resultObj
     * @param Order $cOrder
     */
    public function __construct(ResultObj $resultObj, Order $cOrder)
    {
        parent::__construct($resultObj);
        $this->coreOrder = $cOrder;
    }

    /**
     * @return string
     */
    public function getSelfHash(): string
    {
        $orderString = serialize($this->coreOrder); //TODO change it to something better-better ACTUALLY IT SHOULD NOT BE CACHED
        return md5($orderString);
    }

    /**
     * @return $this
     */
    public function convert()
    {
        $data = new RequestObj();

        $item = $this->coreOrder->getItems()->getFirst();
        $data->setDeliveryType($item->getField('type'))
            ->setCargoComment($item->getField('comment'))
            ->setWidth( (int)(ceil($item->getWidth() / 10) ?: 1) )
            ->setLength( (int)(ceil($item->getLength() / 10) ?: 1) )
            ->setHeight( (int)(ceil($item->getHeight() / 10) ?: 1) )
            ->setWeight($item->getWeight() / 1000)
            ->setQuantity(1); //$item->getQuantity()

        $this->setRequestObj($data);

        return $this;

    }


}