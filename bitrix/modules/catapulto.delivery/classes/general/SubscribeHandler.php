<?php

namespace Ipol\Catapulto;

IncludeModuleLangFile(__FILE__);

/**
 * Class subscribeHandler
 *
 * @package Ipol\Catapulto
 * Класс, работающий с подписками на события Битрикса и обработчик аяксовых обращений для вызова конкретного
 * метода. Все подписки в первую очередь летят в этот класс, который уже вызывает нужные функции.
 * Это сделано для упрощения дебага: все отключается в одном месте.
 */
class SubscribeHandler extends AbstractGeneral
{
    public static $link = true;

    /**
     * @param $action
     * Вызывается из /js/ajax.php, чтобы все было в одном месте. Делегирует реквест в нужный Главный класс.
     */
    public static function getAjaxAction($action)
    {
        // примеры вызовов
        if (method_exists('\Ipol\Catapulto\AgentHandler', $action)) {
            \Ipol\Catapulto\AgentHandler::$action($_POST);
        }
        elseif (method_exists('\Ipol\Catapulto\WidgetHandler', $action)) {
            \Ipol\Catapulto\WidgetHandler::$action($_POST);
        }
        elseif (method_exists('\Ipol\Catapulto\StatusHandler', $action)) {
            \Ipol\Catapulto\StatusHandler::$action($_POST);
        }
        elseif (method_exists('\Ipol\Catapulto\PostingHandler', $action)) {
            \Ipol\Catapulto\PostingHandler::$action($_REQUEST);
        }
        elseif (method_exists('\Ipol\Catapulto\OptionsHandler', $action)) {
            \Ipol\Catapulto\OptionsHandler::$action($_POST);
        }
        elseif (method_exists('\Ipol\Catapulto\AuthHandler', $action)) {
            \Ipol\Catapulto\AuthHandler::$action($_POST);
        }
        elseif (method_exists('\Ipol\Catapulto\OrderHandler', $action)) {
            \Ipol\Catapulto\OrderHandler::$action($_POST);
        }
        elseif (method_exists('\Ipol\Catapulto\CargoesHandler', $action)) {
            \Ipol\Catapulto\CargoesHandler::$action($_POST);
        }
    }

    /**
     * Регистрация всех подписок, объявленных в getDepencences
     */
    public static function register()
    {
        foreach (self::getDependences() as $regArray) {
            RegisterModuleDependences($regArray[0], $regArray[1], $regArray[2], $regArray[3], $regArray[4], (isset($regArray[5]) ? $regArray[5] : false));
        }
    }

    /**
     * @return array
     * Список всех подписок методов на события Битрикса.
     * Формат: модуль Битрикса, событие модуля Битрикса, код текущего модуля, путь к Главному классу, сортировка (если надо)
     */
    protected static function getDependences()
    {
        return [
            ['main', 'OnEpilog', self::$MODULE_ID, 'Ipol\Catapulto\SubscribeHandler', 'onEpilog'],
            ['sale', 'OnSaleOrderBeforeSaved', self::$MODULE_ID, '\Ipol\Catapulto\SubscribeHandler', 'onBeforeOrderCreate'],
            ['sale', 'OnSaleComponentOrderOneStepComplete', self::$MODULE_ID, 'Ipol\Catapulto\SubscribeHandler', 'onOrderCreate'],
            ["sale", "OnSaleComponentOrderUserResult", self::$MODULE_ID, 'Ipol\Catapulto\SubscribeHandler', 'getOrderCreatePaysystem'],

            // Add module delivery handler classes
            ['sale', 'onSaleDeliveryHandlersClassNamesBuildList', self::$MODULE_ID, 'Ipol\Catapulto\SubscribeHandler', 'onSaleDeliveryHandlersClassNamesBuildList'],
            ['sale', 'onSaleDeliveryTrackingClassNamesBuildList', self::$MODULE_ID, 'Ipol\Catapulto\SubscribeHandler', 'onSaleDeliveryTrackingClassNamesBuildList'],

            // rate id
            ["sale", "OnSaleComponentOrderOneStepProcess", self::$MODULE_ID, 'Ipol\Catapulto\SubscribeHandler', "loadWidjet", 900],
            ["main", "OnEndBufferContent", self::$MODULE_ID, 'Ipol\Catapulto\SubscribeHandler', "addWidjetData"],
            ["sale", "OnSaleComponentOrderOneStepDelivery", self::$MODULE_ID, 'Ipol\Catapulto\SubscribeHandler', "prepareData", 900],
        ];
    }

    /**
     * Сброс всех подписок (при удалении модуля или разлогинивании)
     */
    public static function unRegister()
    {
        foreach (self::getDependences() as $regArray) {
            UnRegisterModuleDependences($regArray[0], $regArray[1], $regArray[2], $regArray[3], $regArray[4]);
        }
    }

    // Events

    /**
     * Пример обработчика подписки на события
     */
    public static function onEpilog()
    {
        Admin\OrderSender::init();
    }

    /**
     * сохранение delivery Variant Id
     *
     * @param $order
     */
    public static function handleServiceData($order)
    {
        //OrderPropsHandler::handleServiceData($order);
    }

    /**
     * Store specific calculation data from $_SESSION to order prop
     *
     * @param $oId
     * @param $arFields
     */
    public static function onOrderCreate($oId, $arFields)
    {
        OrderPropsHandler::onOrderCreate($oId, $arFields);
    }

    public static function getOrderCreatePaysystem($arUserResult, $obOrder, $arParams)
    {
        DeliveryHandler::getOrderCreatePaysystem($arUserResult, $obOrder, $arParams);
    }

    // Widget

    /**
     *
     */
    public static function loadWidjet()
    {
        WidgetHandler::loadWidjet();
    }

    /**
     * @param $content
     */
    public static function addWidjetData(&$content)
    {
        WidgetHandler::addWidjetData($content);
    }

    /**
     * @param $arResult
     * @param $arUserResult
     */
    public static function prepareData($arResult, $arUserResult)
    {
        WidgetHandler::prepareData($arResult, $arUserResult);
    }

    /**
     * @param $entity
     * @param $values
     *
     * @return bool
     */
    public static function onBeforeOrderCreate($entity, $values)
    {
        return WidgetHandler::checkDeliveryTypeProp($entity, $values);
    }

    /**
     * Register module delivery handler classes
     *
     * @return \Bitrix\Main\EventResult
     */
    public static function onSaleDeliveryHandlersClassNamesBuildList()
    {
        $result = new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            [
                // Delivery service
                '\Ipol\Catapulto\Bitrix\Handler\DeliveryHandler' => '/bitrix/modules/' . self::$MODULE_ID . '/classes/lib/Bitrix/Handler/DeliveryHandler.php',
            ]
        );

        return $result;
    }

    /**
     * Register module delivery tracking class
     *
     * @return \Bitrix\Main\EventResult
     */
    public static function onSaleDeliveryTrackingClassNamesBuildList()
    {
        $result = new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            [
                '\Ipol\Catapulto\Bitrix\Handler\Tracking' => '/bitrix/modules/' . self::$MODULE_ID . '/classes/lib/Bitrix/Handler/Tracking.php',
            ]
        );

        return $result;
    }
}
