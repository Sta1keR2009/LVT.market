<?php

namespace Ipol\Catapulto\Bitrix\Adapter;


use Bitrix\Sale\Shipment;
use Ipol\Catapulto\AbstractGeneral;
use Ipol\Catapulto\Bitrix\Adapter;
use Ipol\Catapulto\Bitrix\Entity\Options;
use Ipol\Catapulto\Bitrix\Handler\Deliveries;
use Ipol\Catapulto\Bitrix\Tools;
use Ipol\Catapulto\Core\Delivery\CargoCollection;
use Ipol\Catapulto\Core\Delivery\CargoItem;
use Ipol\Catapulto\Core\Entity\Money;
use Ipol\Catapulto\Core\Order\ItemCollection;
use Ipol\Catapulto\OrderHandler;
use Ipol\Catapulto\OrderPropsHandler;
use Ipol\Catapulto\OrderPropsTable;
use Ipol\Catapulto\OrdersTable;
use Ipol\Catapulto\PostingHandler;

IncludeModuleLangFile(dirname(dirname(dirname(dirname(__DIR__)))) . '/include.php');

class Order
{
    protected $bitrixId;
    protected $orderNumber;
    /**
     * @var Options
     */
    protected $options;
    protected $baseOrder;

    /** @var Sender */
    protected $sender;
    /**
     * @var Receiver
     */
    protected $receiver;
    /**
     * @var AddressTo
     */

    protected $buyer;
    protected $addressTo;
    /*
     * $
     * */
    protected $addressFrom;
    protected $payment;
    protected $goods;
    /**
     * @var orderItems
     */
    protected $items;

    protected $moduleLbl;

    public function __construct(Options $options)
    {
        $this->options     = $options;
        $this->baseOrder   = (new \Ipol\Catapulto\Core\Order\Order())
            ->setField('forcePVZAddress','')
            ->setField('isPVZ','N');
        $this->sender      = new Sender($options);
        $this->receiver    = new Receiver($options);
        $this->buyer       = new Buyer($options);
        $this->addressFrom = new AddressFrom($options);
        $this->addressTo   = new AddressTo($options);
        $this->payment     = new Payment($options);
        $this->goods       = new OrderGoods($options);
        $this->items       = new OrderItems($options);

        $this->moduleLbl   = CATAPULTO_DELIVERY_LBL;

        return $this;
    }

