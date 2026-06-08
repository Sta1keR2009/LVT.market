<?php

use Bitrix\Sale\Delivery\CalculationResult;
use Bitrix\Sale\Delivery\Services\Base;
use Bitrix\Sale\Shipment;
use Bitrix\Main\Localization\Loc;

/**
 * Custom delivery handler that calculates shipping cost via Catapulto API.
 * Registered in init.php via onSaleDeliveryHandlersClassNamesBuildList event.
 * Origin: Lytkarino warehouse -> customer city (door-to-door).
 */
class CatapultoSaleDeliveryHandler extends Base
{
    public const HANDLER_CODE = 'catapulto_lvt';

    protected static $isCalculatePriceImmediately = true;
    protected static $canHasProfiles = false;
    protected static $whetherAdminExtraServicesShow = false;

    public static function getClassTitle(): string
    {
        return 'Доставка через Catapulto (LVT)';
    }

    public static function getClassDescription(): string
    {
        return 'Расчёт доставки от склада Лыткарино до города получателя через агрегатор Catapulto';
    }

    public function isCompatible(Shipment $shipment): bool
    {
        $order = $shipment->getCollection()->getOrder();
        $props = $order->getPropertyCollection();
        if (!$props) return false;

        $locationProp = $props->getDeliveryLocation();
        if (!$locationProp || !$locationProp->getValue()) return false;

        return true;
    }

    public function isCalculatePriceImmediately(): bool
    {
        return self::$isCalculatePriceImmediately;
    }

    protected function calculateConcrete(Shipment $shipment = null): CalculationResult
    {
        $result = new CalculationResult();

        if (!$shipment) {
            $result->setDeliveryPrice(0);
            return $result;
        }

        $order = $shipment->getCollection()->getOrder();
        $props = $order->getPropertyCollection();

        $locationCode = '';
        if ($locationProp = $props->getDeliveryLocation()) {
            $locationCode = $locationProp->getValue();
        }

        $cityName = '';
        if ($locationCode) {
            $cityName = $this->getCityNameByLocationCode($locationCode);
        }
        if (!$cityName && !empty($_COOKIE['lvt_sale_location_code'])) {
            $cityName = $this->getCityNameByLocationCode((string)$_COOKIE['lvt_sale_location_code']);
        }
        if (!$cityName && !empty($_COOKIE['lvt_display_city'])) {
            $cityName = trim(rawurldecode((string)$_COOKIE['lvt_display_city']));
        }
        if (!$cityName) {
            $result->setDeliveryPrice(0);
            $result->setPeriodDescription('Укажите город доставки');
            return $result;
        }

        try {
            require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/CatapultoProductDeliveryCalculator.php';
            require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/CatapultoOrderDeliveryService.php';

            if (CatapultoOrderDeliveryService::isLytkarinoCity($cityName)) {
                $result->setDeliveryPrice(0);
                $result->setPeriodDescription('По готовности заказа');
                $result->setDescription('Самовывоз со склада: Лыткарино');
                return $result;
            }

            $selectedRate = CatapultoOrderDeliveryService::getSelectedRate();
            if ($selectedRate && !empty($selectedRate['rateKey'])) {
                $price = (float)($selectedRate['price'] ?? 0);
                $result->setDeliveryPrice($price);
                $period = trim((string)($selectedRate['periodText'] ?? ''));
                if ($period !== '') {
                    $result->setPeriodDescription($period);
                }
                $label = trim((string)($selectedRate['operatorName'] ?? ''));
                $rateName = trim((string)($selectedRate['rateName'] ?? ''));
                $pvzAddress = trim((string)($selectedRate['pvzAddress'] ?? ''));
                $deliveryAddress = trim((string)($selectedRate['deliveryAddress'] ?? ''));
                $desc = '';
                if ($label !== '' && $rateName !== '') {
                    $desc = $label . ': ' . $rateName;
                } elseif ($label !== '') {
                    $desc = $label;
                }
                if ($pvzAddress !== '') {
                    $desc = $desc !== '' ? ($desc . '. ПВЗ: ' . $pvzAddress) : ('ПВЗ: ' . $pvzAddress);
                } elseif ($deliveryAddress !== '') {
                    $desc = $desc !== '' ? ($desc . '. Адрес: ' . $deliveryAddress) : ('Адрес: ' . $deliveryAddress);
                }
                if ($desc !== '') {
                    $result->setDescription($desc);
                }
                return $result;
            }

            $quote = CatapultoOrderDeliveryService::quoteForCurrentBasket($cityName);
            if (!empty($quote['ok']) && !empty($quote['deliveries'])) {
                $cheapest = CatapultoOrderDeliveryService::getCheapestDelivery($quote);
                if ($cheapest) {
                    $price = (float)($cheapest['price'] ?? 0);
                    $period = (string)($cheapest['periodText'] ?? '');

                    $result->setDeliveryPrice($price);
                    $result->setPeriodDescription($period);
                    $result->setDescription(
                        CatapultoOrderDeliveryService::HANDLER_MARKER . '. '
                        . 'Расчёт доставки от склада Лыткарино до города получателя'
                    );
                    return $result;
                }
            }

            $result->setDeliveryPrice(0);
            $result->setPeriodDescription(!empty($quote['ok']) ? 'Расчёт доставки недоступен' : 'Расчёт доставки недоступен');
        } catch (\Throwable $e) {
            $result->setDeliveryPrice(0);
            $result->setPeriodDescription('Ошибка расчёта доставки');
        }

        return $result;
    }

    private function getCityNameByLocationCode(string $code): string
    {
        if (!\Bitrix\Main\Loader::includeModule('sale')) {
            return '';
        }

        $code = trim($code);
        if ($code === '') {
            return '';
        }

        $lang = defined('LANGUAGE_ID') ? LANGUAGE_ID : 'ru';
        $path = '';

        if (ctype_digit($code)) {
            $path = (string)\Bitrix\Sale\Location\Admin\LocationHelper::getLocationStringById((int)$code, [
                'LANGUAGE_ID' => $lang,
                'DELIMITER' => ', ',
                'INVERSE' => false,
            ]);
        }

        if ($path === '') {
            $path = (string)\Bitrix\Sale\Location\Admin\LocationHelper::getLocationStringByCode($code, [
                'LANGUAGE_ID' => $lang,
                'DELIMITER' => ', ',
                'INVERSE' => false,
            ]);
        }

        $path = trim($path);
        if ($path !== '') {
            return $path;
        }

        $row = \Bitrix\Sale\Location\LocationTable::getList([
            'filter' => ['=CODE' => $code],
            'select' => ['ID'],
            'limit' => 1,
        ])->fetch();

        if (!empty($row['ID'])) {
            return (string)\Bitrix\Sale\Location\Admin\LocationHelper::getLocationStringById((int)$row['ID'], [
                'LANGUAGE_ID' => $lang,
                'DELIMITER' => ', ',
                'INVERSE' => false,
            ]);
        }

        return '';
    }

    public static function getChildrenClassNames(): array
    {
        return [];
    }

    public static function canHasProfiles(): bool
    {
        return self::$canHasProfiles;
    }

    public static function whetherAdminExtraServicesShow(): bool
    {
        return self::$whetherAdminExtraServicesShow;
    }
}
