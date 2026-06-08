<?php

namespace Ipol\Catapulto\Api\Entity\Request;


class TariffId extends AbstractRequest
{

    /** @var int tariff id (implements url) */
    private $id;

    /** @var int min 0, max 365 */
    protected $pickup_days_shift = 0;

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
     * @return int
     */
    public function getPickupDaysShift(): int
    {
        return $this->pickup_days_shift;
    }

    /**
     * @param int $pickup_days_shift
     *
     * @return TariffId
     */
    public function setPickupDaysShift(int $pickup_days_shift): TariffId
    {
        $this->pickup_days_shift = $pickup_days_shift;
        return $this;
    }



}