    /**
     * @param $bitrixId
     * @return $this
     * Заполняет заказ данными по заказу в Битрисе и настройкам по умолчанию.
     */
    public function newOrder($bitrixId)
    {
        $this->bitrixId    = $bitrixId;
        $this->orderNumber = \Ipol\Catapulto\Bitrix\Handler\Order::getOrderNumber($bitrixId);

        $order = \Ipol\Catapulto\Bitrix\Handler\Order::getOrderById($this->bitrixId);
        $this->getBaseOrder()->setNumber($this->orderNumber);
        $this->getBaseOrder()->setField('orderId', $this->orderNumber);
        
        $orderDelivery = false;

        $deliveryType = 'courier';
        $orderProps = OrderPropsTable::getByBitrixId(intval($bitrixId));
        $this->getBaseOrder()->setField('cargoData', new CargoCollection());
        $customCargoData = false;
        $defaultCargoId = 0;
        if($order) {
            $shipmentCollection = $order->getShipmentCollection();
            $shipmentCollection->rewind();
            /** @var Shipment $obShipment */
            while($obShipment = $shipmentCollection->next()){
                if ($obShipment->isSystem()) {
//                    continue;
                }

                $delivery = \Ipol\Catapulto\Bitrix\Handler\Deliveries::defineDelivery($obShipment->getField('DELIVERY_ID'));
                $orderDelivery = $obShipment->getField('DELIVERY_ID');
                if (in_array($delivery,array('pickup','postamat')))
                {
                    $deliveryType = $delivery;
                }
            }
            $shipmentCollection->rewind();

            $arProps = $order->getPropertyCollection()->getArray();

            foreach ($arProps['properties'] as $property) {
                $value = array_pop($property['VALUE']);

                //For old versions compability - resave data into table
                if ($property['CODE'] == OrderPropsHandler::getRateProp() && $value) {
                    if (empty($orderProps['RATE_RESULT_ID'])) {
                        OrderPropsTable::saveProps(intval($bitrixId),['RATE_RESULT_ID'=>$value]);
                        $orderProps['RATE_RESULT_ID'] = $value;
                    }
                }
                if ($property['CODE'] == OrderPropsHandler::getRateResultProp() && $value) {
                    if (empty($orderProps['RATE_RESULT'])) {
                        OrderPropsTable::saveProps(intval($bitrixId),['RATE_RESULT'=>$value]);
                        $orderProps['RATE_RESULT'] = $value;
                    }
                }

            }
            
            $this->getPayment()->fromOrder($bitrixId);

            if ($orderProps) {
                if ($orderProps['RATE_RESULT_ID']) {
                    $this->getBaseOrder()->setField('rateResultId', $orderProps['RATE_RESULT_ID']);
                }
                if ($orderProps['RATE_RESULT']) {
                    if ($arRate = @json_decode($orderProps['RATE_RESULT'],true)) {
                        $this->getBaseOrder()->setField('rateResult',$arRate);

                        // If is PVZ order - add terminal codes
                        if (!empty($arRate['terminal_code'])) {
                            $this->getBaseOrder()->setField('receiver_terminal_code',$arRate['terminal_code']);
                        }

                        // add operator field
                        if (!empty($arRate['operator'])) {
                            $this->getBaseOrder()->setField('operator',$arRate['operator']);
                            $this->getBaseOrder()->setField(
                                'sender_terminal_code', Adapter::getSenderTerminalCode($arRate['warehouse']['ID'], $arRate['operator'])
                            );
                        }
                        if (is_array($arRate['cargoes']) && count($arRate['cargoes'])>0) {
                            $defaultCargoId = $arRate['cargoes'][0];
                        }
                        
                        //is_cod
                        $this->getPayment()->getCorePayment()->setIsCod($arRate['was_cod'] ?? false);

                        //insurancePersent
                        $this->baseOrder->setField('insurance_cost', floatval($arRate['insurance_config'] ?? 0));

                        //basePrice....
                        $this->baseOrder->setField('baseDeliveryPrice', (float)($arRate['base_price'] ?? 0));
                        
                        //basePriceWithServices....
                        $this->baseOrder->setField('baseDeliveryPriceWithServices', (float)($arRate['base_price_with_services'] ?? 0));

                        //isFitting
                        $this->baseOrder->setField('isFittingInRate', $arRate['is_fitting'] ?? false);

                        //isPartialRedInRate
                        $this->baseOrder->setField('isPartialRedInRate', $arRate['with_partial_red'] ?? false);
                        
                        if (isset($arRate['cargoes']) && !empty($arRate['cargoes'])) {
                            $this->getBaseOrder()->setField('cargoes_ids', $arRate['cargoes']);
                        }
                        
                        $pickupDay = $arRate['pickup_date'] ?? $arRate['pickup_day'];
                        $deliveryDay = $arRate['delivery_date'] ?? $arRate['delivery_day'];
                        
                        $this->getBaseOrder()->setField('pickupDay', $pickupDay);
                        $this->getBaseOrder()->setField('deliveryDay', $deliveryDay);

                        if(!empty($arRate['warehouse'])) {
                            $this->getSender()->getCoreSender()->setField('senderId', $arRate['warehouse']['CATAPULTO_CONTACT_ID']);
                            $this->getSender()->getCoreSender()->setField('senderCityId', $arRate['warehouse']['CATAPULTO_CITY_ID']);
                            $this->getSender()->getCoreSender()->setField('senderCity', $arRate['warehouse']['BX_LOC_NAME']);
                            $this->getSender()->getCoreSender()->setField('warehouseId', $arRate['warehouse']['ID']);
                        }

                        $this->setOrderServicesData($arRate);

                    }
                }
                if ($orderProps['CUSTOM_GABS']) {
                    if ($gabs = @json_decode($orderProps['CUSTOM_GABS'],true)) {
                        $this->getBaseOrder()->setField('custom_gabs',$gabs);
                    }
                }
                
                if (!empty($orderProps['DADATA_ADDR'])) {
                    $dadata = json_decode($orderProps['DADATA_ADDR'], true);
                    if (!empty($dadata)) {
                        $this->getBaseOrder()->setField('dadata', $dadata);
                    }
                }
            }

            if ($orderProps['OTHER'] && ($otherPropsOther = @json_decode($orderProps['OTHER'],true))) {
                $this->getBaseOrder()->setField('saved_en_ensurance', $otherPropsOther['en_ensurance']);
                $this->getBaseOrder()->setField('isPVZ', $otherPropsOther['isPVZ'] ?? 'N');
                $this->getBaseOrder()->setField('forcePVZAddress', $otherPropsOther['forcePVZAddress'] ?? '');

                if (isset($otherPropsOther['customCargo'])) {
                    $customCargoData = @json_decode($otherPropsOther['customCargo'],true);
                    $this->getBaseOrder()->setField('customCargoData', $customCargoData);
                    if (!isset($customCargoData['cargoes'])) $customCargoData = false;
                }
            }

        }

        $this->getBaseOrder()->setStatus('NEW');
        $this->getReceiver()->fromOrder($bitrixId);
        $this->getBuyer()->fromOrder($bitrixId);
        $this->getAddressFrom()->fromDefaults($orderDelivery);
        
        $this->getGoods()->fromOrder($bitrixId);
        $this->getItems()->fromOrder($bitrixId);
        
        if (($this->getBaseOrder()->getField('isPVZ') === 'Y') && !empty($this->getBaseOrder()->getField('forcePVZAddress')) ) {
            $this->getAddressTo()->fromOrderWithAddressLine($bitrixId,$this->getBaseOrder()->getField('forcePVZAddress'));
            $this->addressTo->getCoreAddress()->setField('isDadataRewritten',true);
        }

        if (!($this->addressTo->getCoreAddress()->getField('pvz_address_filled') === true)) {
            $this->getAddressTo()->fromOrder($bitrixId,$deliveryType);
            $this->fillAddressFromDadataProp($orderProps, $arRate);
        }

        $this->setOrderCustomCargoData(
            $order,
            $customCargoData,
            $defaultCargoId
        );

        $this->compileOrder();

        $this->setDefaultFields();

        return $this;
    }

    private function fillAddressFromDadataProp($orderProps, $arRate)
    {
        if ($orderProps && $orderProps['DADATA_ADDR']) {
            if ($savedAddress = @json_decode($orderProps['DADATA_ADDR'],true)) {
                $this->addressTo->getCoreAddress()->setFlat(null) //clearing...
                    //->setBuilding(null)
                    ->setCity(null)
                    ->setZip(null)
                    //->setHouse(null)
                    //->setStreet(null)
                    ->setLine(null)
                    ->setRegion(null)
                    ->setCountry(null)
                    ->setCode(null)
                    ->setComment('')
                    ->setEntrance(null)
                    ->setFields([])
                    ->setFloor(null)
                    ->setIntercom(null)
                    //->setLat(0)
                    ->setLng(0);
                $this->addressTo->getCoreAddress()->setField('isDadata',false);
                $this->addressTo->getCoreAddress()->setField('isDadataRewritten',true);

                $strLine = '';
                $zip = null;
                if (isset($arRate) && is_array($arRate) && isset($arRate['receiver_zip'])) {
                    $arRate['receiver_zip'] = intval($arRate['receiver_zip']);
                    if ($arRate['receiver_zip'] > 0) $zip = $arRate['receiver_zip'];
                }
                if ($savedAddress['zip']) $zip = intval($savedAddress['zip']);
                $this->addressTo->getCoreAddress()->setZip($zip);

                if ($savedAddress['country']) $this->addressTo->getCoreAddress()->setCountry($savedAddress['country']);
                if ($savedAddress['region']) $this->addressTo->getCoreAddress()->setRegion($savedAddress['region']);
                if (!$savedAddress['city']) {
                    if ($savedAddress['settlement']) $this->addressTo->getCoreAddress()->setCity($savedAddress['settlement']);
                } else $this->addressTo->getCoreAddress()->setCity($savedAddress['city']);
                if ($savedAddress['street']) {
                    $this->addressTo->getCoreAddress()->setStreet($savedAddress['street']);
                    $strLine = $savedAddress['street'];
                }
                if ($savedAddress['house']) {
                    if ($savedAddress['houset']) $savedAddress['house'] = $savedAddress['houset'] . ' ' . $savedAddress['house'];
                    if (!empty($strLine)) $strLine.=', '.$savedAddress['house'];

                    if ($savedAddress['block']) {
                        if ($savedAddress['blockt']) $savedAddress['block'] = $savedAddress['blockt'] . ' ' . $savedAddress['block'];
                        if (!empty($strLine)) $strLine.=', '.$savedAddress['block'];
                        $savedAddress['house'] .= ', ' . $savedAddress['block'];
                    }

                    $this->addressTo->getCoreAddress()->setBuilding($savedAddress['house']);
                }
                if ($savedAddress['flat']) {
                    if ($savedAddress['flatt']) $savedAddress['flat'] = $savedAddress['flatt'] . ' ' . $savedAddress['flat'];
                    if (!empty($strLine)) $strLine.=', '.$savedAddress['flat'];
                    $this->addressTo->getCoreAddress()->setFlat($savedAddress['flat']);
                }

                if (!empty($strLine)) $this->addressTo->getCoreAddress()->setLine($strLine);

                if (!empty($savedAddress['value'])) $this->addressTo->getCoreAddress()->setField('dadataValue',$savedAddress['value']);
                if (!empty($savedAddress['unrestricted_value'])) $this->addressTo->getCoreAddress()->setField('dadata_unrestricted_value',$savedAddress['unrestricted_value']);
            }
        }
    }

