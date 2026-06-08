<?php

namespace Ipol\Catapulto\Api;


class Tools
{
    /**
     * @param $var mixed
     * @return bool
     */
    public static function isSeqArr($var)
    {// true if is SEQUENTIAL array
        return (is_array($var) && (empty($var) || array_keys($var) === range(0, count($var) - 1)));
    }

}