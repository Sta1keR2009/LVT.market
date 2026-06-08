<?php

namespace Ipol\Catapulto\Api\Entity\Response\Part\RateId;

class RateCollection extends \Ipol\Catapulto\Api\Entity\AbstractCollection
{

    protected $RateItems;

    public function __construct()
    {
        parent::__construct('RateItems');
        $this->setChildClass(Rate::class);
    }

    /**
     * @return Rate
     */
    public function getFirst(){
        return parent::getFirst();
    }

    /**
     * @return Rate
     */
    public function getNext(){
        return parent::getNext();
    }

}