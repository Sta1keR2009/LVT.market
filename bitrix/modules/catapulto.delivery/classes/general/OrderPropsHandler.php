<?php

namespace Ipol\Catapulto;

use Bitrix\Sale\Order;
use Ipol\Catapulto\Bitrix\Tools;
use Ipol\Catapulto\Bitrix\Handler\Deliveries;

IncludeModuleLangFile(__FILE__);

class OrderPropsHandler extends abstractGeneral
{
    protected static $senderProp     = 'SENDER_CONTACT_ID';
    protected static $receiverProp   = 'RECEIVER_CONTACT_ID';
    protected static $rateResultProp = 'RATE_RESULT';
    protected static $rateProp       = 'RATE_RESULT_ID';
    protected static $customGabs     = 'GOODS_GABS';

    /**
     * @param $id
     * @param $arFields
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function onOrderCreate($id, $arFields)
    {
        if (
            !\Bitrix\Main\Loader::includemodule('sale')
            || !Deliveries::isActive()
            || !self::controlProps()
            || !$id
        ) {
            return;
        }

        if (Tools::getArrVal(WidgetHandler::getDeliveryTypeSavingLink(), $_REQUEST)) {
            $arVal = [];
            $sVal  = $_REQUEST[WidgetHandler::getDeliveryTypeSavingLink()];
            if (!empty($sVal)) {
                $arVal = json_decode($sVal, 1);
            }
            
            //Добавляем данные о складе
            if (!empty($_SESSION['IPOL_CATAPULTO_DELIVERY']['RATE_WAREHOUSE']) || !empty($arVal['sender_warehouse_id'])) {
                if (isset($arVal['rate_result'])) {
                    $arVal['rate_result'] = json_decode($arVal['rate_result'], 1);
                }
                
                if (!is_array($arVal['rate_result'])) {
                    $arVal['rate_result'] = [];
                }
                
                if (!empty($_SESSION['IPOL_CATAPULTO_DELIVERY']['RATE_WAREHOUSE'])) {
                    $arVal['rate_result']['warehouse'] = $_SESSION['IPOL_CATAPULTO_DELIVERY']['RATE_WAREHOUSE'];
                    unset($_SESSION['IPOL_CATAPULTO_DELIVERY']['RATE_WAREHOUSE']);
                }
                else {
                    $arWarehouse                       = WarehousesTable::getWarehouses(['=ID' => $arVal['sender_warehouse_id']]);
                    $arWarehouse                       = array_shift($arWarehouse);
                    $arWarehouse['CUSTOM']             = true;
                    $arWarehouse['FROM_SESSION']       = false;
                    $arVal['rate_result']['warehouse'] = $arWarehouse;
                }
                
                $arVal['rate_result'] = Tools::jsonEncode($arVal['rate_result']);
            }

            $propData = [];
            $isPVZ = 'N';
            $forcePVZAddress = '';
            foreach ($arVal as $sCode => $sValue) {
                $sPropCode = '';
                switch ($sCode) {
                    case 'rate_result_id':
                        $propData['RATE_RESULT_ID'] = $sValue;
                        break;
                    case 'rate_result':
                        $propData['RATE_RESULT'] = $sValue;
                        break;
                    case 'dadata':
                        $propData['DADATA_ADDR'] = $sValue;
                        break;
                    case 'isPVZ':
                        $isPVZ = $sValue ? 'Y' : 'N';
                        break;
                    case 'PVZAddress':
                        $forcePVZAddress = $sValue;
                        break;
                }

                if ($sPropCode && $sValue) {
                    self::updateProp($id, $sPropCode, $sValue);
                }
            }

            $propData['OTHER'] = Tools::jsonEncode([
                'en_ensurance' => (Option::get('mindEnsurance') === 'Y')?'Y':'N',
                'isPVZ' => $isPVZ,
                'forcePVZAddress' => $forcePVZAddress,
            ]);

            if (!empty($propData)) {
                OrderPropsTable::saveProps( intval($id), $propData );
            }
        }
    }

    /**
     * @param int $action � 1-add/update, 2-delete
     *
     * @return bool|null
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function controlProps(int $action = 1): bool
    {
        if (!\Bitrix\Main\Loader::includeModule("sale")) {
            return false;
        }
        $arProps = [];

        $return = true;
        foreach ($arProps as $arProp) {
            $subReturn = self::handleProp($arProp, $action);
            if (!$subReturn) {
                $return = $subReturn;
            }
        }

        return $return;
    }

    /**
     * @return string
     */
    public static function getSenderProp(): string
    {
        return CATAPULTO_DELIVERY_LBL . self::$senderProp;
    }