    private function setOrderServicesData($arRate) {
        $this->baseOrder->setField('isFittingAvailable', false);
        $this->baseOrder->setField('isPRAvailable', false);
        $this->baseOrder->setField('isSmsAmount', false);
        foreach ($arRate['services'] as $service) {
            switch ($service['name']) {
                case AbstractGeneral::CTPT_SERVICE_FITTING:
                    $this->baseOrder->setField('isFittingAvailable', true);
                    break;
                case AbstractGeneral::CTPT_SERVICE_PR:
                    $this->baseOrder->setField('isPRAvailable', true);
                    break;
                case AbstractGeneral::CTPT_SERVICE_SMS:
                    $this->baseOrder->setField('isSmsAmount', true);
                    break;
            }
        }
    }

    private function setOrderCustomCargoData(
        $order,
        $customCargoData,
        $defaultCargoId
    )
    {
        $cargoes = new CargoCollection();
        $isSingleProduct = true;
        $this->goods = new OrderGoods($this->options);
        $this->items = new OrderItems($this->options);
        $this->goods->fromOrder($order->getId());
        $this->items->fromOrder($order->getId());
        if (!$customCargoData) {
            $cargo = new \Ipol\Catapulto\Core\Delivery\Cargo();
            $cargo->setName(Tools::getMessage('ORDER_GARGO') . ' 1');
            if ($defaultCargoId > 0) $cargo->setCargoId($defaultCargoId);
            $cargo->setId( $order->getId() . '_' . (new \DateTime())->getTimestamp() );
            $cargo->setOrd( $order->getId() );
            $this->items->getCoreItems()->getFirst();
            $arItems = [];
            while ($itm = $this->items->getCoreItems()->getNext()) {
                $cargoItem = (new CargoItem())
                    ->setPrice(new Money(floatval($itm->getPrice())))
                    ->setCost(new Money(floatval($itm->getCost())))
                    ->setHeight($itm->getHeight() ?? 0) //Defaul value ???
                    ->setLength($itm->getLength() ?? 0)
                    ->setWidth($itm->getWidth() ?? 0)
                    ->setWeight($itm->getWeight() ?? 0)
                    ->setQuantity($itm->getQuantity())
                    ->setName($itm->getName())
                    ->setId($itm->getId())
                    ->setVatRate($itm->getVatRate())
                    ->setArticul($itm->getArticul())
                ;
                if ($itm->getField('gbsdefault')) $cargoItem->setField('warn', 1);
                /*if ($cargoItem->ready())*/ $cargo->add($cargoItem);
                $arItems[] = [
                    'id' => $cargoItem->getId(),
                    'name' => $cargoItem->getName(),
                    'length' => $cargoItem->getLength(),
                    'width' => $cargoItem->getWidth(),
                    'height' => $cargoItem->getHeight(),
                    'weight' => $cargoItem->getWeight(),
                    'quantity' => $cargoItem->getQuantity(),
                    'articul' => $cargoItem->getArticul(),
                    'cost' => [
                        'amount' => $cargoItem->getCost()->getAmount(),
                        'currency' => $cargoItem->getCost()->getCurrency(),
                    ],
                    'price' => [
                        'amount' => $cargoItem->getPrice()->getAmount(),
                        'currency' => $cargoItem->getPrice()->getCurrency(),
                    ],
                    'fields' => $itm->getField('gbsdefault') ? ['warn'=>1] : [],
                ];
            }
            $cargo->calculateDimensions(true);
            $cargoes->add($cargo);
            $dims = $cargoes->getTotalDimensions();
            $cargo->setLength($dims['L']);
            $cargo->setWidth($dims['W']);
            $cargo->setHeight($dims['H']);
            $cargo->setWeight($cargoes->getTotalWeight());
            $isSingleProduct = $cargo->isSingleProduct();

            $crgData = [
                'id' => $cargo->getId(),
                'ord' => $cargo->getOrd(),
                'ccargo_id' => $cargo->getCargoId(),
                'length' => $cargo->getLength(),
                'width' => $cargo->getWidth(),
                'height' => $cargo->getHeight(),
                'weight' => $cargo->getWeight(),
                'fields' => '',
                'items' => $arItems,
            ];

            //create and save cargo data
            $customCargoData = [
                'cargoes' => [$crgData],
                'fields' => '',
            ];

            PostingHandler::saveCustomCargoData([
                'bitrixId' => $order->getId(),
                'cargo' => $customCargoData,
                'changed' => false,
            ]);
        } else {
            foreach ($customCargoData['cargoes'] as $cargoData) {
                $cargo = new \Ipol\Catapulto\Core\Delivery\Cargo();
                $cargo
                    ->setName($cargoData['name'])
                    ->setWeight(intval($cargoData['weight']))
                    ->setLength(intval($cargoData['length']))
                    ->setWidth(intval($cargoData['width']))
                    ->setHeight(intval($cargoData['height']))
                    ->setOrd($order->getId())
                    ->setCargoId(0)
                ;
                if (isset($cargoData['ccargo_id'])) $cargo->setCargoId(intval($cargoData['ccargo_id']));
                $cargo->setId( $order->getId() . '_' . (new \DateTime())->getTimestamp() );
                if (isset($cargoData['id'])) $cargo->setId($cargoData['id']);
                foreach ($cargoData['items'] as $item) {
                    $cargoItem = (new CargoItem())
                        ->setPrice(new Money(floatval($item['price']['amount'])))
                        ->setCost(new Money(floatval($item['cost']['amount'])))
                        ->setHeight( (intval($item['height']) > 0) ? intval($item['height']) : 0 ) //Defaul value ???
                        ->setLength( (intval($item['length']) > 0) ? intval($item['length']) : 0 )
                        ->setWidth( (intval($item['width']) > 0) ? intval($item['width']) : 0 )
                        ->setWeight( (intval($item['weight']) > 0) ? intval($item['weight']) : 0 )
                        ->setQuantity(floatval($item['quantity']))
                        ->setName($item['name'])
                        ->setId(intval($item['id']))
                        ->setVatRate(0)
                        ->setArticul($item['articul']);
                    /*if ($cargoItem->ready())*/ $cargo->add($cargoItem);
                }
                $cargoes->add($cargo);
                if (!$cargo->isSingleProduct()) $isSingleProduct = false;
            }
            if (count($customCargoData['cargoes']) > 1) $isSingleProduct = false;
        }

        $this->getBaseOrder()->setField('cargoData', $cargoes);
        $this->getBaseOrder()->setField('customCargoData', $customCargoData);
        $this->getBaseOrder()->setField('isSingleProduct', $isSingleProduct);
        $this->getBaseOrder()->setField('cargoDataValid', $this->validateOrderCargo($cargoes));

        $this->compileOrder();

        $this->setDefaultFields();

        return $this;
    }

