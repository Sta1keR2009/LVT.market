<?php

namespace Ipol\Catapulto;

use \Bitrix\Main\Result;
use \Bitrix\Main\Error;

use Ipol\Catapulto\Bitrix\Adapter\Cargo;
use Ipol\Catapulto\Bitrix\Adapter\Order;
use Ipol\Catapulto\Bitrix\Controller\CancelOrder;
use Ipol\Catapulto\Bitrix\Controller\Contact;
use Ipol\Catapulto\Bitrix\Controller\ShipmentGoods;
use Ipol\Catapulto\Bitrix\Controller\ShipmentNp;
use Ipol\Catapulto\Bitrix\Entity\BasicResponse;
use Ipol\Catapulto\Bitrix\Entity\DefaultGabarites;
use Ipol\Catapulto\Bitrix\Entity\Options;
use Ipol\Catapulto\Bitrix\Handler\Deliveries;
use Ipol\Catapulto\Bitrix\Handler\GoodsPicker;
use Ipol\Catapulto\Bitrix\Tools;
use Ipol\Catapulto\Core\Entity\Money;

IncludeModuleLangFile(__FILE__);

class OrderHandler extends AbstractGeneral
{
    /**
     * @var \Ipol\Catapulto\Bitrix\Adapter\Order
     */
    public static $order;
    
    /**
     * @return \Ipol\Catapulto\Bitrix\Adapter\Order
     */
    public static function getOrder()
    {
        return self::$order;
    }
    
    protected static function initOrder()
    {
        $options     = new Options();
        self::$order = new Order($options);
    }
    
    public static function loadCMSOrder($bitrixId)
    {
        self::initOrder();
        return self::getOrder()->newOrder($bitrixId)->getBaseOrder();
    }
    
    public static function loadRequestOrder()
    {
        self::initOrder();
        return self::getOrder()->requestOrder()->getBaseOrder();
    }
    
    public static function loadUploadOrder($bitrixId, $mode = 1)
    {
        self::initOrder();
        return self::getOrder()->uploadedOrder($bitrixId)->getBaseOrder();
    }
    
    public static function loadUploadOrderByCatapultoId($catapultoId)
    {
        self::initOrder();
        return self::getOrder()->uploadedOrderByCatapultoId($catapultoId)->getBaseOrder();
    }
    
    public static function calculateOrder()
    {
    }
    
    public static function sendOrdersMass($arPostData = [])
    {
        $obReturn = new BasicResponse();
        
        $arOrdersData = $arPostData['orders'] ?? [];
        
        if (empty($arOrdersData)) {
            $obReturn->setSuccess(false)->setErrorText(Tools::getMessage('ERROR_ORDER_NOT_SELECTED'));
            if (Tools::isModuleAjaxRequest()) {
                echo Tools::jsonEncode([
                    'success' => $obReturn->isSuccess(),
                    'error'   => $obReturn->getErrorText()
                ]);
            }
            
            return $obReturn;
        }
        
        $arOrdersResult = [];
        ob_start();
        foreach ($arOrdersData as $arOrderData) {
            $orderReturn = self::sendOrder([], $arOrderData);
            
            if (!$orderReturn->isSuccess()) {
                $error      = $orderReturn->getErrorText();
                $jsonErrors = json_decode($error, true);
                if ($jsonErrors) {
                    $error = '';
                    foreach ($jsonErrors as $jsonError) {
                        if (is_array($jsonError)) {
                            $error .= implode("\n", $jsonError);
                        }
                        else {
                            $error .= $jsonError . "\n";
                        }
                    }
                }
                
                $arOrdersResult[$arOrderData['id']] = [
                    'success' => false,
                    'error'   => $error
                ];
            }
            else {
                $arOrdersResult[$arOrderData['id']] = [
                    'success' => true,
                    'error'   => ''
                ];
            }
        }
        ob_end_clean();
        
        if (!empty($arOrdersResult)) {
            foreach ($arOrdersResult as $arOrderResult) {
                if (!$arOrderResult['success']) {
                    $obReturn->setSuccess(false);
                    $obReturn->setErrorText('See details in [orders_result]');
                }
            }
        }
        
        if (Tools::isModuleAjaxRequest()) {
            echo Tools::jsonEncode([
                'success'       => $obReturn->isSuccess(),
                'error'         => $obReturn->getErrorText(),
                'orders_result' => $arOrdersResult
            ]);
        }
        
        return $obReturn;
    }
    
