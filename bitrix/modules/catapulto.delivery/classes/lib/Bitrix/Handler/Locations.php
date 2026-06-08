<?php
namespace Ipol\Catapulto\Bitrix\Handler;

use \Bitrix\Sale\Location\LocationTable;
use \Bitrix\Sale\Location\TypeTable;

/**
 * Class Locations
 * @package namespace Ipol\Catapulto\Bitrix\Handler
 */
class Locations
{
    /**
     * Useful Bitrix location types for delivery calculation
     */
    const DELIVERABLE_TYPES = ['CITY', 'VILLAGE'];

    /**
     * Typical Bitrix location types
     */
    const TYPE_COUNTRY    = 'COUNTRY';
    const TYPE_REGION     = 'REGION';
    const TYPE_SUBREGION  = 'SUBREGION';
    const TYPE_CITY       = 'CITY';
    const TYPE_VILLAGE    = 'VILLAGE';

    /**
     * Location search directions for location chain
     */
    const FIND_FIRST = 1;
    const FIND_LAST  = 2;

    /**
     * Checks if really used Location 2.0
     *
     * @return bool
     */
    public static function isLocation20()
    {
        return (method_exists("\CSaleLocation","isLocationProMigrated") && \CSaleLocation::isLocationProMigrated());
    }

    /**
     * Try to get Bitrix location Id by possibly Id or Code
     *
     * @param string $possiblyId
     * @return string|false
     */
    public static function getNormalId($possiblyId)
    {
        if (self::isLocation20() && $possiblyId)
        {
            $possiblyId = (string) $possiblyId;
            if ($possiblyId != '' && $possiblyId !== (string) intval($possiblyId))
            {
                // We have code instead of Id
                $location = LocationTable::getList(array('filter' => array('=CODE' => $possiblyId), 'select' => array('ID', 'CODE')))->fetch();
                if(!empty($location['ID']))
                    return $location['ID'];
                else
                    return false; // This code sucks
            }
        }
        return $possiblyId;
    }

    /**
     * Try to get upper deliverable Bitrix location. Useful while STREET location type loaded.
     *
     * @param string $id Bitrix location id
     * @return string
     */
    public static function getDeliverableParentId($id)
    {
        $city = LocationTable::getList(array('filter' => array('=ID' => $id), 'select' => array('ID', 'TYPE_ID', 'PARENT_ID')))->fetch();
        $deliverableTypes = [];
        $deliverableTypesDb = TypeTable::getList(array('filter' => array('=CODE' => self::DELIVERABLE_TYPES), 'select' => array('ID', 'CODE')));
        while ($tmp = $deliverableTypesDb->fetch())
        {
            $deliverableTypes[$tmp['ID']] = $tmp['CODE'];
        }

        if (!array_key_exists($city['TYPE_ID'], $deliverableTypes))
        {
            $newCityId = false;
            while (!$newCityId)
            {
                if (empty($city['PARENT_ID']))
                    break;
                $city = LocationTable::getList(array('filter' => array('=ID' => $city['PARENT_ID']), 'select' => array('ID', 'TYPE_ID', 'PARENT_ID')))->fetch();
                if (array_key_exists($city['TYPE_ID'], $deliverableTypes))
                    $newCityId = $city['ID'];
            }
            return $newCityId;
        }
        else
            return $id;
    }

    /**
     * Try to get Bitrix location Id by possibly Id or Code. If succeed, try to get upper deliverable Bitrix location if needed.
     *
     * @param string $possiblyId
     * @return string|false bitrix location id while success or false
     */
    public static function getDeliverableLocationId($possiblyId)
    {
        $id = self::getNormalId($possiblyId);
        if ($id !== false)
            return self::getDeliverableParentId($id);

        return false;
    }

