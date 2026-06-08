<?php

namespace Ipol\Catapulto\Api\Entity\Request;


class Contact extends AbstractRequest
{

    // TODO redo data types when api learns to accept null

    /**
     * @var int ID геообъекта (из справочника городов)
     */
    protected $locality_id;

    /** @var int Почтовый индекс (из справочника городов) */
    protected $zip;

    /** @var string Улица */
    protected $street;

    /** @var string Здание */
    protected $building;

    /** @var string Офис/квартира */
    protected $door_number;

    /** @var string Комментарий */
    protected $comment;

    /** @var string Компания */
    protected $company;

    /** @var string ФИО */
    protected $name;

    /** @var string Телефон (format +78001112233) */
    protected $phone = '+78001112233';

    /** @var string Код страны */
    protected $iso = 'ru';

    /**
     * @return int
     */
    public function getLocalityId(): int
    {
        return $this->locality_id;
    }

    /**
     * @param int $locality_id
     *
     * @return Contact
     */
    public function setLocalityId(int $locality_id): Contact
    {
        $this->locality_id = $locality_id;
        return $this;
    }

    /**
     * @return int
     */
    public function getZip(): int
    {
        return $this->zip;
    }

    /**
     * @param int $zip
     *
     * @return Contact
     */
    public function setZip(int $zip): Contact
    {
        $this->zip = $zip;
        return $this;
    }

    /**
     * @return string
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * @param string $street
     *
     * @return Contact
     */
    public function setStreet(string $street): Contact
    {
        $this->street = $street;
        return $this;
    }

    /**
     * @return string
     */
    public function getBuilding(): string
    {
        return $this->building;
    }

    /**
     * @param string $building
     *
     * @return Contact
     */
    public function setBuilding(string $building): Contact
    {
        $this->building = $building;
        return $this;
    }

    /**
     * @return string
     */
    public function getDoorNumber(): string
    {
        return $this->door_number;
    }

    /**
     * @param string $door_number
     *
     * @return Contact
     */
    public function setDoorNumber(string $door_number): Contact
    {
        $this->door_number = $door_number;
        return $this;
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     *
     * @return Contact
     */
    public function setComment(string $comment): Contact
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return string
     */
    public function getCompany(): string
    {
        return $this->company;
    }

    /**
     * @param string $company
     *
     * @return Contact
     */
    public function setCompany(string $company): Contact
    {
        $this->company = $company;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return Contact
     */
    public function setName(string $name): Contact
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     *
     * @return Contact
     */
    public function setPhone(string $phone): Contact
    {
        $this->phone = $phone;
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
     * @return Contact
     */
    public function setIso(string $iso): Contact
    {
        $this->iso = $iso;
        return $this;
    }


}