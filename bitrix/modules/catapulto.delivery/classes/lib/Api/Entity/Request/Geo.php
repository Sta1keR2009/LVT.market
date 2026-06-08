<?php

namespace Ipol\Catapulto\Api\Entity\Request;

class Geo extends AbstractRequest
{
    /** @var string */
    protected $term;

    /** @var string */
    protected $city_name;

    /** @var string */
    protected $iso = 'ru';

    /** @var int $limit Min - 1, max - 50 */
    protected $limit = 50;

    /** @var string|null $city_fias_id */
    protected $city_fias_id = '';

    /** @var string|null $settlement_fias_id */
    protected $settlement_fias_id = '';

    /** @var string|null $fias_level */
    protected $fias_level = '';

    /** @var string|null $settlement_type */
    protected $settlement_type = '';

    /**
     * @return string
     */
    public function getTerm(): string
    {
        return $this->term;
    }

    /**
     * @param string $term
     *
     * @return Geo
     */
    public function setTerm(string $term): Geo
    {
        $this->term = $term;
        return $this;
    }

    /**
     * @return string
     */
    public function getCityName(): string
    {
        return $this->city_name;
    }

    /**
     * @param string $city_name
     * @return Geo
     */
    public function setCityName(string $city_name): Geo
    {
        $this->city_name = $city_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getIso(): string
    {
        return $this->iso;
    }

    /**
     * @param string $iso
     *
     * @return Geo
     */
    public function setIso(string $iso): Geo
    {
        $this->iso = $iso;
        return $this;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     *
     * @return Geo
     */
    public function setLimit(int $limit): Geo
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCityFiasId(): ?string
    {
        return $this->city_fias_id;
    }

    /**
     * @param string|null $city_fias_id
     * @return Geo
     */
    public function setCityFiasId(?string $city_fias_id): Geo
    {
        $this->city_fias_id = $city_fias_id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSettlementFiasId(): ?string
    {
        return $this->settlement_fias_id;
    }

    /**
     * @param string|null $settlement_fias_id
     * @return Geo
     */
    public function setSettlementFiasId(?string $settlement_fias_id): Geo
    {
        $this->settlement_fias_id = $settlement_fias_id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFiasLevel(): ?string
    {
        return $this->fias_level;
    }

    /**
     * @param string|null $fias_level
     * @return Geo
     */
    public function setFiasLevel(?string $fias_level): Geo
    {
        $this->fias_level = $fias_level;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSettlementType(): ?string
    {
        return $this->settlement_type;
    }

    /**
     * @param string|null $settlement_type
     * @return Geo
     */
    public function setSettlementType(?string $settlement_type): Geo
    {
        $this->settlement_type = $settlement_type;
        return $this;
    }


}
