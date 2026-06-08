<?php


namespace Ipol\Catapulto\Core\Delivery;


/**
 * Class shipmentMerger
 * @package Ipol\Catapulto\Core
 * @subpackage Delivery
 * "Merges" all shipments into one final. Cost maximized terms - minimized
 */
class ShipmentMerger
{
    protected $price   = 0;
    protected $termMin = 0;
    protected $termMax = 0;
    protected $details = '';

    /**
     * @return string
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param string $details
     */
    public function setDetails($details)
    {
        $this->details = $details;
    }

    /**
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return int
     */
    public function getTermMin()
    {
        return $this->termMin;
    }

    /**
     * @return int
     */
    public function getTermMax()
    {
        return $this->termMax;
    }

    public function addShipment($price, $termMin, $termMax = false, $details = false)
    {
        $this->price += $price;
        $this->termMin = ($this->termMin > $termMin) ? $this->termMin : $termMin;
        if($termMax)
            $this->termMax = ($this->termMax > $termMax) ? $this->termMax : $termMax;
        if($this->termMax < $this->termMin)
            $this->termMax = $this->termMin;
        if($details)
            $this->details = $details;

        return $this;
    }

    public function getMergedArray()
    {
        return array(
            'price'   => $this->price,
            'termMin' => $this->termMin,
            'termMax' => $this->termMax,
            'details' => $this->details
        );
    }
}