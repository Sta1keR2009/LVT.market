<?php

namespace Ipol\Catapulto\Api\Entity\Response;

use Ipol\Catapulto\Api\BadResponseException;

/**
 * Class ContactId
 * @package Ipol\Catapulto\Api\Entity\Response
 */
class ContactId extends AbstractResponse
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
     * @return Contact
     */
    public function setId(int $id): ContactId
    {
        $this->id = $id;
        return $this;
    }


}