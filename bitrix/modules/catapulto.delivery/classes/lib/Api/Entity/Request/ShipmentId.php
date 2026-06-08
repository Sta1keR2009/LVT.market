<?php

namespace Ipol\Catapulto\Api\Entity\Request;

class ShipmentId extends AbstractRequest
{

    /** @var string */
    private $id;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return ShipmentId
     */
    public function setId(string $id): ShipmentId
    {
        $this->id = $id;
        return $this;
    }


}