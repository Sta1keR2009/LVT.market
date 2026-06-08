<?php

namespace Ipol\Catapulto\Catapulto\Controller;

use Ipol\Catapulto\Api\Entity\Request\RateId as RequestObj;
use Ipol\Catapulto\Catapulto\Entity\RateReadResult as ResultObj;


class RequestRateRead extends AutomatedCommonRequest
{

    /** @var string  */
    protected $id;

    /** @var int  */
    protected $pickup_days_shift;

    /** @var array  */
    protected $shipping_type_filter;

    /** @var array  */
    protected $services_filter;

    /** @var bool */
    protected $need_insurance;

    /** @var float */
    protected $insured_value;

    /**
     * @param ResultObj $resultObj
     * @param string $id
     * @param int $pickup_days_shift
     * @param array $shipping_type_filter
     * @param array $services_filter
     */
    public function __construct(
        ResultObj $resultObj,
        string $id,
        int $pickup_days_shift,
        array $shipping_type_filter,
        array $services_filter,
        bool $need_insurance,
        float $insured_value
    )
    {
        parent::__construct($resultObj);
        $this->id = $id;
        $this->pickup_days_shift = $pickup_days_shift;
        $this->shipping_type_filter = $shipping_type_filter;
        $this->services_filter = $services_filter;
        $this->need_insurance = $need_insurance;
        $this->insured_value = $insured_value;
    }

    /**
     * @return string
     */
    public function getSelfHash(): string
    {
        $argsHash = serialize(
            [
            $this->id,
            $this->pickup_days_shift,
            $this->shipping_type_filter,
            $this->services_filter,
            $this->need_insurance,
            $this->insured_value
            ]
        ); //TODO change it to something better-better ACTUALLY IT SHOULD NOT BE CACHED
        return md5($argsHash);
    }

    /**
     * @return $this
     */
    public function convert()
    {
        $data = new RequestObj();

        $data->setId($this->id)
            ->setPickupDaysShift($this->pickup_days_shift)
            ->setServicesFilter(implode(',', $this->services_filter))
            ->setShippingTypeFilter(implode(',', $this->shipping_type_filter))
            ->setNeedInsurance($this->need_insurance)
            ->setInsuredValue($this->insured_value)
        ;

        $this->setRequestObj($data);

        return $this;

    }


}