    //compare saved cargo data && bitrix order data
    private function validateOrderCargo($customCargoData) {
        $orderItems = [];
        $coreItems = $this->items->getCoreItems();
        $coreItems->reset();
        while ($itm = $coreItems->getNext()) {
            $orderItems[] = [
                'id' => intval($itm->getId()),
                'qty' => floatval($itm->getQuantity()),
                'cqty' => 0,
            ];
        }
        $customCargoData->reset();
        while ($cargo = $customCargoData->getNext()) {
            $cargo->reset();
            while ($itm = $cargo->getNext()) {
                $itemQty = floatval($itm->getQuantity());
                $itemId = intval($itm->getId());
                $isExist = false;
                foreach ($orderItems as &$orderItem) {
                    if ($orderItem['id'] == $itemId) {
                        $orderItem['cqty'] += $itemQty;
                        $isExist = true;
                    }
                }
                unset($orderItem);
                if (!$isExist) $orderItems[] = [
                    'id' => $itemId,
                    'qty' => 0,
                    'cqty' => $itemQty,
                ];
            }
        }
        $isValid = true;
        foreach ($orderItems as $orderItem) {
            if ($orderItem['qty'] != $orderItem['cqty']) $isValid = false;
        }

        return $isValid;
    }
    private function validateSendOrderCargo($customCargoData) {
        $orderItems = [];
        $coreItems = $this->items->getCoreItems();
        $coreItems->reset();
        while ($itm = $coreItems->getNext()) {
            $orderItems[] = [
                'id' => intval($itm->getId()),
                'qty' => floatval($itm->getQuantity()),
            ];
        }
        foreach ($customCargoData['cargoes'] as $cargo) {
            foreach ($cargo['items'] as $cargoItem) {
                $itemQty = floatval($cargoItem['qty']);
                $itemId = intval($cargoItem['id']);
                foreach ($orderItems as &$orderItem) {
                    if ($orderItem['id'] == $itemId) $orderItem['qty'] -= $itemQty;
                }
                unset($orderItem);
            }
        }
        $isValid = true;
        foreach ($orderItems as $orderItem) {
            if ($orderItem['qty'] != 0) $isValid = false;
        }

        return $isValid;
    }

    /**
     * Привязывает базовые сущности заказа (адрес, отправитель, итп) к базовому заказу
     */
    protected function compileOrder()
    {
        $this->getBaseOrder()
            //->addReciever($this->getReceiver()->getCoreReceiver())
            ->setSender($this->getSender()->getCoreSender())
            ->setBuyers($this->getBuyer()->getBuyerCollection())
            ->setAddressTo($this->getAddressTo()->getCoreAddress())
            ->setAddressFrom($this->getAddressFrom()->getCoreAddress())
            ->setPayment($this->getPayment()->getCorePayment())
            ->setNumber($this->getOrderNumber())
            ->setGoods($this->getGoods()->getCoreGoods())
            ->setItems($this->getItems()->getCoreItems());
    }

    /**
     * Устанавливает поля по умолчанию для заказа с учетом настроек модуля и данных в самом заказе.
     * ! только для newOrder
     */
    protected function setDefaultFields()
    {
        $this->getBaseOrder()
            ->setField('needInsurance', true /*($this->options->fetchMindEnsurance() == 'Y')*/)
            ->setField('senderCreateDate',\Ipol\Catapulto\Bitrix\Handler\Order::getOrderDate($this->bitrixId,true))
            ->setField('deliveryPaySide', 'sender')
        ;
    }

