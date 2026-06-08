<?php

namespace Ipol\Catapulto\Api\Entity\Response\Part\Geo;

use \Ipol\Catapulto\Api\Entity\AbstractCollection;
use \Ipol\Catapulto\Api\Entity\Response\Part\Geo\GeoItem;

class GeoCollection extends AbstractCollection
{
    protected $GeoItems;

    public function __construct()
    {
        parent::__construct('GeoItems');
        $this->setChildClass(GeoItem::class);
    }

    /**
     * @return GeoItem
     */
    public function getFirst(){
        return parent::getFirst();
    }

    /**
     * @return GeoItem
     */
    public function getNext(){
        return parent::getNext();
    }

}