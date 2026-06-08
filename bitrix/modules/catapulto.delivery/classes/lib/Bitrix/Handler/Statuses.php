<?php

namespace Ipol\Catapulto\Bitrix\Handler;


class Statuses
{
    public static function getOrderStatuses(){
        $arStatuses = array();
        $dbStatuses = self::getDBStatuses(array('TYPE' => 'O', 'LID' => 'ru'));
        while ($arStatus = $dbStatuses->Fetch()) {
            $arStatuses[$arStatus['ID']] = $arStatus['NAME'] . " [{$arStatus['ID']}]";
        }

        return $arStatuses;
    }

    public static function getShipmentStatuses(){
        $arStatuses = array();
        $dbStatuses = self::getDBStatuses(array('TYPE' => 'D', 'LID' => 'ru'));
        while ($arStatus = $dbStatuses->Fetch()) {
            $arStatuses[$arStatus['ID']] = $arStatus['NAME'] . " [{$arStatus['ID']}]";
        }

        return $arStatuses;
    }

    protected static function getDBStatuses($arFilter = array()){
        return $dbStatuses = \CSaleStatus::GetList(
            array('SORT' => 'asc'),
            $arFilter,
            false,
            false,
            array(
                'ID',
                'TYPE',
                'NAME'
            )
        );
    }
}