    /**
     * @return $this
     * Устанавливает поля из запроса (по сути - из формы отправления заявки)
     */
    public function requestOrder()
    {
        // Deal with cp1251
        if (Tools::isModuleAjaxRequest()) {
            $_REQUEST = Tools::encodeFromUTF8($_REQUEST);
        }

        $this->bitrixId    = $_REQUEST['orderId'];
        $this->orderNumber = $_REQUEST['number'];

        $request = self::fromRequest();

        $this->getBaseOrder()->setNumber($this->orderNumber);

        $this->setArrayFields($request['order']);

        //$this->getReceiver()->fromArray($request['receiver']);
        $this->getSender()->fromArray($request['sender']);
        $this->getBuyer()->fromArray($request['buyer']);
        $this->getAddressTo()->fromArray($request['addressTo']);
        $this->getAddressFrom()->fromArray($request['addressFrom']);

        $this->getPayment()->fromArray($request['payment']);
        $this->getGoods()->fromArray($request['goods']);
        $this->getItems()->fromOrder($this->bitrixId);

        $arDateCreate  = \Ipol\Catapulto\Bitrix\Handler\Order::getOrderDate($this->bitrixId,true);

        $orderSavedProps = OrderPropsTable::getByBitrixId($this->bitrixId);
        $this->getBaseOrder()->setField('receiver_loc_id', 0);
        $this->getBaseOrder()->setField('receiver_zip', 0);
        $this->getBaseOrder()->setField('sender_loc_id', 0);
        $this->getBaseOrder()->setField('sender_zip', 0);
        $this->getBaseOrder()->setField('dadata', false);

        if($request['warehouseId']) {
            $this->getBaseOrder()->setField('warehouseCustom', $request['warehouseId']);
        }

        if ($orderSavedProps) {
            if (!empty($orderSavedProps['RATE_RESULT'])) {
                $rateResult = json_decode($orderSavedProps['RATE_RESULT'], true);
                if (!empty($rateResult)) {
                    if (isset($rateResult['receiver_loc_id']) && !empty($rateResult['receiver_loc_id'])) {
                        $this->getBaseOrder()->setField('receiver_loc_id', (int)$rateResult['receiver_loc_id']);
                    }
                    if (isset($rateResult['receiver_zip']) && !empty($rateResult['receiver_zip'])) {
                        $this->getBaseOrder()->setField('receiver_zip', (int)$rateResult['receiver_zip']);
                    }
                    if (isset($rateResult['sender_loc_id']) && !empty($rateResult['sender_loc_id'])) {
                        $this->getBaseOrder()->setField('sender_loc_id', (int)$rateResult['receiver_loc_id']);
                    }
                    if (isset($rateResult['sender_zip']) && !empty($rateResult['sender_zip'])) {
                        $this->getBaseOrder()->setField('sender_zip', (int)$rateResult['sender_zip']);
                    }
                    if (isset($rateResult['cargoes']) && !empty($rateResult['cargoes'])) {
                        $this->getBaseOrder()->setField('cargoes_ids', $rateResult['cargoes']);
                    }

                    if(!empty($rateResult['warehouse'])) {
                        $this->getSender()->getCoreSender()->setField('senderId', $rateResult['warehouse']['CATAPULTO_CONTACT_ID']);
                        $this->getSender()->getCoreSender()->setField('senderCityId', $rateResult['warehouse']['CATAPULTO_CITY_ID']);
                        $this->getSender()->getCoreSender()->setField('senderCity', $rateResult['warehouse']['BX_LOC_NAME']);
                        $this->getSender()->getCoreSender()->setField('warehouseId', $rateResult['warehouse']['ID']);
                    }
                    
                    $isFittingInService    = false;
                    $isPartialRedInService = false;
                    $isSmsAmount           = false;
                    if (isset($rateResult['services']) && is_array($rateResult['services'])) {
                        //scan services...
                        foreach ($rateResult['services'] as $srv) {
                            switch ($srv['name']) {
                                case AbstractGeneral::CTPT_SERVICE_FITTING:
                                    $isFittingInService = true;
                                    break;
                                case AbstractGeneral::CTPT_SERVICE_PR:
                                    $isPartialRedInService = true;
                                    break;
                                case AbstractGeneral::CTPT_SERVICE_SMS:
                                    $isSmsAmount = true;
                                    break;
                            }
                        }
                    }
                    $this->getBaseOrder()->setField('is_fitting_in_service', $isFittingInService);
                    $this->getBaseOrder()->setField('is_partial_red_in_service', $isPartialRedInService);
                    $this->getBaseOrder()->setField('isSmsAmount', $isSmsAmount);
                }
            }
            if (!empty($orderSavedProps['DADATA_ADDR'])) {
                $dadata = json_decode($orderSavedProps['DADATA_ADDR'], true);
                if (!empty($dadata)) {
                    $this->baseOrder->setField('dadata', $dadata);
                }
            }
            if (!empty($orderSavedProps['OTHER'])) {
                $otherProps = json_decode($orderSavedProps['OTHER'], true);
                if (!$otherProps) $otherProps = [];
                if (isset($otherProps['customCargo'])) {
                    $customCargoData = json_decode($otherProps['customCargo'], true);
                    if (!$customCargoData) $customCargoData = [];
                    $cargoes = [];
                    foreach ($customCargoData['cargoes'] as $crg) {
                        if (!isset($crg['ccargo_id'])) continue;
                        $cargoes[$crg['ccargo_id']] = [
                            'id' => $crg['ccargo_id'],
                            'ord' => $this->bitrixId,
                            'ccargo_id' => $crg['ccargo_id'],
                            'items' => [],
                        ];
                        foreach ($crg['items'] as $crgItem) {
                            $cargoes[$crg['ccargo_id']]['items'][] = [
                                'id'      => $crgItem['id'],
                                'name'    => $crgItem['name'],
                                'qty'     => $crgItem['quantity'],
                                'articul' => $crgItem['articul'],
                                'weight'  => $crgItem['weight'],
                                'height'  => $crgItem['height'],
                                'width'   => $crgItem['width'],
                                'length'  => $crgItem['length'],
                            ];
                        }
                    }
                    
                    if(!empty($cargoes)) {
                        $cargoes['cargoes'] = $cargoes;
                    }
                    
                    $this->baseOrder->setField('customCargoData', $cargoes);
                    $this->baseOrder->setField('cargoDataValid', $this->validateSendOrderCargo($cargoes));
                }
                if (isset($otherProps['need_reselect'])) $this->baseOrder->setField('need_reselect', $otherProps['need_reselect']);
            }
        }


        $this->getBaseOrder()->setField('createDate',$arDateCreate['timestamp']);

        // insurance checkbox
        if(array_key_exists('needInsurance',$_REQUEST) && $_REQUEST['needInsurance']) {
            $this->getBaseOrder()->setField('needInsurance',true);
        } else {
            $this->getBaseOrder()->setField('needInsurance',false);
        }
        
        // sms
        if(array_key_exists('smsAmount',$_REQUEST) && $_REQUEST['smsAmount']) {
            $this->getBaseOrder()->setField('smsAmount',true);
        } else {
            $this->getBaseOrder()->setField('smsAmount',false);
        }

        // isFitting checkbox
        $this->getBaseOrder()->setField('withFitting',false);
        if (isset($_REQUEST['fitting']) && (boolval($_REQUEST['fitting']) == true))
            $this->getBaseOrder()->setField('withFitting',true);

        $this->getBaseOrder()->setField('withPartialRedemption', false);
        if (isset($_REQUEST['partialRedemption']) && (boolval($_REQUEST['partialRedemption']) == true))
            $this->getBaseOrder()->setField('withPartialRedemption',true);


        $this->getBaseOrder()->setField('deliveryPaySide', 'sender');
        if (isset($_REQUEST['deliveryPaySide']) && (boolval($_REQUEST['deliveryPaySide']) == true))
            $this->getBaseOrder()->setField('deliveryPaySide','receiver');

        $this->compileOrder();

        return $this;
    }

