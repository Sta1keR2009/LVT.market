<?php

use Bitrix\Main\Data\Cache;

/**
 * Онлайн item_data_get PromElec с файловым кешем до 2 суток.
 */
class PromElecOnlineHelper
{
    private const CACHE_DIR = '/promelec/item_data/';
    private const CACHE_TTL = 172800;

    /**
     * @return array{ok:bool, item?:array, error?:string}
     */
    public static function fetchItemCached(int $itemId): array
    {
        if ($itemId <= 0) {
            return ['ok' => false, 'error' => 'Некорректный item_id'];
        }
        $cache = Cache::createInstance();
        $key = 'item_' . $itemId;
        if ($cache->initCache(self::CACHE_TTL, $key, self::CACHE_DIR)) {
            $v = $cache->getVars();
            if (is_array($v['payload'] ?? null)) {
                return ['ok' => true, 'item' => $v['payload']];
            }
        }
        $raw = self::requestItem($itemId);
        if (!$raw['ok']) {
            return $raw;
        }
        $cache->startDataCache();
        $cache->endDataCache(['payload' => $raw['item']]);

        return ['ok' => true, 'item' => $raw['item']];
    }

    /**
     * @return array{ok:bool, item?:array, error?:string}
     */
    private static function requestItem(int $itemId): array
    {
        $cfgPath = $_SERVER['DOCUMENT_ROOT'] . '/config/api_promelec.php';
        if (!is_file($cfgPath)) {
            return ['ok' => false, 'error' => 'Нет config/api_promelec.php'];
        }
        $cfg = include $cfgPath;
        if (!is_array($cfg)) {
            return ['ok' => false, 'error' => 'Конфиг PromElec не массив'];
        }
        $login = (string) ($cfg['login'] ?? '');
        $password = (string) ($cfg['password'] ?? '');
        $customerId = (int) ($cfg['customer_id'] ?? 0);
        $url = (string) ($cfg['api_url'] ?? '');
        if ($login === '' || $password === '' || $customerId <= 0 || $url === '') {
            return ['ok' => false, 'error' => 'Неполный конфиг PromElec'];
        }
        $body = json_encode([
            'login' => $login,
            'password' => strtoupper(md5($password)),
            'customer_id' => $customerId,
            'method' => 'item_data_get',
            'item_id' => $itemId,
        ], JSON_UNESCAPED_UNICODE);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_TIMEOUT => 45,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);
        $resp = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (!is_string($resp) || $resp === '') {
            return ['ok' => false, 'error' => 'Пустой ответ PromElec HTTP ' . $code];
        }
        $data = json_decode($resp, true);
        if (!is_array($data)) {
            return ['ok' => false, 'error' => 'Некорректный JSON PromElec'];
        }
        if (!empty($data['error'])) {
            return ['ok' => false, 'error' => (string) $data['error']];
        }
        // Ответ — один товар или массив с одним элементом
        if (isset($data['item_id'])) {
            return ['ok' => true, 'item' => $data];
        }
        if (isset($data[0]) && is_array($data[0])) {
            return ['ok' => true, 'item' => $data[0]];
        }

