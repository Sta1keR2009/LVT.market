<?php

namespace Ipol\Catapulto\Bitrix\Adapter;


use Ipol\Catapulto\Bitrix\Adapter;
use Ipol\Catapulto\Bitrix\Entity\Options;
use Ipol\Catapulto\Core\Order\Goods;

class OrderGoods
{
    protected $coreGoods;
    protected $options;

    public function __construct(Options $options)
    {
        $this->options   = $options;
        $this->coreGoods = new Goods();
        return $this;
    }

    public function fromOrder($bitrixId)
    {
        $goods = \Ipol\Catapulto\Bitrix\Handler\GoodsPicker::fromOrder($bitrixId);

        $goods = Adapter::getCargo($goods);

        $gabs = $goods->getCargo()->getGabs();
        $this->getCoreGoods()->setWeight($gabs['W'])
            ->setVolume($gabs['V'])
            ->setLength($gabs['G']['L'])
            ->setWidth($gabs['G']['W'])
            ->setHeight($gabs['G']['H'])
            ->setPositions(1);

        return $this;
    }

//    public function setGoods(Goods $obGoods){
//        $this->coreGoods = $obGoods;
//    }

    public function fromArray($array)
    {
        $this->getCoreGoods()->setWeight($array['weight'])
            ->setLength($array['length'])
            ->setWidth($array['width'])
            ->setHeight($array['height'])
            ->setPositions($array['positions']);
        return $this;
    }

    /**
     * @return Goods
     */
    public function getCoreGoods()
    {
        return $this->coreGoods;
    }
}