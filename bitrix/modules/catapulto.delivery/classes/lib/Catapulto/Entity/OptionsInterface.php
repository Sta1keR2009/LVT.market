<?php


namespace Ipol\Catapulto\Catapulto\Entity;


/**
 * Interface OptionsInterface
 * @package Ipol\Catapulto\Others
 * gets options - used "fetch",because "get" for getters
 * add specific options with "public function fetch<Option>" for overload
 */
interface OptionsInterface
{
    public static function fetchOption($handle);

    /**
     * @param $option
     * @param $handle
     * @return $this
     *
     * DOESNT sets anything in cms options! Only in container-class!
     */
    public function pushOption($option, $handle);

}