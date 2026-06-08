<?php

namespace Ipol\Catapulto\Api\Methods;

use Ipol\Catapulto\Api\Adapter\CurlAdapter;

class GetWSToken extends GeneralMethod
{
    public function __construct(CurlAdapter $adapter, $encoder = null)
    {
        parent::__construct(null, $adapter, \Ipol\Catapulto\Api\Entity\Response\GetWSToken::class, $encoder);
    }
}
