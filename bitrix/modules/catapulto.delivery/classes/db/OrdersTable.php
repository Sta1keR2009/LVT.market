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
 * Class OrdersTable
 * @package Ipol\Catapulto
 */
class OrdersTable extends DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'catapulto_delivery_orders';
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
            new IntegerField(
                'CATAPULTO_ID',
                [
                ]
            ),
            new StringField(
                'NUMBER',
                [
                    'validation' => [__CLASS__, 'validateNumber'],
                ]
            ),
            new StringField(
                'TRACKING_NUMBER',
                [
                    'validation' => [__CLASS__, 'validateTrackingNumber'],
                ]
            ),
            new StringField(
                'TRACKING_LINK',
                [
                    'validation' => [__CLASS__, 'validateTrackingLink'],
                    'default_value'=>''
                ]
            ),
            new StringField(
                'KEY',
                [
                    'validation' => [__CLASS__, 'validateKey'],
                ]
            ),
            new StringField(
                'MAIN_STATUS',
                [
                    'validation' => [__CLASS__, 'validateMainStatus'],
                ]
            ),
            new StringField(
                'MAIN_STATUS_DISPLAY',
                [
                    'validation' => [__CLASS__, 'validateMainStatusDisplay'],
                ]
            ),
            new StringField(
                'PICKUP_DAY',
                [
                    'validation' => [__CLASS__, 'validatePickupDay'],
                ]
            ),
            new StringField(
                'DELIVERY_DAY',
                [
                    'validation' => [__CLASS__, 'validateDeliveryDay'],
                ]
            ),
            new FloatField(
                'PRICE',
                []
            ),
            new FloatField(
                'WEIGHT',
                []
            ),
            new StringField(
                'OPERATOR',
                [
                    'validation' => [__CLASS__, 'validateOperator'],
                ]
            ),
            new IntegerField(
                'SENDER_CONTACT_ID',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateSenderContactId'],
                ]
            ),
            new StringField(
                'SENDER_COMPANY',
                [
                    'validation' => [__CLASS__, 'validateSenderCompany'],
                ]
            ),
            new IntegerField(
                'RECEIVER_CONTACT_ID',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateReceiverContactId'],
                ]
            ),
            new StringField(
                'RECEIVER_LOCALITY',
                [
                    'validation' => [__CLASS__, 'validateReceiverLocality'],
                ]
            ),
            new StringField(
                'RECEIVER_NAME',
                [
                    'validation' => [__CLASS__, 'validateReceiverName'],
                ]
            ),
            new StringField(
                'RECEIVER_COMPANY',
                [
                    'validation' => [__CLASS__, 'validateReceiverCompany'],
                ]
            ),
            new StringField(
                'RECEIVER_PHONE',
                [
                    'validation' => [__CLASS__, 'validateReceiverPhone'],
                ]
            ),
            new TextField(
                'RECEIVER_ADDRESS',
                []
            ),
            new StringField(
                'DESCRIPTION',
                [
                    'validation' => [__CLASS__, 'validateDescription'],
                ]
            ),
            new BooleanField(
                'WITH_INSURANCE',
                []
            ),
            new FloatField(
                'INSURANCE_COST',
                []
            ),
            new TextField(
                'DOCUMENTS',
                []
            ),
            new StringField(
                'LAST_TRACKING_TEXT',
                [
                    'validation' => [__CLASS__, 'validateLastTrackingText'],
                ]
            ),
            new StringField(
                'PROBLEM_TEXT',
                [
                    'validation' => [__CLASS__, 'validateProblemText'],
                ]
            ),
            new BooleanField(
                'IS_POD',
                []
            ),
            new FloatField(
                'SUM_TO_PAY',
                []
            ),
            new TextField(
                'PAYMENT',
                []
            ),
            new StringField(
                'TRACKING_STATUS',
                [
                    'validation' => [__CLASS__, 'validateTrackingStatus'],
                ]
            ),
            new IntegerField(
                'RATE_RESULT_ID',
                [
                    'required' => true,
                    'validation' => [__CLASS__, 'validateRateResultId'],
                ]
            ),
            new StringField(
                'TERMINAL_CODE',
                [
                    'validation' => [__CLASS__, 'validateTerminalCode'],
                ]
            ),
            new StringField(
                'POD',
                [
                    'validation' => [__CLASS__, 'validatePod'],
                ]
            ),
            new TextField(
                'MESSAGE',
                [
                ]
            ),
            new StringField(
                'OK',
                [
                    'validation' => [__CLASS__, 'validateOk'],
                ]
            ),
            new StringField(
                'UPTIME',
                [
                    'validation' => [__CLASS__, 'validateUptime'],
                ]
            ),
        ];
    }

    /**
     * Returns validators for NUMBER field.
     *
     * @return array
     */
    public static function validateNumber()
    {
        return [
            new LengthValidator(0, 30),
        ];
    }
    /**
     * Returns validators for TRACKING_NUMBER field.
     *
     * @return array
     */
    public static function validateTrackingNumber()
    {
        return [
            new LengthValidator(0, 50),
        ];
    }

    /**
     * Returns validators for TRACKING_LINK field.
     *
     * @return array
     */
    public static function validateTrackingLink()
    {
        return [
            new LengthValidator(0, 250),
        ];
    }

    /**
     * Returns validators for KEY field.
     *
     * @return array
     */
    public static function validateKey()
    {
        return [
            new LengthValidator(0, 30),
        ];
    }

    /**
     * Returns validators for MAIN_STATUS field.
     *
     * @return array
     */
    public static function validateMainStatus()
    {
        return [
            new LengthValidator(0, 30),
        ];
    }

    /**
     * Returns validators for MAIN_STATUS_DISPLAY field.
     *
     * @return array
     */
    public static function validateMainStatusDisplay()
    {
        return [
            new LengthValidator(0, 100),
        ];
    }

    /**
     * Returns validators for PICKUP_DAY field.
     *
     * @return array
     */
    public static function validatePickupDay()
    {
        return [
            new LengthValidator(0, 10),
        ];
    }

    /**
     * Returns validators for DELIVERY_DAY field.
     *
     * @return array
     */
    public static function validateDeliveryDay()
    {
        return [
            new LengthValidator(0, 10),
        ];
    }

    /**
     * Returns validators for OPERATOR field.
     *
     * @return array
     */
    public static function validateOperator()
    {
        return [
            new LengthValidator(0, 30),
        ];
    }

    /**
     * Returns validators for SENDER_CONTACT_ID field.
     *
     * @return array
     */
    public static function validateSenderContactId()
    {
        return [
            new LengthValidator(0, 11),
        ];
    }

    /**
     * Returns validators for SENDER_COMPANY field.
     *
     * @return array
     */
    public static function validateSenderCompany()
    {
        return [
            new LengthValidator(0, 255),
        ];
    }

    /**
     * Returns validators for RECEIVER_CONTACT_ID field.
     *
     * @return array
     */
    public static function validateReceiverContactId()
    {
        return [
            new LengthValidator(0, 11),
        ];
    }

    /**
     * Returns validators for RECEIVER_LOCALITY field.
     *
     * @return array
     */
    public static function validateReceiverLocality()
    {
        return [
            new LengthValidator(0, 255),
        ];
    }

    /**
     * Returns validators for RECEIVER_NAME field.
     *
     * @return array
     */
    public static function validateReceiverName()
    {
        return [
            new LengthValidator(0, 255),
        ];
    }

    /**
     * Returns validators for RECEIVER_COMPANY field.
     *
     * @return array
     */
    public static function validateReceiverCompany()
    {
        return [
            new LengthValidator(0, 255),
        ];
    }

    /**
     * Returns validators for RECEIVER_PHONE field.
     *
     * @return array
     */
    public static function validateReceiverPhone()
    {
        return [
            new LengthValidator(0, 150),
        ];
    }

    /**
     * Returns validators for DESCRIPTION field.
     *
     * @return array
     */
    public static function validateDescription()
    {
        return [
            new LengthValidator(0, 65000),
        ];
    }

    /**
     * Returns validators for LAST_TRACKING_TEXT field.
     *
     * @return array
     */
    public static function validateLastTrackingText()
    {
        return [
            new LengthValidator(0, 150),
        ];
    }

    /**
     * Returns validators for PROBLEM_TEXT field.
     *
     * @return array
     */
    public static function validateProblemText()
    {
        return [
            new LengthValidator(0, 255),
        ];
    }

    /**
     * Returns validators for TRACKING_STATUS field.
     *
     * @return array
     */
    public static function validateTrackingStatus()
    {
        return [
            new LengthValidator(0, 30),
        ];
    }

    /**
     * Returns validators for RATE_RESULT_ID field.
     *
     * @return array
     */
    public static function validateRateResultId()
    {
        return [
            new LengthValidator(0, 11),
        ];
    }

    /**
     * Returns validators for TERMINAL_CODE field.
     *
     * @return array
     */
    public static function validateTerminalCode()
    {
        return [
            new LengthValidator(0, 100),
        ];
    }

    /**
     * Returns validators for POD field.
     *
     * @return array
     */
    public static function validatePod()
    {
        return [
            new LengthValidator(0, 100),
        ];
    }

    /**
     * Returns validators for OK field.
     *
     * @return array
     */
    public static function validateOk()
    {
        return [
            new LengthValidator(0, 1),
        ];
    }

    /**
     * Returns validators for UPTIME field.
     *
     * @return array
     */
    public static function validateUptime()
    {
        return [
            new LengthValidator(0, 10),
        ];
    }

    // Getters and helpers

    /**
     * Returns order data by order id (primary index)
     *
     * @param int $id
     * @param array $select
     * @return array
     */
    public static function getByOrderId($id, $select = array())
    {
        return self::getList(array_filter(['select' => $select ?: null, 'filter' => ['=ID' => $id]]))->fetch();
    }

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

    /**
     * Returns order data by Catapulto id
     *
     * @param string $catapultoId
     * @param array $select
     * @return array
     */
    public static function getByCatapultoId($catapultoId, $select = array())
    {
        return self::getList(array_filter(['select' => $select ?: null, 'filter' => ['=CATAPULTO_ID' => $catapultoId]]))->fetch();
    }

    /**
     * Returns order data by Catapilto Key
     *
     * @param string $key
     * @param array $select
     * @return array
     */
    public static function getByKey($key, $select = array())
    {
        return self::getList(array_filter(['select' => $select ?: null, 'filter' => ['=KEY' => $key]]))->fetch();
    }

    /**
     * Returns order data by Catapulto number
     *
     * @param string $number
     * @param array $select
     * @return array
     */
    public static function getByNumber($number, $select = array())
    {
        return self::getList(array_filter(['select' => $select ?: null, 'filter' => ['=NUMBER' => $number]]))->fetch();
    }

    /**
     * Return number of rows with some data
     *
     * @return int
     */
    public static function getDataCount()
    {
        $params = ['select' => ['CNT'], 'runtime' => [new ExpressionField('CNT', 'COUNT(*)')]];
        $result = self::getList($params)->fetch();
        return $result['CNT'];
    }

    /**
     * Get boolean Fields map in OrdersTable
     *
     * @return string[]
     */
    public static function getBooleanFieldsMap()
    {
        return [
            'WITH_INSURANCE',
            'IS_POD',
        ];
    }


}
