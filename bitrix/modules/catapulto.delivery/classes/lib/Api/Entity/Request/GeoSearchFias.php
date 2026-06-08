<?php

namespace Ipol\Catapulto\Api\Entity\Request;

class GeoSearchFias extends AbstractRequest
{
    /** @var string */
    protected $locality_name;
    
    /** @var string */
    protected $locality_type;
    
    /** @var string */
    protected $region_name = '';
    
    public function getLocalityName(): string
    {
        return $this->locality_name;
    }
    
    public function setLocalityName(string $localityName): self
    {
        $this->locality_name = $localityName;
        return $this;
    }
    
    public function getLocalityType(): string
    {
        return $this->locality_type;
    }
    
    public function setLocalityType(string $localityType): self
    {
        $this->locality_type = $localityType;
        return $this;
    }
    
    public function getRegionName(): string
    {
        return $this->region_name;
    }
    
    public function setRegionName(string $regionName): self
    {
        $this->region_name = $regionName;
        return $this;
    }
}