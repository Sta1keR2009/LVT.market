<?php

namespace Ipol\Catapulto\Catapulto\Controller;

use Ipol\Catapulto\Api\Entity\Request\Geo as RequestObj;
use Ipol\Catapulto\Catapulto\Entity\GeoResult as ResultObj;


class RequestGeo extends AutomatedCommonRequest
{
    /**
     * @param ResultObj $resultObj
     * @param string|int $term
     * @param string|null $iso
     * @param int|null $limit
     */
    public function __construct(
        ResultObj $resultObj,
        $term,
        ?string $cityName = null,
        ?string $iso = null,
        ?int $limit,
        ?string $cityFiasId = null,
        ?string $settlementFiasId = null,
        ?string $fiasLevel = null,
        ?string $settlementType = null
    )
    {
        parent::__construct($resultObj);

        $data = new RequestObj();
        $data->setTerm((string)$term)
            ->setCityName($cityName)
            ->setIso($iso)
            ->setLimit($limit)
            ->setCityFiasId($cityFiasId ?? 'null')
            ->setSettlementFiasId($settlementFiasId ?? 'null')
            ->setFiasLevel($fiasLevel ?? 'null')
            ->setSettlementType($settlementType ?? 'null')
        ;

        $this->setRequestObj($data);
    }


}
