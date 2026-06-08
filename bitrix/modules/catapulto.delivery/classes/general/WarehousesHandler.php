<?php

namespace Ipol\Catapulto;

use \Ipol\Catapulto\WarehousesTable;

IncludeModuleLangFile(__FILE__);

class WarehousesHandler extends AbstractGeneral
{
    public static function getDefaultWarehouse(): array
    {
        $defaultWarehouseId = \Ipol\Catapulto\Option::get('DEFAULT_WAREHOUSE_ID');
        
        static $warehousesData = [];
        
        if (empty($warehousesData)) {
            $warehousesData = WarehousesTable::getWarehouses(['=ID' => $defaultWarehouseId]);
        }
        
        return $warehousesData[$defaultWarehouseId] ?? [];
    }
    
    /**
     * Находит ближайший склад по координатам:
     * если заданы координаты назначения, то ищет ближайший склад по координатам;
     * если склад 1, то он и будет ближайшим;
     * если по какой-то причине поиск не удался, используется склад по-умолчанию.
     *
     * @param array $coords
     *
     * @return array
     */
    public static function getNearestWarehouse(array $coords): array
    {
        $arResultWarehouses = [];
        
        if ($coords['lat'] && $coords['lon']) {
            $warehousesData = WarehousesTable::getWarehouses(['=ACTIVE' => 1]);
            
            if (count($warehousesData) === 1) {
                $arResultWarehouses = array_shift($warehousesData);
            }
            else {
                $storeId  = 0;
                $distance = 0;
                foreach ($warehousesData as $arWarehouse) {
                    $a1 = deg2rad((float)$coords['lat']);
                    $b1 = deg2rad((float)$coords['lon']);
                    $a2 = deg2rad((float)$arWarehouse['LAT']);
                    $b2 = deg2rad((float)$arWarehouse['LON']);
                    
                    $calculatedDistance = 12742016 * asin(sqrt(pow(sin(($a2 - $a1) / 2), 2) + cos($a2) * cos($a1) * pow(sin(($b2 - $b1) / 2), 2)));
                    
                    if (($calculatedDistance < $distance) || ($distance === 0)) {
                        $storeId  = $arWarehouse['ID'];
                        $distance = $calculatedDistance;
                    }
                }
                
                if ($storeId) {
                    $arResultWarehouses = $warehousesData[$storeId];
                }
            }
        }
        
        return $arResultWarehouses ? : static::getDefaultWarehouse();
    }
    
    /**
     * @param $cityCode
     *
     * @return array
     */
    public static function getLocationCoordinates($cityCode): array
    {
        $obCache   = new \CPHPCache();
        $cacheTime = 86400;
        $cachePath = '/' . self::$MODULE_ID . '/' . __FUNCTION__ . '_loc';
        if ($obCache->InitCache($cacheTime, $cityCode, $cachePath)) {
            $coords = $obCache->GetVars();
            $lat    = $coords['LAT'];
            $lon    = $coords['LON'];
        }
        else {
            $posFromTable = \Ipol\Catapulto\GeocoderCacheTable::getList(['filter' => ['=BX_LOC' => $cityCode]])->fetch();
            if (!$posFromTable) {
                //get location From Yandex Geocoder
                //Get Destination Location String for Geocoder
                $loc = (\Bitrix\Sale\Location\LocationTable::getList([
                    'filter' => [
                        '=NAME.LANGUAGE_ID'      => LANGUAGE_ID,
                        '=TYPE.NAME.LANGUAGE_ID' => LANGUAGE_ID,
                        'CODE'                   => $cityCode
                    ],
                    'select' => [
                        'ID',
                        'CODE',
                        'PARENT_ID',
                        'NAME_RU' => 'NAME.NAME',
                        'TYPE_NM' => 'TYPE.NAME.NAME'
                    ]
                ]))->fetchAll();
                
                $parentId       = 0;
                $locationString = '';
                if (!empty($loc)) {
                    $parentId       = (int)$loc[0]['PARENT_ID'];
                    $locationString .= $loc[0]['TYPE_NM'] . ' ' . $loc[0]['NAME_RU'];
                }
                while ($parentId !== 0) {
                    $loc            = (\Bitrix\Sale\Location\LocationTable::getList([
                        'filter' => [
                            '=NAME.LANGUAGE_ID'      => LANGUAGE_ID,
                            '=TYPE.NAME.LANGUAGE_ID' => LANGUAGE_ID,
                            'ID'                     => $parentId
                        ],
                        'select' => [
                            'ID',
                            'CODE',
                            'PARENT_ID',
                            'NAME_RU' => 'NAME.NAME',
                            'TYPE_NM' => 'TYPE.NAME.NAME'
                        ]
                    ]))->fetchAll();
                    $parentId       = (int)$loc[0]['PARENT_ID'];
                    $locationString = $loc[0]['NAME_RU'] . ', ' . $locationString;
                }
                
                //get Destination Location Coordinates from Ymap Geocoder
                $toCoords = [];
                if ($locationString) {
                    $apiKey = COption::GetOptionString(self::$MODULE_ID, 'apikeyYandex', '');
                    $url    = "https://geocode-maps.yandex.ru/1.x/?format=json&geocode=" . urlencode($locationString) . "&apikey={$apiKey}";
                    $ch     = curl_init();
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                    $proxyServer = COption::GetOptionString(self::$MODULE_ID, "proxyServerForGeocoder", "");
                    if ($proxyServer != '') {
                        curl_setopt($ch, CURLOPT_PROXY, $proxyServer);
                    }
                    //curl_setopt($ch, CURLOPT_PROXY, 'rgm-s-wpx05.hq.root.ad:3128');
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                    $content   = curl_exec($ch);
                    //$curl_info = curl_getinfo($ch);
                    curl_close($ch);
                    $content  = json_decode($content);
                    $toCoords = explode(' ', $content->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos);
                }
                
                $lat = (float)$toCoords[1];
                $lon = (float)$toCoords[0];
                //save results to table - except GEO errors
                if (($lat > 0) && ($lon > 0)) {
                    \Ipol\Catapulto\GeocoderCacheTable::add([
                        'BX_LOC' => $cityCode,
                        'LAT'    => $lat,
                        'LON'    => $lon
                    ]);
                }
            }
            else {
                $lat = (float)$posFromTable['LAT'];
                $lon = (float)$posFromTable['LON'];
            }
            
            if (($lat > 0) && ($lon > 0)) {
                $obCache->StartDataCache();
                $obCache->EndDataCache(['LAT' => $lat, 'LON' => $lon]);
            }
        }
        
        return ['LAT' => $lat, 'LON' => $lon];
    }
    
}