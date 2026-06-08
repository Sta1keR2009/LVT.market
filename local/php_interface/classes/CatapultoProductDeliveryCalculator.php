<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Data\Cache;

class CatapultoProductDeliveryCalculator
{
    private const DEFAULT_CITY = 'Москва';
    private const DEFAULT_WEIGHT_G = 1000;
    private const DEFAULT_DIMENSION_MM = 100;
    /** @see Catapulto widget: door = d2d/w2d, pickup point = w2w/d2w */
    private const SHIPPING_TYPE_FILTER_COURIER = 'd2d,w2d';
    private const SHIPPING_TYPE_FILTER_ALL = 'd2d,w2d,w2w,d2w';
    private const RATE_POLL_ATTEMPTS = 4;
    private const RATE_POLL_INTERVAL_SEC = 1;
    private const RATE_POLL_MIN_RESULTS = 4;
    private const CACHE_TTL = 86400;
    private const CACHE_DIR = '/catapulto_delivery';
    private string $lastCityResolveSource = 'unknown';

    private const OPERATOR_NAMES = [
        'cdek' => 'СДЭК',
        'cse' => 'КСЭ',
        'dostavista' => 'Dostavista',
        'yandex_dostavka' => 'Яндекс Доставка',
        'dpd' => 'DPD',
        'boxberry' => 'Boxberry',
        'pochta' => 'Почта России',
        'pek' => 'ПЭК',
        'dl' => 'Деловые Линии',
        'ems' => 'EMS',
        'iml' => 'IML',
        'hermes' => 'Hermes',
    ];

    /**
     * @param int|null $inboundShiftDays Сдвиг даты отгрузки Catapulto (дни до поступления на склад Лыткарино)
     */
    public function calculate(
        int $productId,
        int $quantity = 1,
        ?string $city = null,
        ?int $inboundShiftDays = null,
        bool $includePvz = false,
        ?string $fullAddress = null
    ): array
    {
        $quantity = max(1, $quantity);
        $shift = max(0, min(120, (int)($inboundShiftDays ?? 0)));
        $this->bootstrapModules();

        $targetCity = $this->resolveCity($city);
        $addressKey = self::normalizeAddressForCache($fullAddress);
        // Единый ключ кеша: всегда считаем полный набор тарифов (курьер + ПВЗ).
        $cacheId = md5($productId . '|' . self::normalizeCityForCache($targetCity) . '|' . $quantity . '|st_v9|' . $shift . '|' . $addressKey);

        $cache = Cache::createInstance();
        if ($cache->initCache(self::CACHE_TTL, $cacheId, self::CACHE_DIR)) {
            $cached = $cache->getVars();
            if (!empty($cached['result'])) {
                $cachedResult = $cached['result'];
                $cachedResult['fromCache'] = true;

                return $this->filterDeliveriesByMode($cachedResult, $includePvz);
            }
        }

        $cache->startDataCache();

        try {
            $result = $this->doCalculate($productId, $quantity, $targetCity, $shift, true, $fullAddress);
        } catch (\Throwable $e) {
            $cache->abortDataCache();
            throw $e;
        }

        $cache->endDataCache(['result' => $result]);

        return $this->filterDeliveriesByMode($result, $includePvz);
    }

    public static function normalizeCityForCache(?string $city): string
    {
        $city = trim(rawurldecode((string)$city));
        if ($city === '') {
            return '';
        }

        if (mb_strpos($city, ',') !== false) {
            $parts = array_values(array_filter(array_map('trim', explode(',', $city)), static function (string $part): bool {
                if ($part === '') {
                    return false;
                }
                $lower = mb_strtolower($part);

                return !in_array($lower, ['россия', 'russia'], true);
            }));
            if ($parts) {
                $city = (string)end($parts);
            }
        }

        return mb_strtolower($city);
    }

    public static function normalizeAddressForCache(?string $address): string
    {
        $address = trim(preg_replace('/\s+/u', ' ', rawurldecode((string)$address)) ?? '');
        if ($address === '') {
            return '';
        }

        return md5(mb_strtolower($address));
    }

    public function getLastCityResolveSource(): string
    {
        return $this->lastCityResolveSource;
    }

