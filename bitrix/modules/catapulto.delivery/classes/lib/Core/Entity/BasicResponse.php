<?php

namespace Ipol\Catapulto\Core\Entity;

use Exception;
use Ipol\Catapulto\Api\Entity\Response\AbstractResponse;

/**
 * Class BasicResponse
 * @package Ipol\Catapulto\Core
 * @subpackage Entity
 * Helper-object for processing basic server-responses such as pdf-printing etc.
 */
class BasicResponse extends BasicEntity
{
    /**
     * @var bool
     * Was request successfully finished?
     */
    protected $success;
    /**
     * @var mixed
     * Stores API-answer
     */
    protected $response;
    /**
     * @var false|Exception
     * Exception object
     */
    protected $error;
    /**
     * @var int
     * Stores error code
     */
    protected $code;

    public function __construct()
    {
        $this->success  = false;
        $this->error    = false;
        $this->response = false;
        $this->fields   = array();
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }

    /**
     * @param bool $success
     * @return $this
     */
    public function setSuccess(bool $success)
    {
        $this->success = $success;

        return $this;
    }

    /**
     * @return mixed|AbstractResponse
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param mixed|AbstractResponse $response
     * @return $this
     */
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * @return false|Exception
     */
    public function getError()
    {
        return $this->error;
    }

    public function isError(): bool
    {
        return (!$this->isSuccess() && $this->getError());
    }

    /**
     * @param mixed $error
     * @return $this
     */
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param int $code
     * @return $this
     */
    public function setCode(int $code)
    {
        $this->code = $code;

        return $this;
    }
}