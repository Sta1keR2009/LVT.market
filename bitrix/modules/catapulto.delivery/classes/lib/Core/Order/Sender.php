<?php


namespace Ipol\Catapulto\Core\Order;


/**
 * Class Sender
 * @package Ipol\Catapulto\Core
 * @subpackage Order
 */
class Sender extends AbstractPerson
{
    /**
     * @var mixed
     */
    protected $company;

    /**
     * Sender constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return mixed
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param mixed $company
     * @return $this
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }
}