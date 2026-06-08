<?php


namespace Ipol\Catapulto\Core\Delivery;


use Ipol\Catapulto\Core\Entity\Money;

/**
 * Class Tariff
 * @package Ipol\Catapulto\Core
 * @subpackage Delivery
 * Basic delivery service tariff: date/term, alsa tariff id and it's variation
 */
class Tariff
{
    /**
     * @var //TODO Mebiys
     */
    protected $id;
    /**
     * @var //TODO Mebiys
     * special definitions for tariff's id
     */
    protected $variant;
    /**
     * @var Money
     */
    protected $price;
    /**
     * @var int
     * days to deliver
     */
    protected $term;
    /**
     * @var //TODO Mebiys
     * data when the goods will be delivered
     */
    protected $data;
    /**
     * @var //TODO Mebiys
     */
    protected $details;
    /**
     * @var //TODO Mebiys
     */
    protected $error;
    /**
     * @var string
     */
    protected $errorText;

    /**
     * Tariff constructor.
     * @param $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
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
     * @return string
     */
    public function getErrorText()
    {
        return $this->errorText;
    }

    /**
     * @param string $errorText
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getVariant()
    {
        return $this->variant;
    }

    /**
     * @param mixed $variant
     * @return $this
     */
    public function setVariant($variant)
    {
        $this->variant = $variant;

        return $this;
    }

    /**
     * @return Money|null
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param Money $price
     * @return $this
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * @param mixed $term
     * @return $this
     */
    public function setTerm($term)
    {
        $this->term = $term;

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
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param mixed $details
     * @return $this
     */
    public function setDetails($details)
    {
        $this->details = $details;

        return $this;
    }

}