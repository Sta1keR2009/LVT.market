<?php


namespace Ipol\Catapulto\Core\Order;


use Ipol\Catapulto\Core\Entity\FieldsContainer;

/**
 * Class AbstractPerson
 * @package Ipol\Catapulto\Core
 * @subpackage Order
 */
abstract class AbstractPerson
{
    use FieldsContainer;

    /**
     * @var null|string
     */
    protected $firstName;
    /**
     * @var null|string
     */
    protected $secondName;
    /**
     * @var null|string
     */
    protected $patronymic;
    /**
     * @var null|string
     */
    protected $email;
    /**
     * @var null|string
     */
    protected $phone;

    /**
     * AbstractPerson constructor.
     */
    public function __construct()
    {
    }

    /**
     * Return mix of First/Second/Patronymic names
     * @return string
     */
    public function getFullName()
    {
        return trim(implode(' ', [$this->firstName, $this->secondName, $this->patronymic]));
    }

    /**
     * @return null|string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     * @return $this
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getSecondName()
    {
        return $this->secondName;
    }

    /**
     * @param string $secondName
     * @return $this
     */
    public function setSecondName($secondName)
    {
        $this->secondName = $secondName;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPatronymic()
    {
        return $this->patronymic;
    }

    /**
     * @param string $patronymic
     * @return $this
     */
    public function setPatronymic($patronymic)
    {
        $this->patronymic = $patronymic;

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setFullName($name)
    {
        $name = explode(' ', $name);
        $this->setFirstName($name[0])
             ->setSecondName($name[1]);
        if(count($name) > 2)
        {
            $this->setPatronymic(trim(implode(' ', array_slice($name,2))));
        }

        return $this;
    }

    /**
     * @return null|string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     * @return $this
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

}