<?php

namespace Ipol\Catapulto\Api\Entity\Response;

use Ipol\Catapulto\Api\BadResponseException;

/**
 * Class ShipmentNpData
 * @package Ipol\Catapulto\Api\Entity\Response
 */
class ShipmentNpData extends AbstractResponse
{

    /** @var string */
    protected $status;

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return ShipmentNpData
     */
    public function setStatus(string $status): ShipmentNpData
    {
        $this->status = $status;
        return $this;
    }



}
