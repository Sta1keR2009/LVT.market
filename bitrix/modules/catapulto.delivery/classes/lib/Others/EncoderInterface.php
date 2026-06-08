<?php

namespace Ipol\Catapulto\Others;


/**
 * Interface EncoderInterface
 * @package Ipol\Catapulto\Others
 * Encodes handle from API-server into cms encoding
 */
interface EncoderInterface
{
    public function encodeToAPI($handle);

    public function encodeFromAPI($handle);
}