    public static function checkCargo($obOrder)
    {
        $errorMsg    = '';
        $isGabsError = false;
        $cargoData   = $obOrder->getField('customCargoData');
        
        //Новая проверка ВГХ - данные от custom items...
        $cargoesIds = [];
        foreach ($obOrder->getField('cargoes_ids') as $cargoId) {
            $cargoesIds[$cargoId] = false; //checked
        }
        
        foreach ($cargoData['cargoes'] as $crgData) {
            $cargoesIds[$crgData['ccargo_id']] = true;
            
            foreach ($crgData['items'] as $itm) {
                $wei = (int)$itm['weight'];
                $h   = (int)$itm['height'];
                $l   = (int)$itm['length'];
                $w   = (int)$itm['width'];
                if ($wei == 0) {
                    $errorMsg    .= Tools::getMessage('GABSERROR_weight') . ' [' . $itm['id'] . '] ' . $itm['name'] . PHP_EOL;
                    $isGabsError = true;
                }
                if ($h == 0) {
                    $errorMsg    .= Tools::getMessage('GABSERROR_height') . ' [' . $itm['id'] . '] ' . $itm['name'] . PHP_EOL;
                    $isGabsError = true;
                }
                if ($l == 0) {
                    $errorMsg    .= Tools::getMessage('GABSERROR_length') . ' [' . $itm['id'] . '] ' . $itm['name'] . PHP_EOL;
                    $isGabsError = true;
                }
                if ($w == 0) {
                    $errorMsg    .= Tools::getMessage('GABSERROR_width') . ' [' . $itm['id'] . '] ' . $itm['name'] . PHP_EOL;
                    $isGabsError = true;
                }
            }
        }
        
        if ($isGabsError) {
            $errorMsg .= Tools::getMessage('GABSERROR_aftertext') . PHP_EOL;
        }
        
        foreach ($cargoesIds as $checked) {
            if (!$checked) {
                $errorMsg .= Tools::getMessage('GABSERROR_cargoerr') . PHP_EOL;
                break;
            }
        }
        
        if (!$obOrder->getField('cargoDataValid')) {
            $errorMsg .= Tools::getMessage('GABSERROR_cargoerr2') . PHP_EOL;
        }
        
        return $errorMsg;
    }
    
