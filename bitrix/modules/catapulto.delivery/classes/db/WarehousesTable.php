<?php

namespace Ipol\Catapulto;

use \Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\EventResult;
use Bitrix\Main\ORM\Fields;
use \Bitrix\Main\ORM\Fields\BooleanField;
use \Bitrix\Main\ORM\Fields\IntegerField;
use \Bitrix\Main\ORM\Fields\StringField;
use \Bitrix\Main\ORM\Fields\ExpressionField;
use \Bitrix\Main\ORM\Fields\Relations\Reference;
use \Bitrix\Main\ORM\Query\Filter\ConditionTree;
use Bitrix\Main\Result;
use Ipol\Catapulto\Bitrix\Tools;

/**
 * Class WarehousesTable
 *
 * @package Ipol\Catapulto
 */
class WarehousesTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'catapulto_delivery_warehouses';
    }
    
    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        $conditionLocationName = new ConditionTree();
        $conditionLocationName->whereColumn('this.BX_LOCATION.ID', 'ref.LOCATION_ID');
        $conditionLocationName->where('ref.LANGUAGE_ID', 'ru');
        
        return [
            new IntegerField('ID', ['primary' => true, 'autocomplete' => true, 'size' => 11]),
            new IntegerField('CATAPULTO_CITY_ID', ['size' => 11]),
            new IntegerField('CATAPULTO_CITY_INDEX', ['size' => 6]),
            new IntegerField('CATAPULTO_CONTACT_ID', ['size' => 11]),
            new StringField('DELIVERY_FROM', ['size' => 30]),
            new StringField('FREE_COURIER_FROM', ['size' => 30]),
            new StringField('FREE_PICKUP_FROM', ['size' => 30]),
            new BooleanField('POA_ENABLED', ['values' => ['0', '1'], 'default' => '1']),
            new StringField('POA_FROM_DATA', []),
            new StringField('BX_LOC', ['size' => 12]),
            new StringField('LAT', ['size' => 12]),
            new StringField('LON', ['size' => 12]),
            new BooleanField('ACTIVE', ['values' => ['0', '1'], 'default' => '0']),
            'FREE_DELIVERY_SETUP' => (new Fields\Relations\OneToMany('FREE_DELIVERY_SETUP', WarehousesFreeDeliveryTable::class, 'WAREHOUSE_SETUP'))
                ->configureCascadeSavePolicy(Fields\Relations\CascadePolicy::FOLLOW)
                ->configureCascadeDeletePolicy(Fields\Relations\CascadePolicy::FOLLOW),
            'OPERATORS_SETUP'     => (new Fields\Relations\OneToMany('OPERATORS_SETUP', WarehousesOperatorsTable::class, 'WAREHOUSE_SETUP'))
                ->configureCascadeSavePolicy(Fields\Relations\CascadePolicy::FOLLOW)
                ->configureCascadeDeletePolicy(Fields\Relations\CascadePolicy::FOLLOW),
            'BX_LOCATION'         => (new Fields\Relations\Reference('BX_LOCATION', \Bitrix\Sale\Location\LocationTable::class, (new ConditionTree)->whereColumn('this.BX_LOC', 'ref.CODE'), ['join_type' => 'LEFT'])),
            'BX_LOCATION_NAME'    => (new Fields\Relations\Reference('BX_LOCATION_NAME', \Bitrix\Sale\Location\Name\LocationTable::class, $conditionLocationName, ['join_type' => 'LEFT'])),
        ];
    }
    
    public static function saveItems(array $requestData): Result
    {
        $result = new Result();
        
        foreach ($requestData['WH_LAT'] as $key => $value) {
            $obWarehouse = static::getList([
                'filter' => ['=ID' => $key],
                'select' => ['*', 'BX_LOCATION', 'BX_LOCATION_NAME', 'OPERATORS_SETUP', 'FREE_DELIVERY_SETUP']
            ])->fetchObject();
            
            //Если склада с ID нет, нужно создать пустой объект и сохранить его как новый склад
            if (!$obWarehouse || !$obWarehouse->getId()) {
                $obWarehouse = static::createObject();
                //$obOperators = WarehousesOperatorsTable::createObject();
                //$obWarehouse->addToOperators($obOperators);
            }
            
            //Данные доверенности для ДЛ
            $poaFromData = [
                'FIO'             => trim($requestData['WH_POA_FROM_DATA_FIO'][$key] ?? ''),
                'EMAIL'           => trim($requestData['WH_POA_FROM_DATA_EMAIL'][$key] ?? ''),
                'PASSPORT_SERIA'  => trim($requestData['WH_POA_FROM_DATA_PASSPORT_SERIA'][$key] ?? ''),
                'PASSPORT_NUMBER' => trim($requestData['WH_POA_FROM_DATA_PASSPORT_NUMBER'][$key] ?? ''),
                'PASSPORT_DATE'   => trim($requestData['WH_POA_FROM_DATA_PASSPORT_DATE'][$key] ?? ''),
            ];
            
            //Основные данные WH
            $obWarehouse->set('CATAPULTO_CITY_ID', $requestData['WH_CATAPULTO_CITY_ID'][$key]);
            $obWarehouse->set('CATAPULTO_CITY_INDEX', $requestData['WH_CATAPULTO_CITY_INDEX'][$key]);
            $obWarehouse->set('CATAPULTO_CONTACT_ID', $requestData['WH_CATAPULTO_CONTACT_ID'][$key]);
            $obWarehouse->set('DELIVERY_FROM', $requestData['WH_DELIVERY_FROM'][$key]);
            $obWarehouse->set('FREE_COURIER_FROM', $requestData['WH_FREE_COURIER_FROM'][$key]);
            $obWarehouse->set('FREE_PICKUP_FROM', $requestData['WH_FREE_PICKUP_FROM'][$key]);
            $obWarehouse->set('POA_ENABLED', ($requestData['WH_POA_ENABLED'][$key] === 'Y' ? '1' : '0'));
            $obWarehouse->set('POA_FROM_DATA', Tools::jsonEncode($poaFromData));
            $obWarehouse->set('BX_LOC', $requestData['WH_BXLOC'][$key]);
            $obWarehouse->set('LAT', $requestData['WH_LAT'][$key]);
            $obWarehouse->set('LON', $requestData['WH_LON'][$key]);
            $obWarehouse->set('ACTIVE', ($requestData['WH_ACTIVE'][$key] === 'Y' ? '1' : '0'));
            
            //return $result;
            //Сохраняем операторов для склада
            $arOperators = [];
            foreach ($requestData['WH_OPERATORS_DELIVERY_FROM'][$key] as $operatorId => $deliveryFrom) {
                $arTypesValues = $requestData['WH_OPERATORS_DELIVERY_TYPE'][$key][$operatorId] ?? [];
                if (empty($arTypesValues) || in_array('all', $arTypesValues, false)) {
                    $arDeliveryTypes = ['pvz', 'postamat', 'courier'];
                }
                else {
                    $arDeliveryTypes = $requestData['WH_OPERATORS_DELIVERY_TYPE'][$key][$operatorId] ?? [];
                }
                
                $arOperators[$operatorId] = [
                    'OPERATOR_ID'      => trim($operatorId),
                    'DELIVERY_FROM'    => trim($deliveryFrom ?? ''),
                    'DELIVERY_TYPE'    => $arDeliveryTypes,
                    'DEFAULT_TERMINAL' => trim($requestData['WH_OPERATORS_DEFAULT_TERMINAL'][$key][$operatorId] ?? ''),
                    'FREE'             => ($requestData['WH_OPERATORS_FREE'][$key][$operatorId] === 'Y' ? 'Y' : 'N'),
                ];
            }
            
            static::setEntityValueOperatorsSetup($obWarehouse, $arOperators);
            
            //Сохраняем настройки бесплатной доставки
            $arFreeDelivery = [];
            foreach ($requestData['WH_FREE_DELIVERY_BX_LOC'][$key] as $k => $v) {
                $arFreeDelivery[$k] = [
                    'BX_LOC'            => trim($v),
                    'CITY_FIAS_ID'      => trim($requestData['WH_FREE_DELIVERY_CITY_FIAS_ID'][$key][$k] ?? ''),
                    'REGION_FIAS_ID'    => trim($requestData['WH_FREE_DELIVERY_REGION_FIAS_ID'][$key][$k] ?? ''),
                    'FREE_COURIER_FROM' => (int)($requestData['WH_FREE_DELIVERY_FREE_COURIER_FROM'][$key][$k] ?? 0),
                    'FREE_PICKUP_FROM'  => (int)($requestData['WH_FREE_DELIVERY_FREE_PICKUP_FROM'][$key][$k] ?? 0),
                ];
            }
            unset($k, $v);
            
            static::setEntityValueFreeDeliverySetup($obWarehouse, $arFreeDelivery);
            
            //Сохраняем объект в БД
            $res = $obWarehouse->save();
            if (!$res->isSuccess()) {
                $result->addErrors($res->getErrors());
                return $result;
            }
        }
        
        if ($requestData['WH_ADD_NEW_STORE'] === 'Y') {
            $obWarehouse = static::createObject();
            $obWarehouse->setDeliveryFrom('door');
            $obWarehouse->setActive(0);
            $res = $obWarehouse->save();
            if (!$res->isSuccess()) {
                $result->addErrors($res->getErrors());
                return $result;
            }
        }
        elseif (isset($requestData['WH_REMOVE_STORE']) && $requestData['WH_REMOVE_STORE'] > 0) {
            $obWarehouse = static::getList([
                'filter' => ['=ID' => $requestData['WH_REMOVE_STORE']],
                'select' => ['ID', 'OPERATORS_SETUP', 'FREE_DELIVERY_SETUP'],
            ])->fetchObject();
            
            if ($obWarehouse && $obWarehouse->getId()) {
                $res = $obWarehouse->delete();
                if (!$res->isSuccess()) {
                    $result->addErrors($res->getErrors());
                    return $result;
                }
            }
        }
        
        return $result;
    }
    
    protected static function setEntityValueFreeDeliverySetup($entity, array $arCurrentFreeDeliveries): void
    {
        $arFreeDeliveries = [];
        foreach ($entity->getFreeDeliverySetup() as $freeDeliverySetup) {
            $arFreeDeliveries[$freeDeliverySetup->getId()] = $freeDeliverySetup;
        }
        unset($freeDeliverySetup);
        
        foreach ($arCurrentFreeDeliveries as $id => $arCurrentFreeDelivery) {
            $freeDeliverySetup = array_key_exists($id, $arFreeDeliveries) ? $arFreeDeliveries[$id] : WarehousesFreeDeliveryTable::createObject();
            $freeDeliverySetup->setWarehouseId($entity->getId());
            $freeDeliverySetup->setBxLoc($arCurrentFreeDelivery['BX_LOC']);
            $freeDeliverySetup->setCityFiasId($arCurrentFreeDelivery['CITY_FIAS_ID']);
            $freeDeliverySetup->setRegionFiasId($arCurrentFreeDelivery['REGION_FIAS_ID']);
            $freeDeliverySetup->setFreeCourierFrom($arCurrentFreeDelivery['FREE_COURIER_FROM']);
            $freeDeliverySetup->setFreePickupFrom($arCurrentFreeDelivery['FREE_PICKUP_FROM']);
            
            $entity->addToFreeDeliverySetup($freeDeliverySetup);
            
            unset($arFreeDeliveries[$id]);
        }
        
        //Удаляем операторы, которые не были переданы в запросе
        foreach ($arFreeDeliveries as $freeDeliverySetup) {
            $entity->removeFromFreeDeliverySetup($freeDeliverySetup);
        }
    }
    
    protected static function setEntityValueOperatorsSetup($entity, array $operators): void
    {
        $operatorsSetup = [];
        foreach ($entity->getOperatorsSetup() as $operatorSetup) {
            $operatorsSetup[$operatorSetup->getOperatorId()] = $operatorSetup;
        }
        unset($operatorSetup);
        
        foreach ($operators as $operator) {
            $operatorId    = $operator['OPERATOR_ID'];
            $operatorSetup = array_key_exists($operatorId, $operatorsSetup) ? $operatorsSetup[$operatorId] : WarehousesOperatorsTable::createObject();
            $operatorSetup->setOperatorId($operatorId);
            $operatorSetup->setWarehouseId($entity->getId());
            $operatorSetup->setFree($operator['FREE'] === 'Y' ? '1' : '0');
            $operatorSetup->setDeliveryFrom(($operator['DELIVERY_FROM'] === 'warehouse' ? 'warehouse' : 'door'));
            $operatorSetup->setDeliveryType(implode(',', $operator['DELIVERY_TYPE']));
            $operatorSetup->setDefaultTerminal(trim($operator['DEFAULT_TERMINAL'] ?? ''));
            
            $entity->addToOperatorsSetup($operatorSetup);
            
            unset($operatorsSetup[$operatorId]);
        }
        
        //Удаляем операторы, которые не были переданы в запросе
        foreach ($operatorsSetup as $operatorSetup) {
            $entity->removeFromOperatorsSetup($operatorSetup);
        }
    }
    
    public static function getWarehouses(array $arFilter = []): array
    {
        static $warehousesData = [];
        
        $key = serialize($arFilter);
        
        if (empty($warehousesData[$key])) {
            $warehousesResult = \Ipol\Catapulto\WarehousesTable::getList([
                'filter' => $arFilter,
                'select' => ['*', 'BX_LOCATION', 'BX_LOCATION_NAME', 'OPERATORS_SETUP', 'FREE_DELIVERY_SETUP']
            ]);
            
            while ($current = $warehousesResult->fetchObject()) {
                $id = $current->getId();
                /* add to select 'TYPE_NM' => 'BX_LOCATION.TYPE.NAME.NAME', but empty location rows will be ignored.
                $bxLocType = '';
                if ($current->getBxLocation() && $current->getBxLocation()->getType() && $current->getBxLocation()->getType()->getName()) {
                    $bxLocType = $current->getBxLocation()->getType()->getName()->getName();
                }*/
                
                $warehousesData[$key][$id] = [
                    'ID'                   => $id,
                    'CATAPULTO_CITY_ID'    => $current->getCatapultoCityId(),
                    'CATAPULTO_CITY_INDEX' => $current->getCatapultoCityIndex(),
                    'CATAPULTO_CONTACT_ID' => $current->getCatapultoContactId(),
                    'DELIVERY_FROM'        => $current->getDeliveryFrom(),
                    'FREE_COURIER_FROM'    => $current->getFreeCourierFrom(),
                    'FREE_PICKUP_FROM'     => $current->getFreePickupFrom(),
                    'POA_ENABLED'          => $current->getPoaEnabled() ? 'Y' : 'N',
                    'POA_FROM_DATA'        => json_decode($current->getPoaFromData(), true),
                    'BX_LOC'               => $current->getBxLoc(),
                    'ACTIVE'               => $current->getActive() ? 'Y' : 'N',
                    'BX_LOC_NAME'          => ($current->getBxLocationName() ? $current->getBxLocationName()->getName() : ''),
                    //'BX_LOC_TYPE'          => $bxLocType,
                    'LAT'                  => $current->getLat(),
                    'LON'                  => $current->getLon(),
                ];
                
                $warehousesData[$key][$id]['TITLE'] = static::getWarehouseTitle([
                    'ID'          => $id,
                    'BX_LOC_NAME' => $warehousesData[$key][$id]['BX_LOC_NAME']
                ]);
                
                foreach ($current->getOperatorsSetup() as $operatorSetup) {
                    $operatorId = $operatorSetup->getOperatorId();
                    
                    $warehousesData[$key][$id]['OPERATORS_SETUP'][$operatorId] = [
                        'ID'               => $operatorSetup->getId(),
                        'OPERATOR_ID'      => $operatorId,
                        'WAREHOUSE_ID'     => $operatorSetup->getWarehouseId(),
                        'FREE'             => $operatorSetup->getFree() ? 'Y' : 'N',
                        'DELIVERY_FROM'    => $operatorSetup->getDeliveryFrom(),
                        'DELIVERY_TYPE'    => $operatorSetup->getDeliveryType() ? explode(',', $operatorSetup->getDeliveryType()) : [],
                        'DEFAULT_TERMINAL' => $operatorSetup->getDefaultTerminal(),
                    ];
                }
                
                foreach ($current->getFreeDeliverySetup() as $freeDeliverySetup) {
                    $freeId = $freeDeliverySetup->getId();
                    
                    $warehousesData[$key][$id]['FREE_DELIVERY_SETUP'][$freeId] = [
                        'ID'                => $freeId,
                        'BX_LOC'            => $freeDeliverySetup->getBxLoc(),
                        'CITY_FIAS_ID'      => $freeDeliverySetup->getCityFiasId(),
                        'REGION_FIAS_ID'    => $freeDeliverySetup->getRegionFiasId(),
                        'FREE_COURIER_FROM' => $freeDeliverySetup->getFreeCourierFrom(),
                        'FREE_PICKUP_FROM'  => $freeDeliverySetup->getFreePickupFrom(),
                    ];
                }
            }
        }
        
        return $warehousesData[$key] ?? [];
    }
    
    public static function getWarehouseTitle(array $arWarehouse): string
    {
        return Tools::getMessage('WH_NAME') . $arWarehouse['ID'] . ' ' . $arWarehouse['BX_LOC_NAME'];
    }
}