    private function doCalculate(
        int $productId,
        int $quantity,
        string $targetCity,
        int $inboundShiftDays = 0,
        bool $includePvz = false,
        ?string $fullAddress = null
    ): array
    {
        if (!\Ipol\Catapulto\AuthHandler::isAuthorized()) {
            throw new \RuntimeException('Catapulto module is not authorized');
        }

        $product = $this->getProduct($productId);

        $geoTerm = trim((string)$fullAddress) !== '' ? trim((string)$fullAddress) : $targetCity;

        $widget = new \Ipol\Catapulto\WidgetHandler();
        $geo = $this->safeWidgetGetGeo($widget, $geoTerm, $targetCity);
        if (!is_array($geo) || empty($geo['id'])) {
            throw new \RuntimeException('Cannot resolve city in Catapulto: ' . $targetCity);
        }

        $resolvedCityName = trim((string)($geo['locality'] ?? $geo['name'] ?? $targetCity));
        if ($resolvedCityName === '') {
            $resolvedCityName = $targetCity;
        }

        $warehouse = \Ipol\Catapulto\WarehousesHandler::getDefaultWarehouse();
        if (empty($warehouse) || empty($warehouse['ID']) || empty($warehouse['CATAPULTO_CONTACT_ID'])) {
            throw new \RuntimeException('Default Catapulto warehouse is not configured');
        }

        $senderCity = (string)($warehouse['CITY_NAME'] ?? 'Лыткарино');

        $createPayload = [
            'warehouseId' => (int)$warehouse['ID'],
            'delivery_type' => 'parcel',
            'location' => ['term' => $resolvedCityName],
            'receiver_locality_id' => (int)$geo['id'],
            'sender_contact_data' => ['cityFrom' => $senderCity],
            'cargo_data' => [
                'cargo_comment' => $product['name'],
                'height' => $product['height_mm'],
                'length' => $product['length_mm'],
                'width' => $product['width_mm'],
                'quantity' => $quantity,
                'weight' => $product['weight_g'],
            ],
        ];

        $createRate = $widget->widgetCreateRate($createPayload);
        if (!is_array($createRate) || empty($createRate['key'])) {
            throw new \RuntimeException('Catapulto rate creation failed');
        }

        $rateId = (string)$createRate['key'];
        $rateParams = [
            'rate_id' => $rateId,
            'shipping_type_filter' => self::SHIPPING_TYPE_FILTER_ALL,
            'pickup_days_shift' => $inboundShiftDays,
            'services_filter' => '',
            'need_insurance' => false,
            'insured_value' => 0,
        ];

        $rate = null;
        $bestRate = null;
        $bestResultsCount = -1;
        for ($attempt = 0; $attempt < self::RATE_POLL_ATTEMPTS; $attempt++) {
            if ($attempt > 0) {
                sleep(self::RATE_POLL_INTERVAL_SEC);
            }
            $rate = $widget->widgetGetRate($rateParams);
            if (is_array($rate)) {
                $resultsCount = is_array($rate['results'] ?? null) ? count($rate['results']) : 0;
                if ($resultsCount > $bestResultsCount) {
                    $bestResultsCount = $resultsCount;
                    $bestRate = $rate;
                }
            }
            if (is_array($rate) && !empty($rate['rate_completed'])) {
                break;
            }
            if ($bestResultsCount >= self::RATE_POLL_MIN_RESULTS && $attempt >= 1) {
                break;
            }
        }

        if (!is_array($rate) && !is_array($bestRate)) {
            throw new \RuntimeException('Catapulto rate read failed after polling');
        }
        if (is_array($bestRate)) {
            $rate = $bestRate;
        }

        $deliveries = [];
        $results = is_array($rate['results'] ?? null) ? $rate['results'] : [];
        $seenComposite = [];
        foreach ($results as $item) {
            $shippingType = strtolower((string)($item['shipping_type'] ?? $item['shipping.type'] ?? ''));
            $allowedTypes = ['d2d', 'w2d', 'w2w', 'd2w'];
            if ($shippingType !== '' && !in_array($shippingType, $allowedTypes, true)) {
                continue;
            }

            $operator = (string)($item['operator'] ?? '');
            $rateName = (string)($item['rate'] ?? '');
            $price = (int)($item['price'] ?? 0);
            $transitDays = (string)($item['transit_days'] ?? '');
            $deliveryDay = (string)($item['delivery_day'] ?? '');

            $normName = preg_replace('/\s+/u', ' ', mb_strtolower(trim($rateName)));
            $compositeKey = strtolower($operator) . '|' . $normName . '|' . $price . '|' . $shippingType;
            if (isset($seenComposite[$compositeKey])) {
                continue;
            }
            $seenComposite[$compositeKey] = true;

            $isPvz = in_array($shippingType, ['w2w', 'd2w'], true);
            $deliveries[] = [
                'operator' => $operator,
                'operatorName' => self::OPERATOR_NAMES[strtolower($operator)] ?? mb_strtoupper($operator),
                'rateName' => $rateName,
                'shippingType' => $shippingType,
                'deliveryMode' => $isPvz ? 'pvz' : 'courier',
                'deliveryModeLabel' => $isPvz ? 'Пункт выдачи' : 'Курьером',
                'periodText' => $this->formatPeriodText($transitDays),
                'deliveryDay' => $this->formatDeliveryDay($deliveryDay),
                'price' => $price,
                'priceFormatted' => number_format($price, 0, '.', ' ') . ' ₽',
                'rateKey' => md5(strtolower($operator) . '|' . $normName . '|' . $price . '|' . $shippingType),
            ];
        }

        usort($deliveries, static function (array $a, array $b): int {
            $cmp = strcmp($a['operator'], $b['operator']);
            return $cmp !== 0 ? $cmp : ($a['price'] <=> $b['price']);
        });

        $pickup = $this->getPickupData();

        return [
            'ok' => true,
            'city' => $resolvedCityName,
            'requestedCity' => $targetCity,
            'requestedAddress' => trim((string)$fullAddress),
            'productName' => $product['name'],
            'quantity' => $quantity,
            'senderCity' => $senderCity,
            'pickupDaysShift' => $inboundShiftDays,
            'rateCompleted' => (bool)($rate['rate_completed'] ?? false),
            'pickup' => $pickup,
            'deliveries' => $deliveries,
            'disclaimer' => 'Стоимость и сроки доставки являются ориентировочными. Итоговая стоимость и срок будут рассчитаны на странице оформления заказа. Сроки ТК учитывают поступление на склад Лыткарино (сдвиг ' . $inboundShiftDays . ' дн.).',
        ];
    }

