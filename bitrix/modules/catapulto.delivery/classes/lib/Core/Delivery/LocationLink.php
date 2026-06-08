<?php


namespace Ipol\Catapulto\Core\Delivery;


/**
 * Class LocationLink
 * @package Ipol\Catapulto\Core
 * @subpackage Delivery
 * Link for locations from CMS and API
 */
class LocationLink
{
    /**
     * @var bool|Location
     */
    protected $cms = false;
    /**
     * @var bool|Location
     */
    protected $api = false;

    /**
     * @return bool
     */
    public function ready()
    {
        return ($this->getCms() && $this->getApi());
    }

    /**
     * @return false|Location
     */
    public function getCms()
    {
        return $this->cms;
    }

    /**
     * @param Location $cms
     * @return $this
     */
    public function setCms(Location $cms)
    {
        $this->cms = $cms;

        return $this;
    }

    /**
     * @return false|Location
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * @param Location $api
     * @return $this
     */
    public function setApi(Location $api)
    {
        $this->api = $api;

        return $this;
    }

}