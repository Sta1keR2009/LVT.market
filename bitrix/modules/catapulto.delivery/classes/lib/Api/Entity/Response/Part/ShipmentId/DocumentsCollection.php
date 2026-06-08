<?php

namespace Ipol\Catapulto\Api\Entity\Response\Part\ShipmentId;

class DocumentsCollection extends \Ipol\Catapulto\Api\Entity\AbstractCollection
{
    protected $Documents;

    public function __construct()
    {
        parent::__construct('Documents');
        $this->setChildClass(Document::class);
    }

    /**
     * @return Document
     */
    public function getFirst(){
        return parent::getFirst();
    }

    /**
     * @return Document
     */
    public function getNext(){
        return parent::getNext();
    }


}