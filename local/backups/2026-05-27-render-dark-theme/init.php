<?php

use Bitrix\Main\EventManager;

// Reserve: force desktop Aspro template type if early bootstrap was skipped.
EventManager::getInstance()->addEventHandler('main', 'OnPageStart', static function (): void {
    if (!defined('TEMPLATE_TYPE')) {
        define('TEMPLATE_TYPE', 'desktop');
    }
}, false, 1);

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
    });
}
