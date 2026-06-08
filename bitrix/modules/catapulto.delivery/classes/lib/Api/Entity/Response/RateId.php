<?php

namespace Ipol\Catapulto\Api\Entity\Response;

use Ipol\Catapulto\Api\BadResponseException;
use Ipol\Catapulto\Api\Entity\Response\Part\RateId\RateCollection;

/**
 * Class RateId
 * @package Ipol\Catapulto\Api\Entity\Response
 */
class RateId extends AbstractResponse
{

    /** @var int */
    protected $count;

    /** @var RateCollection */
    protected $results;

    /** @var bool */
    protected $rate_completed;

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @param int $count
     *
     * @return RateId
     */
    public function setCount(int $count): RateId
    {
        $this->count = $count;
        return $this;
    }

    /**
     * @return RateCollection
     */
    public function getResults(): RateCollection
    {
        return $this->results;
    }

    /**
     * @param array $results
     *
     * @return RateId
     */
    public function setResults(array $results): RateId
    {
        $collection = new RateCollection();
        $this->results = $collection->fillFromArray($results);
        return $this;
    }

    /**
     * @return bool
     */
    public function isRateCompleted(): bool
    {
        return $this->rate_completed;
    }

    /**
     * @param bool $rate_completed
     *
     * @return RateId
     */
    public function setRateCompleted(bool $rate_completed): RateId
    {
        $this->rate_completed = $rate_completed;
        return $this;
    }



}
