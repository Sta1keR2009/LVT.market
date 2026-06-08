<?php

namespace Ipol\Catapulto\Bitrix\Handler;


class GoodsPicker
{
    /**
     * @var string if defined - complects will be handled as separeted goods (not recommended at all)
     */
    protected static $complectsBlocker = 'CATAPULTO_DELIVERY_DOWNCOMPLECTS';

    public static function fromBasket()
    {
        return self::getBasketGoods(array("FUSER_ID" => \CSaleBasket::GetBasketUserID(),"ORDER_ID" => "NULL", "LID"=>SITE_ID));
    }

    public static function fromOrder($orderId)
    {
        if($orderId)
        {
            return self::getBasketGoods(array('ORDER_ID' => $orderId));
        }

        return false;
    }

    public static function fromShipment()
    {

    }

    public static function addGoodsQRs(&$arGoods,$bitrixId)
    {
        $order = \Bitrix\Sale\Order::load($bitrixId);

        $shipments = $order->getShipmentCollection();
        foreach ($shipments as $shipment)
        {
            $items = $shipment->getShipmentItemCollection();
            foreach ($items as $item)
            {
                /** @var \Bitrix\Sale\BasketItem $basketItem  */
                $basketItem = $item->getBasketItem();
                $stores = $item->getShipmentItemStoreCollection();
                foreach ($stores as $store)
                {
                    $storeId = $store->getStoreId();
                    $mark = $store->getMarkingCode();

                    foreach ($arGoods as $key => $stuff){
                        if($arGoods[$key]['PRODUCT_ID'] === $basketItem->getProductId()){
                            if(!array_key_exists('QR',$arGoods[$key])){
                                $arGoods[$key]['QR'] = array();
                            }
                            $arGoods[$key]['QR'] []= $mark;
                        }
                    }
                }
            }
        }
    }

    /**
     * @param array $arGoods of type id => quantity
     * @param bool|string|int $source - id of order
     * @return array
     */
    public static function fromArray($arGoods,$source=false)
    {
        $allItems = ($source) ? self::fromOrder($source) : self::fromBasket();
        $arReturn = array();

        foreach($allItems as $key => $val){
            if(array_key_exists($val['PRODUCT_ID'],$arGoods)){
                $_val = $val;
                $_val ['QUANTITY'] = $arGoods[$val['PRODUCT_ID']];
                $arReturn []= $_val;
            }
        }

        return $arReturn;
    }

    protected static function getBasketGoods($arFilter = array())
    {
        $arGoods = array();

        $dbBasketItems = \CSaleBasket::GetList(
            array(),
            $arFilter,
            false,
            false,
            array("ID","PRODUCT_ID", "PRICE","BASE_PRICE", "QUANTITY",'CAN_BUY','DELAY',"NAME","DIMENSIONS","WEIGHT","SET_PARENT_ID","LID","CURRENCY","VAT_RATE","VAT_INCLUDED")
        );

        while ($arItems = $dbBasketItems->Fetch())
            if ($arItems['CAN_BUY'] == 'Y' && $arItems['DELAY'] == 'N'){
                $arItems['DIMENSIONS'] = unserialize($arItems['DIMENSIONS']);
                $arGoods[]=$arItems;
            }

        self::handleComplects($arGoods);

        return $arGoods;
    }

    protected static function handleComplects(&$arGoods)
    {
        $arComplects = array();
        foreach($arGoods as $good) {
            if (
                array_key_exists('SET_PARENT_ID', $good) &&
                $good['SET_PARENT_ID'] &&
                $good['SET_PARENT_ID'] != $good['ID']
            ) {
                $arComplects[$good['SET_PARENT_ID']] = true;
            }
        }
        if(defined(self::$complectsBlocker) && constant(self::$complectsBlocker) == true){
            foreach($arGoods as $key => $good) {
                if (array_key_exists($good['ID'], $arComplects)) {
                    unset($arGoods[$key]);
                }
            }
        }else {
            foreach ($arGoods as $key => $good) {
                if (
                    array_key_exists('SET_PARENT_ID', $good) &&
                    array_key_exists($good['SET_PARENT_ID'], $arComplects) &&
                    $good['SET_PARENT_ID'] != $good['ID']
                ) {
                    unset($arGoods[$key]);
                }
            }
        }
    }

