<?php

namespace Ipol\Catapulto\Catapulto\Controller;

use Ipol\Catapulto\Api\Entity\Request\ShipmentId as RequestObj;
use Ipol\Catapulto\Catapulto\Entity\ShipmentReadResult as ResultObj;


class RequestShipmentRead extends AutomatedCommonRequest
{

    /** @var string */
    protected $id;

    /**
     * @param ResultObj $resultObj
     *
     */
    public function __construct(ResultObj $resultObj, string $id)
    {
        parent::__construct($resultObj);
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getSelfHash(): string
    {
        return md5($this->id);
    }

    /**
     * @return $this
     */
    public function convert()
    {
        $data = new RequestObj();
        $data->setId($this->id);

        $this->setRequestObj($data);

        return $this;

    }


}