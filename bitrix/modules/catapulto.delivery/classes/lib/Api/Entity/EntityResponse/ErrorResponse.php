<?php


namespace Ipol\Catapulto\Api\Entity\Response;


use Ipol\Catapulto\Api\ApiLevelException;
use Ipol\Catapulto\Api\BadResponseException;
use Ipol\Catapulto\Api\Entity\EncoderInterface;

/**
 * Class ErrorResponse
 * @package Ipol\Catapulto\Api\Entity\Response
 */
class ErrorResponse extends AbstractResponse
{
    /**
     * @var array|null
     */
    protected $message;
    /**
     * @var string
     */
    protected $errorCode;
    /**
     * @var array|null
     */

    /**
     * @var EncoderInterface|null
     */
    protected $encoder;
    
    protected string $status = 'error';
    

    /**
     * ErrorResponse constructor.
     * @param ApiLevelException $apiException
     * @throws BadResponseException
     */
    public function __construct(ApiLevelException $apiException, $encoder = null)
    {
        $this->encoder = $encoder;
        $this->errorCode = $apiException->getCode();
        $this->setMessage($apiException->getAnswer());
        
        //todo Абстрактный базовый класс имеет логику, отличную от данного конструктора. Зачем так сделано?
        parent::__construct($apiException->getAnswer());
    }

    /**
     * @return array|string|null
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param array|string $message
     * @return ErrorResponse
     */
    public function setMessage($message): ErrorResponse
    {
        if ($this->encoder) $this->message = $this->encoder->encodeFromAPI($message);
        else $this->message = $message;
        return $this;
    }

    /**
     * @return string|int|null
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @param string|int $errorCode
     * @return ErrorResponse
     */
    public function setErrorCode($errorCode): ErrorResponse
    {
        $this->errorCode = $errorCode;
        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

}