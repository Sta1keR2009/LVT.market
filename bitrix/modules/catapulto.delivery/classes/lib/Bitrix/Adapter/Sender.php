<?php

namespace Ipol\Catapulto\Bitrix\Adapter;


use Ipol\Catapulto\Bitrix\Entity\Options;

class Sender
{
    protected $coreSender;
    protected $options;

    public function __construct(Options $options)
    {
        $this->coreSender = new \Ipol\Catapulto\Core\Order\Sender();
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

//        $senderContactIdProp = CATAPULTO_DELIVERY_LBL . 'SENDER_CONTACT_ID';
//        $arProps = $order->getPropertyCollection ()->getArray();
//
//        foreach($arProps['properties'] as $property)
//        {
//            if ($property['CODE'] == $senderContactIdProp && $value = array_pop($property['VALUE']))
//            {
//                $this->getCoreSender()->setField('senderId',$value);
//            }
//        }
        
        //$this->getCoreSender()->setField('senderId', Options::fetchOption('senderId'));
        //$this->getCoreSender()->setField('senderCity',$this->options->fetchSenderCity());

    }

    public function fromArray($array)
    {
        $arPossFields = array('firstName','email','phone');
        foreach($array as $key => $value){
            if(in_array($key,$arPossFields)) {
                $action = 'set' . ucfirst($key);
                $this->getCoreSender()->$action($value);
            } else {
                $this->getCoreSender()->setField($key,$value);
            }
        }
        return $this;
    }

    /**
     * @return \Ipol\Catapulto\Core\Order\Sender
     */
    public function getCoreSender()
    {
        return $this->coreSender;
    }
}