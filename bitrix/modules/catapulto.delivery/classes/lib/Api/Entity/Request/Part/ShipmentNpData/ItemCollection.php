<?php

namespace Ipol\Catapulto\Api\Entity\Request\Part\ShipmentNpData;

class ItemCollection extends \Ipol\Catapulto\Api\Entity\AbstractCollection
{

    protected $Items;

    public function __construct()
    {
        parent::__construct('Items');
        $this->setChildClass(Item::class);
    }

    /**
     * @return Item
     */
    public function getFirst(){
        return parent::getFirst();
    }

    /**
     * @return Item
     */
    public function getNext(){
        return parent::getNext();
    }

}