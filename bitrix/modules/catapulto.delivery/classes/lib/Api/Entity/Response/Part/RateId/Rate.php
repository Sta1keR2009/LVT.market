<?php

namespace Ipol\Catapulto\Api\Entity\Response\Part\RateId;

use Ipol\Catapulto\Api\Entity\AbstractEntity;
use Ipol\Catapulto\Api\Entity\Response\Part\AbstractResponsePart;

/**
 * Class Rate
 * @package Ipol\Catapulto\Api\Entity\Response\Part\Geo
 */
class Rate extends AbstractEntity
{
    use AbstractResponsePart;

    /** @var int */
    protected $id;

    /** @var string */
    protected $operator;

    /** @var string */
    protected $rate;

    /** @var string */
    protected $rate_description;

    /** @var string|null */
    protected $pickup_day;

    /** @var string|null */
    protected $delivery_day;

    /** @var string|null */
    protected $transit_days;

    /** @var string|null */
    protected $delivery_time;

    /** @var int */
    protected $price;

    /** @var int|null */
    protected $delivery_success_rating;

    /** @var float|null */
    protected $operator_rating;
    
    /**
     * @var bool|null
     */
    protected $need_insurance;
    
    /** @var float|null */
    protected $insurance_config;

    /** @var string */
    protected $shipping_type;

    /**
     * @var RateServiceCollection
     */
    protected $additional_services;

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
     * @return Rate
     */
    public function setId(int $id): Rate
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getOperator(): string
    {
        return $this->operator;
    }

    /**
     * @param string $operator
     *
     * @return Rate
     */
    public function setOperator(string $operator): Rate
    {
        $this->operator = $operator;
        return $this;
    }

    /**
     * @return string
     */
    public function getRate(): string
    {
        return $this->rate;
    }

    /**
     * @param string $rate
     *
     * @return Rate
     */
    public function setRate(string $rate): Rate
    {
        $this->rate = $rate;
        return $this;
    }

    /**
     * @return string
     */
    public function getRateDescription(): string
    {
        return $this->rate_description;
    }

    /**
     * @param string $rate_description
     *
     * @return Rate
     */
    public function setRateDescription(string $rate_description): Rate
    {
        $this->rate_description = $rate_description;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPickupDay(): ?string
    {
        return $this->pickup_day;
    }

    /**
     * @param string|null $pickup_day
     *
     * @return Rate
     */
    public function setPickupDay(?string $pickup_day): Rate
    {
        $this->pickup_day = $pickup_day;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDeliveryDay(): ?string
    {
        return $this->delivery_day;
    }

    /**
     * @param string|null $delivery_day
     *
     * @return Rate
     */
    public function setDeliveryDay(?string $delivery_day): Rate
    {
        $this->delivery_day = $delivery_day;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTransitDays(): ?string
    {
        return $this->transit_days;
    }

    /**
     * @param string|null $transit_days
     *
     * @return Rate
     */
    public function setTransitDays(?string $transit_days): Rate
    {
        $this->transit_days = $transit_days;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDeliveryTime(): ?string
    {
        return $this->delivery_time;
    }

    /**
     * @param string|null $delivery_time
     *
     * @return Rate
     */
    public function setDeliveryTime(?string $delivery_time): Rate
    {
        $this->delivery_time = $delivery_time;
        return $this;
    }

    /**
     * @return int
     */
    public function getPrice(): int
    {
        return $this->price;
    }

    /**
     * @param int $price
     *
     * @return Rate
     */
    public function setPrice(int $price): Rate
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getDeliverySuccessRating(): ?int
    {
        return $this->delivery_success_rating;
    }

    /**
     * @param int|null $delivery_success_rating
     *
     * @return Rate
     */
    public function setDeliverySuccessRating(?int $delivery_success_rating): Rate
    {
        $this->delivery_success_rating = $delivery_success_rating;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getOperatorRating(): ?float
    {
        return $this->operator_rating;
    }

    /**
     * @param float|null $operator_rating
     *
     * @return Rate
     */
    public function setOperatorRating(?float $operator_rating): Rate
    {
        $this->operator_rating = $operator_rating;
        return $this;
    }
    
    /**
     * @param bool|null $need_insurance
     *
     * @return Rate
     */
    public function setNeedInsurance(?bool $need_insurance): Rate
    {
        $this->need_insurance = $need_insurance;
        return $this;
    }
    
    /**
     *
     * @return bool|null
     */
    public function getNeedInsurance(): ?bool
    {
        return $this->need_insurance;
    }
    
    /**
     * @param float|null $insurance_config
     *
     * @return Rate
     */
    public function setInsuranceConfig(?float $insurance_config): Rate
    {
        $this->insurance_config = $insurance_config;
        return $this;
    }
    
    /**
     *
     * @return float|null
     */
    public function getInsuranceConfig(): ?float
    {
        return $this->insurance_config;
    }



    /**
     * @return string
     */
    public function getShippingType(): string
    {
        return $this->shipping_type;
    }

    /**
     * @param string $shipping_type
     *
     * @return Rate
     */
    public function setShippingType(string $shipping_type): Rate
    {
        $this->shipping_type = $shipping_type;
        return $this;
    }

    /**
     * @return RateServiceCollection
     */
    public function getAdditionalServices(): RateServiceCollection
    {
        return $this->additional_services;
    }

    /**
     * @param RateServiceCollection $additional_services
     * @return Rate
     */
    public function setAdditionalServices(array $additional_services): Rate
    {
        $this->additional_services = (new RateServiceCollection())->fillFromArray($additional_services);
        return $this;
    }

}