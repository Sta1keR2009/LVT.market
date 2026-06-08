<?php

namespace Ipol\Catapulto;

use Bitrix\Main\ORM\Fields\BooleanField;
use \Bitrix\Main\ORM\Data\DataManager;
use \Bitrix\Main\ORM\Fields\FloatField;
use \Bitrix\Main\ORM\Fields\IntegerField;
use \Bitrix\Main\ORM\Fields\StringField;
use \Bitrix\Main\ORM\Fields\TextField;
use \Bitrix\Main\ORM\Fields\ExpressionField;
use \Bitrix\Main\ORM\Fields\Validators\LengthValidator;

/**
 * Class OrderPropsTable
 * @package Ipol\Catapulto
 */
class OrderPropsTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'catapulto_delivery_orders_props';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return [
            new IntegerField(
                'ID',
                [
                    'primary' => true,
                    'autocomplete' => true,
                ]
            ),
            new IntegerField(
                'BITRIX_ID',
                [
                    'required' => true,
                ]
            ),

            new TextField(
                'RATE_RESULT',
                []
            ),
            new StringField(
                'RATE_RESULT_ID',
                [
                    'validation' => [__CLASS__, 'validateRateResultId'],
                ]
            ),
            new TextField(
                'CUSTOM_GABS',
                []
            ),
            new TextField(
                'DADATA_ADDR',
                []
            ),
            new TextField(
                'CARGOES',
                []
            ),

            new TextField(
                'OTHER',
                []
            ),

        ];
    }

    /**
     * Returns validators for NUMBER field.
     *
     * @return array
     */
    public static function validateRateResultId()
    {
        return [
            new LengthValidator(0, 20),
        ];
    }

    // Getters and helpers

    /**
     * Returns order data by Bitrix id
     *
     * @param int $bitrixId
     * @param array $select
     * @return array
     */
    public static function getByBitrixId($bitrixId, $select = array())
    {
        return self::getList(array_filter(['select' => $select ?: null, 'filter' => ['=BITRIX_ID' => $bitrixId]]))->fetch();
    }


    public static function saveProps(int $orderId, array $data)
    {
        $data = array_merge(['BITRIX_ID'=>$orderId],$data);
        $currentRecord = self::getByBitrixId($orderId);
        if (!$currentRecord) {
            $res = self::add($data);
        } else {
            $res = self::update($currentRecord['ID'],$data);
        }
    }


}