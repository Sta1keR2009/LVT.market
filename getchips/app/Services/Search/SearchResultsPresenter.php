<?php

namespace App\Services\Search;

use Illuminate\Support\Collection;

/**
 * Нормализация цен в USD, наценка из конфига, отображение в RUB/USD, фильтрация.
 */
final class SearchResultsPresenter
{
    private const MARKUP_TARGET_PROVIDERS = ['getchips', 'mouser'];

    private const MARKUP_TARGET_SUPPLIER_PARTS = ['getchips', 'mouser'];

    public function __construct(
        private readonly float $markupPercent,
        private readonly float $usdToRub,
    ) {}

    public static function fromConfig(): self
    {
        $p = config('services.getchips.pricing', []);

        return new self(
            markupPercent: (float) ($p['markup_percent'] ?? 0),
            usdToRub: max(0.0001, (float) ($p['usd_to_rub'] ?? 1)),
        );
    }

    /**
     * @param  array<string, mixed>  $offer
     * @return array<string, mixed>
     */
    public function enrich(array $offer): array
    {
        $price = isset($offer['unit_price']) ? (float) $offer['unit_price'] : null;
        $currency = strtoupper(trim((string) ($offer['currency'] ?? 'USD')));

        $usdBase = $this->normalizeToUsd($price, $currency);
        $usdWithMarkup = $this->applyMarkup($usdBase, $offer);

        $merged = array_merge($offer, [
            'price_usd_before_markup' => $usdBase,
            'price_usd_with_markup' => $usdWithMarkup,
            'price_display_rub' => $usdWithMarkup === null ? null : $usdWithMarkup * $this->usdToRub,
            'price_display_usd' => $usdWithMarkup,
        ]);

        $merged['price_tiers'] = $this->buildPriceTiers($merged, $currency);

        return $merged;
    }

    /**
     * Ступени цены из raw.priceBreak (Getchips) или одна строка по unit_price.
     *
     * @return list<array{qty:int, usd:float, rub:float}>
     */
    public function buildPriceTiers(array $offer, ?string $currencyOverride = null): array
    {
        $currency = strtoupper(trim((string) ($currencyOverride ?? $offer['currency'] ?? 'USD')));
        $raw = $offer['raw'] ?? [];
        $tiers = [];

        $breaks = $raw['priceBreak'] ?? $raw['price_break'] ?? null;
        if (is_array($breaks)) {
            foreach ($breaks as $b) {
                if (! is_array($b)) {
                    continue;
                }
                $qty = (int) ($b['quantity'] ?? $b['qty'] ?? $b['minQuantity'] ?? 0);
                $p = isset($b['price']) ? (float) $b['price'] : null;
                if ($qty < 1 || $p === null) {
                    continue;
                }
                $usd = $this->normalizeToUsd($p, $currency);
                $usdM = $this->applyMarkup($usd, $offer);
                if ($usdM === null) {
                    continue;
                }
                $tiers[] = [
                    'qty' => $qty,
                    'usd' => $usdM,
                    'rub' => $usdM * $this->usdToRub,
                ];
            }
        }

        if ($tiers !== []) {
            usort($tiers, fn (array $a, array $b): int => $a['qty'] <=> $b['qty']);

            return $this->dedupeTiersByQty($tiers);
        }

        $dispUsd = isset($offer['unit_price']) ? $this->applyMarkup($this->normalizeToUsd((float) $offer['unit_price'], $currency), $offer) : null;
        if ($dispUsd === null) {
            return [];
        }

        $moq = max(1, (int) ($offer['min_order_qty'] ?? 1));

        return [[
            'qty' => $moq,
            'usd' => $dispUsd,
            'rub' => $dispUsd * $this->usdToRub,
        ]];
    }

    /**
     * @param  list<array{qty:int, usd:float, rub:float}>  $tiers
     * @return list<array{qty:int, usd:float, rub:float}>
     */
    private function dedupeTiersByQty(array $tiers): array
    {
        $best = [];
        foreach ($tiers as $t) {
            $q = $t['qty'];
            if (! isset($best[$q]) || $t['usd'] < $best[$q]['usd']) {
                $best[$q] = $t;
            }
        }
        $out = array_values($best);
        usort($out, fn (array $a, array $b): int => $a['qty'] <=> $b['qty']);

        return $out;
    }

    public function displayUnitPrice(array $enriched, string $displayCurrency): ?float
    {
        return $displayCurrency === 'usd'
            ? ($enriched['price_display_usd'] ?? null)
            : ($enriched['price_display_rub'] ?? null);
    }

