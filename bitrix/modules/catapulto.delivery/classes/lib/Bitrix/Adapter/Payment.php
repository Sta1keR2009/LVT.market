<?php

namespace Ipol\Catapulto\Bitrix\Adapter;


use Bitrix\Sale\Shipment;
use Ipol\Catapulto\Bitrix\Adapter;
use Ipol\Catapulto\Bitrix\Entity\Options;
use Ipol\Catapulto\Bitrix\Handler\Deliveries;
use Ipol\Catapulto\Core\Entity\Money;

class Payment
{
    protected $corePayment;
    protected $options;

    public function __construct(Options $options)
    {
        $this->corePayment = new \Ipol\Catapulto\Core\Order\Payment();
        $this->options     = $options;
    }

    public function fromOrder($bitrixId)
    {
        if (!\CModule::includeModule('sale')) {
            throw new \Exception('No sale-module');
        }

        $order = \CSaleOrder::getByID($bitrixId);

        $obMoneyDelivery  = new Money($order['PRICE_DELIVERY']);
        $obMoneyGoods     = new Money($order['PRICE'] - $order['PRICE_DELIVERY']);
        $obMoneyEstimated = new Money($order['PRICE'] - $order['PRICE_DELIVERY']);
        $obMoneyPayed     = new Money($order['SUM_PAID']);
        $obSumToPay       = new Money($order['PRICE']);

        $this->getCorePayment()->setDelivery($obMoneyDelivery)
            ->setGoods($obMoneyGoods)
            ->setPayed($obMoneyPayed)
            ->setEstimated($obMoneyEstimated)
            ->setField('insuranceValue',$obMoneyGoods)
            ->setField('sumToPay', $obSumToPay);

        $nOrder    = \Ipol\Catapulto\Bitrix\Handler\Order::getOrderById($bitrixId);
        $payNal    = false;
        $payCard   = false;
        $allPayed  = false;
        if($nOrder && is_object($nOrder)) {
            $paymentCollection = $nOrder->getPaymentCollection();
            /** @var \Bitrix\Sale\Payment $payment */
            foreach ($paymentCollection as $payment) {
                if(!$payNal && is_array($this->options->fetchPayNal())){
                    $payNal = (in_array($payment->getPaymentSystemId(), $this->options->fetchPayNal()));
                }
                if(!$payCard && is_array($this->options->fetchPayCard())){
                    $payCard = (in_array($payment->getPaymentSystemId(), $this->options->fetchPayCard()));
                }
                if(!$allPayed){
                    $allPayed = $payment->isPaid();
                }
            }
        } else {
            $payNal  = (is_array($this->options->fetchPayNal()) && in_array($order['PAY_SYSTEM_ID'], $this->options->fetchPayNal()));
            $payCard = (is_array($this->options->fetchPayCard()) && in_array($order['PAY_SYSTEM_ID'], $this->options->fetchPayCard()));
        }

        if($payNal){
            $this->getCorePayment()->setType('Cash');
        } elseif($payCard){
            $this->getCorePayment()->setType('Card');
        } else {
            $this->getCorePayment()->setType('Bill');
        }

        // either payed in Bitrix or not card/nal && checking option "checkPayed"
        if (
            $order['SUM_PAID'] == $order['PRICE'] ||
            $order['PAYED'] == 'Y' ||
            (
                !$payNal  &&
                !$payCard &&
                (
                    $this->options->fetchCheckPayed() !== 'Y' ||
                    $allPayed
                )
            )
        ) {
            $this->getCorePayment()->setIsBeznal(true);
            $this->getCorePayment()->setIsNp(false);
            $this->getCorePayment()->setIsCod(false);
        } else {
            $this->getCorePayment()->setIsBeznal(false);
            $this->getCorePayment()->setIsNp(true);
            $this->getCorePayment()->setIsCod(true);
        }

        $this->getCorePayment()->setNdsDefault($this->options->fetchNdsDefault());
        
        $shipmentCollection = $nOrder->getShipmentCollection();
        $shipmentCollection->rewind();
        $setted = false;
        /** @var Shipment $obShipment */
        while($obShipment = $shipmentCollection->next()){
            if ($obShipment->isSystem() && $setted) {
                continue; // TODO: WTF?!
            }

            $setted = Deliveries::getVatRateByDeliveryId($obShipment->getField('DELIVERY_ID'));
        }
        $shipmentCollection->rewind();

        $this->getCorePayment()->setNdsDelivery(($setted)?$setted:0);// TODO: checkNDSPelivery
        $this->getCorePayment()->setIsSmsAmount($this->options->fetchSmsAmount() === 'Y');
    }

    public function fromArray($array)
    {
        $obMoneyDelivery  = new Money(($array['delivery'])?$array['delivery']:0);
        $obMoneyGoods     = new Money(($array['goods'])?$array['goods']:0);
        $obMoneyEstimated = new Money(($array['estimated'])?$array['estimated']:0);
        $obMoneyPayed     = new Money(($array['payed'])?$array['payed']:0);
        $obInsuranceValue = new Money($array['insuranceValue'] ?? 0);
        $obSumToPay = new Money($array['sumToPay'] ?? 0);

        //$type = Adapter::convertPaymentTypes($array['type'],true); TODO: check
        $this->getCorePayment()
            ->setIsBeznal((array_key_exists('isBeznal', $array) && $array['isBeznal'] && $array['isBeznal'] !== 'N'))
            ->setIsNp((array_key_exists('isNp', $array) && $array['isNp'] && $array['isNp'] !== 'N'))
            ->setIsCod((array_key_exists('isCod', $array) && $array['isCod'] && $array['isCod'] !== 'N'))
            ->setIsSmsAmount((array_key_exists('isSmsAmount', $array) && $array['isSmsAmount'] && $array['isSmsAmount'] !== 'N'))
            ->setGoods($obMoneyGoods)
            ->setEstimated($obMoneyEstimated)
            ->setDelivery($obMoneyDelivery)
            ->setPayed($obMoneyPayed)
            ->setNdsDelivery($array['ndsDelivery'])
            ->setField('insuranceValue',$obInsuranceValue)
            ->setField('sumToPay', $obSumToPay);
        ;

        return $this;
    }

    /**
     * @return \Ipol\Catapulto\Core\Order\Payment
     */
    public function getCorePayment()
    {
        return $this->corePayment;
    }
}