    public static function sendOrder($arPostData, $orderData = [])
    {
        $obReturn = new BasicResponse();
        
        $additionalServices = [];
        
        if ($orderData) {
            $isCod   = (array_key_exists('isCod', $orderData) && $orderData['isCod'] && $orderData['isCod'] !== 'N');
            $obOrder = self::loadCMSOrder($orderData['id']);
            $obOrder->setField('needInsurance', true);
            $obOrder->getPayment()
                ->setIsNp((array_key_exists('isNp', $orderData) && $orderData['isNp'] && $orderData['isNp'] !== 'N'))
                ->setIsCod($isCod)
                ->setIsSmsAmount((array_key_exists('isSmsAmount', $orderData) && $orderData['isSmsAmount'] && $orderData['isSmsAmount'] !== 'N'))
                ->setField('sumToPay', new Money($orderData['sumToPay'] ?? 0));
                
            $obOrder->setField('deliveryPaySide', ($isCod ? 'receiver' : 'sender'));
            
            if (!empty($orderData['services'])) {
                $additionalServices = explode(',', $orderData['services']);
            }
        }
        else {
            $obOrder = self::loadRequestOrder();
        }
        
        //Новый функционал - данные ВГХ теперь берутся от данных грузомест, которые, теперь, должны быть обязательно заполнены
        if ($obOrder->getField('need_reselect')) {
            $obReturn->setSuccess(false)->setErrorText(Tools::getMessage('GABSERROR_needreselect'));
            if (Tools::isModuleAjaxRequest()) {
                echo Tools::jsonEncode([
                    'success' => $obReturn->isSuccess(),
                    'error'   => $obReturn->getErrorText()
                ]);
            }
            return $obReturn;
        }
        
        $cargoData = $obOrder->getField('customCargoData');
        $errorMsg  = static::checkCargo($obOrder);
        
        //check services...
        if ($obOrder->getField('withFitting') === true) {
            if (!$obOrder->getField('is_fitting_in_service')) {
                $errorMsg .= Tools::getMessage('SERVICE_ERROR_no_fitting_in_rate') . PHP_EOL;
            }
        }
        if ($obOrder->getField('withPartialRedemption') === true) {
            if (!$obOrder->getField('is_partial_red_in_service')) {
                $errorMsg .= Tools::getMessage('SERVICE_ERROR_no_partial_red_in_rate') . PHP_EOL;
            }
        }
        
        if (!empty($errorMsg)) {
            $obReturn->setSuccess(false)->setErrorText($errorMsg);
            if (Tools::isModuleAjaxRequest()) {
                echo Tools::jsonEncode([
                    'success' => $obReturn->isSuccess(),
                    'error'   => $obReturn->getErrorText()
                ]);
            }
            return $obReturn;
        }
        
        //saveActualGoodsGabsData
        $itemCollection = $obOrder->getItems();
        $itemCollection->reset();
        while ($obItem = $itemCollection->getNext()) {
            $price = new Money($obItem->getPrice());
            $cost  = new Money($obItem->getCost());
            $obItem->setPrice($price)->setCost($cost);
            
            foreach ($cargoData['cargoes'] as $cargoId => $crgData) {
                foreach ($crgData['items'] as $itm) {
                    if (intval($itm['id']) == intval($obItem->getId())) {
                        $obItem->setWeight(floatval($itm['weight']));
                        $obItem->setWidth(floatval($itm['width']));
                        $obItem->setLength(floatval($itm['length']));
                        $obItem->setHeight(floatval($itm['height']));
                        $obItem->setArticul($itm['articul']);
                        break;
                    }
                }
            }
        }
        
        $contactController = new Contact();
        // если есть контакт получателя - обновляем
        if ($obOrder->getBuyers()->getFirst()->getField('receiverId')) {
            // update contact data in catapulto
            $contactUpdate = $contactController->contactUpdate(
                $obOrder->getBuyers()->getFirst()->getField('receiverId'),
                $obOrder
            );
            
            if (!$contactUpdate->isSuccess()) {
                $obReturn = $contactUpdate;
                if (Tools::isModuleAjaxRequest()) {
                    echo Tools::jsonEncode([
                        'success' => $obReturn->isSuccess(),
                        'error'   => $obReturn->getErrorText()
                    ]);
                }
                
                return $obReturn;
            }
        }
        else {
            // если контакт еще не создавался - создаем
            $contactCreate = $contactController->contactCreate($obOrder);
            
            if (!$contactCreate->isSuccess()) {
                $obReturn = $contactCreate;
                if (Tools::isModuleAjaxRequest()) {
                    echo Tools::jsonEncode([
                        'success' => $obReturn->isSuccess(),
                        'error'   => $obReturn->getErrorText()
                    ]);
                }
                
                return $obReturn;
            }
        }
        
        $obOrder->setField('Comment', '');
        
        //send Goods Data
        $goodsController = new ShipmentGoods();
        $obResponse      = $goodsController->create($obOrder);
        
        if (!$obResponse->isSuccess()) {
            $obReturn = $obResponse;
            if (Tools::isModuleAjaxRequest()) {
                echo Tools::jsonEncode([
                    'success' => $obReturn->isSuccess(),
                    'error'   => $obReturn->getErrorText()
                ]);
            }
            return $obReturn;
        }
        
        
        if ($obOrder->getField('withFitting') === true) {
            $additionalServices[] = self::CTPT_SERVICE_FITTING;
        }
        if ($obOrder->getField('withPartialRedemption') === true) {
            $additionalServices[] = self::CTPT_SERVICE_PR;
        }
        if ($obOrder->getPayment()->getIsSmsAmount()) {
            $additionalServices[] = self::CTPT_SERVICE_SMS;
        }
        
        $additionalServices = array_unique($additionalServices);
        
        $obOrder->setField('additional_services', $additionalServices);
        
        if (!$obOrder->getPayment()->getIsBeznal()) {
            // создаем наложенный платеж
            if ($obOrder->getPayment()->getIsNp()) {
                $additionalServices[] = self::CTPT_SERVICE_POD;
            }
            if ($obOrder->getField('deliveryPaySide') == 'receiver') {
                $additionalServices[] = self::CTPT_SERVICE_COD;
            }
            
            $additionalServices = array_unique($additionalServices);
            
            $obOrder->setField('additional_services', $additionalServices);
            $npController = new ShipmentNp();
            $obResponse   = $npController->create($obOrder);
            
            if (!$obResponse->isSuccess()) {
                $obReturn = $obResponse;
                if (Tools::isModuleAjaxRequest()) {
                    echo Tools::jsonEncode([
                        'success' => $obReturn->isSuccess(),
                        'error'   => $obReturn->getErrorText()
                    ]);
                }
                
                return $obReturn;
            }
        }
        
        $controller = new \Ipol\Catapulto\Bitrix\Controller\Order($obOrder);
        $obResponse = $controller->send();
        $obReturn   = $obResponse;
        
        if ($obResponse->isSuccess()) {
            /** @var \Ipol\Catapulto\Api\Entity\Response\Shipment $fastLink */
            $fastLink = $obResponse->getData()->getResponse();
            
            //save order (request-bid) in database
            $resultAdd = self::saveOrder($obOrder);
            
            if (!$resultAdd[0]->isSuccess()) {
                $arErrors       = $resultAdd[0]->getErrors();
                $arReturnErrors = [];
                /** @var \Bitrix\Main\ORM\Fields\FieldError $arError */
                foreach ($arErrors as $arError) {
                    $arReturnErrors [] = $arError->getMessage();
                }
                $obReturn->setSuccess(false)->setErrorText(implode(',', $arReturnErrors));
            }
            else {
                //in database bid
                self::markOrderSended($obOrder->getField('orderId'), $fastLink->getKey(), $fastLink->getStatus());
            }
            
            StatusHandler::checkStatus($fastLink->getKey());
        }
        
        if (Tools::isModuleAjaxRequest()) {
            echo Tools::jsonEncode([
                'success' => $obReturn->isSuccess(),
                'error'   => $obReturn->getErrorText()
            ]);
        }
        
        return $obReturn;
    }
    
