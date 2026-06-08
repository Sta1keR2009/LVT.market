<?php

namespace Ipol\Catapulto\Api\Methods;

use Ipol\Catapulto\Api\Adapter\CurlAdapter;
use Ipol\Catapulto\Api\BadResponseException;
use Ipol\Catapulto\Api\Entity\EncoderInterface;
use Ipol\Catapulto\Api\Entity\Response\CompanyIcon as ObjResponse;
use Ipol\Catapulto\Api\Entity\Response\ErrorResponse;

/**
 * Class CompanyIcon
 * @package Ipol\Catapulto\Api
 * @subpakage Methods
 * @method ObjResponse getResponse
 */

class CompanyIcon extends GeneralMethod
{

    /**
     * CompanyIcon constructor.
     * @param CurlAdapter $adapter
     * @param EncoderInterface|null $encoder
     * @throws BadResponseException
     */
    public function __construct(CurlAdapter $adapter, $encoder = null)
    {
        parent::__construct(null, $adapter, ObjResponse::class, $encoder);
    }

}