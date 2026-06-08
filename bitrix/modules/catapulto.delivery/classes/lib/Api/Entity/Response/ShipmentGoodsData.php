<?php

namespace Ipol\Catapulto\Api\Entity\Response;

use Ipol\Catapulto\Api\BadResponseException;

/**
 * Class ShipmentNpData
 * @package Ipol\Catapulto\Api\Entity\Response
 */
class ShipmentGoodsData extends AbstractResponse
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
     * @return ShipmentGoodsData
     */
    public function setStatus(string $status): ShipmentGoodsData
    {
        $this->status = $status;
        return $this;
    }



}
