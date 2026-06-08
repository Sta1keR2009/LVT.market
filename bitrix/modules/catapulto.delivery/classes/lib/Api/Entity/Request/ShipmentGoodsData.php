<?php

namespace Ipol\Catapulto\Api\Entity\Request;

use Ipol\Catapulto\Api\Entity\Request\Part\ShipmentNpData\Item;
use Ipol\Catapulto\Api\Entity\Request\Part\ShipmentNpData\ItemCollection;

class ShipmentGoodsData extends AbstractRequest
{

    /** @var int */
    protected $calc_result;

    /** @var ItemCollection */
    protected $items;

    /** @var float */
    protected $sum_to_pay;

    protected $from_eshop = true;

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
     * @return ShipmentGoodsData
     */
    public function setCalcResult(int $calc_result): ShipmentGoodsData
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
     * @return ShipmentGoodsData
     */
    public function setItems(ItemCollection $items): ShipmentGoodsData
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
     * @return ShipmentGoodsData
     */
    public function setSumToPay(float $sum_to_pay): ShipmentGoodsData
    {
        $this->sum_to_pay = $sum_to_pay;
        return $this;
    }

    public function isFromEshop(): bool
    {
        return $this->from_eshop;
    }

    public function setFromEshop(bool $from_eshop): ShipmentGoodsData
    {
        $this->from_eshop = $from_eshop;
        return $this;
    }

}
