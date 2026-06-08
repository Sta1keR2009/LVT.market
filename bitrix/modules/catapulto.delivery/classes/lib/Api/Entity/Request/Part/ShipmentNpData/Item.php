<?php

namespace Ipol\Catapulto\Api\Entity\Request\Part\ShipmentNpData;


class Item extends \Ipol\Catapulto\Api\Entity\AbstractEntity
{

    /** @var string */
    protected $name;

    /** @var float */
    protected $quantity;

    /** @var float */
    protected $unit_value;

    /**
     * @var int mm
     */
    protected $width;
    /**
     * @var int mm
     */
    protected $length;
    /**
     * @var int mm
     */
    protected $height;
    /**
     * @var int gr
     */
    protected $weight;

    /**
     * @var string;
     */
    protected $articul;

    /**
     * @var int|null
     */
    protected $cargo_id;

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
     * @return Item
     */
    public function setName(string $name): Item
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return float
     */
    public function getQuantity(): float
    {
        return $this->quantity;
    }

    /**
     * @param float $quantity
     *
     * @return Item
     */
    public function setQuantity(float $quantity): Item
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @return float
     */
    public function getUnitValue(): float
    {
        return $this->unit_value;
    }

    /**
     * @param float $unit_value
     *
     * @return Item
     */
    public function setUnitValue(float $unit_value): Item
    {
        $this->unit_value = $unit_value;
        return $this;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * @param int $width
     * @return Item
     */
    public function setWidth(int $width): Item
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }

    /**
     * @param int $length
     * @return Item
     */
    public function setLength(int $length): Item
    {
        $this->length = $length;
        return $this;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @param int $height
     * @return Item
     */
    public function setHeight(int $height): Item
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @return int
     */
    public function getWeight(): int
    {
        return $this->weight;
    }

    /**
     * @param int $weight
     * @return Item
     */
    public function setWeight(int $weight): Item
    {
        $this->weight = $weight;
        return $this;
    }

    /**
     * @return string
     */
    public function getArticul(): string
    {
        return $this->articul;
    }

    /**
     * @param string $articul
     * @return Item
     */
    public function setArticul(string $articul): Item
    {
        $this->articul = $articul;
        return $this;
    }

    public function getCargoId(): ?int
    {
        return $this->cargo_id;
    }

    public function setCargoId(?int $cargo_id): Item
    {
        $this->cargo_id = $cargo_id;
        return $this;
    }

}
