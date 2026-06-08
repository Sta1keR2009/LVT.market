<?php

use Bitrix\Main\Loader;

/**
 * Оценка максимального срока (в днях) поступления товара на склад Лыткарино
 * по данным складов каталога (аналогично result_modifier карточки товара).
 */
class LvtProductInboundDays
{
    /**
     * @return array{days: int, note: string, stores: array<int, array{DELIVERY_TIME: string, QUANTITY: float}>}
     */
    public static function maxDaysForProduct(int $productId): array
    {
        $productId = (int)$productId;
        if ($productId <= 0) {
            return ['days' => 0, 'note' => '', 'stores' => []];
        }

        if (!Loader::includeModule('catalog')) {
            return ['days' => 0, 'note' => 'catalog module', 'stores' => []];
        }

        $storeData = [];
        $storeProducts = \Bitrix\Catalog\StoreProductTable::getList([
            'filter' => [
                '=PRODUCT_ID' => $productId,
                '>AMOUNT' => 0,
            ],
            'select' => ['STORE_ID', 'AMOUNT'],
        ]);

        while ($storeProduct = $storeProducts->fetch()) {
            $store = \Bitrix\Catalog\StoreTable::getList([
                'filter' => ['ID' => $storeProduct['STORE_ID']],
                'select' => ['ID', 'TITLE', 'UF_SROK_DOST', 'ACTIVE'],
            ])->fetch();

            if (!$store || $store['ACTIVE'] !== 'Y') {
                continue;
            }

            $title = (string)($store['TITLE'] ?? '');
            $sid = (int)$store['ID'];
            if (in_array($sid, [4, 5], true)
                || stripos($title, 'digi-key') !== false
                || stripos($title, 'mouser') !== false) {
                continue;
            }

            $dt = (string)($store['UF_SROK_DOST'] ?? '');
            if ($dt === '') {
                $dt = '4-5 недель';
            }

            $storeData[] = [
                'ID' => $sid,
                'DELIVERY_TIME' => $dt,
                'QUANTITY' => (float)$storeProduct['AMOUNT'],
            ];
        }

        $maxDays = 0;
        $notes = [];
        foreach ($storeData as $row) {
            $d = self::parseDeliveryTimeToDays((string)$row['DELIVERY_TIME']);
            if ($d > $maxDays) {
                $maxDays = $d;
            }
            $notes[] = 'склад ' . $row['ID'] . ': ' . $row['DELIVERY_TIME'] . ' → ~' . $d . ' дн.';
        }

        $note = $maxDays > 0
            ? 'Учтён срок поступления на Лыткарино (макс. по складам с остатком): ~' . $maxDays . ' дн. ' . implode('; ', array_slice($notes, 0, 3))
            : '';

        return [
            'days' => min(120, $maxDays),
            'note' => $note,
            'stores' => $storeData,
        ];
    }

    /**
     * Грубая оценка календарных дней для сдвига Catapulto pickup_days_shift.
     */
    public static function parseDeliveryTimeToDays(string $s): int
    {
        $s = mb_strtolower(trim($s));
        if ($s === '') {
            return 0;
        }

        if (preg_match('/(\d+)\s*[-–]\s*(\d+)\s*недел/u', $s, $m)) {
            return min(120, (int)$m[2] * 7);
        }
        if (preg_match('/(\d+)\s*недел/u', $s, $m)) {
            return min(120, (int)$m[1] * 7);
        }
        if (preg_match('/(\d+)\s*[-–]\s*(\d+)\s*(?:раб\.?\s*)?дн/u', $s, $m)) {
            return min(120, (int)$m[2]);
        }
        if (preg_match('/(\d+)\s*(?:раб\.?\s*)?дн/u', $s, $m)) {
            return min(120, (int)$m[1]);
        }
        if (preg_match('/(\d+)\s*[-–]\s*(\d+)/u', $s, $m)) {
            return min(120, (int)$m[2]);
        }
        if (preg_match('/(\d+)/u', $s, $m)) {
            return min(120, (int)$m[1]);
        }

        return 0;
    }
}
