<?php

namespace Ipol\Catapulto\Catapulto\Controller;

use Ipol\Catapulto\Api\Entity\Request\Part\ShipmentNpData\Item;
use Ipol\Catapulto\Api\Entity\Request\Part\ShipmentNpData\ItemCollection;
use Ipol\Catapulto\Api\Entity\Request\ShipmentNpData as RequestObj;
use Ipol\Catapulto\Catapulto\Entity\ShipmentNpCreateResult as ResultObj;
use Ipol\Catapulto\Core\Order\Order;


class RequestShipmentNpCreate extends AutomatedCommonRequest
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

        $data->setCalcResult($this->coreOrder->getField('rateResultId'))
            ->setSumToPay(
                $this->coreOrder->getPayment()->getField('sumToPay')->getAmount()
            )
            ->setDeliveryPaySide($this->coreOrder->getField('deliveryPaySide'))
            ->setHeight($this->coreOrder->getGoods()->getHeight())
            ->setWidth($this->coreOrder->getGoods()->getWidth())
            ->setLength($this->coreOrder->getGoods()->getLength())
            ->setWeight($this->coreOrder->getGoods()->getWeight());

        $itemCollection = new ItemCollection();
        $items = $this->coreOrder->getItems();
        $items->reset();

        while ($product = $items->getNext()) {
            $item = new Item();
            $item->setName($product->getName())
                ->setQuantity($product->getQuantity())
                ->setUnitValue($product->getPrice()->getAmount())
                ->setWeight($product->getWeight())
                ->setLength($product->getLength())
                ->setHeight($product->getHeight())
                ->setWidth($product->getWidth())
                ->setArticul($product->getArticul());

            $itemCollection->add($item);
        }

        $data->setItems($itemCollection);

        $this->setRequestObj($data);

        return $this;

    }


}