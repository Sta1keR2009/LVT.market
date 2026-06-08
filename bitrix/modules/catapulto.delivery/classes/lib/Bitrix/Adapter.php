<?php

namespace Ipol\Catapulto\Bitrix;

use Ipol\Catapulto\Bitrix\Adapter\Cargo;
use \Ipol\Catapulto\Bitrix\Adapter\Location;
use Ipol\Catapulto\Bitrix\Entity\DefaultGabarites;
use Ipol\Catapulto\Bitrix\Handler\PaySystems;
use \Ipol\Catapulto\Bitrix\Tools;
use Ipol\Catapulto\OperatorsTable;
use Ipol\Catapulto\Option;
use Ipol\Catapulto\OrdersTable;
use Ipol\Catapulto\OrderHandler;

// TODO: make da big cache here from oderds and other continuing stuff

/**
 * Class Adapter
 *
 * @package namespace Ipol\Catapulto\Bitrix\Adapter
 */
class Adapter
{

    // BITRIX
    // order
    public static function getOrderData($bitrixId,$mode=1)
    {
        if(OrdersTable::getByBitrixId($bitrixId))
            return OrderHandler::loadUploadOrder($bitrixId,$mode);
        elseif($mode === 1)
            return OrderHandler::loadCMSOrder($bitrixId);

        return false;
    }

    public static function getCargo($arItems)
    {
        $obCargo = new Cargo(new DefaultGabarites());

        // Something wrong with basket items CAN_BUY flag or basket was empty at all
        if (empty($arItems)) {
            $arItems = [Tools::makeSimpleGood()];
        }

        $obCargo->set($arItems);

        return $obCargo;
    }

    // statuses
    public static function statusIsSending($status)
    {
        return (in_array($status,array('NEW','ERROR')));
    }
    public static function statusIsFinal($status)
    {
        return (in_array($status,array('GIVEN','ANNULED')));
    }
    public static function statusIsReady($status)
    {
        // todo: check sending status
        return (in_array($status,array('OK','REGISTRED')));
    }
    public static function statusIsCancelable($status)
    {
        // todo: check sending status
        return (in_array($status,array('in_proccess')));
    }

    // NDS
    public static function getNDSTypes()
    {
        return array(0=>'0','10'=>'10%','20'=>'20%');
    }

    // Some tools for module tables

    /**
     * Make hash to represent cell dimensions used for delivery point container dimensions check
     *
     * @param int $a this and below are cell dimensions, order does not matter
     * @param int $b
     * @param int $c
     * @return int
     */
    public static function makeDimensionsHash($a, $b, $c)
    {
        $arr = [$a, $b, $c];

        array_walk($arr, function (&$val, $key) {$val = (int)floor($val / 10);});
        sort($arr);

        return ($arr[0] + $arr[1]*1000 + $arr[2]*1000000);
    }

    /**
     * Make sync hash which used for update check
     *
     * @param array $data
     * @return string
     */
    public static function makeSyncHash($data)
    {
        if (!is_array($data))
            $data = array($data);

        return md5('SYNCHASH_'.implode('|', $data));
    }

    // Payments
    public static function getPaymentCorresponds()
    {
        $arPayments = PaySystems::getAll();
        $nal        = Option::get('payNal');
        $card       = Option::get('payCard');
        if (!is_array($nal)) $nal = [];
        if (!is_array($card)) $card = [];

        foreach ($arPayments as $payId => $payName) {
            $arPayments[$payId] = 'BILL';
            if (in_array($payId, $nal)) $arPayments[$payId] = 'CASH';
            if (in_array($payId, $card)) $arPayments[$payId] = 'CARD';
            if (in_array($payId, $nal) && in_array($payId, $card)) $arPayments[$payId] = 'CASH_CARD';
        }

        return $arPayments;
    }