    /**
     * @return array
     * Связка данных из массива запроса с полями заказа
     */
    protected static function fromRequest()
    {
        return [
            /*'receiver'    => array(
                'firstName' => $_REQUEST['buyerName'],
                'phone'     => $_REQUEST['buyerPhone'],
                'email'     => $_REQUEST['buyerEmail'],
                'PersonType' => $_REQUEST['buyerType'],
                'Company'    => $_REQUEST['buyerLegalName']
            ),*/
            'sender'    => [
                'senderId' => $_REQUEST['senderId'],
            ],
            'buyer'     => [
                'firstName'    => $_REQUEST['buyerName'],
                'phone'        => $_REQUEST['buyerPhone'],
                'company'      => $_REQUEST['buyerCompany'],
                'receiverId'   => $_REQUEST['receiverId'],
                'rateResultId' => $_REQUEST['rateResultId'],
            ],
            'addressTo' => [
                'zip'      => $_REQUEST['buyerZip'],
                'city'     => $_REQUEST['buyerCity'],
                'street'   => $_REQUEST['buyerStreet'],
                'building' => $_REQUEST['buyerBuilding'],
                'flat'     => $_REQUEST['buyerDoorNumber'],
                'line'     => $_REQUEST['addressLine'],
                'comment'  => $_REQUEST['comment']
            ],

            'payment' => [
                'goods'          => $_REQUEST['payment_sum'],
                'estimated'      => $_REQUEST['price'],
                'isBeznal'       => $_REQUEST['payment_isBeznal'],
                'isNp'           => $_REQUEST['payment_np'],
                'isCod'          => $_REQUEST['deliveryPaySide'],
                'isSmsAmount'    => $_REQUEST['smsAmount'],
                'delivery'       => ($_REQUEST['delivery_sum'] - $_REQUEST['extraPrice']),
                'payed'          => 0,//$_REQUEST['payment_prepayment'],
                'ndsDelivery'    => 0,//$_REQUEST['payment_ndsDeliveryRate'],
                'sumToPay'       => $_REQUEST['sumToPay'],
                'insuranceValue' => (array_key_exists('needInsurance', $_REQUEST) && $_REQUEST['needInsurance']) ? $_REQUEST['insuranceValue'] : null,
            ],

            'goods' => [
                'length' => $_REQUEST['length'],
                'width'  => $_REQUEST['width'],
                'height' => $_REQUEST['height'],
                'weight' => $_REQUEST['weight']
            ],

            'order' => [
                'orderId'                => $_REQUEST['orderId'],
                'rateResultId'           => $_REQUEST['rateResultId'],
                'pickupDay'              => $_REQUEST['pickupDay'],
                'deliveryDay'            => $_REQUEST['deliveryDay'],
                'needInsurance'          => (array_key_exists('needInsurance', $_REQUEST) && $_REQUEST['needInsurance']),
                'smsAmount'              => (array_key_exists('smsAmount', $_REQUEST) && $_REQUEST['smsAmount']),
                'sender_terminal_code'   => $_REQUEST['sender_terminal_code'],
                'receiver_terminal_code' => $_REQUEST['receiver_terminal_code'],
                'operator'               => $_REQUEST['operator']
            ],

            'items' => $_REQUEST['items']
        ];
    }

    /**
     * @param $array
     * Устанавливаем поля из массива в базовый заказ
     */
    protected function setArrayFields($array)
    {
        foreach($array as $key => $val)
        {
            $this->getBaseOrder()->setField($key,(string)$val);
        }
    }

