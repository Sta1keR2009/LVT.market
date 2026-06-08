<?php

namespace Ipol\Catapulto\Catapulto\Controller;

use Ipol\Catapulto\Catapulto\Entity\CompanyIconResult as ResultObj;

class RequestCompanyIcon extends AutomatedCommonRequest
{
    /**
     * RequestCompanyIcon constructor.
     * @param ResultObj $resultObj
     */
    public function __construct(ResultObj $resultObj)
    {
        parent::__construct($resultObj);
    }


}