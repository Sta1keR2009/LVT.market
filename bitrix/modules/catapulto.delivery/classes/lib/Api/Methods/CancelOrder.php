<?php

namespace Ipol\Catapulto\Api\Methods;

use Ipol\Catapulto\Api\Adapter\CurlAdapter;
use Ipol\Catapulto\Api\Entity\Request\CancelOrder as ObjRequest;
use Ipol\Catapulto\Api\Entity\Response\CancelOrder as ObjResponse;

class CancelOrder extends GeneralMethod
{

    public function __construct(ObjRequest $data, CurlAdapter $adapter, $encoder = null)
    {
        parent::__construct($data, $adapter, ObjResponse::class, $encoder);
    }

}