    public static function getOrderBarcode()
    {
        /*
        $controller = new \Ipol\Fivepost\Bitrix\Controller\Order(self::$MODULE_ID,self::$MODULE_LBL);

        if(array_key_exists(self::$MODULE_LBL.'action',$_REQUEST) && $_REQUEST[self::$MODULE_LBL.'action'] === __METHOD__){
            echo $controller->generateBarcode();
        } else {
            return $controller->generateBarcode();
        }
*/
        return false;
    }
    
    /**
     * Erase order from orders table
     *
     * @param $bitrixId
     *
     * @return \Bitrix\Main\Result
     */
    public static function eraseOrder($bitrixId)
    {
        $eraseResult = new Result();
        
        if (!empty($bitrixId)) {
            $order = OrdersTable::getByBitrixId($bitrixId, ['ID', 'BITRIX_ID']);
            if (isset($order['ID']) && $order['ID']) {
                $deleteResult = OrdersTable::delete($order['ID']);
                if ($deleteResult->isSuccess()) {
                    $eraseResult->setData(['ERASED_BITRIX_ID' => $bitrixId]);
                }
                else {
                    $eraseResult->addError(new Error(implode(', ', $deleteResult->getErrorMessages())));
                }
            }
            else {
                $eraseResult->addError(new Error('Erase failed cause no orders found by given Bitrix ID.'));
            }
        }
        else {
            $eraseResult->addError(new Error('Erase failed cause no Bitrix ID given.'));
        }
        
        return $eraseResult;
    }
    