    public static function addBasketGoodProperties(&$arGoods,$arPropertiesCode)
    {
        $arSKUGoods = array();
        $arRequest  = array();
        foreach ($arGoods as $arGood){
            if(!array_key_exists($arGood['LID'],$arRequest)){
                $arRequest[$arGood['LID']] = array();
            }
            $arRequest[$arGood['LID']] []= $arGood['PRODUCT_ID'];
            if($_tmpGoods = \CCatalogSku::GetProductInfo($arGood['PRODUCT_ID'])){
                $arSKUGoods[$arGood['PRODUCT_ID']] = $_tmpGoods['ID'];
                $arRequest[$arGood['LID']][$arGood['PRODUCT_ID']] = $_tmpGoods['ID'];
            }
        }

        $arGoodProperties = self::getGoodsProperties($arRequest,$arPropertiesCode);

        foreach($arGoodProperties as $arGoodProperty)
        {
            foreach ($arGoods as $key => $arGood){
                if($arGood['PRODUCT_ID'] == $arGoodProperty['ID'] && $arGood['LID'] == $arGoodProperty['LID'])
                {
                    if(!array_key_exists('PROPERTIES',$arGoods[$key]))
                    {
                        $arGoods[$key]['PROPERTIES'] = array();
                    }
                    foreach ($arPropertiesCode as $propertyCode){
                        if($propertyCode && array_key_exists($propertyCode,$arGoodProperty)) {
                            $arGoods[$key]['PROPERTIES'][$propertyCode] = $arGoodProperty[$propertyCode];
                        }
                    }
                    break;
                }
            }
        }

        // picking from parent goods
        foreach($arPropertiesCode as $propertyCode) {
            if(!$propertyCode) {
                continue;
            }
            
            foreach ($arGoods as $key => $arGood) {
                if(
                    (
                        !array_key_exists('PROPERTIES',$arGood)   ||
                        !array_key_exists($propertyCode,$arGood['PROPERTIES']) ||
                        !$arGood['PROPERTIES'][$propertyCode]
                    ) && array_key_exists($arGood['PRODUCT_ID'],$arSKUGoods)
                ){
                    foreach($arGoodProperties as $arGoodProperty)
                    {
                        if($arSKUGoods[$arGood['PRODUCT_ID']] == $arGoodProperty['ID'] && $arGood['LID'] == $arGoodProperty['LID']){
                            if(!array_key_exists('PROPERTIES',$arGoods[$key]))
                            {
                                $arGoods[$key]['PROPERTIES'] = array();
                            }
                            if(array_key_exists($propertyCode,$arGoodProperty)) {
                                $arGoods[$key]['PROPERTIES'][$propertyCode] = $arGoodProperty[$propertyCode];
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param array $arGoods - array of LID => goods
     * @param array $arPropertiesCode - array of propertyCodes
     * @return array
     */
    public static function getGoodsProperties($arGoods,$arPropertiesCode)
    {
        $hasIblock = \cmodule::includeModule('iblock');
        if(!$hasIblock)
            return array();
        foreach ($arPropertiesCode as $key => $code){
            $arPropertiesCode[$key] = "PROPERTY_".$code;
        }

        $arReturn = array();

        $arSelect = array_merge(array('ID','LID'),$arPropertiesCode);

        foreach($arGoods as $lid => $arIDs)
        {
            $bdRequest = \CIBlockElement::GetList(array(),array('ID'=> $arIDs,'LID'=>$lid),false,false,$arSelect);
            while($arGood = $bdRequest->Fetch()) {
                $_arGood = array();
                foreach ($arGood as $key => $value) {
                    if ($key == 'ID' || $key == 'LID') {
                        $_arGood[$key] = $arGood[$key];
                    } elseif (strpos($key, 'PROPERTY_') === 0 && strpos($key, '_VALUE') && !strpos($key, '_VALUE_ID')) {
                        $property = substr($key, 9);
                        $_arGood[substr($property, 0, strlen($property) - 6)] = $arGood[$key];
                    }
                }
                $arReturn [] = $_arGood;
            }
        }

        return $arReturn;
    }
}