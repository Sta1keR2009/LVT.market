<?php

namespace Ipol\Catapulto\Api\Entity\Request;


class ContactId extends AbstractRequest
{

    // TODO redo data types when api learns to accept null

    /** @var int id обновляемого контакта */
    private $id;

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
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return ContactId
     */
    public function setId(int $id): ContactId
    {
        $this->id = $id;
        return $this;
    }


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
     * @return ContactId
     */
    public function setLocalityId(?int $locality_id): ContactId
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
     * @return ContactId
     */
    public function setZip(?int $zip): ContactId
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
     * @return ContactId
     */
    public function setStreet(?string $street): ContactId
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
     * @return ContactId
     */
    public function setBuilding(?string $building): ContactId
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
     * @return ContactId
     */
    public function setDoorNumber(?string $door_number): ContactId
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
     * @return ContactId
     */
    public function setComment(?string $comment): ContactId
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
     * @return ContactId
     */
    public function setCompany(?string $company): ContactId
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
     * @return ContactId
     */
    public function setName(?string $name): ContactId
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
     * @return ContactId
     */
    public function setPhone(?string $phone): ContactId
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
     * @return ContactId
     */
    public function setIso(?string $iso): ContactId
    {
        $this->iso = $iso;
        return $this;
    }


}