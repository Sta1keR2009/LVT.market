<?php

namespace App\Services\Suppliers;

use App\Services\Suppliers\Contracts\SupplierSearchProviderInterface;
use App\Services\Suppliers\Data\ComponentSearchQuery;
use App\Services\Suppliers\Data\SupplierOffer;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GetchipsProvider implements SupplierSearchProviderInterface
{
    public function name(): string
    {
        return 'getchips';
    }

    /**
     * @return array<int, SupplierOffer>
     */
    public function search(ComponentSearchQuery $query): array
    {
        $token = (string) config('services.getchips.token');
        $endpoint = (string) config('services.getchips.endpoint');
        $componentNumber = trim((string) $query->componentNumber);
        $cacheTtl = max(300, (int) config('services.getchips.cache_ttl', 604800));
        $cacheKey = $this->buildRowsCacheKey($componentNumber, $query->amount);

        if ($token === '') {
            Log::warning('Getchips token is empty, provider is skipped.');
            return [];
        }

        $cachedRows = Cache::get($cacheKey);
        if (!is_array($cachedRows) || $cachedRows === []) {
            // На этом же сервере обычно есть прогретый кеш lvtec — используем как warm fallback.
            $cachedRows = $this->loadRowsFromLvtecBitrixCache($componentNumber, $query->amount);
            if ($cachedRows !== []) {
                Cache::put($cacheKey, $cachedRows, now()->addSeconds($cacheTtl));
            }
        }

        $timeout = (int) config('services.getchips.timeout', 25);
        $maxAttempts = max(1, (int) config('services.getchips.rate_limit_attempts', 2));
        /** Паузы между попытками при HTTP 429 (секунды, не больше 30). */
        $backoffSeconds = array_values(array_filter(array_map('intval', explode(',', (string) config('services.getchips.rate_limit_backoff_seconds', '2,5')))));

        $response = null;
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            if ($attempt > 0) {
                $sleep = $backoffSeconds[$attempt - 1] ?? 15;
                $sleep = min(30, max(1, $sleep));
                sleep($sleep);
            }

            $response = Http::acceptJson()
                ->withOptions([
                    'curl' => [
                        \CURLOPT_IPRESOLVE => \CURL_IPRESOLVE_V4,
                    ],
                ])
                ->timeout($timeout)
                ->get($endpoint, [
                    'input' => $componentNumber,
                    'qty' => $query->amount,
                    'token' => $token,
                ]);

            if ($response->successful()) {
                $rows = $response->json('data', []);
                if (is_array($rows) && $rows !== []) {
                    Cache::put($cacheKey, $rows, now()->addSeconds($cacheTtl));

                    return $this->rowsToOffers($rows, $query);
                }
                break;
            }

            if ((int) $response->status() === 429) {
                Log::warning('Getchips API returned 429, will retry if attempts remain.', [
                    'component' => $componentNumber,
                    'attempt' => $attempt + 1,
                    'max' => $maxAttempts,
                ]);

                continue;
            }
            break;
        }

        if ($cachedRows !== []) {
            Log::warning('Getchips API unavailable/rate-limited, serving cached rows.', [
                'component' => $componentNumber,
                'status' => $response ? $response->status() : null,
                'cached_rows' => count($cachedRows),
            ]);

            return $this->rowsToOffers($cachedRows, $query);
        }

        if ($response === null || ! $response->successful()) {
            Log::warning('Getchips API exhausted retries (likely 429).', [
                'component' => $componentNumber,
                'status' => $response ? $response->status() : null,
            ]);

            return [];
        }

        $rows = $response->json('data', []);
        if (!is_array($rows) || $rows === []) {
            return [];
        }

        Cache::put($cacheKey, $rows, now()->addSeconds($cacheTtl));

        return $this->rowsToOffers($rows, $query);
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, SupplierOffer>
     */
    private function rowsToOffers(array $rows, ComponentSearchQuery $query): array
    {
        $offers = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $requestedQuantityPrice = isset($row['quantity_price']) ? (float) $row['quantity_price'] : null;
            $minQuantityPrice = isset($row['price']) ? (float) $row['price'] : null;
            $effectivePrice = $requestedQuantityPrice ?? $minQuantityPrice;

            $offers[] = new SupplierOffer(
                provider: $this->name(),
                supplier: (string) ($row['donor'] ?? 'Unknown supplier'),
                partNumber: (string) ($row['title'] ?? $query->componentNumber),
                brand: isset($row['brand']) ? (string) $row['brand'] : null,
                stock: isset($row['quantity']) ? (int) $row['quantity'] : null,
                minOrderQty: isset($row['eQuantity']) ? (int) $row['eQuantity'] : (isset($row['minq']) ? (int) $row['minq'] : null),
                packSize: isset($row['sPack']) ? (int) $row['sPack'] : null,
                packaging: isset($row['packaging']) ? (string) $row['packaging'] : null,
                unitPrice: $effectivePrice,
                totalPrice: $effectivePrice !== null ? $effectivePrice * $query->amount : null,
                currency: $this->normalizeCurrency($row['currency'] ?? null),
                leadTimeDays: isset($row['orderdays']) ? (int) $row['orderdays'] : null,
                note: isset($row['donorID']) ? 'Supplier ID: ' . $row['donorID'] : null,
                raw: $row,
            );
        }

        return $offers;
    }

    private function buildRowsCacheKey(string $componentNumber, int $amount): string
    {
        return 'getchips:rows:' . md5(mb_strtoupper($componentNumber) . '|' . max(1, $amount));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function loadRowsFromLvtecBitrixCache(string $componentNumber, int $amount): array
    {
        if ($componentNumber === '' || mb_strlen($componentNumber) < 3) {
            return [];
        }

        $legacyCacheId = md5(mb_strtoupper($componentNumber) . '|' . max(1, $amount));
        $cacheFileHash = md5($legacyCacheId);
        $cacheFile = '/var/www/www-root/data/www/lvtec.ru/bitrix/cache/getchips/catalog_offers/'
            . substr($cacheFileHash, 0, 2) . '/' . $cacheFileHash . '.php';
        if (!is_file($cacheFile)) {
            return [];
        }

        $raw = @file_get_contents($cacheFile);
        if (!is_string($raw) || $raw === '') {
            return [];
        }
        if (!preg_match('/\\$ser_content\\s*=\\s*\'(.*)\';\\s*return\\s+true;/sU', $raw, $m)) {
            return [];
        }
        $serContent = stripcslashes((string)($m[1] ?? ''));
        if ($serContent === '') {
            return [];
        }

        $decoded = @unserialize($serContent, ['allowed_classes' => false]);
        if (!is_array($decoded)) {
            return [];
        }
        $vars = $decoded['VARS'] ?? null;
        if (!is_array($vars)) {
            return [];
        }
        $offers = $vars['offers'] ?? null;
        if (!is_array($offers) || $offers === []) {
            return [];
        }

        return $offers;
    }

    private function normalizeCurrency(mixed $currency): ?string
    {
        if ($currency === null || $currency === '') {
            return null;
        }
        if ((string) $currency === '1') {
            return 'USD';
        }
        return (string) $currency;
    }
}
