<?php

namespace Ipol\Catapulto\Api\Entity\Request;

class CancelOrder extends AbstractRequest
{

    /**
     * @var string
     */
    protected $key;

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return CancelOrder
     */
    public function setKey(string $key): CancelOrder
    {
        $this->key = $key;
        return $this;
    }

}