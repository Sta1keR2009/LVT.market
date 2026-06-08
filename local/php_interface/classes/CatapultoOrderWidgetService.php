<?php

use Bitrix\Main\Loader;
use Ipol\Catapulto\AuthHandler;
use Ipol\Catapulto\Bitrix\Adapter\Cargo as CargoAdapter;
use Ipol\Catapulto\Bitrix\Entity\DefaultGabarites;
use Ipol\Catapulto\Bitrix\Handler\GoodsPicker;
use Ipol\Catapulto\Bitrix\Tools;
use Ipol\Catapulto\Option;
use Ipol\Catapulto\WarehousesHandler;
use Ipol\Catapulto\WidgetHandler;

class CatapultoOrderWidgetService
{
    public const WIDGET_CONTAINER_ID = 'lvt-catapulto-pvz-widget';

    public static function getConfig(?string $city, ?string $fullAddress = null): array
    {
        if (!Loader::includeModule('catapulto.delivery')) {
            return ['ok' => false, 'error' => 'catapulto_module'];
        }

        if (!AuthHandler::isAuthorized()) {
            return ['ok' => false, 'error' => 'catapulto_not_authorized'];
        }

        $city = trim(rawurldecode((string)$city));
        if ($city === '') {
            return ['ok' => false, 'error' => 'city_required'];
        }

        try {
            $widgetParams = self::buildWidgetParams($city, $fullAddress);
            $scriptInfo = self::resolveWidgetScript();

            return [
                'ok' => true,
                'city' => $city,
                'widgetParams' => $widgetParams,
                'widgetScript' => $scriptInfo['url'],
                'widgetScriptIsModule' => $scriptInfo['isModule'],
                'widgetEngine' => $scriptInfo['engine'] ?? 'legacy',
                'hasYandexKey' => trim((string)($widgetParams['widget_yandex_key'] ?? '')) !== '',
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    private static function buildWidgetParams(string $city, ?string $fullAddress = null): array
    {
        $base = self::invokeWidgetProtected('getBaseWidgetParams');

        $fullAddress = trim((string)$fullAddress);
        $locationAddress = $fullAddress !== '' ? $fullAddress : $city;

        $location = [
            'address' => $locationAddress,
            'city' => $city,
        ];

        $params = array_merge($base, [
            'location' => $location,
            'cargo' => self::buildCargoParams(),
            'need_insurance' => true,
            'insured_value' => self::getBasketInsuredValue(),
            'popup_mode' => true,
            'only_delivery_type' => 'Pvz',
            'startTabMap' => true,
            'defaultAddress' => $locationAddress,
            'link' => self::WIDGET_CONTAINER_ID,
        ]);

        return $params;
    }

    private static function getBasketInsuredValue(): float
    {
        try {
            return (float)WidgetHandler::getBasketFinalPrice();
        } catch (\Throwable $e) {
            $goods = GoodsPicker::fromBasket();
            if (empty($goods)) {
                return 0.0;
            }

            return (float)array_sum(array_column($goods, 'PRICE'));
        }
    }

    private static function buildCargoParams(): array
    {
        $goods = GoodsPicker::fromBasket();
        if (empty($goods)) {
            $goods = [Tools::makeSimpleGood()];
        }

        $obCargo = new CargoAdapter(new DefaultGabarites());
        $cargos = $obCargo->set($goods)->getCargo();
        $dims = $cargos->getDimensions();

        $cargoComment = '';
        foreach ($goods as $item) {
            $cargoComment .= ($item['NAME'] ?? '') . '(' . ($item['QUANTITY'] ?? 1) . ');';
        }

        return [
            'length' => $dims['L'],
            'width' => $dims['W'],
            'height' => $dims['H'],
            'quantity' => $cargos->getQuantity(),
            'weight' => $cargos->getWeight(),
            'cargo_comment' => $cargoComment,
        ];
    }

    /**
     * @return array{url: string, isModule: bool}
     */
    private static function resolveWidgetScript(): array
    {
        $jsPath = Tools::getJSPath();
        $minRel = $jsPath . 'widjet/catapultowidget.min.js';
        $minAbs = $_SERVER['DOCUMENT_ROOT'] . $minRel;
        $useLocal = Option::get('use_widget_local') === 'Y';

        if (($useLocal || is_readable($minAbs)) && is_readable($minAbs)) {
            return ['url' => $minRel, 'isModule' => false, 'engine' => 'v3'];
        }

        $legacyRel = $jsPath . 'widjet/catapultowidget.js';
        if (is_readable($_SERVER['DOCUMENT_ROOT'] . $legacyRel)) {
            return ['url' => $legacyRel, 'isModule' => false, 'engine' => 'legacy'];
        }

        return [
            'url' => 'https://widgetcdn.catapulto.ru/assets/js/catapulto-widget/v3/catapulto-widget.js',
            'isModule' => true,
            'engine' => 'v3',
        ];
    }

    private static function invokeWidgetProtected(string $method): array
    {
        $warehouse = WarehousesHandler::getDefaultWarehouse();

        $rm = new \ReflectionMethod(WidgetHandler::class, $method);
        $rm->setAccessible(true);

        return (array)$rm->invoke(null);
    }
}