    /**
     * Get CMS location data
     *
     * @param string $possiblyId Bitrix location Id or Code
     *
     * @return \Ipol\Catapulto\Core\Delivery\Location|false
     */
    public static function getCmsLocation($possiblyId)
    {
        $location = false;
        static $cache = [];

        $cacheId = md5('getCmsLocation|' . $possiblyId);

        if (isset($cache[$cacheId])) {
            return $cache[$cacheId];
        }

        $cacheTime     = 3600;
        $cachePath     = '/' . CATAPULTO_DELIVERY . '/' . 'getCmsLocation';
        $cacheInstance = \Bitrix\Main\Data\Cache::createInstance();

        if ($cacheInstance->initCache($cacheTime, $cacheId, $cachePath)) {
            $location = $cacheInstance->GetVars();
        }
        else {
            $bxLocation = new Location($possiblyId);

            if ($bxLocation->getBxId()) {
                $location = $bxLocation->getCoreLocation();
                if ($cacheInstance->startDataCache()) {
                    $cacheInstance->endDataCache($location);
                }
            }
        }

        return $cache[$cacheId] = $location;
    }

    /**
     * Convert address from Catapulto format to string
     *
     * @param array $arAddress
     *
     * @return string
     */
    public static function getAddressString($arAddress)
    {
        if (!is_array($arAddress)) {
            return null;
        }

        $address = [
            $arAddress['zip'],
            !empty($arAddress['region1']) ? $arAddress['region1'] . ' ' . $arAddress['region1_type'] : null,
            !empty($arAddress['region2']) ? $arAddress['region2'] . ' ' . $arAddress['region2_type'] : null,
            !empty($arAddress['region3']) ? $arAddress['region3'] . ' ' . $arAddress['region3_type'] : null,
            !empty($arAddress['locality']) ? $arAddress['locality_type'] . ' ' . $arAddress['locality'] : null,
            !empty($arAddress['street']) ? $arAddress['street_type'] . $arAddress['street'] : null,
            $arAddress['building'],
            $arAddress['door_number']
        ];

        // remove empty values
        $address = array_filter($address, function ($val) {
            return (!empty($val) && $val !== "empty");
        });

        return implode(', ', $address);
    }

    /**
     * Callback function
     * Converts bool values from grid to text values
     *
     * @param string $val grid value
     * @param string $k   grid key
     */
    public static function convertBooleanValues(&$val, $k)
    {
        $boolMap = OrdersTable::getBooleanFieldsMap();
        if (in_array($k, $boolMap) && strlen($val) > 0) {
            $val = Tools::getMessage('BOOLEAN_' . $val);
        }
    }

    /**
     * Get
     *
     * @param $operatorId
     *
     * @return string
     */
    public static function getOperatorForGrid($operatorId) {
        if ($operator = OperatorsTable::getByOperatorId($operatorId)) {
            $result = null;
            if (!empty($operator['ICON'])) {
                $result .= sprintf("<img src='%s' style='display: block; height: 20px; margin: 0 auto 5px auto;'>", $operator['ICON']);
            }

            $result .= sprintf("<div style='text-align: center;font-size: 0.9em;'>%s</div>",$operator['OPERATOR_DISPLAY']);
        }

        return $result ?? $operatorId;
    }

    /**
     * ������� ������ ���������� ��� �������� ���������� ������������ ��� ������� ���������
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function getOperatorsForOptions(array $codes = [], string $fieldType = 'text', string $defaultValue = '')
    {
        $result = [];

        $operators = OperatorsTable::getList();
        while ($item = $operators->fetch()) {
            if ($item['OP_ENABLED'] != 'Y') continue;
            foreach ($codes as $code) {
                $result[$code.'_'.$item['OPERATOR_ID']] = [
                    'group' => $code,
                    'hasHint' => 'N',
                    'default' => $defaultValue,
                    'type' => $fieldType,
                    'name' => ($fieldType === 'text') ? $item['OPERATOR_DISPLAY'] : '',
                    'additionalData' => [
                        'id' => $item['OPERATOR_ID']
                    ],
                    'multiple' => false,
                ];
            }
        }

        return $result;
    }
    
    /**
     * @param $warehouseId
     * @param $operator
     *
     * @return string
     */
    public static function getSenderTerminalCode($warehouseId, $operator)
    {
        $terminal = '';
        if ($warehouseId) {
            $arWarehouse = \Ipol\Catapulto\WarehousesTable::getWarehouses(['=ID' => $warehouseId]);
            if ($arWarehouse) {
                $arWarehouse = array_shift($arWarehouse);
                $terminal    = $arWarehouse['OPERATORS_SETUP'][$operator]['DEFAULT_TERMINAL'];
            }
        }
        
        return $terminal;
    }
}
