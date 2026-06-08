<?php


namespace Ipol\Catapulto\Catapulto\Controller;


use Error;
use Ipol\Catapulto\Api\BadResponseException;
use Ipol\Catapulto\Api\Entity\Response\ErrorResponse;
use Ipol\Catapulto\Catapulto\AppLevelException;
use Ipol\Catapulto\Catapulto\Entity\AbstractResult;
use Ipol\Catapulto\Catapulto\ErrorResponseException;
use ReflectionClass;

class AutomatedCommonRequest extends RequestController
{
    /**
     * @var AbstractResult|mixed
     */
    protected $resultObject;

    /**
     * BasicController constructor.
     * @param AbstractResult|mixed $resultObj
     */
    public function __construct($resultObj)
    {
        $this->resultObject = $resultObj;
    }

    /**
     * @return $this|mixed
     */
    public function convert()
    {
        return $this;
    }

    /**
     * @return AbstractResult|mixed
     */
    public function execute()
    {
        $result = $this->getResultObject();

        try {
            if ($this->getRequestObj()) {
                $requestProcess = $this->getSdk()->{$this->getSdkMethodName()}($this->getRequestObj());
            } else {
                $requestProcess = $this->getSdk()->{$this->getSdkMethodName()}();
            }

            $result->setSuccess($requestProcess->getResponse()->getSuccess())
                ->setResponse($requestProcess->getResponse());
            if ($result->isSuccess()) {
                $result->parseFields();
            } elseif (is_a($requestProcess->getResponse(), ErrorResponse::class)) {
                $result->setError(new ErrorResponseException($requestProcess->getResponse()));
            }
        } catch (BadResponseException | AppLevelException $e) {
            $result->setSuccess(false)
                ->setError($e);
        } finally {
            return $result;
        }
    }

    /**
     * @return AbstractResult|mixed
     */
    public function getResultObject()
    {
        return $this->resultObject;
    }

    /**
     * @param AbstractResult|mixed $resultObject
     * @return $this|mixed - mixed for child-classes
     */
    public function setResultObject($resultObject)
    {
        $this->resultObject = $resultObject;
        return $this;
    }

    public function getSelfHash(): string
    {
        $extended = new ReflectionClass(get_class($this)); //real running classname - extension-class

        if ($extended->getMethod('convert')->getDeclaringClass()->name === get_class($this) &&
            get_class($this) !== __CLASS__) {
            throw new Error('Default getSelfHash() method is not suitable for converted requests. Declare custom method in extended class.');
        }
        return md5($this->getSelfHashByRequestObj());
    }

    protected function getSelfHashByRequestObj(): string
    {
        if (!is_null($this->getRequestObj())) {
            $resString = get_class($this->getRequestObj());
            $resString .= serialize($this->getRequestObj()->getFields());
        } else {
            $resString = get_class($this->getResultObject());
        }

        return $resString;
    }

}