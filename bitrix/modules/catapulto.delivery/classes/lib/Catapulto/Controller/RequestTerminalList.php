<?php

namespace Ipol\Catapulto\Catapulto\Controller;

use Ipol\Catapulto\Api\Entity\Request\Terminal as RequestObj;
use Ipol\Catapulto\Catapulto\Entity\TerminalListResult as ResultObj;
use Ipol\Catapulto\Core\Order\Order;


class RequestTerminalList extends AutomatedCommonRequest
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
        $argsHash = serialize([
            $this->coreOrder->getAddressFrom()->getCode(),
            $this->coreOrder->getAddressTo()->getCode(),
            $this->coreOrder->getAddressFrom()->getField('locality_id'),
            $this->coreOrder->getAddressTo()->getField('locality_id'),
            $this->coreOrder->getField('company') ?? null,
            $this->coreOrder->getField('limit') ?? null,
            $this->coreOrder->getField('page') ?? null,
            $this->coreOrder->getField('isSenderShown') ?? null,
        ]);
        return md5($argsHash);
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

        $fields = ['company', 'limit', 'page', 'isSenderShown','services_filter','cargoes','lat','lon','radius_km','iso'];
        foreach ($fields as $field) {
            if (!empty($this->coreOrder->getField($field))) {
                $data->{'set'.ucfirst($field)}($this->coreOrder->getField($field));
            }
        }

        $this->setRequestObj($data);

        return $this;

    }


}
