<?php

use Bitrix\Main\EventManager;

if (!defined('LVT_LOCAL_CLASSES_AUTOLOAD_REGISTERED')) {
    define('LVT_LOCAL_CLASSES_AUTOLOAD_REGISTERED', true);

    spl_autoload_register(
        static function (string $className): void {
            static $classMap = null;

            if ($classMap === null) {
                $classMap = [];
                $classesDir = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes';
                if (is_dir($classesDir)) {
                    foreach (glob($classesDir . '/*.php') ?: [] as $filePath) {
                        $base = pathinfo($filePath, PATHINFO_FILENAME);
                        if ($base === '') {
                            continue;
                        }

                        $classMap[strtolower($base)] = $filePath;
                    }
                }
            }

            $normalized = ltrim($className, '\\');
            if ($normalized === '') {
                return;
            }

            $shortClass = $normalized;
            if (strpos($normalized, '\\') !== false) {
                $parts = explode('\\', $normalized);
                $shortClass = (string)end($parts);
            }

            $lookup = strtolower($shortClass);
            if ($lookup === '' || empty($classMap[$lookup])) {
                return;
            }

            require_once $classMap[$lookup];
        }
    );
}

if (!defined('LVT_CATALOG_MENU_FILTER_EVENTS_REGISTERED')) {
    define('LVT_CATALOG_MENU_FILTER_EVENTS_REGISTERED', true);

    EventManager::getInstance()->addEventHandler(
        'aspro.lite',
        'AfterAsproGetMenuChildsExt',
        static function ($arParams, &$aMenuLinksExt): void {
            if (!$aMenuLinksExt || !is_array($aMenuLinksExt) || !LvtCatalogMenuFilter::isEnabled()) {
                return;
            }

            if (empty($arParams['IS_CATALOG_IBLOCK']) && ($arParams['MENU_TYPE'] ?? '') !== 'catalog') {
                return;
            }

            $aMenuLinksExt = LvtCatalogMenuFilter::filterFlatMenuLinks($aMenuLinksExt);
        }
    );
}

if (!defined('LVT_GEO_DELIVERY_ASSETS_REGISTERED')) {
    define('LVT_GEO_DELIVERY_ASSETS_REGISTERED', true);

    EventManager::getInstance()->addEventHandler('main', 'OnEpilog', static function (): void {
        if (defined('ADMIN_SECTION') && ADMIN_SECTION === true) {
            return;
        }
        if (defined('PUBLIC_AJAX_MODE') && PUBLIC_AJAX_MODE === true) {
            return;
        }

        $asset = \Bitrix\Main\Page\Asset::getInstance();
        $asset->addJs('/local/js/lvt_geo_city_bootstrap.js');
        $asset->addJs('/local/js/catapulto_delivery_card.js');
        $asset->addCss('/local/css/lvt_geo_city.css');
        $asset->addCss('/local/css/catapulto_delivery_card.css');

        $requestUri = (string)($_SERVER['REQUEST_URI'] ?? '');
        if (preg_match('#/(order|personal/order)/#', $requestUri)) {
            $asset->addJs('/local/js/lvt_order_city.js');
            $asset->addJs('/local/js/lvt_order_address.js');
            $asset->addJs('/local/js/lvt_order_catapulto.js');
            $asset->addJs('/local/js/lvt_order_catapulto_pvz.js');
            $asset->addCss('/local/css/lvt_order_catapulto.css');
        }
    });
}

if (!defined('LVT_FRONT_SWIPER_ASSETS_REGISTERED')) {
    define('LVT_FRONT_SWIPER_ASSETS_REGISTERED', true);

    EventManager::getInstance()->addEventHandler('main', 'OnEpilog', static function (): void {
        if (defined('ADMIN_SECTION') && ADMIN_SECTION === true) {
            return;
        }
        if (defined('PUBLIC_AJAX_MODE') && PUBLIC_AJAX_MODE === true) {
            return;
        }
        if (!\Bitrix\Main\Loader::includeModule('aspro.lite') || !\CLite::IsMainPage()) {
            return;
        }

        \Aspro\Lite\Functions\Extensions::init('swiper');

        $templatePath = (string)(defined('SITE_TEMPLATE_PATH') ? SITE_TEMPLATE_PATH : '/bitrix/templates/aspro-lite-render');
        $asset = \Bitrix\Main\Page\Asset::getInstance();
        $asset->addCss($templatePath . '/vendor/css/carousel/swiper/swiper-bundle.min.css');
        $asset->addCss($templatePath . '/css/slider.swiper.min.css');
        $asset->addCss($templatePath . '/css/slider.min.css');
        $asset->addJs($templatePath . '/vendor/js/carousel/swiper/swiper-bundle.min.js');
        $asset->addJs($templatePath . '/js/slider.swiper.min.js');
    }, false, 200);
}

if (!defined('LVT_CATAPULTO_DELIVERY_HANDLER_REGISTERED')) {
    define('LVT_CATAPULTO_DELIVERY_HANDLER_REGISTERED', true);

    EventManager::getInstance()->addEventHandler(
        'sale',
        'onSaleDeliveryHandlersClassNamesBuildList',
        static function (): array {
            require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/CatapultoSaleDeliveryHandler.php';

            return [
                '\CatapultoSaleDeliveryHandler' => '/local/php_interface/classes/CatapultoSaleDeliveryHandler.php',
            ];
        }
    );
}
