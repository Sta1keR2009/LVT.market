<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

/**
 * Отображение позиций Getchips/PromElec в «Поделиться корзиной» (товар-заглушка + свойства GETCHIPS_*).
 */
class GetchipsShareBasketDisplayHelper
{
    private const CATALOG_IBLOCK_FALLBACK = 11;

    /** @var list<string> */
    private const HIDDEN_BASKET_PROP_CODES = [
        'GETCHIPS_CONTEXT_DETAIL_URL',
        'GETCHIPS_CONTEXT_ELEMENT_ID',
        'GETCHIPS_IMG',
        'GETCHIPS_TIERS_RUB_JSON',
        'GETCHIPS_PRICE_RUB',
        'GETCHIPS_SOURCE_CCY',
        'GETCHIPS_SOURCE_PRICE',
        'GETCHIPS_PROVIDER',
        'GETCHIPS_URL',
        'GETCHIPS_RATE_DATE',
        'GETCHIPS_LEAD_DAYS',
        'GETCHIPS_BRAND',
    ];

    public static function enrichShareBasketResult(array &$arResult): void
    {
        if (empty($arResult['SHARE_BASKET']['ITEMS']) || !is_array($arResult['SHARE_BASKET']['ITEMS'])) {
            return;
        }

        self::bootstrap();

        foreach ($arResult['SHARE_BASKET']['ITEMS'] as $block => &$arItems) {
            if (!is_array($arItems)) {
                continue;
            }
            foreach ($arItems as &$arItem) {
                if (empty($arItem['PRODUCT']) || !is_array($arItem['PRODUCT'])) {
                    continue;
                }
                if (self::applyToProduct($arItem['PRODUCT'], $arItem['BASKET_PROPS'] ?? [])) {
                    $arItem['BASKET_PROPS'] = self::filterBasketPropsForDisplay($arItem['BASKET_PROPS'] ?? []);
                }
            }
            unset($arItem);
        }
        unset($arItems);
    }

    /**
     * @param array<string, mixed> $arProduct
     * @param list<array<string, mixed>>|array<string, mixed> $basketProps
     */
    public static function applyToProduct(array &$arProduct, $basketProps): bool
    {
        $propsByCode = self::basketPropsByCode($basketProps);
        $part = self::propValue($propsByCode, 'GETCHIPS_PART');
        if ($part === '') {
            return false;
        }

        self::bootstrap();

        $ctxElementId = (int) self::propValue($propsByCode, 'GETCHIPS_CONTEXT_ELEMENT_ID');
        $stubId = (int) (defined('GETCHIPS_STUB_PRODUCT_ID') ? GETCHIPS_STUB_PRODUCT_ID : 0);
        if ($stubId > 0 && $ctxElementId === $stubId) {
            $ctxElementId = 0;
        }

        $detailUrl = self::resolveDetailUrl($propsByCode, $part, $ctxElementId);
        if ($detailUrl !== '') {
            $arProduct['DETAIL_PAGE_URL'] = $detailUrl;
        }

        $gcImg = self::resolveImageSrc($propsByCode, $ctxElementId);
        if ($gcImg !== '') {
            $arProduct['PREVIEW_PICTURE'] = ['SRC' => $gcImg];
            unset($arProduct['DETAIL_PICTURE']);
        }

        $arProduct['NAME'] = $part;
        $arProduct['PROPERTY_CML2_ARTICLE_VALUE'] = $part;

        return true;
    }

    /**
     * @param list<array<string, mixed>>|array<string, mixed> $basketProps
     * @return list<array<string, mixed>>
     */
    public static function filterBasketPropsForDisplay($basketProps): array
    {
        if (!is_array($basketProps) || $basketProps === []) {
            return [];
        }

        $hidden = array_flip(self::HIDDEN_BASKET_PROP_CODES);
        $out = [];
        foreach ($basketProps as $key => $prop) {
            if (!is_array($prop)) {
                continue;
            }
            $code = strtoupper(trim((string) ($prop['CODE'] ?? $key)));
            if ($code === '' || isset($hidden[$code])) {
                continue;
            }
            $out[$code] = $prop;
        }

        return $out;
    }

    private static function bootstrap(): void
    {
        if (!defined('GETCHIPS_PUBLIC_SITE_URL')) {
            $path = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/getchips_site.php';
            if (is_readable($path)) {
                require_once $path;
            }
        }
        if (!class_exists('GetchipsCatalogOffersHelper', false)) {
            $path = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/GetchipsCatalogOffersHelper.php';
            if (is_readable($path)) {
                require_once $path;
            }
        }
        if (!class_exists('GetchipsPartnumberSearchHelper', false)) {
            $path = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/GetchipsPartnumberSearchHelper.php';
            if (is_readable($path)) {
                require_once $path;
            }
        }
        if (!class_exists('MouserCatalogElementHelper', false)) {
            $path = $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/MouserCatalogElementHelper.php';
            if (is_readable($path)) {
                require_once $path;
            }
        }
    }

