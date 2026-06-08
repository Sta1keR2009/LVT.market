<?php

namespace Ipol\Catapulto\Catapulto\Controller;

use Ipol\Catapulto\Api\Entity\Request\Contact as RequestObj;
use Ipol\Catapulto\Catapulto\Entity\ContactCreateResult as ResultObj;
use Ipol\Catapulto\Core\Order\Order;


class RequestContactCreate extends AutomatedCommonRequest
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

        $data->setLocalityId($this->coreOrder->getAddressTo()->getCode())
            ->setZip($this->coreOrder->getAddressTo()->getZip());

        if ($this->coreOrder->getAddressTo()->getStreet()) {
            $data->setStreet($this->coreOrder->getAddressTo()->getStreet());
        }

        if ($this->coreOrder->getAddressTo()->getBuilding()) {
            $data->setBuilding($this->coreOrder->getAddressTo()->getBuilding());
        }

        if ($this->coreOrder->getAddressTo()->getFlat()) {
            $data->setDoorNumber($this->coreOrder->getAddressTo()->getFlat());
        }

        if ($this->coreOrder->getAddressTo()->getComment()) {
            $data->setComment($this->coreOrder->getAddressTo()->getComment());
        }

        if ($this->coreOrder->getBuyers()) {
            if ($this->coreOrder->getBuyers()->getFirst()->getField('company'))
                $data->setCompany($this->coreOrder->getBuyers()->getFirst()->getField('company'));

            if ($this->coreOrder->getBuyers()->getFirst()->getFullName())
                $data->setName($this->coreOrder->getBuyers()->getFirst()->getFullName());

            if ($this->coreOrder->getBuyers()->getFirst()->getPhone())
                $data->setPhone($this->coreOrder->getBuyers()->getFirst()->getPhone());
        }

        if ($this->coreOrder->getField('iso')) {
            $data->setIso($this->coreOrder->getField('iso'));
        }

        $this->setRequestObj($data);

        return $this;

    }


}