    public function displayCurrencyLabel(string $displayCurrency): string
    {
        return $displayCurrency === 'usd' ? 'USD' : '₽';
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $enriched
     * @param  list<string>  $partNumbers  пустой список = без фильтра
     * @param  list<string>  $suppliers
     * @param  list<string>  $brands
     * @return Collection<int, array<string, mixed>>
     */
    public function filter(
        Collection $enriched,
        array $partNumbers,
        array $suppliers,
        array $brands,
        ?float $priceMin,
        ?float $priceMax,
        string $displayCurrency,
    ): Collection {
        $partNumbers = array_values(array_filter(array_map('trim', $partNumbers)));
        $suppliers = array_values(array_filter(array_map('trim', $suppliers)));
        $brands = array_values(array_filter(array_map('trim', $brands)));

        return $enriched->filter(function (array $o) use ($partNumbers, $suppliers, $brands, $priceMin, $priceMax, $displayCurrency): bool {
            if ($partNumbers !== []) {
                $pn = strtoupper(trim((string) ($o['part_number'] ?? '')));
                $ok = false;
                foreach ($partNumbers as $want) {
                    if ($pn === strtoupper(trim($want))) {
                        $ok = true;
                        break;
                    }
                }
                if (! $ok) {
                    return false;
                }
            }
            if ($suppliers !== []) {
                $s = trim((string) ($o['supplier'] ?? ''));
                if (! in_array($s, $suppliers, true)) {
                    return false;
                }
            }
            if ($brands !== []) {
                $b = strtoupper(trim((string) ($o['brand'] ?? '')));
                $brandOk = false;
                foreach ($brands as $want) {
                    if ($b === strtoupper(trim($want))) {
                        $brandOk = true;
                        break;
                    }
                }
                if (! $brandOk) {
                    return false;
                }
            }
            $p = $this->displayUnitPrice($o, $displayCurrency);
            if ($p === null) {
                if ($priceMin !== null || $priceMax !== null) {
                    return false;
                }

                return true;
            }
            if ($priceMin !== null && $p < $priceMin) {
                return false;
            }
            if ($priceMax !== null && $p > $priceMax) {
                return false;
            }

            return true;
        })->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $enriched
     * @return list<string>
     */
    public function uniqueSuppliers(Collection $enriched): array
    {
        $set = [];
        foreach ($enriched as $o) {
            $s = trim((string) ($o['supplier'] ?? ''));
            if ($s !== '') {
                $set[$s] = true;
            }
        }
        $list = array_keys($set);
        natcasesort($list);

        return array_values($list);
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $enriched
     * @return list<string>
     */
    public function uniqueBrands(Collection $enriched): array
    {
        $set = [];
        foreach ($enriched as $o) {
            $b = trim((string) ($o['brand'] ?? ''));
            if ($b !== '') {
                $set[$b] = true;
            }
        }
        $list = array_keys($set);
        natcasesort($list);

        return array_values($list);
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $enriched
     * @return list<string>
     */
    public function uniquePartNumbers(Collection $enriched): array
    {
        $set = [];
        foreach ($enriched as $o) {
            $p = trim((string) ($o['part_number'] ?? ''));
            if ($p !== '') {
                $set[$p] = true;
            }
        }
        $list = array_keys($set);
        natcasesort($list);

        return array_values($list);
    }

    private function normalizeToUsd(?float $price, string $currency): ?float
    {
        if ($price === null) {
            return null;
        }

        return match ($currency) {
            'RUB', 'RUR' => $price / $this->usdToRub,
            default => $price,
        };
    }

    /**
     * @param  array<string, mixed>|null  $offer
     */
    private function applyMarkup(?float $usd, ?array $offer = null): ?float
    {
        if ($usd === null) {
            return null;
        }

        if (! $this->shouldApplyMarkup($offer)) {
            return $usd;
        }

        return $usd * (1 + $this->markupPercent / 100);
    }

    /**
     * @param  array<string, mixed>|null  $offer
     */
    private function shouldApplyMarkup(?array $offer): bool
    {
        if (! is_array($offer) || $offer === []) {
            return false;
        }

        $provider = strtolower(trim((string) ($offer['provider'] ?? '')));
        if (in_array($provider, self::MARKUP_TARGET_PROVIDERS, true)) {
            return true;
        }

        $supplier = strtolower(trim((string) ($offer['supplier'] ?? '')));
        if ($supplier === '') {
            return false;
        }

        foreach (self::MARKUP_TARGET_SUPPLIER_PARTS as $needle) {
            if (str_contains($supplier, $needle)) {
                return true;
            }
        }

        return false;
    }
}
