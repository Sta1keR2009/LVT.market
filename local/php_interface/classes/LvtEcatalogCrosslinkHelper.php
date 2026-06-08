<?php

use Bitrix\Main\Loader;

/**
 * Вариант B: ссылка с карточки ИБ 11 на позицию в e-каталоге (ИБ 41) по артикулу или коду PromElec.
 */
class LvtEcatalogCrosslinkHelper
{
    public const ECATALOG_IBLOCK_ID = 41;

    public static function propertyPlainValue(array $arResult, string $code): string
    {
        $p = $arResult['PROPERTIES'][$code] ?? null;
        if (!is_array($p)) {
            return '';
        }
        $v = $p['VALUE'] ?? '';
        if (is_array($v)) {
            $v = reset($v);
        }

        return trim((string) $v);
    }

    /**
     * URL детальной в /ecatalog/ или пустая строка.
     */
    public static function resolveEcatalogDetailUrl(array $arResult): string
    {
        if (!Loader::includeModule('iblock')) {
            return '';
        }
        require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/classes/GetchipsCatalogOffersHelper.php';
        $article = GetchipsCatalogOffersHelper::resolvePrArticle($arResult);
        $promRaw = self::propertyPlainValue($arResult, 'promelec');
        $promId = (int) preg_replace('/\D/', '', $promRaw);

        $filter = [
            'IBLOCK_ID' => self::ECATALOG_IBLOCK_ID,
            'ACTIVE' => 'Y',
            'CHECK_PERMISSIONS' => 'Y',
            'MIN_PERMISSION' => 'R',
        ];
        $or = ['LOGIC' => 'OR'];
        $has = false;
        if ($article !== '' && mb_strlen($article) >= 2) {
            $or[] = ['PROPERTY_pr_article' => $article];
            $or[] = ['PROPERTY_CML2_ARTICLE' => $article];
            $has = true;
        }
        if ($promId > 0) {
            $or[] = ['PROPERTY_promelec' => $promRaw];
            $or[] = ['PROPERTY_promelec' => $promId];
            $has = true;
        }
        if (!$has) {
            return '';
        }
        $filter[] = $or;

        $res = CIBlockElement::GetList(
            ['ID' => 'ASC'],
            $filter,
            false,
            ['nTopCount' => 1],
            ['DETAIL_PAGE_URL']
        );
        if ($row = $res->GetNext()) {
            $u = trim((string) ($row['DETAIL_PAGE_URL'] ?? ''));

            return $u !== '' && stripos($u, '/ecatalog/') !== false ? $u : '';
        }

        return '';
    }
}
