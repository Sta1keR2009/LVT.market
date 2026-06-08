<?php

use Bitrix\Main\Loader;

/**
 * Разбор позиции Mouser Search API → ступени цен в ₽, наличие (RU), характеристики для модалки.
 */
class MouserPartOffersHelper
{
    /** @var array<string, string>|null */
    private static $attrRuMap = null;

    /**
     * @return array<string, string>
     */
    private static function loadAttrRuMap(): array
    {
        if (self::$attrRuMap !== null) {
            return self::$attrRuMap;
        }
        $path = dirname(__DIR__) . '/mouser_attribute_ru_map.php';
        $m = (is_file($path) && is_array($loaded = include $path)) ? $loaded : [];
        self::$attrRuMap = [];
        foreach ($m as $k => $v) {
            if (is_string($k) && is_string($v)) {
                self::$attrRuMap[mb_strtolower(trim($k))] = $v;
            }
        }

        return self::$attrRuMap;
    }

    public static function attributeNameToRu(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return '';
        }
        $map = self::loadAttrRuMap();
        $k = mb_strtolower($name);

        return $map[$k] ?? $name;
    }

    public static function attributeValueToRu(string $attributeNameEn, string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        $v = $value;
        $rep = [
            'RoHS Compliant' => 'Соответствует RoHS',
            'RoHS Compliant By Exemption' => 'Соответствует RoHS (по исключению)',
            'Not RoHS Compliant' => 'Не соответствует RoHS',
            'Lead Free' => 'Без свинца',
            'Contains Lead' => 'Содержит свинец',
            'In Stock' => 'На складе',
            'Out of Stock' => 'Нет в наличии',
            'Ships Today' => 'Отправка сегодня',
            'Factory Stock' => 'Склад завода',
            'SMD/SMT' => 'SMD/SMT',
            'Through Hole' => 'Выводной монтаж',
            'Not Available' => 'Нет данных',
            'Mouser does not ship this product to your region' => 'Mouser не поставляет эту продукцию в ваш регион',
            'Currently Mouser does not ship this product to your region' => 'В настоящее время Mouser не поставляет эту продукцию в ваш регион',
        ];
        foreach ($rep as $en => $ru) {
            if (stripos($v, $en) !== false) {
                $v = str_ireplace($en, $ru, $v);
            }
        }

        return $v;
    }

    /**
     * Количество на складе: только явные числовые поля или число в строке «N In Stock».
     * Если числа нет — null (считаем, что количество в API не передано).
     */
    public static function parseStockQtyFromPart(array $p): ?int
    {
        $ais = $p['AvailabilityInStock'] ?? null;
        if ($ais !== null && $ais !== '' && is_numeric($ais)) {
            return max(0, (int) $ais);
        }
        $fs = $p['FactoryStock'] ?? null;
        if ($fs !== null && $fs !== '' && is_numeric($fs)) {
            return max(0, (int) $fs);
        }
        $avail = trim((string) ($p['Availability'] ?? ''));
        if ($avail === '') {
            return null;
        }
        if (preg_match('/^(\d[\d\s,\xc2\xa0]*)\s+In Stock/i', $avail, $m)) {
            $n = (int) preg_replace('/\D/', '', $m[1]);

            return $n;
        }
        if (preg_match('/^0+\s+In Stock/i', $avail)) {
            return 0;
        }
        if (stripos($avail, 'Out of Stock') !== false || stripos($avail, 'Non-Stocked') !== false) {
            return 0;
        }

        return null;
    }

    public static function availabilityLabelRu(array $p): string
    {
        $q = self::parseStockQtyFromPart($p);
        if ($q === null) {
            return 'Нет в наличии';
        }
        if ($q <= 0) {
            return 'Нет в наличии';
        }

        return 'В наличии: ' . $q . ' шт.';
    }

    public static function leadTimeDisplayRu(array $p): string
    {
        $s = trim((string) ($p['LeadTime'] ?? ''));
        if ($s === '') {
            return '—';
        }
        $out = $s;
        $out = preg_replace_callback('/(\d+)\s*Days?\b/i', static function ($m) {
            $n = (int) $m[1];

            return $n . ' ' . self::daysWordRu($n);
        }, $out);
        $out = preg_replace_callback('/(\d+)\s*Weeks?\b/i', static function ($m) {
            $n = (int) $m[1];

            return $n . ' ' . self::weeksWordRu($n);
        }, $out);
        $phrases = [
            'Ships Today' => 'Отправка сегодня',
            'Ships from Factory' => 'Поставка с завода',
            'Ships from Stock' => 'Отправка со склада',
        ];
        foreach ($phrases as $en => $ru) {
            if (stripos($out, $en) !== false) {
                $out = str_ireplace($en, $ru, $out);
            }
        }

        return $out;
    }

    private static function daysWordRu(int $n): string
    {
        $n = abs($n) % 100;
        $n1 = $n % 10;
        if ($n > 10 && $n < 20) {
            return 'дней';
        }
        if ($n1 > 1 && $n1 < 5) {
            return 'дня';
        }
        if ($n1 === 1) {
            return 'день';
        }

        return 'дней';
    }

    private static function weeksWordRu(int $n): string
    {
        $n = abs($n) % 100;
        $n1 = $n % 10;
        if ($n > 10 && $n < 20) {
            return 'недель';
        }
        if ($n1 > 1 && $n1 < 5) {
            return 'недели';
        }
        if ($n1 === 1) {
            return 'неделя';
        }

        return 'недель';
    }

    public static function categoryTextFromPart(array $p): string
    {
        $c = $p['Category'] ?? null;
        if (is_string($c)) {
            return trim($c);
        }
        if (is_array($c)) {
            return trim((string) ($c['Name'] ?? $c['Text'] ?? $c['Value'] ?? ''));
        }

        return '';
    }

    /**
     * @return list<array{label:string, value:string, href:string}>
     */
    public static function buildSpecsRowsFromPart(array $p): array
    {
        $rows = [];
        $seen = [];

        $push = static function (string $labelRu, string $value, string $href = '') use (&$rows, &$seen): void {
            $labelRu = trim($labelRu);
            $value = trim($value);
            if ($labelRu === '' || $value === '') {
                return;
            }
            $k = mb_strtolower($labelRu);
            if (isset($seen[$k])) {
                return;
            }
            $seen[$k] = true;
            $rows[] = ['label' => $labelRu, 'value' => $value, 'href' => $href];
        };

        $mfr = trim((string) ($p['Manufacturer'] ?? ''));
        if ($mfr !== '') {
            $push('Производитель', $mfr);
        }
        $mpn = trim((string) ($p['ManufacturerPartNumber'] ?? ''));
        if ($mpn !== '') {
            $push('Артикул производителя', $mpn);
        }
        $mpnM = trim((string) ($p['MouserPartNumber'] ?? ''));
        if ($mpnM !== '') {
            $push('Номер Mouser', $mpnM);
        }
        $desc = trim((string) ($p['Description'] ?? ''));
        if ($desc !== '') {
            $push('Описание', $desc);
        }
        $cat = self::categoryTextFromPart($p);
        if ($cat !== '') {
            $push('Категория', $cat);
        }
        $rohs = trim((string) ($p['ROHSStatus'] ?? $p['RoHSStatus'] ?? ''));
        if ($rohs !== '') {
            $push('RoHS', self::attributeValueToRu('RoHS', $rohs));
        }
        $life = trim((string) ($p['LifecycleStatus'] ?? ''));
        if ($life !== '') {
            $push('Статус жизненного цикла', self::attributeValueToRu('Lifecycle', $life));
        }

        $push('Наличие', self::availabilityLabelRu($p));
        $push('Срок поставки', self::leadTimeDisplayRu($p));

        $ds = trim((string) ($p['DataSheetUrl'] ?? ''));
        if ($ds !== '') {
            $push('Даташит', 'Открыть документ', $ds);
        }
        $detail = trim((string) ($p['ProductDetailUrl'] ?? ''));
        if ($detail !== '') {
            $push('Карточка на сайте Mouser', 'Перейти', $detail);
        }

        $attrs = $p['ProductAttributes'] ?? [];
        if (is_array($attrs)) {
            foreach ($attrs as $a) {
                if (!is_array($a)) {
                    continue;
                }
                $nEn = trim((string) ($a['AttributeName'] ?? $a['Name'] ?? ''));
                $v = trim((string) ($a['AttributeValue'] ?? $a['Value'] ?? ''));
                if ($nEn === '' || $v === '') {
                    continue;
                }
                $lab = self::attributeNameToRu($nEn);
                $val = self::attributeValueToRu($nEn, $v);
                $push($lab, $val);
            }
        }

        return $rows;
    }

    public static function parseMouserPriceToFloat($v): ?float
    {
        if ($v === null || $v === '') {
            return null;
        }
        if (is_numeric($v)) {
            return (float) $v;
        }
        $s = preg_replace('/[^\d.,-]/', '', (string) $v);
        $s = str_replace(',', '', $s);

        return is_numeric($s) ? (float) $s : null;
    }

    /**
     * @return list<array{qty:int, price:float}>
     */
    public static function buildTiersFromPart(array $p): array
    {
        $breaks = $p['PriceBreaks'] ?? [];
        if (!is_array($breaks)) {
            return [];
        }
        $out = [];
        foreach ($breaks as $b) {
            if (!is_array($b)) {
                continue;
            }
            $qty = (int) ($b['Quantity'] ?? $b['Qty'] ?? 0);
            $price = self::parseMouserPriceToFloat($b['Price'] ?? null);
            if ($qty <= 0 || $price === null || $price <= 0) {
                continue;
            }
            $out[] = ['qty' => $qty, 'price' => $price];
        }
        usort($out, static function ($a, $b) {
            return $a['qty'] <=> $b['qty'];
        });

        return $out;
    }

    public static function currencyFromPart(array $p): string
    {
        $breaks = $p['PriceBreaks'] ?? [];
        if (is_array($breaks)) {
            foreach ($breaks as $b) {
                if (is_array($b) && !empty($b['Currency'])) {
                    return strtoupper(trim((string) $b['Currency']));
                }
            }
        }

        return 'USD';
    }

    /**
     * @param list<array{qty:int, price:float}> $tiers
     * @return list<array{qty:int, rub:float}>
     */
    public static function convertTiersToRub(array $tiers, string $cur): array
    {
        $cur = strtoupper(trim($cur));
        if ($cur === '') {
            $cur = 'USD';
        }
        $result = [];
        foreach ($tiers as $t) {
            $p = (float) $t['price'];
            $rub = GetchipsCatalogOffersHelper::convertToRubByCbr($p, $cur);
            $result[] = ['qty' => (int) $t['qty'], 'rub' => $rub];
        }

        return $result;
    }

    public static function parseLeadDays(string $lead): int
    {
        $lead = trim($lead);
        if ($lead === '') {
            return 0;
        }
        if (preg_match('/(\d+)\s*Day/i', $lead, $m)) {
            return max(0, (int) $m[1]);
        }
        if (preg_match('/(\d+)\s*Week/i', $lead, $m)) {
            return max(0, (int) $m[1]) * 7;
        }
        if (preg_match('/(\d+)\s*Month/i', $lead, $m)) {
            return max(0, (int) $m[1]) * 30;
        }
        if (stripos($lead, 'today') !== false || stripos($lead, 'stock') !== false) {
            return 1;
        }

        return 0;
    }

    /**
     * @return array{
     *   tiers_rub: list<array{qty:int, rub:float}>,
     *   source_currency: string,
     *   first_source_price: ?float,
     *   lead_days: int,
     *   lead_label_ru: string,
     *   stock_label_ru: string,
     *   in_stock_qty: ?int,
     *   min_order: int,
     *   order_step: int,
     *   price_sort_value: float,
     *   lead_sort_value: float,
     *   brand_card: array{NAME: string, URL: string, IMG_SRC: string}
     * }
     */
    public static function enrichPartForTable(array $p): array
    {
        require_once __DIR__ . '/GetchipsCatalogOffersHelper.php';

        $tiers = self::buildTiersFromPart($p);
        $cur = self::currencyFromPart($p);
        $tiersRub = self::convertTiersToRub($tiers, $cur);
        $firstSrc = $tiers !== [] ? (float) $tiers[0]['price'] : null;

        $leadRaw = (string) ($p['LeadTime'] ?? '');
        $leadDays = self::parseLeadDays($leadRaw);
        $leadLabelRu = self::leadTimeDisplayRu($p);

        $stockQty = self::parseStockQtyFromPart($p);
        $stockLabelRu = self::availabilityLabelRu($p);

        $minOrder = max(1, (int) ($p['Min'] ?? 1));
        $orderStep = max(1, (int) ($p['Mult'] ?? 1));

        $firstRub = 0.0;
        if ($tiersRub !== []) {
            $sorted = $tiersRub;
            usort($sorted, static function ($a, $b) {
                return ((int) ($a['qty'] ?? 0)) <=> ((int) ($b['qty'] ?? 0));
            });
            $firstRub = (float) ($sorted[0]['rub'] ?? 0);
        }

        $mfr = trim((string) ($p['Manufacturer'] ?? ''));
        $brandCard = GetchipsCatalogOffersHelper::resolveBrandFromIblock6($mfr);

        return [
            'tiers_rub' => $tiersRub,
            'source_currency' => $cur,
            'first_source_price' => $firstSrc,
            'lead_days' => $leadDays,
            'lead_label_ru' => $leadLabelRu,
            'stock_label_ru' => $stockLabelRu,
            'in_stock_qty' => $stockQty,
            'min_order' => $minOrder,
            'order_step' => $orderStep,
            'price_sort_value' => $firstRub,
            'lead_sort_value' => $leadDays > 0 ? (float) $leadDays : 999999.0,
            'brand_card' => $brandCard,
        ];
    }

    public static function absoluteImageUrl(string $path): string
    {
        require_once __DIR__ . '/GetchipsCatalogOffersHelper.php';

        return GetchipsCatalogOffersHelper::toAbsoluteCatalogPublicUrl($path);
    }
}
