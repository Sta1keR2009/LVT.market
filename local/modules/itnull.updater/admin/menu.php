<?php
/**
 * Файл меню модуля ITNULL Updater
 * Добавляет пункты в раздел Marketplace административной панели
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

// Загружаем локализацию
Loc::loadMessages(__FILE__);

// Проверяем, установлен ли модуль
if (!Loader::includeModule('itnull.updater')) {
    return [];
}

// Формируем пункты меню
$aMenu = [
    'parent_menu' => 'global_menu_marketplace',
    'sort' => 100,
    'text' => Loc::getMessage('ITNULL_UPDATER_MENU_MAIN') ?: 'ITNULL Updater',
    'title' => Loc::getMessage('ITNULL_UPDATER_MENU_MAIN_TITLE') ?: 'Управление обновлениями',
    'icon' => 'update_menu_icon',
    'page_icon' => 'update_page_icon',
    'items_id' => 'menu_itnull_updater',
    'items' => [
        [
            'text' => Loc::getMessage('ITNULL_UPDATER_MENU_UPDATES') ?: 'Обновления',
            'title' => Loc::getMessage('ITNULL_UPDATER_MENU_UPDATES_TITLE') ?: 'Управление обновлениями модулей',
            'url' => 'itnull_updater.php?lang=' . LANGUAGE_ID,
            'more_url' => ['itnull_updater.php'],
        ],
        [
            'text' => Loc::getMessage('ITNULL_UPDATER_MENU_SETTINGS') ?: 'Настройки',
            'title' => Loc::getMessage('ITNULL_UPDATER_MENU_SETTINGS_TITLE') ?: 'Настройки модуля',
            'url' => 'settings.php?mid=itnull.updater&lang=' . LANGUAGE_ID,
            'more_url' => ['settings.php?mid=itnull.updater'],
        ],
    ],
];

return $aMenu;