    /**
     * @param $bitrixId - ID заказа в Битриксе
     * @param $mode - всегда 1 (2 - для отгрузок, не подключено)
     * @return $this
     * Заполняет заказ данными по сведениям, хранящимся в таблице модуля (то есть - по отосланному заказу)
     */
    public function uploadedOrder($bitrixId)
    {
        $this->bitrixId = $bitrixId;

        $arOrder = OrdersTable::getByBitrixId($bitrixId);

        // bx order object
        $obOrder = \Ipol\Catapulto\Bitrix\Handler\Order::getOrderById($this->bitrixId);

        $this->setDefaultFields();
        $this->getBaseOrder()->setField('cargoData', new CargoCollection());

        $arDateCreate  = \Ipol\Catapulto\Bitrix\Handler\Order::getOrderDate($this->bitrixId,true);

        $dbFields = $this->fromDB($arOrder);

        if($arOrder) {
            // add order props from bitrix order entity
            $orderProps = OrderPropsTable::getByBitrixId(intval($bitrixId));

            $arProps = $obOrder->getPropertyCollection()->getArray();
            foreach ($arProps['properties'] as $property) {
                $value = array_pop($property['VALUE']);

                // rate_result
                /*if ($property['CODE'] == OrderPropsHandler::getRateResultProp() && $value) {
                    if ($arRate = @json_decode($value,true)) {
                        $this->getBaseOrder()->setField('rateResult',$arRate);
                    }
                }*/

                if ($property['CODE'] == OrderPropsHandler::getRateProp() && $value) {
                    if (empty($orderProps['RATE_RESULT_ID'])) {
                        OrderPropsTable::saveProps(intval($bitrixId),['RATE_RESULT_ID'=>$value]);
                        $orderProps['RATE_RESULT_ID'] = $value;
                    }
                    //OrderPropsHandler::updateProp($bitrixId,OrderPropsHandler::getRateProp(),'');
                }
                if ($property['CODE'] == OrderPropsHandler::getRateResultProp() && $value) {
                    if (empty($orderProps['RATE_RESULT'])) {
                        OrderPropsTable::saveProps(intval($bitrixId),['RATE_RESULT'=>$value]);
                        $orderProps['RATE_RESULT'] = $value;
                    }
                    //OrderPropsHandler::updateProp($bitrixId,OrderPropsHandler::getRateResultProp(),'');
                }
            }

            $sender = $this->getSender()->fromArray($dbFields['sender'])->getCoreSender();

            $customCargoData = false;
            $defaultCargoId = 0;
            if ($orderProps) {
                if ($orderProps['RATE_RESULT']) {
                    if ($arRate = @json_decode($orderProps['RATE_RESULT'],true)) {
                        $this->getBaseOrder()->setField('rateResult',$arRate);

                        if (is_array($arRate['cargoes']) && count($arRate['cargoes'])>0) {
                            $defaultCargoId = $arRate['cargoes'][0];
                        }

                        $this->baseOrder->setField('insurance_cost', floatval($arRate['insurance_config'] ?? 0));
                        $this->baseOrder->setField('baseDeliveryPrice',floatval($arRate['base_price'] ?? 0));
                        $this->baseOrder->setField('baseDeliveryPriceWithServices',floatval($arRate['base_price_with_services'] ?? 0));
                        $this->baseOrder->setField('isFittingInRate', $arRate['is_fitting'] ?? false);
                        $this->baseOrder->setField('isPartialRedInRate', $arRate['with_partial_red'] ?? false);

                        if (!empty($arRate['warehouse'])) {
                            $sender->setField('senderId', $arRate['warehouse']['CATAPULTO_CONTACT_ID']);
                            $sender->setField('senderCityId', $arRate['warehouse']['CATAPULTO_CITY_ID']);
                            $sender->setField('senderCity', $arRate['warehouse']['BX_LOC_NAME']);
                            $sender->setField('warehouseId', $arRate['warehouse']['ID']);
                        }

                        $this->setOrderServicesData($arRate);
                    }
                }
                if ($orderProps['OTHER'] && ($otherPropsOther = @json_decode($orderProps['OTHER'],true))) {
                    $this->getBaseOrder()->setField('saved_en_ensurance', $otherPropsOther['en_ensurance']);
                    $this->getBaseOrder()->setField('isPVZ', $otherPropsOther['isPVZ'] ?? 'N');
                    $this->getBaseOrder()->setField('forcePVZAddress', $otherPropsOther['forcePVZAddress'] ?? '');

                    if (isset($otherPropsOther['customCargo'])) {
                        $customCargoData = @json_decode($otherPropsOther['customCargo'],true);
                        if (!isset($customCargoData['cargoes'])) $customCargoData = false;
                    }
                }
            }

            $this->setOrderCustomCargoData($obOrder, $customCargoData, $defaultCargoId);

            $this->getBaseOrder()->setStatus($arOrder['MAIN_STATUS']);
            $this->getBaseOrder()->setLink($arOrder['CATAPULTO_ID']);
            $this->getBaseOrder()->setField('catapultoStatus',$arOrder['OZON_STATUS']);

            $this->setArrayFields($dbFields['order']);
            $this->getBuyer()->fromArray($dbFields['buyer']);
            $this->getPayment()->fromArray($dbFields['payment']);

            $this->getBaseOrder()->setGoods($this->getGoods()->fromOrder($this->bitrixId)->getCoreGoods());
            $this->getBaseOrder()->setItems($this->getItems()->fromOrder($this->bitrixId)->getCoreItems());


            $this->getBaseOrder()
                ->setSender($sender)
                ->setBuyers($this->getBuyer()->getBuyerCollection())
                ->setPayment($this->getPayment()->getCorePayment())
                ->setNumber(($this->getOrderNumber()) ? $this->getOrderNumber() : \Ipol\Catapulto\Bitrix\Handler\Order::getOrderNumber($bitrixId))
                ->setField('message',$arOrder['MESSAGE']);

            $this->getAddressTo()->fromArray($dbFields['addressTo']);
            if (
                !empty($dbFields['order']['receiver_address']) &&
                $addressTo = unserialize($dbFields['order']['receiver_address'], ['allowed_classes' => false])
            ) {
                $this->getAddressTo()->getCoreAddress()->setStreet($addressTo['street'] ?? null);
                $this->getAddressTo()->getCoreAddress()->setBuilding($addressTo['building'] ?? null);
                $this->getAddressTo()->getCoreAddress()->setFlat($addressTo['door_number'] ?? null);
                $this->getAddressTo()->getCoreAddress()->setZip($addressTo['zip'] ?? null);
                $this->getAddressTo()->getCoreAddress()->setComment($addressTo['comment'] ?? null);
                $this->getAddressTo()->getCoreAddress()->setLine($addressTo['address_line_1'] ?? null);
                
                $arLineAddress = array_filter([
                    'zip'      => $addressTo['zip'] ?? '',
                    'country'  => $addressTo['country'] ?? '',
                    'locality' => $addressTo['locality'] ?? '',
                    'street'   => $addressTo['street'] ?? '',
                    'building' => $addressTo['building'] ?? '',
                    'flat'     => $addressTo['door_number'] ?? '',
                ]);
                
                $this->getAddressTo()->getCoreAddress()->setField('dadata_unrestricted_value',implode(', ', $arLineAddress));

                unset($dbFields['order']['receiver_address']);
            }

            $this->getAddressFrom()->fromArray($dbFields['addressFrom']);
            $this->getBaseOrder()->setAddressTo($this->getAddressTo()->getCoreAddress());
            $this->getBaseOrder()->setAddressFrom($this->getAddressFrom()->getCoreAddress());

            if (!$arOrder['OK']) {
                $this->getBaseOrder()->setStatus('ERROR');
            }

        }

        return $this;
    }

