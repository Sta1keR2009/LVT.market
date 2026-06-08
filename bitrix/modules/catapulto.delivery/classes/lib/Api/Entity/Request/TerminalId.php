<?php

namespace Ipol\Catapulto\Api\Entity\Request;

class TerminalId extends AbstractRequest
{
    /** @var int */
    private $id;

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
     * @return TerminalId
     */
    public function setId(int $id): TerminalId
    {
        $this->id = $id;
        return $this;
    }

}