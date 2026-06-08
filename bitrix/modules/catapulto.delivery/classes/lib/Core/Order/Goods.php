<?php


namespace Ipol\Catapulto\Core\Order;


/**
 * Class Goods
 * @package Ipol\Catapulto\Core
 * @subpackage Order
 * Shipment parameters for order: its dimensions, weight, volume additional details, number of elements
 */
class Goods
{
    /**
     * @var int
     * cm
     */
    protected $length;
    /**
     * @var int
     * cm
     */
    protected $width;
    /**
     * @var int
     * cm
     */
    protected $height;
    /**
     * @var int
     * gram
     */
    protected $weight;
    /**
     * @var float
     * cm^3
     */
    protected $volume;
    /**
     * @var string
     */
    protected $details;
    /**
     * @var int
     */
    protected $positions;

    /**
     * @return int
     */
    public function getPositions()
    {
        return $this->positions;
    }

    /**
     * @param  $positions
     * @return $this
     */
    public function setPositions($positions)
    {
        $this->positions = $positions;

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
     * @return float
     */
    public function getVolume()
    {
        return (isset($this->volume)) ? $this->volume : ($this->getHeight() * $this->getWidth() * $this->getLength());
    }

    /**
     * Do not use if not necessary - primary logic is to get volume from dimensions
     * Direct set for special occasions
     * @param float $volume
     * @return $this
     */
    public function setVolume($volume)
    {
        $this->volume = $volume;

        return $this;
    }

    /**
     * @return string
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param string $details
     * @return $this
     */
    public function setDetails($details)
    {
        $this->details = $details;

        return $this;
    }
}