    protected function fromDB($arDB)
    {
        $payment = unserialize($arDB['PAYMENT'], ['allowed_classes' => false]);

        $warehouseId = 0;
        if($arDB['BITRIX_ID']) {
            $arProps = OrderPropsTable::getByBitrixId($arDB['BITRIX_ID'], ['RATE_RESULT']);
            $arProps = json_decode($arProps['RATE_RESULT'], true);
            $warehouseId = (int)$arProps['warehouse']['ID'];
        }

        return [
            'sender' => [
                'senderId'   => $arDB['SENDER_CONTACT_ID'],
                'senderCity' => $arDB['SENDER_LOCALITY'],
            ],

            'buyer' => [
                'firstName'  => $arDB['RECEIVER_NAME'],
                'phone'      => $arDB['RECEIVER_PHONE'],
                'company'    => $arDB['RECEIVER_COMPANY'],
                'receiverId' => $arDB['RECEIVER_CONTACT_ID']
            ],

            'addressTo' => [
                'city' => $arDB['RECEIVER_LOCALITY'],
            ],

            'payment' => [
                'delivery'       => $payment['delivery'],
                'goods'          => $payment['goods'],
                'estimated'      => $payment['estimated'],
                'payed'          => $payment['payed'],
                'isBeznal'       => $payment['isBeznal'],
                'isNp'           => $payment['isNp'],
                'isCod'          => $payment['isCod'],
                'isSmsAmount'    => $payment['isSmsAmount'],
                'insuranceValue' => $payment['insuranceValue'],
                'sumToPay'       => $payment['sumToPay']
            ],

            'order' => [
                'orderId'                => $arDB['BITRIX_ID'],
                'catapultoId'            => $arDB['CATAPULTO_ID'],
                'catapultoNumber'        => $arDB['NUMBER'],
                'rateResultId'           => $arDB['RATE_RESULT_ID'],
                'receiver_terminal_code' => $arDB['TERMINAL_CODE'],
                'sender_terminal_code'   => Adapter::getSenderTerminalCode($warehouseId, $arDB['OPERATOR']),
                'operator'               => $arDB['OPERATOR'],
                'catapulto_status'       => $arDB['OZON_STATUS'],
                'needInsurance'          => $arDB['WITH_INSURANCE'],
                'smsAmount'              => $payment['isSmsAmount'],
                'receiver_address'       => $arDB['RECEIVER_ADDRESS'],
                'tracking_link'          => $arDB['TRACKING_LINK'],
            ],
        ];
    }

    /**
     * @param $catapultoId
     * @return order
     * @throws \Exception
     * Заполняет заказ данными по таблице модуля (отправленный), выборка - по ID катапульты.
     */
    public function uploadedOrderByCatapultoId($catapultoId)
    {
        $obOrder = OrdersTable::getByCatapultoId($catapultoId);
        if($obOrder)
        {
            return $this->uploadedOrder($obOrder['BITRIX_ID']);
        }else
        {
            throw new \Exception('No order with catapulto_id '.$catapultoId);
        }
    }
    /**
     * @param $catapultoKey
     * @return order
     * @throws \Exception
     * Заполняет заказ данными по таблице модуля (отправленный), выборка - по key катапульты.
     */
    public function uploadedOrderByCatapultoKey($catapultoKey)
    {
        $obOrder = OrdersTable::getByKey($catapultoKey);
        if ($obOrder) {
            return $this->uploadedOrder($obOrder['BITRIX_ID']);
        }
        else {
            throw new \Exception('No order with catapulto_key ' . $catapultoKey);
        }
    }


    /**
     * @param $code
     * @return bool
     * Конгениальная проверка чекбоксов
     */
    protected function checkBoolOption($code)
    {
        $method = 'get'.ucfirst($code);
        return ($this->options->$method() === 'Y');
    }

    protected static function makeFivepostTimeFromTimestamp($timeStamp){
        $strDateCreate = new \DateTime( 'now', new \DateTimeZone('UTC'));
        $strDateCreate->setTimestamp($timeStamp);
        return str_replace('+00:00', '.000Z', $strDateCreate->format('c'));
    }

    protected static function makeDBTime($timeStamp){
        $obDate = \Bitrix\Main\Type\DateTime::createFromTimestamp($timeStamp);
        return $obDate;
    }

    /**
     * @return mixed
     */
    public function getBitrixId()
    {
        return $this->bitrixId;
    }

    /**
     * @return orderItems
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param mixed $items
     * @return $this
     */
    public function setItems($items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @return \Ipol\Catapulto\Core\Order\Order
     */
    public function getBaseOrder()
    {
        return $this->baseOrder;
    }

    /**
     * @return Sender
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param Sender $sender
     *
     * @return Order
     */
    public function setSender(Sender $sender)
    {
        $this->sender = $sender;
        return $this;
    }


    /**
     * @return Receiver
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * @param Receiver $receiver
     * @return $this
     */
    public function setReceiver(Receiver $receiver)
    {
        $this->receiver = $receiver;

        return $this;
    }

    /**
     * @return Buyer
     */
    public function getBuyer()
    {
        return $this->buyer;
    }

    /**
     * @param Buyer $buyer
     * @return $this
     */
    public function setBuyer(Buyer $buyer)
    {
        $this->buyer = $buyer;

        return $this;
    }

    // Getters/setters
    /**
     * @return addressTo
     */
    public function getAddressTo()
    {
        return $this->addressTo;
    }

    /**
     * @return addressFrom
     */
    public function getAddressFrom()
    {
        return $this->addressFrom;
    }

    /**
     * @param mixed $addressTo
     * @return $this
     */
    public function setAddressTo(addressTo $addressTo)
    {
        $this->addressTo = $addressTo;

        return $this;
    }

    /**
     * @return payment
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @param mixed $payment
     * @return $this
     */
    public function setPayment(payment $payment)
    {
        $this->payment = $payment;

        return $this;
    }

    /**
     * @return OrderGoods
     */
    public function getGoods()
    {
        return $this->goods;
    }

    /**
     * @param mixed $obGoods
     * @return $this
     */
    public function setGoods($obGoods)
    {
        $this->goods = $obGoods;

        return $this;
    }


    /**
     * @return mixed
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }
}