    /**
     * Get Bitrix location data by possibly Id or Code
     *
     * @param string $possiblyId
     * @param bool $itIsId set true if you really know: this is location id, not code
     * @param bool $getDeliverableParent get upper deliverable Bitrix location
     * @return array
     */
    public static function getByBitrixId($possiblyId, $itIsId = false, $getDeliverableParent = true)
    {
        $result = array();

        $bitrixId = ($itIsId) ? $possiblyId : self::getNormalId($possiblyId);
        if ($bitrixId !== false)
        {
            $bitrixId = ($getDeliverableParent) ? self::getDeliverableParentId($bitrixId) : $bitrixId;

            if ($bitrixId !== false)
            {
                $chain = self::getLocationChainById($bitrixId);
                if (!empty($chain))
                {
                    $locationKey = count($chain) - 1;

                    $result = array(
                        'ID'        => $chain[$locationKey]['ID'],
                        'CODE'      => $chain[$locationKey]['CODE'],
                        'NAME'      => $chain[$locationKey]['LOCATION_NAME'],
                        'NAME_NORM' => $chain[$locationKey]['NAME_NORM'],
                        'PARENT_ID' => $chain[$locationKey]['PARENT_ID'],
                        'TYPE_CODE' => $chain[$locationKey]['TYPE_CODE']
                    );

                    $possibleCountry = self::getFromLocationChain($chain, self::TYPE_COUNTRY, self::FIND_FIRST);
                    $result['COUNTRY'] = !empty($possibleCountry) ? $possibleCountry['LOCATION_NAME'] : '';

                    $possibleRegion = self::getFromLocationChain($chain, self::TYPE_REGION, self::FIND_FIRST);
                    if (!empty($possibleRegion))
                    {
                        $result['REGION'] = $possibleRegion['LOCATION_NAME'];
                    }
                    else
                    {
                        // Case for federal cities like Moscow
                        $possibleRegion = self::getFromLocationChain($chain, self::TYPE_CITY, self::FIND_FIRST);
                        $result['REGION'] = !empty($possibleRegion) ? $possibleRegion['LOCATION_NAME'] : '';
                    }

                    $result['AREA'] = '';
                    $possibleArea = self::getFromLocationChain($chain, self::TYPE_SUBREGION, self::FIND_FIRST);
                    if (!empty($possibleArea))
                    {
                        $result['AREA'] = $possibleArea['LOCATION_NAME'];
                    }
                    else
                    {
                        // Case for city inside other city and same
                        $possibleArea = self::getFromLocationChain($chain, self::TYPE_CITY, self::FIND_LAST);
                        if (!empty($possibleArea) && $possibleArea['ID'] !== $possibleRegion['ID']) // Cause upper city can be already set as region
                            $result['AREA'] = $possibleArea['LOCATION_NAME'];
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Get Bitrix location chain by location Id
     *
     * @param string $id location id
     * @return array
     */
    public static function getLocationChainById($id)
    {
        $chain = [];
        $locationTypes = [];

        $locationTypesDb = TypeTable::getList(array('select' => array('ID', 'CODE')));
        while ($tmp = $locationTypesDb->fetch())
        {
            $locationTypes[$tmp['ID']] = $tmp['CODE'];
        }

        $node = LocationTable::getList(array('filter' => array('=ID' => $id), 'select' => array('ID', 'LEFT_MARGIN', 'RIGHT_MARGIN')))->fetch();

        if (!empty($node))
        {
            $chainDb = LocationTable::getList(array(
                'filter' => array('<=LEFT_MARGIN' => $node['LEFT_MARGIN'], '>=RIGHT_MARGIN' => $node['RIGHT_MARGIN'], 'NAME.LANGUAGE_ID' => 'ru'),
                'select' => array('ID', 'CODE', 'TYPE_ID', 'LOCATION_NAME' => 'NAME.NAME', 'PARENT_ID'),
                'order'  => array('LEFT_MARGIN' => 'asc')
            ));

            while ($tmp = $chainDb->fetch())
            {
                $tmp['TYPE_CODE'] = $locationTypes[$tmp['TYPE_ID']];
                $chain[] = $tmp;
            }
        }

        return $chain;
    }

    /**
     * Looks in location chain for desired element and return it if exists
     *
     * @param array $chain - @see Locations::getLocationChainById()
     * @param string $typeCode - use Locations::TYPE_COUNTRY and other TYPE_* constants
     * @param string $direction - use Locations::FIND_FIRST or FIND_LAST
     * @return array
     */
    public static function getFromLocationChain($chain, $typeCode, $direction)
    {
        if (!empty($chain))
        {
            $direction = (in_array($direction, [self::FIND_FIRST, self::FIND_LAST]) ? $direction : self::FIND_FIRST);
            $typeCode = (in_array($typeCode, [self::TYPE_COUNTRY, self::TYPE_REGION, self::TYPE_SUBREGION, self::TYPE_CITY, self::TYPE_VILLAGE]) ? $typeCode : self::TYPE_CITY);

            $column = array_column($chain, 'TYPE_CODE');
            if ($direction === self::FIND_LAST)
            {
                $column = array_reverse($column);
                $chain = array_reverse($chain);
            }

            $key = array_search($typeCode, $column);
            if ($key !== false)
                return $chain[$key];
        }

        return array();
    }
}