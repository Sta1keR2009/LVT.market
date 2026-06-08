<?php

namespace Ipol\Catapulto\Bitrix\Controller;

use Ipol\Catapulto\Bitrix\Entity\BasicResponse;
use Ipol\Catapulto\Bitrix\Entity\ErrorCollector;
use Ipol\Catapulto\Core\Entity\Money;
use Ipol\Catapulto\Core\Order\Address;
use Ipol\Catapulto\Core\Order\Order as CoreOrder;
use Ipol\Catapulto\Option;

class Order extends AbstractController
{
    
    /**
     * @var \Ipol\Catapulto\Core\Order\Order
     */
    protected $order;
    protected $result;
    
    public function __construct($order = false)
    {
        parent::__construct(CATAPULTO_DELIVERY, CATAPULTO_DELIVERY_LBL);
        
        if ($order) {
            $this->order = $order;
        }
    }
    
    /**
     * @return \Ipol\Catapulto\Bitrix\Entity\BasicResponse
     */
    public function send()
    {
        $obReturn = new BasicResponse();
        
        $this->convertPayment();
        
        $cOrder = $this->convertOrder();
        
        // наложенный платеж
        /*if (!$this->order->getPayment()->getIsBeznal()) {
            $obNpResponse = $this->application->shipmentNpCreate($this->order);
        }*/
        
        $obResponse = $this->application->shipmentCreate($cOrder);
        
        if ($obResponse && $obResponse->isSuccess()) {
            $obReturn->setSuccess(true)->setData($obResponse);
        } else {
            return new ErrorCollector($obResponse);
        }
        
        return $obReturn;
    }
    
    public function delete()
    {/*
        $obReturn = new BasicResponse();

        try {
            ob_start();
            $obResponse = $this->application->cancelOrderByNumber($this->order->getNumber());
            ob_get_clean();
            if($obResponse && $obResponse->isSuccess()){
                $obResponse->setSuccess(true);
            } else {
                $obReturn->setSuccess(false)
                    ->setErrorText($this->application->getLastError());
            }
        }catch(\Exception $e){
            $obReturn->setSuccess(false)
                ->setErrorText($e->getMessage());
        }

        return $obReturn;*/
    }
    
    public function getResult()
    {
        return $this->result;
    }
    
    public function setResult($result)
    {
        $this->result = $result;
    }
    
    /**
     * @return \Ipol\Catapulto\Core\Order\Order
     */
    public function getOrder()
    {
        return $this->order;
    }
    
    /**
     * @param mixed $order
     *
     * @return $this
     */
    public function setOrder(\Ipol\Catapulto\Core\Order\Order $order)
    {
        $this->order = $order;
        
        return $this;
    }
    
    protected function convertPayment()
    {
        if ($this->order->getPayment()->getIsBeznal()) {
            $prePayment = Money::sum($this->order->getPayment()->getGoods(), $this->order->getPayment()->getDelivery());
            $this->order->getPayment()->setPayed($prePayment);
        }
    }
    
    private function convertOrder()
    {
        $cOrder = new CoreOrder();
        
        $sender = new Address();
        $sender->setCode((int)$this->order->getSender()->getField('senderId'));
        
        $receiver = new Address();
        $receiver->setCode((int)$this->order->getBuyers()->getFirst()->getField('receiverId'));
        
        // костыль с заменой формата даты, т.к. катапульта отдает везде дату по разному
        $pickupDay = explode('-', $this->order->getField('pickupDay'));
        $pickupDay = implode('.', array_reverse($pickupDay));
        
        $deliveryDay = explode('-', $this->order->getField('deliveryDay'));
        $deliveryDay = implode('.', array_reverse($deliveryDay));
        
        $cOrder->setAddressFrom($sender)
            ->setAddressTo($receiver)
            ->setNumber((int)$this->order->getField('orderId')) // cms order id
            ->setField('rate_id', (int)$this->order->getBuyers()->getFirst()->getField('rateResultId'))
            ->setField('pickup_day', $pickupDay)
            ->setField('delivery_day', $deliveryDay)
            ->setField('need_insurance', $this->order->getField('needInsurance')) // страховка
            ->setField('insurance_value', ($this->order->getField('needInsurance') ? $this->order->getPayment()->getField('insuranceValue') : new Money(0))) // страховая стоимость
        ;
        
        // Если не заданы доп.услуги - передаем пустой массив
        if (!$this->order->getField('additional_services')) {
            $cOrder->setField('additional_services', []); // дополнительные услуги, если нужны
        }
        else {
            $cOrder->setField('additional_services', $this->order->getField('additional_services'));
        }
        
        $cOrder->setField('receiver_terminal_code', $this->order->getField('receiver_terminal_code') ?? '');
        $cOrder->setField('sender_terminal_code', $this->order->getField('sender_terminal_code') ?? '');
        
        $operator    = $this->order->getField('operator');
        $arWarehouse = $this->order->getField('rateResult')['warehouse'] ?? [];
        
        if ($operator === 'dellin') {
            if ($arWarehouse['POA_ENABLED'] === 'Y') {
                $cOrder->setField('sender_poa', true)
                    ->setField('from_name', $arWarehouse['FIO'] ?? '')
                    ->setField('from_passport_series', $arWarehouse['PASSPORT_SERIA'] ?? '')
                    ->setField('from_passport_number', $arWarehouse['PASSPORT_NUMBER'] ?? '')
                    ->setField('from_passport_date', $arWarehouse['PASSPORT_DATE'] ?? '')
                    ->setField('send_receiver_poa_email', $arWarehouse['EMAIL'] ?? '');
            }
        }
        
        return $cOrder;
        
    }
}