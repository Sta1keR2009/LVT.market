<?php

namespace Ipol\Catapulto\Bitrix\Adapter;


use Ipol\Catapulto\Bitrix\Entity\Options;
use Ipol\Catapulto\Core\Order\BuyerCollection;
use Ipol\Catapulto\OrderPropsTable;

class Buyer
{
    protected $coreBuyer;
    protected $options;

    public function __construct(Options $options)
    {
        $this->coreBuyer = new \Ipol\Catapulto\Core\Order\Buyer();
        $this->options      = $options;
    }

    public function fromOrder($bId)
    {
        if(!\CModule::includeModule('sale'))
        {
            throw new \Exception('No sale-module');
        }

        $order = \Ipol\Catapulto\Bitrix\Handler\Order::getOrderById($bId);
        if(!$order)
        {
            throw new \Exception('Order '.$bId.' not found');
        }

        $arConnector = array();
        foreach(array('firstName','email','phone', 'company') as $code)
        {
            $arConnector[$this->options->fetchOption($code)] = $code;
            $method = 'set'.ucfirst($code);
            if (method_exists($this->getCoreBuyer(), $method)) {
                $this->getCoreBuyer()->$method(false);
            } else {
                $this->getCoreBuyer()->setField($code, false);
            }
        }

        // $arProps = $order->loadPropertyCollection()->getArray();

        $arProps = $order->getPropertyCollection ()->getArray();

        foreach($arProps['properties'] as $property)
        {
            if(
                array_key_exists($property['CODE'],$arConnector) &&
                $arConnector[$property['CODE']]                  &&
                $value = array_pop($property['VALUE'])
            )
            {
                $method = 'set'.ucfirst($arConnector[$property['CODE']]);
                if($arConnector[$property['CODE']] === 'phone'){
                    $value = self::normalizePhone($value);
                }
                if (method_exists($this->getCoreBuyer(), $method)) {
                    $this->getCoreBuyer()->$method($value);
                } else {
                    $this->getCoreBuyer()->setField($arConnector[$property['CODE']], $value);
                }
            }

        }

        $orderProps = OrderPropsTable::getByBitrixId(intval($bId));
        if ($orderProps && $orderProps['RATE_RESULT_ID']) {
            $this->getCoreBuyer()->setField('rateResultId',$orderProps['RATE_RESULT_ID']);
        }

    }

    public function fromArray($array)
    {
        $arPossFields = array('firstName','email','phone');
        foreach($array as $key => $value){
            if(in_array($key,$arPossFields)) {
                $action = 'set' . ucfirst($key);
                $this->getCoreBuyer()->$action($value);
            } else {
                $this->getCoreBuyer()->setField($key,$value);
            }
        }
        return $this;
    }

    /**
     * @return \Ipol\Catapulto\Core\Order\Buyer
     */
    public function getCoreBuyer()
    {
        return $this->coreBuyer;
    }

    /**
     * @return BuyerCollection
     * makes buyer collection while making stuff to core
     */
    public function getBuyerCollection()
    {
        $collection = new BuyerCollection();
        $collection->add($this->coreBuyer);

        return $collection;
    }

    public static function normalizePhone($phone)
    {
        $phone = preg_replace("/[^0-9:#]/", "", $phone);

        $length = strlen($phone);
        if ($length > 10) {
            $switcher = '7';
            if (
                $switcher &&
                strpos($phone, $switcher) !== 0
                && strpos($phone, '8') === 0
            ) {
                $phone = $switcher.substr($phone, 1);
            }
            $phone = '+'.$phone;
        }
        else if ($length == 10)
        {
            $phone = '+7'.$phone;
        }

        return $phone;
    }
}