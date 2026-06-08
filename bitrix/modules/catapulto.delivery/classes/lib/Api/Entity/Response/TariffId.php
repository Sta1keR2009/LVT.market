<?php

namespace Ipol\Catapulto\Api\Entity\Response;

use Ipol\Catapulto\Api\BadResponseException;
use Ipol\Catapulto\Api\Entity\Response\Part\RateId\RateCollection;

/**
 * Class TariffId
 * @package Ipol\Catapulto\Api\Entity\Response
 */
class TariffId extends AbstractResponse
{

    /** @var int */
    protected $id;

    /** @var array */
    protected $time_slots;

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
     * @return TariffId
     */
    public function setId(int $id): TariffId
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return array
     */
    public function getTimeSlots(): array
    {
        return $this->time_slots;
    }

    /**
     * @param array $time_slots
     *
     * @return TariffId
     */
    public function setTimeSlots(array $time_slots): TariffId
    {
        $this->time_slots = $time_slots;
        return $this;
    }


    public function setFields($fields)
    {
        /*
         * Since only one tariffId can be queried, we get the first element
         * of the array without wrapping it in the collection
         */
        return parent::setFields(reset($fields));
    }


}
