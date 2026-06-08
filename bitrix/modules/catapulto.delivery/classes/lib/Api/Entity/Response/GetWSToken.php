<?php

namespace Ipol\Catapulto\Api\Entity\Response;

class GetWSToken extends AbstractResponse
{

    /**
     * @var string
     */
    protected $wsToken;

    /**
     * @return string
     */
    public function getWsToken(): string
    {
        return $this->wsToken;
    }

    /**
     * @param array $wsToken
     * @return GetWSToken
     */
    public function setWsToken(array $wsToken): GetWSToken
    {
        $this->wsToken = $wsToken['ws_token'] ?? '';
        return $this;
    }


    public function setFields($fields)
    {
        return parent::setFields(['wsToken'=>$fields]);
    }


}
