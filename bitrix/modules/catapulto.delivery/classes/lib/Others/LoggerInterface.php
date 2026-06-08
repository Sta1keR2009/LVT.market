<?php

namespace Ipol\Catapulto\Others;

interface LoggerInterface
{

    /**
     * @param $wat - what we are going to log
     * @param $label - label: wat - just to be clear
     * @param $src - if given: file where to write
     * @param $flags - flags 4 file (append, etc)
     * @return mixed
     */
    public function toLog($wat, $label = "", $src = false, $flags = false);
}