    private function getPickupData(): array
    {
        return [
            'name' => 'Самовывоз',
            'locations' => ['Лыткарино', 'Москва'],
            'price' => 0,
            'priceFormatted' => 'Бесплатно',
        ];
    }

    private function bootstrapModules(): void
    {
        foreach (['iblock', 'catalog', 'sale', 'catapulto.delivery'] as $module) {
            if (!Loader::includeModule($module)) {
                throw new \RuntimeException('Failed to include module: ' . $module);
            }
        }
    }

    private function getProduct(int $productId): array
    {
        $res = \CIBlockElement::GetList([], ['ID' => $productId], false, false, ['ID', 'NAME']);
        $element = $res->GetNext();
        if (!$element) {
            throw new \RuntimeException('Product not found: ' . $productId);
        }

        $catalogProduct = \CCatalogProduct::GetByID($productId) ?: [];
        $weight = (int)round((float)($catalogProduct['WEIGHT'] ?? 0));
        $length = (int)round((float)($catalogProduct['LENGTH'] ?? 0));
        $width = (int)round((float)($catalogProduct['WIDTH'] ?? 0));
        $height = (int)round((float)($catalogProduct['HEIGHT'] ?? 0));

        return [
            'name' => (string)$element['NAME'],
            'weight_g' => max(1, $weight ?: self::DEFAULT_WEIGHT_G),
            'length_mm' => max(1, $length ?: self::DEFAULT_DIMENSION_MM),
            'width_mm' => max(1, $width ?: self::DEFAULT_DIMENSION_MM),
            'height_mm' => max(1, $height ?: self::DEFAULT_DIMENSION_MM),
        ];
    }

