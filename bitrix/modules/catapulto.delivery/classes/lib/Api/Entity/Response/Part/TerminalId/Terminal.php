<?php

namespace Ipol\Catapulto\Api\Entity\Response\Part\TerminalId;

class Terminal extends \Ipol\Catapulto\Api\Entity\AbstractEntity
{

    /** @var int */
    protected $id;

    /** @var string */
    protected $operator;

    /** @var string */
    protected $coordinates;

    /** @var string */
    protected $point_type;

    /** @var bool */
    protected $card;

    /** @var bool */
    protected $cash;

    /** @var string */
    protected $cityName;

    /** @var string */
    protected $code;

    /** @var string */
    protected $name;

    /** @var string */
    protected $work_time;

    /** @var string */
    protected $address;

    /** @var string|null */
    protected $phone;

    /** @var string */
    protected $note;

    /** @var bool */
    protected $selfPickup;

    /** @var int|null */
    protected $maxShipmentWeight = null;

    /** @var int|null */
    protected $maxWeight = null;

    /** @var int|null */
    protected $maxLength = null;

    /** @var int|null */
    protected $maxWidth = null;

    /** @var int|null */
    protected $maxHeight = null;

    /** @var string|array|null */
    protected $services = null;

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
     * @return Terminal
     */
    public function setId(int $id): Terminal
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
     * @return Terminal
     */
    public function setOperator(string $operator): Terminal
    {
        $this->operator = $operator;
        return $this;
    }

    /**
     * @return string
     */
    public function getCoordinates(): string
    {
        return $this->coordinates;
    }

    /**
     * @param string $coordinates
     *
     * @return Terminal
     */
    public function setCoordinates(string $coordinates): Terminal
    {
        $this->coordinates = $coordinates;
        return $this;
    }

    /**
     * @return string
     */
    public function getPointType(): string
    {
        return $this->point_type;
    }

    /**
     * @param string $point_type
     *
     * @return Terminal
     */
    public function setPointType(string $point_type): Terminal
    {
        $this->point_type = $point_type;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCard(): bool
    {
        return $this->card;
    }

    /**
     * @param bool $card
     *
     * @return Terminal
     */
    public function setCard(bool $card): Terminal
    {
        $this->card = $card;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCash(): bool
    {
        return $this->cash;
    }

    /**
     * @param bool $cash
     *
     * @return Terminal
     */
    public function setCash(bool $cash): Terminal
    {
        $this->cash = $cash;
        return $this;
    }

    /**
     * @return string
     */
    public function getCityName(): string
    {
        return $this->cityName;
    }

    /**
     * @param string $cityName
     *
     * @return Terminal
     */
    public function setCityName(string $cityName): Terminal
    {
        $this->cityName = $cityName;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     *
     * @return Terminal
     */
    public function setCode(string $code): Terminal
    {
        $this->code = $code;
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
     * @return Terminal
     */
    public function setName(string $name): Terminal
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getWorkTime(): string
    {
        return $this->work_time;
    }

    /**
     * @param string $work_time
     *
     * @return Terminal
     */
    public function setWorkTime(string $work_time): Terminal
    {
        $this->work_time = $work_time;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @param string $address
     *
     * @return Terminal
     */
    public function setAddress(string $address): Terminal
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string|null $phone
     * @return Terminal
     */
    public function setPhone(?string $phone): Terminal
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return string
     */
    public function getNote(): string
    {
        return $this->note;
    }

    /**
     * @param string $note
     *
     * @return Terminal
     */
    public function setNote(string $note): Terminal
    {
        $this->note = $note;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSelfPickup(): bool
    {
        return $this->selfPickup;
    }

    /**
     * @param bool $selfPickup
     *
     * @return Terminal
     */
    public function setSelfPickup(bool $selfPickup): Terminal
    {
        $this->selfPickup = $selfPickup;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getMaxShipmentWeight(): ?int
    {
        return $this->maxShipmentWeight;
    }

    /**
     * @param int|null $maxShipmentWeight
     *
     * @return Terminal
     */
    public function setMaxShipmentWeight(?int $maxShipmentWeight): Terminal
    {
        $this->maxShipmentWeight = $maxShipmentWeight;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getMaxWeight(): ?int
    {
        return $this->maxWeight;
    }

    /**
     * @param int|null $maxWeight
     *
     * @return Terminal
     */
    public function setMaxWeight(?int $maxWeight): Terminal
    {
        $this->maxWeight = $maxWeight;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getMaxLength(): ?int
    {
        return $this->maxLength;
    }

    /**
     * @param int|null $maxLength
     *
     * @return Terminal
     */
    public function setMaxLength(?int $maxLength): Terminal
    {
        $this->maxLength = $maxLength;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getMaxWidth(): ?int
    {
        return $this->maxWidth;
    }

    /**
     * @param int|null $maxWidth
     *
     * @return Terminal
     */
    public function setMaxWidth(?int $maxWidth): Terminal
    {
        $this->maxWidth = $maxWidth;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getMaxHeight(): ?int
    {
        return $this->maxHeight;
    }

    /**
     * @param int|null $maxHeight
     *
     * @return Terminal
     */
    public function setMaxHeight(?int $maxHeight): Terminal
    {
        $this->maxHeight = $maxHeight;
        return $this;
    }

    /**
     * @return string|array|null
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @param string|array|null $services
     *
     * @return Terminal
     */
    public function setServices($services): Terminal
    {
        $this->services = $services;
        return $this;
    }



}
