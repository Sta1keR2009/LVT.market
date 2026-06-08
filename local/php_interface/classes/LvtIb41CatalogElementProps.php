<?php

/**
 * Показ характеристик IB=41: подмешиваем в DISPLAY_PROPERTIES все непустые пользовательские свойства.
 */
class LvtIb41CatalogElementProps
{
    private const IBLOCK_ID = 41;

    /** Служебные свойства — не выводим в блок «Характеристики». */
    private const SKIP_CODES = [
        'ETM_RELATED_CODES',
        'ETM_VARIANT_CODES',
        'ETM_PURCHASED_CODES',
        'ETM_ANALOGS',
        'ETM_RELATED_PENDING',
        'ETM_VARIANT_OPTIONS',
        'ETM_SIMILAR_ATTR',
        'ETM_IMAGE_URLS',
        'ETM_VIDEO_URLS',
        'ETMCODE',
    ];

    public static function mergeIntoDisplayProperties(array &$arResult, array $arParams): void
    {
        $iblockId = (int) ($arParams['IBLOCK_ID'] ?? 0);
        $elementId = (int) ($arResult['ID'] ?? 0);
        if ($iblockId !== self::IBLOCK_ID || $elementId <= 0) {
            return;
        }
        if (!CModule::IncludeModule('iblock')) {
            return;
        }

        if (!is_array($arResult['DISPLAY_PROPERTIES'] ?? null)) {
            $arResult['DISPLAY_PROPERTIES'] = [];
        }

        $props = self::collectElementEtmProperties($iblockId, $elementId, $arResult['PROPERTIES'] ?? []);
        if ($props === []) {
            return;
        }

        foreach ($props as $code => $prop) {
            if (isset($arResult['DISPLAY_PROPERTIES'][$code])) {
                continue;
            }
            $arResult['DISPLAY_PROPERTIES'][$code] = CIBlockFormatProperties::GetDisplayValue(
                $arResult,
                $prop
            );
        }
    }

    /**
     * @param array<string, mixed> $existingProperties
     * @return array<string, array>
     */
    private static function collectElementEtmProperties(int $iblockId, int $elementId, array $existingProperties): array
    {
        $collected = [];

        foreach ($existingProperties as $code => $prop) {
            if (!self::isPublicPropertyCode((string) $code)) {
                continue;
            }
            if (!self::propertyHasValue($prop)) {
                continue;
            }
            $collected[$code] = $prop;
        }

        if ($collected !== []) {
            return $collected;
        }

        $rs = CIBlockElement::GetProperty(
            $iblockId,
            $elementId,
            ['sort' => 'asc', 'id' => 'asc'],
            ['ACTIVE' => 'Y']
        );
        while ($row = $rs->Fetch()) {
            $code = (string) ($row['CODE'] ?? '');
            if (!self::isPublicPropertyCode($code)) {
                continue;
            }
            if ($row['VALUE'] === null || $row['VALUE'] === '') {
                continue;
            }

            if (!isset($collected[$code])) {
                $collected[$code] = $row;
                $collected[$code]['VALUE'] = [];
                $collected[$code]['~VALUE'] = [];
                $collected[$code]['DESCRIPTION'] = [];
                $collected[$code]['PROPERTY_VALUE_ID'] = [];
            }

            $collected[$code]['VALUE'][] = $row['VALUE'];
            $collected[$code]['~VALUE'][] = $row['~VALUE'] ?? $row['VALUE'];
            $collected[$code]['DESCRIPTION'][] = $row['DESCRIPTION'] ?? '';
            $collected[$code]['PROPERTY_VALUE_ID'][] = $row['PROPERTY_VALUE_ID'];
        }

        foreach ($collected as $code => $prop) {
            if (is_array($prop['VALUE']) && count($prop['VALUE']) === 1) {
                $collected[$code]['VALUE'] = $prop['VALUE'][0];
                $collected[$code]['~VALUE'] = $prop['~VALUE'][0];
                $collected[$code]['DESCRIPTION'] = $prop['DESCRIPTION'][0];
            }
        }

        return $collected;
    }

    private static function isPublicPropertyCode(string $code): bool
    {
        if ($code === '') {
            return false;
        }
        if (in_array($code, self::SKIP_CODES, true)) {
            return false;
        }
        if (str_ends_with($code, '_CODES')) {
            return false;
        }

        return true;
    }

  /**
   * @param array<string, mixed> $prop
   */
    private static function propertyHasValue(array $prop): bool
    {
        if (!array_key_exists('VALUE', $prop)) {
            return false;
        }
        $value = $prop['VALUE'];
        if (is_array($value)) {
            foreach ($value as $v) {
                if ($v !== null && $v !== '') {
                    return true;
                }
            }

            return false;
        }

        return $value !== null && $value !== '';
    }
}
