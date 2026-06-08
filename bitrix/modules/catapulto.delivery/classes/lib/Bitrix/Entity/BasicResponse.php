<?php
namespace Ipol\Catapulto\Bitrix\Entity;


/**
 * Class BasicResponse
 * @package Ipol\Catapulto\Bitrix\Entity
 *
 * class for exchanging information, where returning scalar value is not enough - to avoid arrays
 */
class BasicResponse
{
    /**
     * @var bool T - OK, F - trouble
     */
    protected $success = true;
    /**
     * @var bool if T - success is ok, but have notifications in errorText
     */
    protected $warning = false;
    /**
     * @var mixed - just for additional info
     */
    protected $data;
    /**
     * @var string - here lies text of error
     */
    protected $errorText;
    /**
     * @var int - here lies index of error for better notification
     */
    protected $errorCode;
    /**
     * @return mixed
     */
    public function isSuccess()
    {
        return $this->success;
    }
    /**
     * @param mixed $success
     * @return $this
     */
    public function setSuccess($success)
    {
        $this->success = $success;

        return $this;
    }
    /**
     * @return bool
     */
    public function isWarning()
    {
        return $this->warning;
    }
    /**
     * @param bool $warning
     * @return $this
     */
    public function setWarning($warning)
    {
        $this->warning = $warning;

        return $this;
    }
    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
    /**
     * @param mixed $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
    /**
     * @return mixed
     */
    public function getErrorText()
    {
        return $this->errorText;
    }
    /**
     * @param mixed $errorText
     * @return $this
     */
    public function setErrorText($errorText)
    {
        $this->errorText = $errorText;

        return $this;
    }
    /**
     * @return mixed
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }
    /**
     * @param mixed $errorCode
     * @return $this
     */
    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;

        return $this;
    }
}