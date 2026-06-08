<?php

namespace Ipol\Catapulto\Api\Entity\Request;

class Cargo extends AbstractRequest
{

    /** @var string */
    protected $delivery_type;

    /** @var string */
    protected $cargo_comment;

    /** @var int */
    protected $height;

    /** @var int */
    protected $length;

    /** @var int */
    protected $width;

    /** @var int */
    protected $quantity;

    /** @var float */
    protected $weight;

    /**
     * @return string
     */
    public function getDeliveryType(): string
    {
        return $this->delivery_type;
    }

    /**
     * @param string $delivery_type
     *
     * @return Cargo
     */
    public function setDeliveryType(string $delivery_type): Cargo
    {
        $this->delivery_type = $delivery_type;
        return $this;
    }

    /**
     * @return string
     */
    public function getCargoComment(): string
    {
        return $this->cargo_comment;
    }

    /**
     * @param string|null $cargo_comment
     *
     * @return Cargo
     */
    public function setCargoComment(?string $cargo_comment): Cargo
    {
        $this->cargo_comment = $cargo_comment;
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
     *
     * @return Cargo
     */
    public function setHeight(int $height): Cargo
    {
        $this->height = $height;
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
     *
     * @return Cargo
     */
    public function setLength(int $length): Cargo
    {
        $this->length = $length;
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
     *
     * @return Cargo
     */
    public function setWidth(int $width): Cargo
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     *
     * @return Cargo
     */
    public function setQuantity(int $quantity): Cargo
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @return float
     */
    public function getWeight(): float
    {
        return $this->weight;
    }

    /**
     * @param float $weight
     *
     * @return Cargo
     */
    public function setWeight(float $weight): Cargo
    {
        $this->weight = $weight;
        return $this;
    }



}