    /**
     * Ajax wrapper for eraseOrder
     *
     * @param $request
     */
    public static function eraseOrderAjaxBid($request)
    {
        $eraseResult = self::eraseOrder($request['bitrixId']);
        $arReturn    = ['success' => $eraseResult->isSuccess()];
        if (!$eraseResult->isSuccess()) {
            $arReturn['error'] = implode(', ', $eraseResult->getErrorMessages());
        }
        echo Tools::jsonEncode($arReturn);
    }
    
    /**
     * @param $bitrixId
     *
     * @return BasicResponse
     */
    public static function deleteOrder($bitrixId)
    {
        /*
        if(is_array($bitrixId)){
            $bitrixId = $bitrixId['bitrixId'];
        }

        $obReturn = new BasicResponse();
        $obOrder = self::loadUploadOrder($bitrixId);

        $controller = new \Ipol\Fivepost\Bitrix\Controller\Order(self::$MODULE_ID,self::$MODULE_LBL,$obOrder);

        $obResult = $controller->delete();

        if($obResult->isSuccess()){
            $obReturn->setSuccess(true);

            StatusHandler::checkStatusByBI($bitrixId);
        } else {
            $obReturn->setSuccess(false)
                ->setErrorText($obResult->getErrorText());
        }

        if(array_key_exists(self::$MODULE_LBL.'action',$_REQUEST) && $_REQUEST[self::$MODULE_LBL.'action']){
            echo json_encode(array(
                'success' => $obReturn->isSuccess(),
                'error'   => $obReturn->getErrorText()
            ));
        } else {
            return $obReturn;
        }
        */
    }
    
    
    /**
     * @param \Ipol\Catapulto\Core\Order\Order $obOrder
     *
     * @return array (\Bitrix\Main\ORM\Data\AddResult,typeUpdate)'
     * Полностью сохраняет имеющийся заказ (базу) в БД.
     */
    public static function saveOrder($obOrder)
    {
        $arAdd = [
            'BITRIX_ID'            => $obOrder->getField('orderId'),
            'SENDER_CONTACT_ID'    => $obOrder->getSender()->getField('senderId'),
            'RECEIVER_CONTACT_ID'  => $obOrder->getBuyers()->getFirst()->getField('receiverId'),
            'RECEIVER_LOCALITY'    => $obOrder->getAddressTo()->getCity(),
            'RECEIVER_NAME'        => $obOrder->getBuyers()->getFirst()->getFirstName(),
            'RECEIVER_COMPANY'     => $obOrder->getBuyers()->getFirst()->getField('company'),
            'RECEIVER_PHONE'       => $obOrder->getBuyers()->getFirst()->getPhone(),
            'RECEIVER_ADDRESS'     => serialize(Contact::prepareForDB($obOrder)),
            'WEBSHOP_ORDER_NUMBER' => $obOrder->getField('orderId'),
            'RATE_RESULT_ID'       => $obOrder->getField('rateResultId'),
            'TERMINAL_CODE'        => $obOrder->getField('receiver_terminal_code'),
            'OPERATOR'             => $obOrder->getField('operator'),
            'WITH_INSURANCE'       => $obOrder->getField('needInsurance'),
            'IS_POD'               => (!$obOrder->getPayment()->getIsBeznal() ? true : false),
            'SUM_TO_PAY'           => $obOrder->getPayment()->getField('sumToPay')->getAmount(),
            'PAYMENT'              => serialize([
                'delivery'       => $obOrder->getPayment()->getDelivery()->getAmount(),
                'goods'          => $obOrder->getPayment()->getGoods()->getAmount(),
                'estimated'      => $obOrder->getPayment()->getEstimated()->getAmount(),
                'payed'          => $obOrder->getPayment()->getPayed()->getAmount(),
                'isBeznal'       => $obOrder->getPayment()->getIsBeznal(),
                'isNp'           => $obOrder->getPayment()->getIsNp(),
                'isCod'          => $obOrder->getPayment()->getIsCod(),
                'isSmsAmount'    => $obOrder->getPayment()->getIsSmsAmount(),
                'insuranceValue' => $obOrder->getPayment()->getField('insuranceValue')->getAmount(),
                'sumToPay'       => $obOrder->getPayment()->getField('sumToPay')->getAmount()
            ]),
            'INSURANCE_COST'       => $obOrder->getPayment()->getField('insuranceValue')->getAmount(),
            'UPTIME'               => time()
        ];
        
        $check = OrdersTable::getByBitrixId($obOrder->getField('orderId'));
        if ($check) {
            $obAddDB = OrdersTable::update($check['ID'], $arAdd);
            $type    = 'update';
        }
        else {//update
            $obAddDB = OrdersTable::add($arAdd);
            $type    = 'new';
        }
        
        return [$obAddDB, $type];
    }
    
