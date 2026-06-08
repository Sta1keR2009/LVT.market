<?php

namespace Ipol\Catapulto\Api\Entity\Request;


class RateId extends AbstractRequest
{

    /** @var string $id */
    private $id;

    /** @var string */
    protected $shipping_type_filter;

    /** @var string|null */
    protected $pickup_days_shift;

    /** @var string|null */
    protected $services_filter;

    /** @var bool  */
    protected $need_insurance = false;

    /** @var float  */
    protected $insured_value = 0;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return RateId
     */
    public function setId(string $id): RateId
    {
        $this->id = $id;
        return $this;
    }


    /**
     * @return string
     */
    public function getShippingTypeFilter(): ?string
    {
        return $this->shipping_type_filter;
    }

    /**
     * @param string $shipping_type_filter
     *
     * @return RateId
     */
    public function setShippingTypeFilter(?string $shipping_type_filter): RateId
    {
        $this->shipping_type_filter = $shipping_type_filter;
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
     * @return RateId
     */
    public function setPickupDaysShift(int $pickup_days_shift): RateId
    {
        $this->pickup_days_shift = $pickup_days_shift;
        return $this;
    }

    /**
     * @return string
     */
    public function getServicesFilter(): ?string
    {
        return $this->services_filter;
    }

    /**
     * @param string $services_filter
     *
     * @return RateId
     */
    public function setServicesFilter(?string $services_filter): RateId
    {
        $this->services_filter = $services_filter;
        return $this;
    }

    /**
     * @return bool
     */
    public function isNeedInsurance(): bool
    {
        return $this->need_insurance;
    }

    /**
     * @param bool $need_insurance
     *
     * @return RateId
     */
    public function setNeedInsurance(bool $need_insurance): RateId
    {
        $this->need_insurance = $need_insurance;
        return $this;
    }

    /**
     * @return float
     */
    public function getInsuredValue()
    {
        return $this->insured_value;
    }

    /**
     * @param float $insured_value
     *
     * @return RateId
     */
    public function setInsuredValue($insured_value)
    {
        $this->insured_value = $insured_value;
        return $this;
    }


}