<?php

namespace Ipol\Catapulto\Bitrix\Adapter;


use Ipol\Catapulto\Bitrix\Entity\Options;

class Address
{
    protected $coreAddress;
    protected $options;

    public function __construct(Options $options)
    {
        $this->coreAddress = new \Ipol\Catapulto\Core\Order\Address();
        $this->options      = $options;
    }

    public function fromArray($array)
    {
        if (!$array) return $this;
        $arSetters = array('zip','country','region','city','street', 'building','flat','line','comment','code');
        foreach($arSetters as $part)
        {
            if(array_key_exists($part,$array))
            {
                $method = 'set'.ucfirst($part);
                $this->getCoreAddress()->$method($array[$part]);
            }
        }

        return $this;
    }

    /**
     * @return \Ipol\Catapulto\Core\Order\Address
     */
    public function getCoreAddress()
    {
        return $this->coreAddress;
    }
}