    public static function markOrderSended($bitrixId, $catapultoKey, $status)
    {
        $tableId = OrdersTable::getByBitrixId($bitrixId);
        if ($tableId) {
            OrdersTable::update($tableId['ID'], [
                'OK'     => 'Y',
                'STATUS' => $status,
                'KEY'    => $catapultoKey,
            ]);
        }
    }
    
    // other
    public static function countCargoGabs($params)
    {
        $answer = ['success' => false];
        if (!$params['orderId']) {
            $answer['error'] = 'No order Id';
        }
        elseif (!count($params['items'])) {
            $answer['error'] = 'No items';
        }
        else {
            $arItems = GoodsPicker::fromArray($params['items'], $params['orderId']);
            $obCargo = new Cargo(new DefaultGabarites());
            $obCargo->set($arItems);
            
            $answer = [
                'success'    => true,
                'weight'     => $obCargo->getCargo()->getWeight(),
                'dimensions' => $obCargo->getCargo()->getDimensions(),
                'cargo'      => $params['cargo']
            ];
        }
        
        if ($params[self::$MODULE_LBL . 'action']) {
            echo Tools::jsonEncode($answer);
        }
        
        return $answer;
    }
    
    /**
     * @param $orderId
     *
     * @return bool|mixed
     * С каким тарифом был оформлен заказ
     */
    public static function getOrderTarif($orderId)
    {
        //        $order = orderHandler::loadCMSOrder($orderId);
        //        return $order->getField('deliveryMode');
    }
    
    // PVZ
    //public static function getCityPVZ($city,$default = false){
    //        if(is_array($city)){
    //            $default = $city['default'];
    //            $city    = $city['city'];
    //        }
    //
    //        $arReturn = self::_getCityPVZ($city,$default);
    //
    //        if(!$arReturn['success'] && empty($arReturn['result']) && strpos($city,Tools::getMessage('SIGN_YO'))){
    //            $arReturn = self::_getCityPVZ(str_replace(Tools::getMessage('SIGN_YO'),Tools::getMessage('SIGN_YE'),$city),$default);
    //        }
    //
    //        if($_REQUEST[self::$MODULE_LBL.'action']) {
    //            echo Tools::jsonEncode($arReturn);
    //        }
    //
    //        return $arReturn;
    //}
    
    public static function getSavedPVZ($code)
    {
        //        if(is_array($code)){
        //            $code = $code['code'];
        //        }
        //
        //        $arReturn = array('success'=>false,'result'=>false);
        //
        //        $pickupPointController = new pickupPoints();
        //        if($code) {
        //            $obPVZ = $pickupPointController->getById($code);
        //            if($obPVZ){
        //                $arReturn = array('success' => true,  'result' => array(
        //                    'city'    => $obPVZ->getCity(),
        //                    'address' => $obPVZ->getAddress(),
        //                    'id'      => $obPVZ->getId()
        //                ));
        //            } else {
        //                $arReturn = array('success' => false, 'result' => 'Not found');
        //            }
        //        }
        //
        //        if($_REQUEST[self::$MODULE_LBL.'action']) {
        //            echo Tools::jsonEncode($arReturn);
        //        }
        //
        //        return $arReturn;
    }
    
