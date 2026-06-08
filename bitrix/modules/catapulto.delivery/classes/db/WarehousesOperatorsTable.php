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

/**
 * Class WarehousesOperatorsTable
 *
 * @package Ipol\Catapulto
 */
class WarehousesOperatorsTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'catapulto_delivery_warehouses_operators';
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
            new StringField('OPERATOR_ID', ['size' => 30]),
            new BooleanField('FREE', ['values' => ['0', '1'], 'default' => '0']),
            new StringField('DELIVERY_FROM', ['size' => 30]),
            new StringField('DELIVERY_TYPE', ['size' => 30]),
            new StringField('DEFAULT_TERMINAL', ['size' => 100]),
            'WAREHOUSE_SETUP' => (new Fields\Relations\Reference('OPERATORS_SETUP', WarehousesTable::getEntity(), (new ConditionTree)->whereColumn('this.WAREHOUSE_ID', 'ref.ID'))),
        ];
    }
}
