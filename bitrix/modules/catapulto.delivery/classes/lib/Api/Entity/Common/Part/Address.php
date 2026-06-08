<?php

namespace Ipol\Catapulto\Api\Entity\Common\Part;


class Address extends \Ipol\Catapulto\Api\Entity\AbstractEntity
{

    /** @var string|null */
    protected $id;

    /** @var string|null */
    protected $country;

    /** @var string|null */
    protected $locality;

    /** @var string|null */
    protected $locality_type;

    /** @var string|null */
    protected $state_province;

    /** @var string|null */
    protected $region1;

    /** @var string|null */
    protected $region1_type;

    /** @var string|null */
    protected $region2;

    /** @var string|null */
    protected $region2_type;

    /** @var string|null */
    protected $region3;

    /** @var string|null */
    protected $region3_type;

    /** @var string|null */
    protected $street;

    /** @var string|null */
    protected $street_type;

    /** @var string|null */
    protected $building;

    /** @var string|null */
    protected $door_number;

    /** @var string|null */
    protected $zip;

    /** @var string|null */
    protected $comment;

    /** @var string|null */
    protected $address_line_1;

    /** @var string|null */
    protected $address_line_2;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     *
     * @return Address
     */
    public function setId(?string $id): Address
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param string|null $country
     *
     * @return Address
     */
    public function setCountry(?string $country): Address
    {
        $this->country = $country;
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
     * @return Address
     */
    public function setLocality(?string $locality): Address
    {
        $this->locality = $locality;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLocalityType(): ?string
    {
        return $this->locality_type;
    }

    /**
     * @param string|null $locality_type
     *
     * @return Address
     */
    public function setLocalityType(?string $locality_type): Address
    {
        $this->locality_type = $locality_type;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStateProvince(): ?string
    {
        return $this->state_province;
    }

    /**
     * @param string|null $state_province
     *
     * @return Address
     */
    public function setStateProvince(?string $state_province): Address
    {
        $this->state_province = $state_province;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRegion1(): ?string
    {
        return $this->region1;
    }

    /**
     * @param string|null $region1
     *
     * @return Address
     */
    public function setRegion1(?string $region1): Address
    {
        $this->region1 = $region1;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRegion1Type(): ?string
    {
        return $this->region1_type;
    }

    /**
     * @param string|null $region1_type
     *
     * @return Address
     */
    public function setRegion1Type(?string $region1_type): Address
    {
        $this->region1_type = $region1_type;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRegion2(): ?string
    {
        return $this->region2;
    }

    /**
     * @param string|null $region2
     *
     * @return Address
     */
    public function setRegion2(?string $region2): Address
    {
        $this->region2 = $region2;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRegion2Type(): ?string
    {
        return $this->region2_type;
    }

    /**
     * @param string|null $region2_type
     *
     * @return Address
     */
    public function setRegion2Type(?string $region2_type): Address
    {
        $this->region2_type = $region2_type;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRegion3(): ?string
    {
        return $this->region3;
    }

    /**
     * @param string|null $region3
     *
     * @return Address
     */
    public function setRegion3(?string $region3): Address
    {
        $this->region3 = $region3;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRegion3Type(): ?string
    {
        return $this->region3_type;
    }

    /**
     * @param string|null $region3_type
     *
     * @return Address
     */
    public function setRegion3Type(?string $region3_type): Address
    {
        $this->region3_type = $region3_type;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStreet(): ?string
    {
        return $this->street;
    }

    /**
     * @param string|null $street
     *
     * @return Address
     */
    public function setStreet(?string $street): Address
    {
        $this->street = $street;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStreetType(): ?string
    {
        return $this->street_type;
    }

    /**
     * @param string|null $street_type
     *
     * @return Address
     */
    public function setStreetType(?string $street_type): Address
    {
        $this->street_type = $street_type;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBuilding(): ?string
    {
        return $this->building;
    }

    /**
     * @param string|null $building
     *
     * @return Address
     */
    public function setBuilding(?string $building): Address
    {
        $this->building = $building;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDoorNumber(): ?string
    {
        return $this->door_number;
    }

    /**
     * @param string|null $door_number
     *
     * @return Address
     */
    public function setDoorNumber(?string $door_number): Address
    {
        $this->door_number = $door_number;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getZip(): ?string
    {
        return $this->zip;
    }

    /**
     * @param string|null $zip
     *
     * @return Address
     */
    public function setZip(?string $zip): Address
    {
        $this->zip = $zip;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param string|null $comment
     *
     * @return Address
     */
    public function setComment(?string $comment): Address
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAddressLine1(): ?string
    {
        return $this->address_line_1;
    }

    /**
     * @param string|null $address_line_1
     *
     * @return Address
     */
    public function setAddressLine1(?string $address_line_1): Address
    {
        $this->address_line_1 = $address_line_1;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAddressLine2(): ?string
    {
        return $this->address_line_2;
    }

    /**
     * @param string|null $address_line_2
     *
     * @return Address
     */
    public function setAddressLine2(?string $address_line_2): Address
    {
        $this->address_line_2 = $address_line_2;
        return $this;
    }



}