    /**
     * Обновление заказа при выборе способа доставки в виджете при отправлении заявки в Catapulto через админку.
     *
     * @param $arRequest
     *
     * @return string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function updateOrderProps($arRequest)
    {
        $arAnswer = [
            'success' => true,
        ];
        
        if (
            !\Bitrix\Main\Loader::includemodule('sale')
            || !Deliveries::isActive()
            || !OrderPropsHandler::controlProps()
            || !$arRequest['orderId']
        ) {
            $arAnswer['success'] = false;
            echo Tools::jsonEncode($arAnswer);
        }
        
        //Update order properties
        if (!empty($arRequest['props'])) {
            $currentProps = OrderPropsTable::getByBitrixId(intval($arRequest['orderId']));
            $needReselect = false;
            if ($currentProps) {
                $otherProps      = json_decode($currentProps['OTHER'], true);
                $customCargoData = $otherProps['customCargo'] ?? [];
                $needReselect    = $otherProps['need_reselect'] ?? false;
            }
            
            //Добавляем данные о складе
            if (!empty($_SESSION['IPOL_CATAPULTO_DELIVERY']['RATE_WAREHOUSE'])) {
                if (isset($arRequest['props']['rate_result'])) {
                    $arRequest['props']['rate_result'] = json_decode($arRequest['props']['rate_result'], 1);
                }
                
                if (!is_array($arRequest['props']['rate_result'])) {
                    $arRequest['props']['rate_result'] = [];
                }
                
                $arRequest['props']['rate_result']['warehouse'] = $_SESSION['IPOL_CATAPULTO_DELIVERY']['RATE_WAREHOUSE'];
                unset($_SESSION['IPOL_CATAPULTO_DELIVERY']['RATE_WAREHOUSE']);
                
                $arRequest['props']['rate_result'] = Tools::jsonEncode($arRequest['props']['rate_result']);
            }
            
            $propData   = [];
            $otherProps = [
                'en_ensurance'    => 'N',
                'isPVZ'           => 'N',
                'forcePVZAddress' => '',
                'customCargo'     => $customCargoData,
                'need_reselect'   => $needReselect,
            ];
            foreach ($arRequest['props'] as $sCode => $sValue) {
                switch ($sCode) {
                    case 'rate_result_id':
                        $propData['RATE_RESULT_ID'] = $sValue;
                        break;
                    case 'rate_result':
                        $propData['RATE_RESULT'] = $sValue;
                        break;
                    case 'custom_gabs':
                        $propData['CUSTOM_GABS'] = Tools::jsonEncode($sValue); //  json_encode($sValue, JSON_UNESCAPED_UNICODE)
                        break;
                    case 'dadata':
                        $propData['DADATA_ADDR'] = $sValue;
                        break;
                    case 'new_insurance':
                        $otherProps['en_ensurance'] = ($sValue === 'true') ? 'Y' : 'N';
                        break;
                    case 'isPVZ': //this prop only for reselect rate
                        $otherProps['isPVZ'] = ($sValue === 'true') ? 'Y' : 'N';
                        break;
                    case 'PVZAddress': //this prop only for reselect rate
                        $otherProps['forcePVZAddress'] = $sValue;
                        break;
                    case 'reselected':
                        if ((bool)$sValue == true) {
                            $otherProps['need_reselect'] = false;
                        }
                        break;
                }
            }
            $propData['OTHER'] = Tools::jsonEncode($otherProps);
            if (!empty($propData)) {
                OrderPropsTable::saveProps(intval($arRequest['orderId']), $propData);
            }
            
            if (\Ipol\Catapulto\Option::get('updateOrderDelivery') === 'Y') {
                self::updateOrderDeliveryPrice($arRequest['orderId'], $arRequest['props']['rate_cost']);
            }
        }
        
        echo Tools::jsonEncode($arAnswer);
    }
    
    /**
     * Обновление стоимости доставки
     *
     * @param $orderId
     * @param $deliveryPrice
     *
     * @return void
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     */
    public static function updateOrderDeliveryPrice($orderId, $deliveryPrice)
    {
        $order = \Bitrix\Sale\Order::load($orderId);
        
        $shipmentCollection = $order->getShipmentCollection();
        
        foreach ($shipmentCollection as $shipment) {
            if (!$shipment->isSystem()) {
                $shipment->setField("PRICE_DELIVERY", $deliveryPrice);
                $shipment->setField('BASE_PRICE_DELIVERY', $deliveryPrice);
                break;
            }
        }
        
        $basketPrice = 0;
        
        /** @var \Bitrix\Sale\BasketItem $basketItem */
        foreach ($order->getBasket()->getBasketItems() as $basketItem) {
            $basketPrice += $basketItem->getPrice() * $basketItem->getQuantity();
        }
        
        $discounts = $order->getDiscount()->getApplyResult();
        
        if ($discounts['PRICES']['DELIVERY']["BASE_PRICE"] != $discounts['PRICES']['DELIVERY']["PRICE"]) {
            $order->getPaymentCollection()->current()->setField('SUM', $basketPrice + $discounts['PRICES']['DELIVERY']["PRICE"]);
        }
        else {
            $order->getPaymentCollection()->current()->setField('SUM', $basketPrice + $deliveryPrice);
        }
        
        $order->save();
    }
    