    /**
     * @return string
     */
    public static function getReceiverProp(): string
    {
        return CATAPULTO_DELIVERY_LBL . self::$receiverProp;
    }

    /**
     * @return string
     */
    public static function getRateResultProp(): string
    {
        return CATAPULTO_DELIVERY_LBL . self::$rateResultProp;
    }


    /**
     * @return string
     */
    public static function getRateProp(): string
    {
        return CATAPULTO_DELIVERY_LBL . self::$rateProp;
    }

    public static function getCustomGabsProp(): string
    {
        return CATAPULTO_DELIVERY_LBL . self::$customGabs;
    }

    /**
     * @param $arProp
     * @param $action
     *
     * @return bool
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected static function handleProp($arProp, $action): bool
    {
        $tmpGet       = \CSaleOrderProps::GetList(["SORT" => "ASC"], ["CODE" => $arProp['CODE']]);
        $existedProps = [];
        while ($tmpElement = $tmpGet->Fetch()) {
            $existedProps[$tmpElement['PERSON_TYPE_ID']] = $tmpElement['ID'];
        }

        if ($action == '1') {
            $return   = true;
            $arPayers = \Bitrix\Sale\Internals\PersonTypeTable::getList(
                [
                    'order'  => ['ID' => 'ASC'],
                    'filter' => ['ACTIVE' => 'Y']
                ]
            )->fetchAll();

            foreach ($arPayers as $arPayer) {
                $tmpVal   = \CSaleOrderPropsGroup::GetList(["SORT" => "ASC"], ["PERSON_TYPE_ID" => $arPayer['ID']], false, ['nTopCount' => '1'])->Fetch();
                $arFields = [
                    "PERSON_TYPE_ID"  => $arPayer['ID'],
                    "NAME"            => $arProp['NAME'],
                    "TYPE"            => "TEXT",
                    "REQUIED"         => "N",
                    "DEFAULT_VALUE"   => "",
                    "SORT"            => 100,
                    "CODE"            => $arProp['CODE'],
                    "USER_PROPS"      => "N",
                    "IS_LOCATION"     => "N",
                    "IS_LOCATION4TAX" => "N",
                    "PROPS_GROUP_ID"  => $tmpVal['ID'],
                    "SIZE1"           => 10,
                    "SIZE2"           => 1,
                    "DESCRIPTION"     => $arProp['DESCR'],
                    "IS_EMAIL"        => "N",
                    "IS_PROFILE_NAME" => "N",
                    "IS_PAYER"        => "N",
                    "IS_FILTERED"     => "Y",
                    "IS_ZIP"          => "N",
                    "UTIL"            => "Y"
                ];

                if (!array_key_exists($arPayer['ID'], $existedProps)) {
                    if (!\CSaleOrderProps::Add($arFields)) {
                        $return = false;
                    }
                }
            }
            return $return;
        }
        elseif ($action == '2') {
            foreach ($existedProps as $existedPropId) {
                if (!\CSaleOrderProps::Delete($existedPropId)) {
                    echo "Error delete prop id" . $existedPropId . "<br>";
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param $orderId
     * @param $sCode
     * @return null
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function loadProp($orderId, $sCode)
    {
        $order      = Order::load($orderId);
        $collection = $order->getPropertyCollection();

        foreach ($collection as $property) {
            if (is_object($property)) {
                if ($property->getField('CODE') == $sCode) {
                    return $property->getField('VALUE');
                }
            }
        }

        return null;
    }

    /**
     * @param $orderId
     * @param $sCode
     * @param $sValue
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\NotImplementedException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function updateProp($orderId, $sCode, $sValue)
    {
        $order      = Order::load($orderId);
        $collection = $order->getPropertyCollection();

        foreach ($collection as $property) {
            if (is_object($property)) {
                if ($property->getField('CODE') == $sCode) {
                    $property->setField('VALUE', $sValue);
                    $order->save();
                    break;
                }
            }
        }
    }

    public static function onBeforeOrderCreate(&$order, &$arFields)
    {
        return false;
    }
}
