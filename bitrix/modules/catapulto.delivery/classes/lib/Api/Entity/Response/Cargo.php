<?php

namespace Ipol\Catapulto\Api\Entity\Response;

use Ipol\Catapulto\Api\BadResponseException;

/**
 * Class Cargo
 * @package Ipol\Catapulto\Api\Entity\Response
 */
class Cargo extends AbstractResponse
{

    /** @var int */
    protected $id;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Cargo
     */
    public function setId(int $id): Cargo
    {
        $this->id = $id;
        return $this;
    }

}