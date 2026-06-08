<?php

namespace Ipol\Catapulto\Bitrix\Handler;


use Ipol\Catapulto\Bitrix\Tools;

class PaySystems
{
    public static function getAll()
    {
        $arPaySystems = [];
        $rsPaySys     = \Bitrix\Sale\PaySystem\Manager::getList(
            [
                'filter' => ['ACTIVE' => 'Y'],
                'select' => ['ID', 'NAME']
            ]
        );
        
        while ($arSys = $rsPaySys->Fetch()) {
            $arPaySystems[$arSys['ID']] = $arSys['NAME'];
        }
        
        return $arPaySystems;
    }
    
    public static function getTypesNP(): array
    {
        return [
            'NP'     => Tools::getMessage("LBL_PAY_TYPE_NP"),
            'COD'    => Tools::getMessage("LBL_PAY_TYPE_COD"),
            'NP,COD' => Tools::getMessage("LBL_PAY_TYPE_NP_COD")
        ];
    }
}