<?php
namespace Ipol\Catapulto;

use \Bitrix\Main\ORM\Data\DataManager;
use \Bitrix\Main\ORM\Fields\BooleanField;
use \Bitrix\Main\ORM\Fields\DatetimeField;
use \Bitrix\Main\ORM\Fields\IntegerField;
use \Bitrix\Main\ORM\Fields\StringField;
use \Bitrix\Main\ORM\Fields\ExpressionField;
use \Bitrix\Main\ORM\Fields\Validators\LengthValidator;

/**
 * Class OperatorsTable
 * @package Ipol\Catapulto
 */
class OperatorsTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'catapulto_delivery_operators';
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
            new StringField(
                'OPERATOR_ID',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateOperatorId'],
                ]
            ),
            new StringField(
                'OPERATOR_DISPLAY',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateOperatorDisplay'],
                ]
            ),
            new BooleanField(
                'OP_ENABLED',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'Y',
                ]
            ),
            new StringField(
                'ICON',
                [
                    'validation' => [__CLASS__, 'validateIcon'],
                ]
            ),
            new StringField(
                'SMALL_ICON',
                [
                    'validation' => [__CLASS__, 'validateSmallIcon'],
                ]
            ),
            new StringField(
                'PNG_ICON',
                [
                    'validation' => [__CLASS__, 'validatePngIcon'],
                ]
            ),
            new BooleanField(
                'SYNC_IS_ACTIVE',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'Y',
                ]
            ),
            new BooleanField(
                'SYNC_IS_UPDATABLE',
                [
                    'values' => array('N', 'Y'),
                    'default' => 'Y',
                ]
            ),
            new DatetimeField(
                'SYNC_LAST_DATE',
                [
                    'required' => true,
                ]
            ),
            new StringField(
                'SYNC_HASH',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateSyncHash'],
                ]
            ),
        ];
    }

    /**
     * Returns validators for OPERATOR_ID field.
     *
     * @return array
     */
    public static function validateOperatorId()
    {
        return [
            new LengthValidator(null, 30),
        ];
    }


    /**
     * Returns validators for OPERATOR_DISPLAY field.
     *
     * @return array
     */
    public static function validateOperatorDisplay()
    {
        return [
            new LengthValidator(null, 30),
        ];
    }


    /**
     * Returns validators for ICON field.
     *
     * @return array
     */
    public static function validateIcon()
    {
        return [
            new LengthValidator(null, 255),
        ];
    }

    /**
     * Returns validators for SMALL_ICON field.
     *
     * @return array
     */
    public static function validateSmallIcon()
    {
        return [
            new LengthValidator(null, 255),
        ];
    }

    /**
     * Returns validators for PNG_ICON field.
     *
     * @return array
     */
    public static function validatePngIcon()
    {
        return [
            new LengthValidator(null, 255),
        ];
    }


    /**
     * Returns validators for SYNC_HASH field.
     *
     * @return array
     */
    public static function validateSyncHash()
    {
        return [
            new LengthValidator(null, 32),
        ];
    }

    // Getters and helpers

    /**
     * Returns operator data by primary ID.
     *
     * @param  int $id primary index
     * @param  array $select
     * @return array
     */
    public static function getByPrimaryId($id, $select = array())
    {
        return self::getList(array_filter(['select' => $select ?: null, 'filter' => ['=ID' => $id]]))->fetch();
    }

    /**
     * Returns operator data by API operator ID.
     *
     * @param  string $operatorId API from operator ID
     * @param  array $select
     * @return array
     */
    public static function getByOperatorId($operatorId, $select = array())
    {
        return self::getList(array_filter(['select' => $select ?: null, 'filter' => ['=OPERATOR_ID' => $operatorId]]))->fetch();
    }

    /**
     * Return number of rows with some data
     *
     * @param  bool $onlyActive
     * @return int
     */
    public static function getDataCount($onlyActive = true)
    {
        $params = ['select' => ['CNT'], 'runtime' => [new ExpressionField('CNT', 'COUNT(*)')]];

        if ($onlyActive)
            $params['filter'] = ['SYNC_IS_ACTIVE' => 'Y'];

        $result = self::getList($params)->fetch();
        return $result['CNT'];
    }

    public static function disableAllOperators()
    {
        $operators = self::getList([
            'select'=>['ID']
        ])->fetchAll();
        foreach ($operators as $operator) self::update($operator['ID'],[
            'OP_ENABLED' => 'N',
        ]);
    }
}
