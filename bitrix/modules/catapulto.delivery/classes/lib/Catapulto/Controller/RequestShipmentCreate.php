<?php

namespace Ipol\Catapulto\Catapulto\Controller;

use Ipol\Catapulto\Api\Entity\Request\Shipment as RequestObj;
use Ipol\Catapulto\Catapulto\Entity\ShipmentCreateResult as ResultObj;
use Ipol\Catapulto\Core\Order\Order;


class RequestShipmentCreate extends AutomatedCommonRequest
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

        $data->setSenderContactId($this->coreOrder->getAddressFrom()->getCode())
            ->setReceiverContactId($this->coreOrder->getAddressTo()->getCode())
            ->setWebshopOrderNumberId((string)$this->coreOrder->getNumber())
            ->setRateResultId($this->coreOrder->getField('rate_id'))
            ->setPickupDay($this->coreOrder->getField('pickup_day'))
            ->setDeliveryDay($this->coreOrder->getField('delivery_day'))
            ->setNeedInsurance($this->coreOrder->getField('need_insurance'))
            ->setInsuranceValue($this->coreOrder->getField('insurance_value')->getAmount())
            ->setAdditionalServices($this->coreOrder->getField('additional_services'))
            ->setSenderTerminalCode($this->coreOrder->getField('sender_terminal_code'))
            ->setReceiverTerminalCode($this->coreOrder->getField('receiver_terminal_code'))
            ->setGenerateSenderPoa(false)
        ;

        if ($this->coreOrder->getField('sender_poa')) {
            $data->setGenerateSenderPoa(true)
                ->setFromName($this->coreOrder->getField('from_name') ?? '')
                ->setFromPassportSeries($this->coreOrder->getField('from_passport_series') ?? '')
                ->setFromPassportNumber($this->coreOrder->getField('from_passport_number') ?? '')
                ->setFromPassportDate($this->coreOrder->getField('from_passport_date') ?? '')
                ->setSendReceiverPoaEmail($this->coreOrder->getField('send_receiver_poa_email') ?? '')
            ;
        }


        $this->setRequestObj($data);

        return $this;

    }


}