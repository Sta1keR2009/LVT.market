<?php
namespace Ipol\Catapulto;

use Ipol\Catapulto\Admin\BitrixLoggerController;
use Ipol\Catapulto\Admin\Logger;
use Ipol\Catapulto\Bitrix\Controller\AbstractController;
use Ipol\Catapulto\Bitrix\Entity\Cache;
use Ipol\Catapulto\Bitrix\Entity\Encoder;
use Ipol\Catapulto\Bitrix\Entity\Options;
use Ipol\Catapulto\Bitrix\Tools;

IncludeModuleLangFile(__FILE__);


/**
 * Class optionsHandler
 * @package Ipol\Catapulto
 * Тут представлены методы для работы со страницей насроек модуля. Обработчики аяксов и прочая
 */

class OptionsHandler extends AbstractGeneral
{
    // common
    /**
     * @param bool $noFdb - вызывается ли по аяксу (нужно что-то в ответ прислать, чтобы понять, что все ок)
     * Очистка кэша модуля.
     */
    public static function clearCache($noFdb = false)
    {
        $cacheObj = new Cache();
        $obCache = new \CPHPCache();
        $obCache->CleanDir($cacheObj->getPath());

        // Cool new D7 cache
        $path = '/'.self::getMODULEID().'/';
        $cacheInstance = \Bitrix\Main\Data\Cache::createInstance();
        $cacheInstance->CleanDir($path);
    }

    /**
     * @param $params
     * Очищаем файлы лога (см. Admin/Logger)
     */
    public static function clearLog($params)
    {
        if(array_key_exists('src',$params)){
            Logger::clearLog($params['src']);
        }
    }

    /**
     * @return bool|string
     *
     * returns path for logs
     */
    public static function getAPILogs()
    {
        $someController = new AbstractController(self::$MODULE_ID,self::$MODULE_LBL);

        $path = BitrixLoggerController::getPath().$someController->getLoggerName().BitrixLoggerController::FILE_FORMAT;

        $return = (file_exists($path)) ? BitrixLoggerController::getRelativePath().$someController->getLoggerName().BitrixLoggerController::FILE_FORMAT : false;

        if(Tools::isModuleAjaxRequest()){
            echo Tools::jsonEncode(array('file'=>$return));
        }

        return $return;
    }

    public static function turnOffLogging()
    {
        $collection = Option::collection();

        foreach ($collection as $optionId => $arDescr)
        {
            if($arDescr['group'] === 'debug_request')
            {
                Option::set($optionId,'N');
            }
        }
    }

    public static function checkLoggingMain()
    {
        $collection = Option::collection();


        foreach ($collection as $optionId => $arDescr)
        {
            if($arDescr['group'] === 'debug_main')
            {
                Option::set($optionId,'Y');
            }
        }
    }

}