    /**
     * @param list<array<string, mixed>>|array<string, mixed> $basketProps
     * @return array<string, array<string, mixed>>
     */
    private static function basketPropsByCode($basketProps): array
    {
        $byCode = [];
        if (!is_array($basketProps)) {
            return $byCode;
        }
        foreach ($basketProps as $key => $prop) {
            if (!is_array($prop)) {
                continue;
            }
            $code = strtoupper(trim((string) ($prop['CODE'] ?? $key)));
            if ($code !== '') {
                $byCode[$code] = $prop;
            }
        }

        return $byCode;
    }

    /**
     * @param array<string, array<string, mixed>> $propsByCode
     */
    private static function propValue(array $propsByCode, string $code): string
    {
        if (!isset($propsByCode[$code])) {
            return '';
        }
        $prop = $propsByCode[$code];
        $v = $prop['~VALUE'] ?? $prop['VALUE'] ?? '';
        if (is_array($v)) {
            $v = reset($v);
        }

        return trim((string) $v);
    }

    /**
     * @param array<string, array<string, mixed>> $propsByCode
     */
    private static function resolveDetailUrl(array $propsByCode, string $part, int $ctxElementId): string
    {
        if ($ctxElementId > 0 && class_exists('GetchipsPartnumberSearchHelper', false)) {
            $fromElement = GetchipsPartnumberSearchHelper::getElementDetailUrl($ctxElementId);
            if ($fromElement !== '') {
                return class_exists('GetchipsCatalogOffersHelper', false)
                    ? GetchipsCatalogOffersHelper::toAbsoluteCatalogPublicUrl($fromElement)
                    : $fromElement;
            }
        }

        $stored = self::propValue($propsByCode, 'GETCHIPS_CONTEXT_DETAIL_URL');
        if ($stored !== '' && class_exists('GetchipsCatalogOffersHelper', false)) {
            return GetchipsCatalogOffersHelper::toAbsoluteCatalogPublicUrl($stored);
        }

        $catalogIblockId = self::catalogIblockId();
        if ($catalogIblockId <= 0) {
            return '';
        }

        $foundId = 0;
        if (class_exists('MouserCatalogElementHelper', false)) {
            $foundId = MouserCatalogElementHelper::findElementIdByPartnerQueries($catalogIblockId, $part);
        }
        if ($foundId <= 0 && class_exists('GetchipsPartnumberSearchHelper', false)) {
            $ids = GetchipsPartnumberSearchHelper::findLocalProductElementIds($catalogIblockId, $part);
            if ($ids !== []) {
                $foundId = (int) reset($ids);
            }
        }
        if ($foundId > 0 && class_exists('GetchipsPartnumberSearchHelper', false)) {
            $url = GetchipsPartnumberSearchHelper::getElementDetailUrl($foundId);

            return $url !== '' && class_exists('GetchipsCatalogOffersHelper', false)
                ? GetchipsCatalogOffersHelper::toAbsoluteCatalogPublicUrl($url)
                : $url;
        }

        return '';
    }

    /**
     * @param array<string, array<string, mixed>> $propsByCode
     */
    private static function resolveImageSrc(array $propsByCode, int $ctxElementId): string
    {
        $gcImg = self::propValue($propsByCode, 'GETCHIPS_IMG');
        if ($gcImg === '' && $ctxElementId > 0 && Loader::includeModule('iblock')) {
            $picRes = CIBlockElement::GetList(
                [],
                ['ID' => $ctxElementId],
                false,
                false,
                ['ID', 'DETAIL_PICTURE', 'PREVIEW_PICTURE']
            );
            if ($picRow = $picRes->GetNext()) {
                $fid = (int) ($picRow['DETAIL_PICTURE'] ?? 0);
                if ($fid <= 0) {
                    $fid = (int) ($picRow['PREVIEW_PICTURE'] ?? 0);
                }
                if ($fid > 0) {
                    $picPath = CFile::GetPath($fid);
                    if (is_string($picPath) && $picPath !== '') {
                        $gcImg = $picPath;
                    }
                }
            }
        }

        if ($gcImg !== '' && class_exists('GetchipsCatalogOffersHelper', false)) {
            return GetchipsCatalogOffersHelper::toAbsoluteCatalogPublicUrl($gcImg);
        }

        return $gcImg;
    }

    private static function catalogIblockId(): int
    {
        $moduleId = defined('VENDOR_MODULE_ID') ? (string) VENDOR_MODULE_ID : 'aspro.lite';
        $id = (int) Option::get($moduleId, 'CATALOG_IBLOCK_ID', 0);

        return $id > 0 ? $id : self::CATALOG_IBLOCK_FALLBACK;
    }
}
