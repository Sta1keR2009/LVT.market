<?php


namespace Ipol\Catapulto\Api\Methods;


use Ipol\Catapulto\Api\Adapter\CurlAdapter;
use Ipol\Catapulto\Api\ApiLevelException;
use Ipol\Catapulto\Api\BadResponseException;
use Ipol\Catapulto\Api\Entity\EncoderInterface;
use Ipol\Catapulto\Api\Entity\Request\AbstractRequest;
use Ipol\Catapulto\Api\Entity\Response\AbstractResponse;
use Ipol\Catapulto\Api\Entity\Response\ErrorResponse;

/**
 * Class GeneralMethod
 * @package Ipol\Catapulto\Api
 * @subpakage Methods
 * @method AbstractResponse|mixed|ErrorResponse getResponse
 */
class GeneralMethod extends AbstractMethod
{
    /**
     * GeneralMethod constructor.
     * @param AbstractRequest|mixed|null $data
     * @param CurlAdapter $adapter
     * @param string $responseClass
     * @param EncoderInterface|mixed|null $encoder
     * @throws BadResponseException
     */
    public function __construct($data, CurlAdapter $adapter, string $responseClass, $encoder = null)
    {
        parent::__construct($adapter, $encoder);

        if(!is_null($data)) {
            $this->setData($this->getEntityFields($data));
        }

        try {
            $jsonServerAnswer = $this->request();
            $response = new $responseClass($jsonServerAnswer);
            $response->setSuccess(true);
        } catch (ApiLevelException $e) {
            $response = new ErrorResponse($e, $encoder);
            $response->setSuccess(false);
        }
        $this->setResponse($this->reEncodeResponse($response));
        $this->setFields();
    }

}