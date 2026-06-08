<?php

namespace Ipol\Catapulto\Catapulto\Controller;

use Ipol\Catapulto\Api\Entity\Request\Cargo as RequestObj;
use Ipol\Catapulto\Catapulto\Entity\CargoCreateResult as ResultObj;
use Ipol\Catapulto\Core\Order\Item;


class RequestCargoCreateByItem extends AutomatedCommonRequest
{

    /** @var Item  */
    protected $coreItem;

    /**
     * @param ResultObj $resultObj
     * @param Item $coreItem
     */
    public function __construct(ResultObj $resultObj, Item $coreItem)
    {
        parent::__construct($resultObj);
        $this->coreItem = $coreItem;
    }

    /**
     * @return string
     */
    public function getSelfHash(): string
    {
        $itemString = serialize($this->coreItem); //TODO change it to something better-better ACTUALLY IT SHOULD NOT BE CACHED
        return md5($itemString);
    }

    /**
     * @return $this
     */
    public function convert()
    {
        $data = new RequestObj();

        //$item = $this->coreOrder->getItems()->getFirst();
        $data->setDeliveryType($this->coreItem->getField('type'))
            ->setCargoComment($this->coreItem->getField('comment'))
            ->setWidth( (int)(ceil($this->coreItem->getWidth() / 10) ?: 1) )
            ->setLength( (int)(ceil($this->coreItem->getLength() / 10) ?: 1) )
            ->setHeight( (int)(ceil($this->coreItem->getHeight() / 10) ?: 1) )
            ->setWeight($this->coreItem->getWeight() / 1000)
            ->setQuantity(1); //$item->getQuantity()

        $this->setRequestObj($data);

        return $this;

    }


}
