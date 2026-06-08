<?php

namespace Ipol\Catapulto\Api\Entity\Response\Part\Terminal;

class Terminal extends \Ipol\Catapulto\Api\Entity\AbstractEntity
{

    /** @var int */
    protected $id;

    /** @var string */
    protected $operator;

    /** @var string */
    protected $operator_display;

    /** @var string|null */
    protected $city;

    /** @var string|null */
    protected $address;

    /** @var string|null */
    protected $note;

    /** @var string */
    protected $coordinates;

    /** @var string */
    protected $point_type;

    /** @var bool */
    protected $card;

    /** @var bool */
    protected $cash;

    /** @var string|null */
    protected $city_name;

    /** @var string|null */
    protected $region;

    /** @var bool  */
    protected $is_need_recalculate = false;
    /** @var int */
    protected $locality_id;
    /** @var string|null */
    protected $fias_id;

    /** @var bool|null  */
    protected $is_dressing_room;

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
    public function getOperatorDisplay(): string
    {
        return $this->operator_display;
    }

    /**
     * @param string $operator_display
     *
     * @return Terminal
     */
    public function setOperatorDisplay(string $operator_display): Terminal
    {
        $this->operator_display = $operator_display;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string|null $city
     *
     * @return Terminal
     */
    public function setCity(?string $city): Terminal
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @param string|null $address
     *
     * @return Terminal
     */
    public function setAddress(?string $address): Terminal
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNote(): ?string
    {
        return $this->note;
    }

    /**
     * @param string|null $note
     *
     * @return Terminal
     */
    public function setNote(?string $note): Terminal
    {
        $this->note = $note;
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

    public function getCityName(): ?string
    {
        return $this->city_name;
    }

    public function setCityName(?string $city_name): Terminal
    {
        $this->city_name = $city_name;
        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): Terminal
    {
        $this->region = $region;
        return $this;
    }

    public function isIsNeedRecalculate(): bool
    {
        return $this->is_need_recalculate;
    }

    public function setIsNeedRecalculate(bool $is_need_recalculate): Terminal
    {
        $this->is_need_recalculate = $is_need_recalculate;
        return $this;
    }

    public function getLocalityId(): int
    {
        return $this->locality_id;
    }

    public function setLocalityId(int $locality_id): Terminal
    {
        $this->locality_id = $locality_id;
        return $this;
    }

    public function getFiasId(): ?string
    {
        return $this->fias_id;
    }

    public function setFiasId(?string $fias_id): Terminal
    {
        $this->fias_id = $fias_id;
        return $this;
    }

    public function getIsDressingRoom(): ?bool
    {
        return $this->is_dressing_room;
    }

    public function setIsDressingRoom(?bool $is_dressing_room): Terminal
    {
        $this->is_dressing_room = $is_dressing_room;
        return $this;
    }


}
