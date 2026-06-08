<?php

namespace Ipol\Catapulto\Api\Entity\Response\Part\Geo;

use Ipol\Catapulto\Api\Entity\AbstractEntity;
use Ipol\Catapulto\Api\Entity\Response\Part\AbstractResponsePart;

/**
 * Class GeoItem
 * @package Ipol\Catapulto\Api\Entity\Response\Part\Geo
 */
class GeoItem extends AbstractEntity
{
    use AbstractResponsePart;

    /** @var int|null */
    protected $id;

    /** @var string|null */
    protected $locality;

    /** @var int|null */
    protected $zip;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     *
     * @return GeoItem
     */
    public function setId(?int $id): GeoItem
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLocality(): ?string
    {
        return $this->locality;
    }

    /**
     * @param string|null $locality
     *
     * @return GeoItem
     */
    public function setLocality(?string $locality): GeoItem
    {
        $this->locality = $locality;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getZip(): ?int
    {
        return $this->zip;
    }

    /**
     * @param int|null $zip
     *
     * @return GeoItem
     */
    public function setZip(?int $zip): GeoItem
    {
        $this->zip = $zip;
        return $this;
    }




}