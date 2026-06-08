<?php
namespace Ipol\Catapulto\Bitrix\Entity;

class DefaultGabarites extends Options
{
    protected $mode;
    protected $weight;
    protected $length;
    protected $width;
    protected $height;

    public function __construct()
    {
        $this->mode   = self::fetchOption('defMode');
        $this->weight = floatval(self::fetchOption('defaultWeight'));
        $this->length = floatval(self::fetchOption('defaultLength'));
        $this->width  = floatval(self::fetchOption('defaultWidth'));
        $this->height = floatval(self::fetchOption('defaultHeight'));
    }

    /**
     * @return mixed
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @return mixed
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @return mixed
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }
}
