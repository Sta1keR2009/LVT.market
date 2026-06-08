<?php

namespace Ipol\Catapulto\Api\Entity\Response\Part\RateId;


use Ipol\Catapulto\Api\Entity\AbstractEntity;

class RateService extends AbstractEntity
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var float
     */
    protected $cost;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return RateService
     */
    public function setName(string $name): RateService
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return RateService
     */
    public function setDescription(string $description): RateService
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return float
     */
    public function getCost(): float
    {
        return $this->cost;
    }

    /**
     * @param float $cost
     * @return RateService
     */
    public function setCost(float $cost): RateService
    {
        $this->cost = $cost;
        return $this;
    }

}