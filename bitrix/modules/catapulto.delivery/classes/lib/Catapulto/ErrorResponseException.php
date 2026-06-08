<?php


namespace Ipol\Catapulto\Catapulto;


use Exception;
use Ipol\Catapulto\Api\Entity\Response\ErrorResponse;

class ErrorResponseException extends Exception
{
    /**
     * ErrorResponseException constructor.
     * @param ErrorResponse $errorResponse
     */
    public function __construct(ErrorResponse $errorResponse)
    {
        $message = is_array($errorResponse->getMessage()) ? json_encode($errorResponse->getMessage(), JSON_UNESCAPED_UNICODE) : $errorResponse->getMessage();
        parent::__construct($message . $errorResponse->getError(), $errorResponse->getErrorCode());
    }
}