<?php


namespace Ipol\Catapulto\Core\Order;


use Ipol\Catapulto\Core\Entity\FieldsContainer;
use Ipol\Catapulto\Core\Entity\Money;

/**
 * Class Item
 * @package Ipol\Catapulto\Core
 * @subpackage Order
 */
class Item
{
    use FieldsContainer;

    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $description;
    /**
     * @var int - gram
     */
    protected $weight;
    /**
     * @var int - mm
     */
    protected $length;
    /**
     * @var int - mm
     */
    protected $width;
    /**
     * @var int - mm
     */
    protected $height;
    /**
     * @var Money estimated cost for insurance
     */
    protected $cost;
    /**
     * @var Money price to be payed for item
     */
    protected $price;
    /**
     * @var float
     */
    protected $quantity;
    /**
     * @var string
     */
    protected $barcode;
    /**
     * @var string
     */
    protected $id;
    /**
     * @var string
     */
    protected $articul;
    /**
     * @var int
     */
    protected $vatRate;
    /**
     * @var Money
     */
    protected $vatSum;

    /**
     * @return int
     */
    public function getVatRate()
    {
        return $this->vatRate;
    }

    /**
     * @param int $vatRate
     * @return $this
     */
    public function setVatRate($vatRate)
    {
        $this->vatRate = $vatRate;

        return $this;
    }

    /**
     * @return Money
     */
    public function getVatSum()
    {
        return $this->vatSum;
    }

    /**
     * @param Money $vatSum
     * @return $this
     */
    public function setVatSum($vatSum)
    {
        $this->vatSum = $vatSum;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param int $weight
     * @return $this
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param int $length
     * @return $this
     */
    public function setLength($length)
    {
        $this->length = $length;

        return $this;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param int $width
     * @return $this
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param int $height
     * @return $this
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @return Money estimated cost for insurance
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @param Money $cost estimated cost for insurance
     * @return $this
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * @return Money price to be payed for item
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param Money $price price to be payed for item
     * @return $this
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return floot
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param float $quantity
     * @return $this
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return string
     */
    public function getBarcode()
    {
        return $this->barcode;
    }

    /**
     * @param string $barcode
     * @return $this
     */
    public function setBarcode($barcode)
    {
        $this->barcode = $barcode;

        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getArticul()
    {
        return $this->articul;
    }

    /**
     * @param string $articul
     * @return $this
     */
    public function setArticul($articul)
    {
        $this->articul = $articul;

        return $this;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return get_object_vars($this);
    }


}
