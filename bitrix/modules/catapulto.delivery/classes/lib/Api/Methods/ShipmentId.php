<?php

namespace Ipol\Catapulto\Api\Methods;

use Ipol\Catapulto\Api\Adapter\CurlAdapter;
use Ipol\Catapulto\Api\BadResponseException;
use Ipol\Catapulto\Api\Entity\EncoderInterface;
use Ipol\Catapulto\Api\Entity\Request\ShipmentId as ObjRequest;
use Ipol\Catapulto\Api\Entity\Response\ShipmentId as ObjResponse;
use Ipol\Catapulto\Api\Entity\Response\ErrorResponse;

/**
 * Class ShipmentId
 * @package Ipol\Catapulto\Api
 * @subpakage Methods
 * @method ObjResponse getResponse
 */

class ShipmentId extends GeneralMethod
{

    /**
     * @param ObjRequest $data
     * @param CurlAdapter $adapter
     * @param EncoderInterface|null $encoder
     * @throws BadResponseException
     */
    public function __construct(ObjRequest $data, CurlAdapter $adapter, $encoder = null)
    {
        $this->setUrlImplement($data->getId());
        parent::__construct($data, $adapter, ObjResponse::class, $encoder);
    }

}