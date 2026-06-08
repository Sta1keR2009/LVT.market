<?php

use \Ipol\Catapulto\Bitrix\Tools;

use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

Loader::includeModule('catapulto.delivery');

/*
 * ADDING MENU:
 * 1. add installer in install\admin - use simple.php
 * 2. add the content for admin page - use admin\simple.php
 * 3. check langs
 * */


if ($GLOBALS['APPLICATION']->GetGroupRight(CATAPULTO_DELIVERY) > 'D') // checking rights
{
    // Main menu block
    $aMenu = [
        'parent_menu' => 'global_menu_store', // IM menu block
        'section'     => 'catapulto',
        'sort'        => 110,
        'text'        => Tools::getMessage('MENU_MAIN_TEXT'),
        'title'       => Tools::getMessage('MENU_MAIN_TITLE'),
        'icon'        => 'catapulto_delivery_menu_icon', // CSS for icon
        'page_icon'   => 'catapulto_delivery_page_icon', // CSS for icon
        'module_id'   => CATAPULTO_DELIVERY,
        'items_id'    => CATAPULTO_DELIVERY_LBL . 'menu',
        'items'       => [],
    ];
    
    // Parent pages
    $aMenu['items'][] = [
        'text'      => Tools::getMessage('MENU_ORDERS_TEXT'),
        'title'     => Tools::getMessage('MENU_ORDERS_TITLE'),
        'module_id' => CATAPULTO_DELIVERY,
        'url'       => 'catapulto_delivery_orders.php?lang=' . LANGUAGE_ID,
        //"more_url" => array("catapulto_delivery_orders_edit.php")  // Use it for admin pages like "Edit order with ID=..." and it will be marked in this menu as "opened"
    ];
    
    $aMenu['items'][] = [
        'text'      => Tools::getMessage('MENU_SYNC_DATA_TEXT'),
        'title'     => Tools::getMessage('MENU_SYNC_DATA_TITLE'),
        'module_id' => CATAPULTO_DELIVERY,
        'url'       => 'catapulto_delivery_sync_data.php?lang=' . LANGUAGE_ID,
    ];
    
    $aMenu['items'][] = [
        'text'      => Tools::getMessage('MENU_ORDERS_MASS_TEXT'),
        'title'     => Tools::getMessage('MENU_ORDERS_MASS_TITLE'),
        'module_id' => CATAPULTO_DELIVERY,
        'url'       => 'catapulto_delivery_orders_mass.php?lang=' . LANGUAGE_ID,
    ];
    
    $aMenu['items'][] = [
        'text'      => Tools::getMessage('MENU_OPTIONS_TEXT'),
        'title'     => Tools::getMessage('MENU_OPTIONS_TITLE'),
        'module_id' => CATAPULTO_DELIVERY,
        'url'       => 'catapulto_delivery_options.php?lang=' . LANGUAGE_ID,
    ];
    
    return $aMenu;
}

return false;