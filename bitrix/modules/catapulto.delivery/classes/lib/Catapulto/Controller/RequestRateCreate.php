<?php

namespace Ipol\Catapulto\Catapulto\Controller;

use Ipol\Catapulto\Api\Entity\Request\Rate as RequestObj;
use Ipol\Catapulto\Catapulto\Entity\RateCreateResult as ResultObj;
use Ipol\Catapulto\Core\Order\Order;


class RequestRateCreate extends AutomatedCommonRequest
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
        $orderString = serialize([
            $this->coreOrder->getAddressFrom()->getCode(),
            $this->coreOrder->getAddressTo()->getCode(),
            $this->coreOrder->getAddressFrom()->getField('locality_id'),
            $this->coreOrder->getAddressTo()->getField('locality_id'),
            $this->coreOrder->getField('cargoes') ?? [],
        ]);
        return md5($orderString);
    }

    /**
     * @return $this
     */
    public function convert()
    {
        $data = new RequestObj();

        // locality id
        if (
            !empty($this->coreOrder->getAddressFrom()->getField('locality_id')) &&
            !empty($this->coreOrder->getAddressTo()->getField('locality_id'))
        )
        {
            $data->setSenderLocalityId($this->coreOrder->getAddressFrom()->getField('locality_id'))
                ->setReceiverLocalityId($this->coreOrder->getAddressTo()->getField('locality_id'));

        }

        // contacts id
        if (
            !empty($this->coreOrder->getAddressFrom()->getCode()) &&
            !empty($this->coreOrder->getAddressTo()->getCode())
        )
        {
            $data->setSenderContactId($this->coreOrder->getAddressFrom()->getCode())
                ->setReceiverContactId($this->coreOrder->getAddressTo()->getCode());
        }

        $data->setCargoes($this->coreOrder->getField('cargoes'));

        $data->setDadataSelectedChoice($this->coreOrder->getField('dadata_variant'));

        if (!empty($this->coreOrder->getField('sender_contact_id'))) {
            $data->setSenderContactId(intval($this->coreOrder->getField('sender_contact_id')));
        }

        if (!empty($this->coreOrder->getField('pickup_days_shift'))) {
            $data->setPickupDaysShift(intval($this->coreOrder->getField('pickup_days_shift')));
        }

        $this->setRequestObj($data);

        return $this;

    }


}
