<?php

namespace Ipol\Catapulto\Catapulto\Controller;

use Ipol\Catapulto\Api\Entity\Request\GeoSearchFias as RequestObj;
use Ipol\Catapulto\Catapulto\Entity\GeoSearchFiasResult as ResultObj;


class RequestGeoSearchFias extends AutomatedCommonRequest
{
    /**
     * @param ResultObj   $resultObj
     * @param string|int  $term
     * @param string|null $iso
     * @param int|null    $limit
     */
    public function __construct(
        ResultObj $resultObj,
        string $localityName,
        string $localityType,
        string $regionName = ''
    )
    {
        parent::__construct($resultObj);
        
        $data = new RequestObj();
        $data->setLocalityName($localityName)
            ->setLocalityType($localityType)
            ->setRegionName($regionName);
        
        $this->setRequestObj($data);
    }
}