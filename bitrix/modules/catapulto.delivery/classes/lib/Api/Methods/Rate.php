<?php

namespace Ipol\Catapulto\Api\Methods;

use Ipol\Catapulto\Api\Adapter\CurlAdapter;
use Ipol\Catapulto\Api\BadResponseException;
use Ipol\Catapulto\Api\Entity\EncoderInterface;
use Ipol\Catapulto\Api\Entity\Response\Rate as ObjResponse;
use Ipol\Catapulto\Api\Entity\Request\Rate as ObjRequest;
use Ipol\Catapulto\Api\Entity\Response\ErrorResponse;

/**
 * Class Rate
 * @package Ipol\Catapulto\Api
 * @subpakage Methods
 * @method ObjResponse getResponse
 */

class Rate extends GeneralMethod
{

    /**
     * @param ObjRequest $data
     * @param CurlAdapter $adapter
     * @param EncoderInterface|null $encoder
     * @throws BadResponseException
     */
    public function __construct(ObjRequest $data,CurlAdapter $adapter, $encoder = null)
    {
        parent::__construct($data, $adapter, ObjResponse::class, $encoder);
    }

}