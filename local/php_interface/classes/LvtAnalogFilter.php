<?php

declare(strict_types=1);

use Bitrix\Main\Loader;

final class LvtAnalogFilter
{
    private const JUNK_PARAMS = [
        'bxajaxid',
        'AJAX_CALL',
        'ajax',
        'AJAX_REQUEST',
        'ajax_get',
        'AJAX_POST',
        'AJAX_MODE',
        'TABS_REQUEST',
    ];

    public static function redirectIfNeeded(): void
    {
        if (empty($_GET['ANALOG_FILTER']) || !is_array($_GET['ANALOG_FILTER'])) {
            return;
        }

        $cleanQuery = self::buildCleanQuery($_GET['ANALOG_FILTER']);
        if ($cleanQuery === []) {
            return;
        }

        $needsRedirect = false;

        foreach (self::JUNK_PARAMS as $param) {
            if (isset($_GET[$param]) && $_GET[$param] !== '') {
                $needsRedirect = true;
                break;
            }
        }

        $setFilter = $_GET['set_filter'] ?? null;
        if ($setFilter === null || !in_array((string)$setFilter, ['Y', 'y'], true)) {
            $needsRedirect = true;
        }

        if (!$needsRedirect && self::currentQueryMatches($cleanQuery)) {
            return;
        }

        $path = (string)(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '/');
        $redirectUrl = $path . '?' . http_build_query($cleanQuery, '', '&', PHP_QUERY_RFC3986);

        LocalRedirect($redirectUrl, false, '302 Found');
    }

    public static function applyFromRequest(int $iblockId): void
    {
        if (empty($_GET['ANALOG_FILTER']) || !is_array($_GET['ANALOG_FILTER'])) {
            return;
        }

        if (!isset($GLOBALS['MAX_SMART_FILTER']) || !is_array($GLOBALS['MAX_SMART_FILTER'])) {
            $GLOBALS['MAX_SMART_FILTER'] = [];
        }

        if (!Loader::includeModule('iblock')) {
            return;
        }

        foreach ($_GET['ANALOG_FILTER'] as $propId => $values) {
            $propId = (int)$propId;
            if ($propId <= 0) {
                continue;
            }

            $values = array_values(array_filter(
                (array)$values,
                static fn($value) => $value !== '' && $value !== null
            ));

            if ($values === []) {
                continue;
            }

            $filterValue = count($values) === 1 ? reset($values) : $values;
            $property = \CIBlockProperty::GetByID($propId, $iblockId)->Fetch();

            if (!$property) {
                $GLOBALS['MAX_SMART_FILTER']['PROPERTY_' . $propId] = $filterValue;
                continue;
            }

            if (!empty($property['CODE'])) {
                $GLOBALS['MAX_SMART_FILTER']['PROPERTY_' . $property['CODE']] = $filterValue;
            }

            if (($property['PROPERTY_TYPE'] ?? '') === 'L') {
                $enumIds = self::resolveEnumIds($propId, $values);
                if ($enumIds !== []) {
                    $GLOBALS['MAX_SMART_FILTER']['PROPERTY_' . $propId] = count($enumIds) === 1
                        ? reset($enumIds)
                        : $enumIds;
                    continue;
                }
            }

            $GLOBALS['MAX_SMART_FILTER']['PROPERTY_' . $propId] = $filterValue;
        }
    }

    private static function buildCleanQuery(array $analogFilter): array
    {
        $query = ['set_filter' => 'Y'];

        foreach ($analogFilter as $propId => $values) {
            $propId = (int)$propId;
            if ($propId <= 0) {
                continue;
            }

            foreach ((array)$values as $value) {
                if ($value === '' || $value === null) {
                    continue;
                }
                $query['ANALOG_FILTER'][$propId][] = $value;
            }
        }

        return isset($query['ANALOG_FILTER']) ? $query : [];
    }

    private static function currentQueryMatches(array $cleanQuery): bool
    {
        foreach ($cleanQuery as $key => $value) {
            if ($key === 'ANALOG_FILTER') {
                if (!isset($_GET['ANALOG_FILTER']) || !is_array($_GET['ANALOG_FILTER'])) {
                    return false;
                }

                foreach ($value as $propId => $propValues) {
                    $current = array_values(array_filter((array)($_GET['ANALOG_FILTER'][$propId] ?? [])));
                    $expected = array_values(array_filter((array)$propValues));
                    sort($current);
                    sort($expected);
                    if ($current !== $expected) {
                        return false;
                    }
                }
                continue;
            }

            if ((string)($_GET[$key] ?? '') !== (string)$value) {
                return false;
            }
        }

        return true;
    }

    private static function resolveEnumIds(int $propertyId, array $values): array
    {
        $enumIds = [];

        foreach ($values as $value) {
            $value = trim((string)$value);
            if ($value === '') {
                continue;
            }

            $enum = \CIBlockPropertyEnum::GetList(
                [],
                ['PROPERTY_ID' => $propertyId, 'VALUE' => $value]
            )->Fetch();

            if ($enum) {
                $enumIds[] = (int)$enum['ID'];
            }
        }

        return array_values(array_unique(array_filter($enumIds)));
    }
}
