<?php

namespace Ipol\Catapulto\Api\Entity\Response;

use Ipol\Catapulto\Api\BadResponseException;

/**
 * Class Rate
 * @package Ipol\Catapulto\Api\Entity\Response
 */
class Rate extends AbstractResponse
{

    /** @var string */
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
     *
     * @return Rate
     */
    public function setKey(string $key): Rate
    {
        $this->key = $key;
        return $this;
    }


}
