<?php

namespace Ipol\Catapulto;

use \Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields;
use \Bitrix\Main\ORM\Fields\BooleanField;
use \Bitrix\Main\ORM\Fields\IntegerField;
use \Bitrix\Main\ORM\Fields\StringField;
use \Bitrix\Main\ORM\Fields\ExpressionField;
use \Bitrix\Main\ORM\Fields\Relations\Reference;
use \Bitrix\Main\ORM\Query\Filter\ConditionTree;
use Bitrix\Main\Result;
use Ipol\Catapulto\Admin\BitrixLoggerController;
use Ipol\Catapulto\Bitrix\Entity\Cache;
use Ipol\Catapulto\Bitrix\Entity\Encoder;
use Ipol\Catapulto\Bitrix\Tools;
use Ipol\Catapulto\Catapulto\CatapultoApplication;

/**
 * Class WarehousesFreeDeliveryTable
 *
 * @package Ipol\Catapulto
 */
class WarehousesFreeDeliveryTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'catapulto_delivery_warehouses_free_delivery';
    }
    
    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return [
            new IntegerField('ID', ['primary' => true, 'autocomplete' => true, 'size' => 11]),
            new IntegerField('WAREHOUSE_ID', ['size' => 11]),
            new StringField('BX_LOC', [
                'size'       => 12,
                //'required'   => true, //отменяем, потому что используем свой валидатор, чтобы сообщать об ошибки заполнения
                'validation' => function () {
                    return [
                        function ($value, $primary, $row, $field) {
                            if ($value === null || $value === '') {
                                if ($primary['ID']) {
                                    return Tools::getMessage('WH_NOT_EMPTY_ISSUE_INPUT_BX_LOC');
                                }
                                else {
                                    return Tools::getMessage('WH_NEED_INPUT_BX_LOC') . ($row['WAREHOUSE_ID'] ? ' (' . Tools::getMessage('WH_NAME') . $row['WAREHOUSE_ID'] . ')' : '');
                                }
                            }
                            return true;
                        },
                        new \Bitrix\Main\ORM\Fields\Validators\LengthValidator(null, 12),
                    ];
                }
            ]),
            new StringField('CITY_FIAS_ID', ['size' => 50]),
            new StringField('REGION_FIAS_ID', ['size' => 50]),
            new IntegerField('FREE_COURIER_FROM', ['size' => 7]),
            new IntegerField('FREE_PICKUP_FROM', ['size' => 7]),
            'WAREHOUSE_SETUP' => (new Fields\Relations\Reference('FREE_DELIVERY_SETUP', WarehousesTable::getEntity(), (new ConditionTree)->whereColumn('this.WAREHOUSE_ID', 'ref.ID'))),
        ];
    }
    
    public static function onBeforeAdd(\Bitrix\Main\ORM\Event $event)
    {
        $result = new \Bitrix\Main\ORM\EventResult();
        $data   = $event->getParameter('fields');
        
        if (!array_key_exists('WAREHOUSE_SETUP', $data)) {
            return $result;
        }
        
        foreach ($data['WAREHOUSE_SETUP']->getFreeDeliverySetup() as $freeDelivery) {
            if (empty($freeDelivery->getBxLoc())) {
                continue;
            }
            
            $cityFias   = $freeDelivery->getCityFiasId();
            $regionFias = $freeDelivery->getRegionFiasId();
            $newBxLoc   = $freeDelivery->getBxLoc();
            
            $needUpdate = $newBxLoc && (empty($cityFias) && empty($regionFias));
            
            if ($needUpdate) {
                $fiasResult = static::getFiasId($newBxLoc);
                
                if ($fiasResult->isSuccess()) {
                    $arData = $fiasResult->getData();
                    if (array_key_exists('CITY_FIAS_ID', $arData)) {
                        $freeDelivery->setCityFiasId($arData['CITY_FIAS_ID']);
                    }
                    elseif (array_key_exists('REGION_FIAS_ID', $arData)) {
                        $freeDelivery->setRegionFiasId($arData['REGION_FIAS_ID']);
                    }
                }
                else {
                    $result->setErrors($fiasResult->getErrors());
                    return $result;
                }
            }
        }
        
        return $result;
    }
    
    
    public static function onBeforeUpdate(\Bitrix\Main\ORM\Event $event)
    {
        $result = new \Bitrix\Main\ORM\EventResult();
        $data   = $event->getParameter('fields');
        
        if (!array_key_exists('WAREHOUSE_SETUP', $data)) {
            return $result;
        }
        
        foreach ($data['WAREHOUSE_SETUP']->getFreeDeliverySetup() as $freeDelivery) {
            if (empty($freeDelivery->getBxLoc())) {
                continue;
            }
            
            $cityFias   = $freeDelivery->getCityFiasId();
            $regionFias = $freeDelivery->getRegionFiasId();
            $oldBxLoc   = $freeDelivery->remindActualBxLoc();
            $newBxLoc   = $freeDelivery->getBxLoc();
            
            $needUpdate = $newBxLoc && ((empty($cityFias) && empty($regionFias)) || ($oldBxLoc && $oldBxLoc !== $newBxLoc));
            
            if ($needUpdate) {
                $fiasResult = static::getFiasId($newBxLoc);
                
                if ($fiasResult->isSuccess()) {
                    $arData = $fiasResult->getData();
                    if (array_key_exists('CITY_FIAS_ID', $arData)) {
                        $freeDelivery->setCityFiasId($arData['CITY_FIAS_ID']);
                        $freeDelivery->setRegionFiasId('');
                    }
                    elseif (array_key_exists('REGION_FIAS_ID', $arData)) {
                        $freeDelivery->setCityFiasId('');
                        $freeDelivery->setRegionFiasId($arData['REGION_FIAS_ID']);
                    }
                }
                else {
                    $result->setErrors($fiasResult->getErrors());
                    return $result;
                }
            }
        }
        
        return $result;
    }
    
    public static function getFiasId($bxLoc): Result
    {
        $result = new Result();
        
        $customBaseUrl = '';
        if (Option::get('isTest') === 'Y') {
            $customBaseUrl = Option::get('customApiUrl');
        }
        
        $catapulto = new CatapultoApplication(
            Option::get('apikey'),
            $customBaseUrl,
            (int)Option::get('timeout'),
            new Encoder(),
            new Cache(),
            new BitrixLoggerController('Catapulto_API')
        );
        
        $arLocation     = static::getLocationDataByCode($bxLoc);
        $locationName   = $arLocation['NAME'];
        $locationType   = $arLocation['TYPE_CODE'] === 'CITY' ? 'city' : 'region';
        $locationRegion = $locationType === 'city' ? $arLocation['REGION'] : '';
        
        $obResponse = $catapulto->geoSearchFias($locationName, $locationType, $locationRegion);
        
        if ($obResponse->isSuccess()) {
            $field = $locationType === 'city' ? 'CITY_FIAS_ID' : 'REGION_FIAS_ID';
            $result->setData([
                $field => $obResponse->getResponse()->getFiasId()
            ]);
        }
        else {
            if ($obResponse->isError()) {
                $result->addError(new \Bitrix\Main\Error($obResponse->getError()->getMessage()));
            }
            else {
                $result->addError(new \Bitrix\Main\Error(Tools::getMessage('WH_API_FIAS_ERRROR')));
            }
        }
        
        return $result;
    }
    
    public static function getLocationDataByCode($code): array
    {
        $arLocation = \Bitrix\Sale\Location\LocationTable::getList([
            'filter' => ['=CODE' => $code, '=NAME.LANGUAGE_ID' => LANGUAGE_ID, '=PARENT.NAME.LANGUAGE_ID' => LANGUAGE_ID],
            'select' => ['NAME_RU' => 'NAME.NAME', 'ID', 'PARENT_NAME' => 'PARENT.NAME.NAME', 'TYPE_CODE' => 'TYPE.CODE']
        ])->fetch();
        
        if (!$arLocation) {
            return [];
        }
        
        $arResult = [
            'ID'          => $arLocation['ID'],
            'NAME'        => $arLocation['NAME_RU'],
            'REGION'      => '',
            'PARENT_NAME' => $arLocation['PARENT_NAME'],
            'TYPE_CODE'   => $arLocation['TYPE_CODE'],
        
        ];
        
        $path = \Bitrix\Sale\Location\LocationTable::getPathToNode(
            $arLocation['ID'],
            [
                'filter' => [
                    '=NAME.LANGUAGE_ID' => LANGUAGE_ID
                ],
                'select' => [
                    'NAME_RU'   => 'NAME.NAME',
                    'TYPE_CODE' => 'TYPE.CODE',
                ]
            ]
        );
        
        foreach ($path as $item) {
            if ($item['TYPE_CODE'] === 'CITY') {
                $arResult['NAME'] = $item['NAME_RU'];
            }
            
            if ($item['TYPE_CODE'] === 'REGION') {
                $arResult['REGION'] = $item['NAME_RU'];
            }
        }
        
        return $arResult;
    }
}
