<?php

namespace Ipol\Catapulto\Api\Entity\Response;

use Ipol\Catapulto\Api\BadResponseException;

/**
 * Class Shipment
 * @package Ipol\Catapulto\Api\Entity\Response
 */
class Shipment extends AbstractResponse
{

    /** @var string */
    protected $status;

    /** @var string */
    protected $key;

    /** @var string|null  */
    protected $number;

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
     * @return Shipment
     */
    public function setStatus(string $status): Shipment
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     *
     * @return Shipment
     */
    public function setKey(string $key): Shipment
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNumber(): ?string
    {
        return $this->number;
    }

    /**
     * @param string|null $number
     *
     * @return Shipment
     */
    public function setNumber(?string $number): Shipment
    {
        $this->number = $number;
        return $this;
    }



}
