<?php

namespace Ipol\Catapulto\Api\Entity\Response;

use Ipol\Catapulto\Api\BadResponseException;
use Ipol\Catapulto\Api\Entity\Response\Part\TerminalId\TerminalCollection;

/**
 * Class Terminal
 * @package Ipol\Catapulto\Api\Entity\Response
 */
class TerminalId extends AbstractResponse
{

    /** @var array */
    protected $data;

    /** @var string */
    protected $status;

    /**
     * @return array|TerminalCollection
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     *
     * @return TerminalId
     */
    public function setData(array $data): TerminalId
    {
        $collection = new TerminalCollection();
        $this->data = $collection->fillFromArray($data);
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
     * @return TerminalId
     */
    public function setStatus(string $status): TerminalId
    {
        $this->status = $status;
        return $this;
    }



}
