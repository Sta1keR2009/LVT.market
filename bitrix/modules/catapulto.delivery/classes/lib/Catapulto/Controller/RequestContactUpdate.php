<?php

namespace Ipol\Catapulto\Catapulto\Controller;

use Ipol\Catapulto\Api\Entity\Request\ContactId as RequestObj;
use Ipol\Catapulto\Catapulto\Entity\ContactUpdateResult as ResultObj;
use Ipol\Catapulto\Core\Order\Order;


class RequestContactUpdate extends AutomatedCommonRequest
{

    /** @var int */
    private $id;

    /** @var Order  */
    protected $coreOrder;

    /**
     * @param int $id updatable contact id
     * @param ResultObj $resultObj
     * @param Order $cOrder
     */
    public function __construct(ResultObj $resultObj, int $id, Order $cOrder)
    {
        parent::__construct($resultObj);
        $this->coreOrder = $cOrder;
        $this->id = $id;
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

        $data->setId($this->id);

        $data->setLocalityId($this->coreOrder->getAddressTo()->getCode())
            ->setZip($this->coreOrder->getAddressTo()->getZip())
            ->setStreet($this->coreOrder->getAddressTo()->getStreet())
            ->setBuilding($this->coreOrder->getAddressTo()->getBuilding())
            ->setDoorNumber($this->coreOrder->getAddressTo()->getFlat())
            ->setComment($this->coreOrder->getAddressTo()->getComment());

        if ($this->coreOrder->getBuyers()) {
            $data->setCompany($this->coreOrder->getBuyers()->getFirst()->getField('company'))
                ->setName($this->coreOrder->getBuyers()->getFirst()->getFullName())
                ->setPhone($this->coreOrder->getBuyers()->getFirst()->getPhone());
        }

        if ($this->coreOrder->getField('iso')) {
            $data->setIso($this->coreOrder->getField('iso'));
        }

        $this->setRequestObj($data);

        return $this;

    }


}