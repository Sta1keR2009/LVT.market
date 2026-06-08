<?php


namespace Ipol\Catapulto\Core\Delivery;


use InvalidArgumentException;
use Ipol\Catapulto\Core\Entity\FieldsContainer;

/**
 * Class Location
 * @package Ipol\Catapulto\Core
 * @subpackage Delivery
 */
class Location
{
    use FieldsContainer;

    /**
     * @var string
     * Some unique id of location in system
     */
    protected $id;
    /**
     * @var string
     * Additional identifier
     */
    protected $code;
    /**
     * @var string
     *
     */
    protected $country;
    /**
     * @var string
     *
     */
    protected $region;
    /**
     * @var
     * Link to parent-element of location in system
     */
    protected $parent;
    /**
     * @var string
     *
     */
    protected $name;
    /**
     * @var string
     * post-code etc
     */
    protected $zip;
    /**
     * @var float
     * latitude
     */
    protected $lat;
    /**
     * @var float
     * longitude
     */
    protected $lng;

    /**
     * @var string
     * 'cms' - from cms, 'api' - from delivery
     */
    protected $link;

    /**
     * Location constructor.
     * @param $link
     */
    public function __construct($link)
    {
        if($link != 'cms' && $link != 'api')
        {
            throw new InvalidArgumentException('Illegal Link for location ("api" or "cms" is acceptable)');
        }
        $this->link = $link;
    }

    /**
     * @return null|string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @param mixed $zip
     * @return $this
     */
    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     * @return $this
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param mixed $region
     * @return $this
     */
    public function setRegion($region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param mixed $parent
     * @return $this
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return null|float
     */
    public function getLat(): ?float
    {
        return $this->lat;
    }

    /**
     * @param float $lat
     * @return Location
     */
    public function setLat(float $lat): Location
    {
        $this->lat = $lat;
        return $this;
    }

    /**
     * @return null|float
     */
    public function getLng(): ?float
    {
        return $this->lng;
    }

    /**
     * @param float $lng
     * @return Location
     */
    public function setLng(float $lng): Location
    {
        $this->lng = $lng;
        return $this;
    }
}