        return ['ok' => false, 'error' => 'Неожиданная структура item_data_get'];
    }

    /**
     * Строки для таблицы: склады/поставщики из vendors + первая цена из pricebreaks.
     *
     * @return list<array{name:string, qty:string, price:string}>
     */
    public static function buildTableRows(array $item): array
    {
        $rows = [];
        $name = trim((string) ($item['name'] ?? 'PromElec'));
        if (!empty($item['vendors']) && is_array($item['vendors'])) {
            foreach ($item['vendors'] as $v) {
                if (!is_array($v)) {
                    continue;
                }
                $vid = (string) ($v['vendor'] ?? '');
                $q = isset($v['quant']) ? (string) (int) $v['quant'] : '—';
                $rows[] = [
                    'name' => $name . ' (склад/поставщик ' . $vid . ')',
                    'qty' => $q,
                    'price' => self::firstPriceLabel($item),
                ];
            }
        }
        if ($rows === []) {
            $rows[] = [
                'name' => $name,
                'qty' => '—',
                'price' => self::firstPriceLabel($item),
            ];
        }

        return $rows;
    }

    /**
     * Нормализованные строки PromElec для рендера в таблице Getchips.
     *
     * @return list<array{
     *   part:string,
     *   supplier:string,
     *   brand:string,
     *   stock:int,
     *   lead_label:string,
     *   lead_sort:float,
     *   tiers_rub:list<array{qty:int,rub:float}>,
     *   min_order:int,
     *   order_step:int,
     *   pack_norm:int,
     *   source_currency:string,
     *   source_price:float,
     *   provider:string,
     *   url:string
     * }>
     */
    public static function buildGetchipsRows(array $item): array
    {
        $rows = [];
        $part = trim((string)($item['producer_code'] ?? $item['name'] ?? ''));
        if ($part === '') {
            $part = '—';
        }
        $brand = trim((string)($item['producer_name'] ?? ''));
        if ($brand === '') {
            $brand = '—';
        }
        $defaultLeadLabel = trim((string)($item['edge'] ?? ''));
        if ($defaultLeadLabel === '') {
            $defaultLeadLabel = '—';
        }
        $defaultLeadSort = 999999.0;
        $sourceUrl = trim((string)($item['url'] ?? $item['link'] ?? ''));

        $pricing = self::extractPricing($item);
        $tiersRub = $pricing['tiers_rub'];
        $sourceCurrency = $pricing['source_currency'];
        $sourcePrice = $pricing['source_price'];
        $minOrder = $pricing['min_order'];
        $packNorm = isset($item['pack_quant']) ? max(1, (int)$item['pack_quant']) : $minOrder;
        $orderStep = $minOrder;

        $vendors = is_array($item['vendors'] ?? null) ? $item['vendors'] : [];
        if ($vendors !== []) {
            foreach ($vendors as $vendor) {
                if (!is_array($vendor)) {
                    continue;
                }
                $vendorId = trim((string)($vendor['vendor'] ?? ''));
                $qty = max(0, (int)($vendor['quant'] ?? 0));
                $supplier = $vendorId !== '' ? ('Внешний склад ' . $vendorId) : 'Внешний склад';
                $vendorTiers = [];
                $vendorPb = $vendor['pricebreaks'] ?? $vendor['price_breaks'] ?? [];
                if (is_array($vendorPb)) {
                    foreach ($vendorPb as $vp) {
                        if (!is_array($vp)) {
                            continue;
                        }
                        $vpQty = max(1, (int)($vp['quant'] ?? 1));
                        $vpPrice = (float)($vp['price'] ?? 0);
                        if ($vpPrice <= 0) {
                            continue;
                        }
                        $vendorTiers[] = ['qty' => $vpQty, 'rub' => $vpPrice];
                    }
                }
                usort($vendorTiers, static function ($a, $b) {
                    return ((int)($a['qty'] ?? 0)) <=> ((int)($b['qty'] ?? 0));
                });
                $rowTiers = $vendorTiers !== [] ? $vendorTiers : $tiersRub;
                $rowMinOrder = 1;
                $deliveryDays = isset($vendor['delivery']) ? (int)$vendor['delivery'] : 0;
                if ((int)$vendorId === 0) {
                    $rowLeadLabel = 'В наличии';
                } else {
                    $rowLeadLabel = $deliveryDays > 0 ? ($deliveryDays . ' дн.') : $defaultLeadLabel;
                }
                $rowLeadSort = $deliveryDays > 0 ? (float)$deliveryDays : $defaultLeadSort;
                $rowSourcePrice = $sourcePrice;
                if ($rowTiers !== []) {
                    $rowSourcePrice = (float)($rowTiers[0]['rub'] ?? $sourcePrice);
                }
                $rows[] = [
                    'part' => $part,
                    'supplier' => $supplier,
                    'brand' => $brand,
                    'stock' => $qty,
                    'lead_label' => $rowLeadLabel,
                    'lead_sort' => $rowLeadSort,
                    'tiers_rub' => $rowTiers,
                    'min_order' => $rowMinOrder,
                    'order_step' => 1,
                    'pack_norm' => $packNorm,
                    'source_currency' => $sourceCurrency,
                    'source_price' => $rowSourcePrice,
                    'provider' => 'promelec',
                    'url' => $sourceUrl,
                ];
            }
        }

        if ($rows === []) {
            $fallbackQty = isset($item['quant']) ? max(0, (int)$item['quant']) : 0;
            $rows[] = [
                'part' => $part,
                'supplier' => 'Внешний склад',
                'brand' => $brand,
                'stock' => $fallbackQty,
                'lead_label' => $defaultLeadLabel,
                'lead_sort' => $defaultLeadSort,
                'tiers_rub' => $tiersRub,
                'min_order' => $minOrder,
                'order_step' => $orderStep,
                'pack_norm' => $packNorm,
                'source_currency' => $sourceCurrency,
                'source_price' => $sourcePrice,
                'provider' => 'promelec',
                'url' => $sourceUrl,
            ];
        }

        return $rows;
    }

    /**
     * @return array{
     *   tiers_rub:list<array{qty:int,rub:float}>,
     *   source_currency:string,
     *   source_price:float,
     *   min_order:int
     * }
     */
    private static function extractPricing(array $item): array
    {
        $tiers = [];
        $sourceCurrency = strtoupper(trim((string)($item['currency'] ?? 'RUB')));
        if ($sourceCurrency === '') {
            $sourceCurrency = 'RUB';
        }
        $sourcePrice = 0.0;

        $priceBreaks = $item['pricebreaks'] ?? $item['price_breaks'] ?? [];
        if (is_array($priceBreaks)) {
            foreach ($priceBreaks as $pb) {
                if (!is_array($pb)) {
                    continue;
                }
                $qty = max(1, (int)($pb['quant'] ?? 1));
                $price = (float)($pb['price'] ?? 0);
                if ($price <= 0) {
                    continue;
                }
                $pbCurrency = strtoupper(trim((string)($pb['currency'] ?? $pb['Currency'] ?? '')));
                if ($pbCurrency !== '') {
                    $sourceCurrency = $pbCurrency;
                }
                if ($sourcePrice <= 0) {
                    $sourcePrice = $price;
                }
                $tiers[] = ['qty' => $qty, 'rub' => $price];
            }
        }

        usort($tiers, static function ($a, $b) {
            return ((int)($a['qty'] ?? 0)) <=> ((int)($b['qty'] ?? 0));
        });
        if ($tiers !== []) {
            $minOrder = max(1, (int)($tiers[0]['qty'] ?? 1));
            return [
                'tiers_rub' => $tiers,
                'source_currency' => $sourceCurrency,
                'source_price' => $sourcePrice > 0 ? $sourcePrice : (float)($tiers[0]['rub'] ?? 0),
                'min_order' => $minOrder,
            ];
        }

        $fallbackPrice = (float)($item['price'] ?? 0);
        if ($fallbackPrice > 0) {
            return [
                'tiers_rub' => [['qty' => 1, 'rub' => $fallbackPrice]],
                'source_currency' => $sourceCurrency,
                'source_price' => $fallbackPrice,
                'min_order' => 1,
            ];
        }
        return [
            'tiers_rub' => [],
            'source_currency' => $sourceCurrency,
            'source_price' => 0.0,
            'min_order' => 1,
        ];
    }

    private static function firstPriceLabel(array $item): string
    {
        $pb = $item['pricebreaks'] ?? $item['price_breaks'] ?? [];
        if (is_array($pb)) {
            foreach ($pb as $b) {
                if (!is_array($b)) {
                    continue;
                }
                $pr = isset($b['price']) ? (float) $b['price'] : 0.0;
                $q = isset($b['quant']) ? (int) $b['quant'] : 0;
                if ($pr > 0) {
                    $ccy = strtoupper(trim((string) ($b['currency'] ?? $b['Currency'] ?? $item['currency'] ?? '')));
                    if ($ccy === '') {
                        $ccy = 'RUB';
                    }

                    return number_format($pr, 2, '.', ' ') . ' ' . $ccy . ' от ' . $q . ' шт.';
                }
            }
        }

        return '—';
    }
}
