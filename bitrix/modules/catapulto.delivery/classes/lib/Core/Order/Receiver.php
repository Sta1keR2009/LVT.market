<?php
namespace Ipol\Catapulto\Core\Order;


/**
 * Class Receiver
 * @package Ipol\Catapulto\Core
 * @subpackage Order
 */
class Receiver extends AbstractPerson
{
    /**
     * @var string
     */
    protected $timeCall;
    /**
     * @var string
     */
    protected $additionalPhone;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return mixed
     */
    public function getTimeCall()
    {
        return $this->timeCall;
    }

    /**
     * @param mixed $timeCall
     * @return $this
     */
    public function setTimeCall($timeCall)
    {
        $this->timeCall = $timeCall;

        return $this;
    }


    /**
     * @return null|string
     */
    public function getAdditionalPhone()
    {
        return $this->additionalPhone;
    }

    /**
     * @param string $additionalPhone
     * @return $this
     */
    public function setAdditionalPhone($additionalPhone)
    {
        $this->additionalPhone = $additionalPhone;

        return $this;
    }
}