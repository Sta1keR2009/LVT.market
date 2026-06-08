<?php

use Bitrix\Main\Loader;
use Bitrix\Sale\Basket;
use Bitrix\Sale\Fuser;

class CatapultoOrderDeliveryService
{
    public const SESSION_KEY = 'lvt_catapulto_selected_rate';
    public const QUOTE_SESSION_KEY = 'lvt_catapulto_quote_cache';
    public const QUOTE_SESSION_TTL = 1800;
    public const HANDLER_MARKER = 'lvt-catapulto-delivery';

    /** @var array<string, array> */
    private static array $requestQuoteCache = [];

    public static function isCatapultoDeliveryDescription(string $text): bool
    {
        $text = mb_strtolower($text);
        if ($text === '') {
            return false;
        }

        return mb_strpos($text, 'catapulto') !== false
            || mb_strpos($text, 'лыткарино') !== false
            || mb_strpos($text, self::HANDLER_MARKER) !== false;
    }

    public static function getSelectedRate(): ?array
    {
        if (empty($_SESSION[self::SESSION_KEY]) || !is_array($_SESSION[self::SESSION_KEY])) {
            return null;
        }

        return $_SESSION[self::SESSION_KEY];
    }

    public static function saveSelectedRate(array $payload): array
    {
        $rateKey = trim((string)($payload['rateKey'] ?? ''));
        $operator = trim((string)($payload['operator'] ?? ''));
        $rateName = trim((string)($payload['rateName'] ?? ''));
        $shippingType = trim((string)($payload['shippingType'] ?? ''));
        $price = max(0, (int)($payload['price'] ?? 0));
        $deliveryMode = trim((string)($payload['deliveryMode'] ?? ''));
        $operatorName = trim((string)($payload['operatorName'] ?? ''));
        $periodText = trim((string)($payload['periodText'] ?? ''));
        $pvzAddress = trim((string)($payload['pvzAddress'] ?? ''));
        $pvzId = trim((string)($payload['pvzId'] ?? ''));
        $pvzCode = trim((string)($payload['pvzCode'] ?? ''));
        $deliveryAddress = trim((string)($payload['deliveryAddress'] ?? ''));

        if ($rateKey === '' || $operator === '' || $rateName === '') {
            return ['ok' => false, 'error' => 'invalid_rate'];
        }

        if ($deliveryMode === 'pickup' && $rateKey === 'lvt-pickup-lytkarino') {
            $price = 0;
        }

        $_SESSION[self::SESSION_KEY] = [
            'rateKey' => $rateKey,
            'operator' => $operator,
            'operatorName' => $operatorName,
            'rateName' => $rateName,
            'shippingType' => $shippingType,
            'deliveryMode' => $deliveryMode,
            'price' => $price,
            'periodText' => $periodText,
            'pvzAddress' => $pvzAddress,
            'pvzId' => $pvzId,
            'pvzCode' => $pvzCode,
            'deliveryAddress' => $deliveryAddress,
            'updatedAt' => time(),
        ];

        return ['ok' => true, 'rate' => $_SESSION[self::SESSION_KEY]];
    }

    public static function isLytkarinoCity(?string $city): bool
    {
        $city = trim(rawurldecode((string)$city));
        if ($city === '') {
            return false;
        }

        $parts = preg_split('/[,;]/u', $city) ?: [];
        foreach ($parts as $part) {
            $part = mb_strtolower(trim((string)$part));
            if ($part === 'лыткарино' || $part === 'lytkarino') {
                return true;
            }
        }

        require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/CatapultoProductDeliveryCalculator.php';

        return CatapultoProductDeliveryCalculator::normalizeCityForCache($city) === 'лыткарино';
    }

    public static function getPickupRatePayload(): array
    {
        return [
            'rateKey' => 'lvt-pickup-lytkarino',
            'operator' => 'pickup',
            'operatorName' => 'Самовывоз',
            'rateName' => 'Склад Лыткарино',
            'shippingType' => 'pickup',
            'deliveryMode' => 'pickup',
            'price' => 0,
            'periodText' => 'По готовности заказа',
            'pvzAddress' => 'Лыткарино',
        ];
    }

    public static function clearSelectedRate(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
    }

