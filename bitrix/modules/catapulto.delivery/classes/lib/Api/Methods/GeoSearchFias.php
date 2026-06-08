<?php

namespace Ipol\Catapulto\Api\Methods;

use Ipol\Catapulto\Api\Adapter\CurlAdapter;
use Ipol\Catapulto\Api\BadResponseException;
use Ipol\Catapulto\Api\Entity\EncoderInterface;
use Ipol\Catapulto\Api\Entity\Response\GeoSearchFias as ObjResponse;
use Ipol\Catapulto\Api\Entity\Request\GeoSearchFias as ObjRequest;
use Ipol\Catapulto\Api\Entity\Response\ErrorResponse;

/**
 * Class GeoSearchFias
 *
 * @package   Ipol\Catapulto\Api
 * @subpakage Methods
 * @method ObjResponse getResponse
 */
class GeoSearchFias extends GeneralMethod
{
    
    /**
     * Geo constructor.
     *
     * @param ObjRequest            $data
     * @param CurlAdapter           $adapter
     * @param EncoderInterface|null $encoder
     *
     * @throws BadResponseException
     */
    public function __construct(ObjRequest $data, CurlAdapter $adapter, $encoder = null)
    {
        parent::__construct($data, $adapter, ObjResponse::class, $encoder);
    }
}