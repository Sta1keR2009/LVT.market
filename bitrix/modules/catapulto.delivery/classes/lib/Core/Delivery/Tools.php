<?php


namespace Ipol\Catapulto\Core\Delivery;


/**
 * Class Tools
 * @package Ipol\Catapulto\Core
 * @subpackage Delivery
 */
class Tools
{
    /**
     * @param $termMin
     * @param $termMax
     * @param string $glue
     * @return mixed|string
     */
    public static function getTerm($termMin, $termMax, $glue = '-')
    {
        if($termMin == $termMax)
            return $termMin;
        else
            return $termMin.$glue.$termMax;
    }

    /**
     * Calculate volumetric weight for given dimensions
     *
     * @param int $length - mm|cm
     * @param int $width - mm|cm
     * @param int $height - mm|cm
     * @return float - gram|kg
     */
    public static function calculateVolumeWeight($length, $width, $height)
    {
        return ($length * $width * $height / 5000);
    }
}