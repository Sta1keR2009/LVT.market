<?php


namespace Ipol\Catapulto\Core\Entity\Packing\Arrangement;


/**
 * Class Box
 * @package Ipol\Catapulto\Core
 * @subpackage Packing
 */
class Box
{
    /**
     * @var float
     */
    protected $length;
    /**
     * @var float
     */
    protected $width;
    /**
     * @var float
     */
    protected $height;
    /**
     * @var float
     */
    protected $weight;

    /**
     * @return float
     */
    public function getLength(): float
    {
        return $this->length;
    }

    /**
     * @param float $length
     * @return Box
     */
    public function setLength(float $length): Box
    {
        $this->length = $length;
        return $this;
    }

    /**
     * @return float
     */
    public function getWidth(): float
    {
        return $this->width;
    }

    /**
     * @param float $width
     * @return Box
     */
    public function setWidth(float $width): Box
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return float
     */
    public function getHeight(): float
    {
        return $this->height;
    }

    /**
     * @param float $height
     * @return Box
     */
    public function setHeight(float $height): Box
    {
        $this->height = $height;
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
     * @return Box
     */
    public function setWeight(float $weight): Box
    {
        $this->weight = $weight;
        return $this;
    }

    /**
     * @return float|int
     * always consider measure units of dimensions
     */
    public function getVolume()
    {
        return $this->getLength()*$this->getWidth()*$this->getHeight();
    }

    /**
     * rotates Box, so that Length>Width>Height
     * @return $this
     */
    public function rotateToNorm()
    {
        $arDims = [$this->getLength(), $this->getWidth(), $this->getHeight()];
        rsort($arDims);
        $this->setLength($arDims[0]);
        $this->setWidth($arDims[1]);
        $this->setHeight($arDims[2]);

        return $this;
    }

}