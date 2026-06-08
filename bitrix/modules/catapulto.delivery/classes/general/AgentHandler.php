<?php

namespace Ipol\Catapulto;

/**
 * Class agentHandler
 *
 * @package Ipol\Catapulto
 * Предназначен для работы с агентами. Через addAgent добавляется агент. Все функции агентов обращаются на этот класс, где уже запускаются нужные обработчики.
 *
 */
class AgentHandler extends AbstractGeneral
{
    public static function addAgent($agent, $interval = 1800)
    {
        $result = null;
        if (
            method_exists('Ipol\Catapulto\AgentHandler', $agent)
            && $agent !== 'addAgent'
        ) {
            $result = \CAgent::AddAgent('\Ipol\Catapulto\AgentHandler::' . $agent . '();', self::$MODULE_ID, "N", $interval);
        }
        
        return $result;
    }
    
    /**
     * Агент обновления статусов
     *
     * @return string
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function refreshStatuses(): string
    {
        StatusHandler::refreshOrderStates();
        return '\Ipol\Catapulto\AgentHandler::refreshStatuses();';
    }
    
    /**
     * Sync variants service data
     *
     * @return string
     */
    public static function syncServiceData()
    {
        $sync   = new \Ipol\Catapulto\Bitrix\Controller\SyncOperators();
        $sync->refreshOperators();
        return '\Ipol\Catapulto\AgentHandler::syncServiceData();';
    }
}