    public static function cancelOrderByBXIdAjax()
    {
        $errorMessage = self::cancelOrder(intval($_REQUEST['bitrixId']), 'BITRIX_ID');
        
        if (empty($errorMessage)) {
            echo json_encode(['r' => true]);
        }
        else {
            echo json_encode(['r' => false, 'mes' => $errorMessage]);
        }
    }
    
    public static function cancelOrderByRecIdAjax()
    {
        $errorMessage = self::cancelOrder(intval($_REQUEST['recId']));
        
        if (empty($errorMessage)) {
            echo json_encode(['r' => true]);
        }
        else {
            echo json_encode(['r' => false, 'mes' => $errorMessage]);
        }
    }
    
    public static function cancelOrdersByRecIdsAjax($ids)
    {
        $errorMessages = '';
        foreach ($ids['ids'] as $recId) {
            $error = self::cancelOrder(intval($recId));
            if (!empty($error)) {
                $errorMessages .= '[' . $recId . ']: ' . $error . "\n";
            }
        }
        
        if (empty($errorMessages)) {
            echo json_encode(['r' => true]);
        }
        else {
            echo json_encode(['r' => false, 'mes' => $errorMessages]);
        }
    }
    
    /**
     * @param $recId - record Id
     *
     * @return string - ERROR MESSAGE or empty string for success operation
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    private static function cancelOrder($recId, $filterBy = 'ID')
    {
        $catOrder = OrdersTable::getRow([
            'select' => [
                'ID',
                'BITRIX_ID',
                'KEY',
                'MAIN_STATUS'
            ],
            'filter' => [
                '=' . $filterBy => $recId
            ]
        ]);
        
        if (!$catOrder) {
            return Tools::getMessage('ERROR_ORDER_NOTFOUND');
        }
        
        if ($catOrder['MAIN_STATUS'] != 'in_proccess') {
            return Tools::getMessage('ERROR_ORDER_WRONGSTATUS');
        }
        $key = $catOrder['KEY'];
        if (empty($key)) {
            return Tools::getMessage('ERROR_ORDER_KEYNOTFOUND');
        }
        
        $cancelOrder = new CancelOrder();
        $obResponse  = $cancelOrder->cancelOrder($key);
        
        if (!$obResponse->isSuccess()) {
            $errMess = $obResponse->getResponse()->getMessage();
            if (empty($errMess)) {
                $errMess = Tools::getMessage('ERROR_CANCEL_UNKNOWN');
            }
            return $errMess;
        }
        
        //К сожалению, здесь непонятно что ожидать, точную информацию получить не удалось. В описании от сваггера здесь может быть ответ такого типа:
        // {"status": "string"}
        // На практике может прилететь и такой ответ: {"detail":"Страница не найдена."}, реальный ответ успеха: {"status":true}
        // От катапульты есть информация что может так же в ответе быть "success, failure"
        $status    = $obResponse->getResponse()->getStatus();
        $isSuccess = false;
        switch ($status) {
            case '1':
            case 'true':
            case 'success':
                $isSuccess = true;
                break;
        }
        if ($isSuccess) {
            //Update order status
            $res = StatusHandler::checkStatus($key); // true/false
            return ''; //success!!!
        }
        
        return Tools::getMessage('ERROR_CANCEL_UNKNOWN');
    }
    
}
