<?php

namespace Ipol\Catapulto\Catapulto\Controller;

use Ipol\Catapulto\Api\Entity\Request\CancelOrder as RequestObj;
use Ipol\Catapulto\Catapulto\Entity\CancelOrderResult as ResultObj;

class RequestCancelOrder extends AutomatedCommonRequest
{
    /**
     * @var string
     */
    protected $key;

    /**
     * RequestCompanyIcon constructor.
     * @param ResultObj $resultObj
     */
    public function __construct(ResultObj $resultObj, string $key)
    {
        parent::__construct($resultObj);
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return RequestCancelOrder
     */
    public function setKey(string $key): RequestCancelOrder
    {
        $this->key = $key;
        return $this;
    }

    /**
     * @return string
     */
    public function getSelfHash(): string
    {
        $keyString = serialize($this->key);
        return md5($keyString);
    }

    public function convert()
    {
        $data = new RequestObj();

        if ($this->key)
            $data->setKey($this->key);

        $this->setRequestObj($data);

        return $this;
    }

}