<?php

namespace Ipol\Catapulto\Api\Entity\Request;

use Ipol\Catapulto\Api\Entity\Request\Part\ShipmentNpData\Item;
use Ipol\Catapulto\Api\Entity\Request\Part\ShipmentNpData\ItemCollection;

class ShipmentNpData extends AbstractRequest
{

    /** @var int */
    protected $calc_result;

    /** @var ItemCollection */
    protected $items;

    /** @var float */
    protected $sum_to_pay;

    /**
     * @var string enum "sender" or "receiver"
     */
    protected $delivery_pay_side;

    /**
     * @var int
     */
    protected $width;
    /**
     * @var int
     */
    protected $length;
    /**
     * @var int
     */
    protected $height;
    /**
     * @var int
     */
    protected $weight;

    /**
     * @return int
     */
    public function getCalcResult(): int
    {
        return $this->calc_result;
    }

    /**
     * @param int $calc_result
     *
     * @return ShipmentNpData
     */
    public function setCalcResult(int $calc_result): ShipmentNpData
    {
        $this->calc_result = $calc_result;
        return $this;
    }

    /**
     * @return ItemCollection
     */
    public function getItems(): ItemCollection
    {
        return $this->items;
    }

    /**
     * @param ItemCollection $items
     *
     * @return ShipmentNpData
     */
    public function setItems(ItemCollection $items): ShipmentNpData
    {
        $this->items = $items;
        return $this;
    }

    /**
     * @return float
     */
    public function getSumToPay(): float
    {
        return $this->sum_to_pay;
    }

    /**
     * @param float $sum_to_pay
     *
     * @return ShipmentNpData
     */
    public function setSumToPay(float $sum_to_pay): ShipmentNpData
    {
        $this->sum_to_pay = $sum_to_pay;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryPaySide(): string
    {
        return $this->delivery_pay_side;
    }

    /**
     * @param string $delivery_pay_side  enum "sender" or "receiver"
     *
     * @return ShipmentNpData
     */
    public function setDeliveryPaySide(string $delivery_pay_side): ShipmentNpData
    {
        $this->delivery_pay_side = $delivery_pay_side;
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
     * @return ShipmentNpData
     */
    public function setWidth(int $width): ShipmentNpData
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
     * @return ShipmentNpData
     */
    public function setLength(int $length): ShipmentNpData
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
     * @return ShipmentNpData
     */
    public function setHeight(int $height): ShipmentNpData
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
     * @return ShipmentNpData
     */
    public function setWeight(int $weight): ShipmentNpData
    {
        $this->weight = $weight;
        return $this;
    }

}