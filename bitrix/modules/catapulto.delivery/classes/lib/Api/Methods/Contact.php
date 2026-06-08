<?php

namespace Ipol\Catapulto\Api\Methods;

use Ipol\Catapulto\Api\Adapter\CurlAdapter;
use Ipol\Catapulto\Api\BadResponseException;
use Ipol\Catapulto\Api\Entity\EncoderInterface;
use Ipol\Catapulto\Api\Entity\Response\Contact as ObjResponse;
use Ipol\Catapulto\Api\Entity\Request\Contact as ObjRequest;
use Ipol\Catapulto\Api\Entity\Response\ErrorResponse;

/**
 * Class Contact
 * @package Ipol\Catapulto\Api
 * @subpakage Methods
 * @method ObjResponse getResponse
 */

class Contact extends GeneralMethod
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