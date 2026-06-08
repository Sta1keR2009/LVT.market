<?php

namespace Ipol\Catapulto\Catapulto\Controller;

use Ipol\Catapulto\Api\Entity\Request\TerminalId as RequestObj;
use Ipol\Catapulto\Catapulto\Entity\TerminalReadResult as ResultObj;


class RequestTerminalRead extends AutomatedCommonRequest
{

    /** @var int */
    protected $id;

    /**
     * @param ResultObj $resultObj
     *
     */
    public function __construct(ResultObj $resultObj, int $id)
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