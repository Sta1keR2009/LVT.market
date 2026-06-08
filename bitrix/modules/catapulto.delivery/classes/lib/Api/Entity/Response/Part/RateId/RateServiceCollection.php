<?php

namespace Ipol\Catapulto\Api\Entity\Response\Part\RateId;

class RateServiceCollection extends \Ipol\Catapulto\Api\Entity\AbstractCollection
{

    protected $RateServices;

    public function __construct()
    {
        parent::__construct('RateServices');
        $this->setChildClass(RateService::class);
    }

    /**
     * @return RateService
     */
    public function getFirst(){
        return parent::getFirst();
    }

    /**
     * @return RateService
     */
    public function getNext(){
        return parent::getNext();
    }

}