<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

class itnull_updater extends CModule
{
    var $MODULE_ID = "itnull.updater";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_GROUP_RIGHTS = "N";
    var $PARTNER_NAME;
    var $PARTNER_URI;

    function __construct()
    {
        $arModuleVersion = array();
        include(__DIR__."/version.php");
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = Loc::getMessage("ITNULL_UPDATER_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("ITNULL_UPDATER_MODULE_DESCRIPTION");
        $this->PARTNER_NAME = Loc::getMessage("ITNULL_UPDATER_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("ITNULL_UPDATER_PARTNER_URI");
    }

    /**
     * Подключение файла класса Patcher напрямую (до регистрации модуля)
     */
    private function includePatcher()
    {
        // Новая структура (стандарт Bitrix D7)
        $patcherFile = __DIR__ . "/../lib/patcher.php";
        if (file_exists($patcherFile) && !class_exists('\Itnull\Updater\Patcher')) {
            require_once($patcherFile);
        }
    }

    function InstallDB()
    {
        // Регистрация модуля
        ModuleManager::registerModule($this->MODULE_ID);

        // Подключаем модуль для автозагрузки классов
        Loader::includeModule($this->MODULE_ID);

        // Установка патча системных файлов
        \Itnull\Updater\Patcher::installPatch();

        // Инициализация опций модуля из default_option.php
        $this->initDefaultOptions();

        return true;
    }

    /**
     * Инициализация настроек по умолчанию из default_option.php
     */
    private function initDefaultOptions()
    {
        // Загружаем настройки по умолчанию
        $defaultOptionFile = __DIR__ . '/../default_option.php';
        if (file_exists($defaultOptionFile)) {
            include($defaultOptionFile);

            if (isset($itnull_updater_default_option) && is_array($itnull_updater_default_option)) {
                foreach ($itnull_updater_default_option as $optionName => $optionValue) {
                    // Устанавливаем только если опция ещё не задана
                    $currentValue = \Bitrix\Main\Config\Option::get($this->MODULE_ID, $optionName, null);
                    if ($currentValue === null) {
                        \Bitrix\Main\Config\Option::set($this->MODULE_ID, $optionName, $optionValue);
                    }
                }
            }
        }
    }

    function UnInstallDB()
    {
        // Подключаем Patcher напрямую, т.к. модуль будет удален
        $this->includePatcher();

        // Удаление патча системных файлов
        \Itnull\Updater\Patcher::uninstallPatch();

        // Удаление опций модуля
        \Bitrix\Main\Config\Option::delete($this->MODULE_ID);

        // Удаление регистрации модуля
        ModuleManager::unregisterModule($this->MODULE_ID);

        return true;
    }
    
    function InstallEvents()
    {
        // Меню создаётся через admin/menu.php, события не требуются
        return true;
    }

    function UnInstallEvents()
    {
        // Очищаем старые обработчики событий (если остались от предыдущих версий)
        $this->cleanupOldEventHandlers();

        return true;
    }

    /**
     * Очистка старых обработчиков событий (для совместимости)
     */
    private function cleanupOldEventHandlers()
    {
        global $DB;

        // Удаляем старые записи, если они есть
        $DB->Query("
            DELETE FROM b_module_to_module
            WHERE TO_MODULE_ID = '" . $DB->ForSql($this->MODULE_ID) . "'
        ");
    }

    function InstallFiles()
    {
        // Копирование админских файлов (кроме menu.php и lang/)
        if (is_dir($p = __DIR__."/../admin")) {
            if ($dir = opendir($p)) {
                while (false !== $item = readdir($dir)) {
                    if ($item == ".." || $item == "." || $item == "menu.php" || $item == "lang") continue;
                    CopyDirFiles($p."/".$item, $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/".$item, true, true);
                }
                closedir($dir);
            }
        }

        // Копирование файлов из install/admin (bxrestore.php и др.)
        if (is_dir($p = __DIR__."/admin")) {
            if ($dir = opendir($p)) {
                while (false !== $item = readdir($dir)) {
                    if ($item == ".." || $item == ".") continue;
                    CopyDirFiles($p."/".$item, $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/".$item, true, true);
                }
                closedir($dir);
            }
        }

        // Копирование публичных файлов
        if (is_dir($p = __DIR__."/../public")) {
            CopyDirFiles($p, $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/public", true, true);
        }

        return true;
    }

    function UnInstallFiles()
    {
        // Удаление админских файлов
        $adminFiles = [
            'itnull_updater.php',
            'itnull_updater_ajax.php',
            'itnullrestore.php'  // Файл восстановления демо-режима
        ];
        foreach ($adminFiles as $file) {
            $filePath = $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin/" . $file;
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }

        return true;
    }
    
    function DoInstall()
    {
        global $APPLICATION;

        // Очищаем старые обработчики событий (если остались)
        $this->cleanupOldEventHandlers();

        if (!IsModuleInstalled($this->MODULE_ID)) {
            $this->InstallFiles();
            $this->InstallDB();
            $this->InstallEvents();
        }

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage("ITNULL_UPDATER_INSTALL_TITLE"),
            __DIR__."/step.php"
        );
    }
    
    function DoUninstall()
    {
        global $APPLICATION;
        
        $this->UnInstallDB();
        $this->UnInstallFiles();
        $this->UnInstallEvents();
        
        $APPLICATION->IncludeAdminFile(
            Loc::getMessage("ITNULL_UPDATER_UNINSTALL_TITLE"),
            __DIR__."/unstep.php"
        );
    }
}