<?php

namespace Ipol\Catapulto;

use \Bitrix\Main\Loader;

define('CATAPULTO_DELIVERY', 'catapulto.delivery'); // обращение к коду модуля - всегда через
define('CATAPULTO_DELIVERY_LBL', 'CATAPULTO_DELIVERY_');

IncludeModuleLangFile(__FILE__);

Loader::includeModule('sale');

// автоподключение Классов библиотек модуля
spl_autoload_register(function ($className) {
    if (strpos($className, __NAMESPACE__) === 0) {
        $classPath = implode(DIRECTORY_SEPARATOR, explode('\\', substr($className, 15)));
        $filename  = __DIR__ . DIRECTORY_SEPARATOR . "classes" . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . $classPath . ".php";
        
        if (is_readable($filename) && file_exists($filename)) {
            require_once $filename;
        }
    }
});

// Подключение Главных классов
Loader::registerAutoLoadClasses(CATAPULTO_DELIVERY, [
    //General
    '\Ipol\Catapulto\AbstractGeneral'             => '/classes/general/AbstractGeneral.php',
    '\Ipol\Catapulto\SubscribeHandler'            => '/classes/general/SubscribeHandler.php',
    '\Ipol\Catapulto\AuthHandler'                 => '/classes/general/AuthHandler.php',
    '\Ipol\Catapulto\Option'                      => '/classes/general/Option.php',
    '\Ipol\Catapulto\OptionsHandler'              => '/classes/general/OptionsHandler.php',
    '\Ipol\Catapulto\OrderHandler'                => '/classes/general/OrderHandler.php',
    '\Ipol\Catapulto\AgentHandler'                => '/classes/general/AgentHandler.php',
    '\Ipol\Catapulto\WidgetHandler'               => '/classes/general/WidgetHandler.php',
    '\Ipol\Catapulto\DeliveryHandler'             => '/classes/general/DeliveryHandler.php',
    '\Ipol\Catapulto\OrderPropsHandler'           => '/classes/general/OrderPropsHandler.php',
    '\Ipol\Catapulto\PostingHandler'              => '/classes/general/PostingHandler.php',
    '\Ipol\Catapulto\StatusHandler'               => '/classes/general/StatusHandler.php',
    '\Ipol\Catapulto\CargoesHandler'              => '/classes/general/CargoesHandler.php',
    '\Ipol\Catapulto\WarehousesHandler'           => 'classes/general/WarehousesHandler.php',
    
    // DB
    '\Ipol\Catapulto\OrdersTable'                 => '/classes/db/OrdersTable.php',
    '\Ipol\Catapulto\OperatorsTable'              => '/classes/db/OperatorsTable.php',
    '\Ipol\Catapulto\OrderPropsTable'             => '/classes/db/OrderPropsTable.php',
    '\Ipol\Catapulto\WarehousesTable'             => '/classes/db/WarehousesTable.php',
    '\Ipol\Catapulto\WarehousesFreeDeliveryTable' => '/classes/db/WarehousesFreeDeliveryTable.php',
    '\Ipol\Catapulto\WarehousesOperatorsTable'    => '/classes/db/WarehousesOperatorsTable.php',
    '\Ipol\Catapulto\GeocoderCacheTable'          => '/classes/db/GeocoderCacheTable.php',
]);
