<?php

namespace Ipol\Catapulto\Api\Entity\Response;

use Ipol\Catapulto\Api\BadResponseException;
use Ipol\Catapulto\Api\Entity\Response\Part\Terminal\Data;

/**
 * Class Terminal
 * @package Ipol\Catapulto\Api\Entity\Response
 */
class Terminal extends AbstractResponse
{

    /** @var array|Data */
    protected $data;

    /** @var string */
    protected $status;

    /**
     * @return Data
     */
    public function getData(): Data
    {
        return $this->data;
    }

    /**
     * @param Data $data
     *
     * @return Terminal
     */
    public function setData(array $data): Terminal
    {
        $collection = new Data();
        $this->data = $collection->setFields($data);
        return $this;
    }


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
    public function setStatus(string $status): Terminal
    {
        $this->status = $status;
        return $this;
    }



}