    public static function quoteForCurrentBasket(?string $city = null, bool $includePvz = true, ?string $fullAddress = null): array
    {
        if (!Loader::includeModule('sale')) {
            throw new \RuntimeException('Sale module is unavailable');
        }

        $basket = Basket::loadItemsForFUser(Fuser::getId(), SITE_ID);
        if (!$basket || $basket->isEmpty()) {
            return ['ok' => false, 'error' => 'empty_basket'];
        }

        $productId = 0;
        $maxWeight = 0.0;
        foreach ($basket->getBasketItems() as $item) {
            $weight = (float)$item->getWeight() * (float)$item->getQuantity();
            if ($weight >= $maxWeight) {
                $maxWeight = $weight;
                $productId = (int)$item->getProductId();
            }
        }

        if ($productId <= 0) {
            return ['ok' => false, 'error' => 'no_product'];
        }

        require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/LvtProductInboundDays.php';

        $inbound = LvtProductInboundDays::maxDaysForProduct($productId);
        $shift = (int)($inbound['days'] ?? 0);
        $cacheKey = self::buildQuoteCacheKey($productId, $city, $shift, $fullAddress);

        if (isset(self::$requestQuoteCache[$cacheKey])) {
            return self::decorateQuoteResult(self::$requestQuoteCache[$cacheKey]);
        }

        $sessionCached = self::getSessionQuote($cacheKey);
        if (is_array($sessionCached)) {
            self::$requestQuoteCache[$cacheKey] = $sessionCached;

            return self::decorateQuoteResult($sessionCached);
        }

        require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/CatapultoProductDeliveryCalculator.php';

        $calculator = new CatapultoProductDeliveryCalculator();
        // Всегда запрашиваем полный набор тарифов (курьер + ПВЗ) — один кеш на все сценарии.
        $result = $calculator->calculate($productId, 1, $city, $shift, true, $fullAddress);
        $result['citySource'] = $calculator->getLastCityResolveSource();
        $result['inboundDaysToLytkarino'] = $shift;
        $result['inboundShiftNote'] = (string)($inbound['note'] ?? '');
        $result['quoteCacheKey'] = $cacheKey;
        $result['fromCache'] = !empty($result['fromCache']);

        self::$requestQuoteCache[$cacheKey] = $result;
        self::setSessionQuote($cacheKey, $result);

        return self::decorateQuoteResult($result);
    }

    public static function getCheapestDelivery(array $quote): ?array
    {
        $deliveries = $quote['deliveries'] ?? [];
        if (!is_array($deliveries) || !$deliveries) {
            return null;
        }

        $cheapest = $deliveries[0];
        foreach ($deliveries as $deliveryOption) {
            if ((float)($deliveryOption['price'] ?? 0) < (float)($cheapest['price'] ?? 0)) {
                $cheapest = $deliveryOption;
            }
        }

        return $cheapest;
    }

    private static function decorateQuoteResult(array $result): array
    {
        $result['selectedRate'] = self::getSelectedRate();

        return $result;
    }

    private static function buildQuoteCacheKey(int $productId, ?string $city, int $shift, ?string $fullAddress = null): string
    {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/CatapultoProductDeliveryCalculator.php';

        $addressKey = CatapultoProductDeliveryCalculator::normalizeAddressForCache($fullAddress);

        return md5(
            $productId . '|'
            . CatapultoProductDeliveryCalculator::normalizeCityForCache($city)
            . '|' . $shift
            . '|' . $addressKey
            . '|quote_v2'
        );
    }

    private static function getSessionQuote(string $cacheKey): ?array
    {
        if (empty($_SESSION[self::QUOTE_SESSION_KEY][$cacheKey]) || !is_array($_SESSION[self::QUOTE_SESSION_KEY][$cacheKey])) {
            return null;
        }

        $entry = $_SESSION[self::QUOTE_SESSION_KEY][$cacheKey];
        $savedAt = (int)($entry['savedAt'] ?? 0);
        $payload = $entry['payload'] ?? null;

        if ($savedAt <= 0 || (time() - $savedAt) > self::QUOTE_SESSION_TTL || !is_array($payload)) {
            unset($_SESSION[self::QUOTE_SESSION_KEY][$cacheKey]);

            return null;
        }

        $payload['fromCache'] = true;

        return $payload;
    }

    private static function setSessionQuote(string $cacheKey, array $payload): void
    {
        if (!isset($_SESSION[self::QUOTE_SESSION_KEY]) || !is_array($_SESSION[self::QUOTE_SESSION_KEY])) {
            $_SESSION[self::QUOTE_SESSION_KEY] = [];
        }

        if (count($_SESSION[self::QUOTE_SESSION_KEY]) > 20) {
            $_SESSION[self::QUOTE_SESSION_KEY] = array_slice($_SESSION[self::QUOTE_SESSION_KEY], -10, null, true);
        }

        $_SESSION[self::QUOTE_SESSION_KEY][$cacheKey] = [
            'savedAt' => time(),
            'payload' => $payload,
        ];
    }
}
