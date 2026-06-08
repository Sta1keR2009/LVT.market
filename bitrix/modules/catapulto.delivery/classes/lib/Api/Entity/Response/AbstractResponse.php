<?php

namespace Ipol\Catapulto\Api\Entity\Response;

use Ipol\Catapulto\Api\BadResponseException;
use Ipol\Catapulto\Api\Entity\AbstractEntity;

/**
 * Class AbstractResponse
 * @package Ipol\Catapulto\Api
 * @subpackage Entity
 */
class AbstractResponse extends AbstractEntity
{
    /**
     * @var string
     */
    protected $origin;

    /**
     * @var mixed
     */
    protected $decoded;
    /**
     * @var null|string
     */
    protected $error;
    /**
     * @var bool
     */
    protected $Success;

    /**
     * AbstractResponse constructor.
     * @param $json
     * @throws BadResponseException
     */
    function __construct($json)
    {
        parent::__construct();

        $this->origin = $json;

        if (empty($json)) {
            throw new BadResponseException('Empty server answer ' . __CLASS__);
        }

        $this->setDecoded(json_decode($json));

        if (is_null($this->decoded)) {
            throw new BadResponseException('Incorrect server answer (fail to decode) ' . __CLASS__);
        }
    }

    /**
     * @return mixed
     */
    public function getDecoded()
    {
        return $this->decoded;
    }

    /**
     * @param mixed $decoded
     * @return $this
     */
    public function setDecoded($decoded)
    {
        $this->decoded = $decoded;
        return $this;
    }

    /**
     * @return bool
     */
    public function getSuccess(): bool
    {
        return $this->Success;
    }

    /**
     * @param bool $Success
     * @return $this
     */
    public function setSuccess(bool $Success)
    {
        $this->Success = $Success;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @param mixed $origin
     * @return AbstractResponse
     */
    public function setOrigin($origin): AbstractResponse
    {
        $this->origin = $origin;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * @param string|null $error
     * @return AbstractResponse
     */
    public function setError(?string $error): AbstractResponse
    {
        $this->error = $error;
        return $this;
    }

}