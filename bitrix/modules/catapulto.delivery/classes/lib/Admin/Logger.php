<?php
namespace Ipol\Catapulto\Admin;

use Ipol\Catapulto\Bitrix\Tools;

/**
 * Class Logger
 * @package Ipol\Catapulto
 * Класс для создания логов. Пишутся либо в Tools::getJSPath()/logs/
 */
class Logger
{
    /**
     * @param $wat - что именно пишем
     * @param bool $src - имя файла, если задан - будет писаться в путь к js-файлам в папке логов, иначе - в корень сайта
     * с названием KM+log.txt
     * @param bool $flags - флаги, типичные для file_put_contents
     */
    public static function toLog($wat, $src = false, $flags = false){
        if(!$flags){
            $flags = array('ADMIN' => false, 'APPEND' => false);
        }

        if(/*!$flags['ADMIN'] ||*/ Tools::isAdmin()) {
            if ($src) {
                file_put_contents(self::getFileName($src), "\n" . date('H:i:s d.m.Y') . "\n" . $wat."\n", FILE_APPEND);
            } else {
                self::toLogFile($wat,$flags);
            }
        }
    }

    /**
     * @param $src - путь к bitrix/js/модуль/src/
     * @return bool|string
     * Получение содержимого лога в папке логов. Нужен, например, для вывода инфы в опциях.
     */
    public static function getLogInfo($src){
        if(
            !self::checkSrc(true) ||
            !file_exists(self::getFileName($src))
        ){
            return '';
        } else {
            return file_get_contents(self::getFileName($src));
        }
    }

    /**
     * @param $src
     * Очищаем файлы лога
     */
    public static function clearLog($src){
        if(
            self::checkSrc(true) ||
            file_exists(self::getFileName($src))
        ) {
            unlink(self::getFileName($src));
        }
    }

    /**
     * @param bool $noCreate - не создавать, если папки нет
     * @return bool
     * проверяем, есть ли папка src в логах
     */
    protected static function checkSrc($noCreate = false){
        $exist = file_exists(self::getRootPath());
        if(!$exist && !$noCreate){
            mkdir(self::getRootPath(),0777, 1);
        }
        return $exist;
    }

    /**
     * @param bool $src
     * @return string
     * получаем полный путь к файлу лога
     */
    protected static function getFileName($src = false){
        if(!$src){
            return self::getRootPath()."Catapulto_common_log".BitrixLoggerController::FILE_FORMAT;
        } else {
            self::checkSrc();
            return self::getRootPath().$src.BitrixLoggerController::FILE_FORMAT;
        }
    }

    /**
     * @return string
     * Получение пути к папке с логами. У нас она лежит в bitrix/js/catapulto.delivery/
     */
    protected static function getRootPath()
    {
        return BitrixLoggerController::getPath();
    }

    // simpleLog

    protected static $fileLink = false;

    /**
     * @param $wat
     * @param array $flags
     * если нам надо тупо записать лог в корень сайта
     */
    protected static function toLogFile($wat, $flags=array('APPEND'=>false)){
        if(!self::$fileLink){
            self::$fileLink = fopen(self::getFileName(),($flags['APPEND']) ? 'a' : 'w');
            fwrite(self::$fileLink,"\n\n".date('H:i:s d.m.Y')."\n");
        }
        fwrite(self::$fileLink,print_r($wat,true)."\n");
    }

    // toOptions

    /**
     * @param $src
     * @return string
     * Выводим содержимое лога в html-е (как правило, в опциях)
     */
    public static function toOptions($src)
    {
        $strInfo   = self::getLogInfo($src);
        $strReturn = '';

        if($strInfo){
            $arInfo = explode("\n\n",$strInfo);
            rsort($arInfo);
            foreach ($arInfo as $text){
                if($text){
                    $strReturn .= str_replace("\n","<br>",$text)."<br>";
                }
            }
        }

        return $strReturn;
    }
}