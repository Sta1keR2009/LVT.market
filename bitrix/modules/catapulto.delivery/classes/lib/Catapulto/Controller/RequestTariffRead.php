<?php

namespace Ipol\Catapulto\Catapulto\Controller;

use Ipol\Catapulto\Api\Entity\Request\TariffId as RequestObj;
use Ipol\Catapulto\Catapulto\Entity\TariffReadResult as ResultObj;


class RequestTariffRead extends AutomatedCommonRequest
{

    /** @var int  */
    protected $id;

    /** @var int  */
    protected $pickup_days_shift;

    /**
     * @param ResultObj $resultObj
     */
    public function __construct(ResultObj $resultObj, int $id, int $pickup_days_shift)
    {
        parent::__construct($resultObj);
        $this->id = $id;
        $this->pickup_days_shift = $pickup_days_shift;
    }

    /**
     * @return string
     */
    public function getSelfHash(): string
    {
        $argsHash = serialize([$this->id, $this->pickup_days_shift]); //TODO change it to something better-better ACTUALLY IT SHOULD NOT BE CACHED
        return md5($argsHash);
    }

    /**
     * @return $this
     */
    public function convert()
    {
        $data = new RequestObj();

        $data->setId($this->id)
            ->setPickupDaysShift($this->pickup_days_shift);

        $this->setRequestObj($data);

        return $this;

    }


}