    private function resolveCity(?string $city): string
    {
        require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/SaleLocationCityService.php';
        $resolved = SaleLocationCityService::resolveCityForDelivery($city);
        $resolvedCity = trim((string)($resolved['city'] ?? ''));
        $resolvedSource = trim((string)($resolved['source'] ?? ''));

        $this->lastCityResolveSource = $resolvedSource !== '' ? $resolvedSource : 'unknown';

        $resolvedCity = $this->normalizeCityLabel($resolvedCity);
        return $resolvedCity !== '' ? $resolvedCity : self::DEFAULT_CITY;
    }

    private function normalizeCityLabel(string $city): string
    {
        $city = trim(rawurldecode($city));
        if ($city === '') {
            return '';
        }

        if (mb_strpos($city, ',') !== false) {
            $parts = array_values(array_filter(array_map('trim', explode(',', $city)), static function (string $part): bool {
                if ($part === '') {
                    return false;
                }
                $lower = mb_strtolower($part);

                return !in_array($lower, ['россия', 'russia'], true);
            }));
            if ($parts) {
                return (string)end($parts);
            }
        }

        return $city;
    }

    /**
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    private function filterDeliveriesByMode(array $result, bool $includePvz): array
    {
        if ($includePvz || empty($result['deliveries']) || !is_array($result['deliveries'])) {
            return $result;
        }

        $result['deliveries'] = array_values(array_filter(
            $result['deliveries'],
            static fn(array $item): bool => ($item['deliveryMode'] ?? '') !== 'pvz'
        ));

        return $result;
    }

    /**
     * Catapulto widgetGetGeo() падает, если API не вернул локацию (getFirst() === false).
     *
     * @return array<string, mixed>|null
     */
    private function safeWidgetGetGeo(\Ipol\Catapulto\WidgetHandler $widget, string $term, ?string $cityFallback = null): ?array
    {
        foreach ($this->buildCityGeoCandidates($term, $cityFallback) as $candidate) {
            try {
                $geo = $widget->widgetGetGeo(['term' => $candidate]);
                if (is_array($geo) && !empty($geo['id'])) {
                    return $geo;
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return null;
    }

    /**
     * @return string[]
     */
    private function buildCityGeoCandidates(string $term, ?string $cityFallback = null): array
    {
        $term = trim(rawurldecode($term));
        $candidates = [];

        $add = static function (string $value) use (&$candidates): void {
            $value = trim($value);
            if ($value !== '' && !in_array($value, $candidates, true)) {
                $candidates[] = $value;
            }
        };

        $add($term);

        if ($term !== '' && mb_strpos($term, ',') !== false) {
            $parts = array_values(array_filter(array_map('trim', explode(',', $term)), static function (string $part): bool {
                if ($part === '') {
                    return false;
                }
                $lower = mb_strtolower($part);

                return !in_array($lower, ['россия', 'russia'], true);
            }));
            if ($parts) {
                $add(implode(', ', $parts));
                $add((string)end($parts));
            }
        }

        $cityLabel = $this->normalizeCityLabel($cityFallback ?? $term);
        $add($cityLabel);

        if ($cityLabel !== '' && preg_match('/^г\.?\s*(.+)$/ui', $cityLabel, $m)) {
            $add(trim((string)$m[1]));
        }

        if ($cityLabel !== self::DEFAULT_CITY) {
            $add(self::DEFAULT_CITY);
        }

        return $candidates;
    }

    private function formatPeriodText(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return 'срок уточняется';
        }
        if (preg_match('/\d+/', $value, $m)) {
            return 'от ' . $m[0] . ' раб. дней';
        }
        return $value;
    }

    private function formatDeliveryDay(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        try {
            $dt = new \DateTime($value);
            $months = ['', 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
            $days = ['вс', 'пн', 'вт', 'ср', 'чт', 'пт', 'сб'];
            return 'с ' . $dt->format('d') . ' ' . $months[(int)$dt->format('n')] . ', ' . $days[(int)$dt->format('w')];
        } catch (\Throwable $e) {
            return $value;
        }
    }
}
