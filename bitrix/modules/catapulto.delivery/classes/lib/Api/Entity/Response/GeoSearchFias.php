<?php

namespace Ipol\Catapulto\Api\Entity\Response;

/**
 * Class GeoSearchFias
 * @package Ipol\Catapulto\Api\Entity\Response
 */
class GeoSearchFias extends AbstractResponse
{

    /** @var string */
    protected $fiasId;
    
    public function getFiasId(): string
    {
        return $this->fiasId;
    }
    
    public function setFiasId(string $fiasId): self
    {
        $this->fiasId = $fiasId;
        return $this;
    }

}