<?php

namespace Ipol\Catapulto\Api\Entity\Response;

class CancelOrder extends AbstractResponse
{

    /**
     * @var string
     */
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
     * @return CancelOrder
     */
    public function setStatus(string $status): CancelOrder
    {
        $this->status = $status;
        return $this;
    }

}