<?php

namespace App\Services\Suppliers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LvtMarketApiClient
{
    public function __construct(
        private readonly MouserApiClient $mouserApiClient,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function search(string $componentNumber, int $amount): array
    {
        if ((bool) config('services.lvt_market.stub', true)) {
            return $this->stubResponse($componentNumber, $amount);
        }

        $baseUrl = rtrim((string) config('services.lvt_market.base_url'), '/');
        $path = '/'.ltrim((string) config('services.lvt_market.search_path', '/api/search/components'), '/');
        $createPath = '/'.ltrim((string) config('services.lvt_market.search_create_path', '/api/search/components'), '/');
        $token = (string) config('services.lvt_market.token');

        if ($baseUrl === '') {
            Log::warning('LVT Market API base_url is empty.');

            return [];
        }

        $rows = $this->requestRows($baseUrl, $path, $token, [
            'componentNum' => $componentNumber,
            'amount' => $amount,
        ]);
        if ($rows !== []) {
            return $rows;
        }

        // If product is absent on lvtec/lvt catalog, ask backend to create card from Mouser.
        $rowsAfterCreate = $this->requestRows($baseUrl, $createPath, $token, [
            'componentNum' => $componentNumber,
            'amount' => $amount,
            'create_from_mouser' => 1,
            'source' => 'mouser',
        ]);
        if ($rowsAfterCreate !== []) {
            return $rowsAfterCreate;
        }

        $fallbackRow = $this->buildMouserFallbackRow($componentNumber, $amount, $baseUrl);
        if ($fallbackRow !== null) {
            return [$fallbackRow];
        }

        return [];
    }

    /**
     * @param array<string, mixed> $query
     * @return array<int, array<string, mixed>>
     */
    private function requestRows(string $baseUrl, string $path, string $token, array $query): array
    {
        try {
            $request = Http::acceptJson()
                ->withOptions([
                    'curl' => [
                        \CURLOPT_IPRESOLVE => \CURL_IPRESOLVE_V4,
                    ],
                ])
                ->baseUrl($baseUrl)
                ->timeout((int) config('services.lvt_market.timeout', 8));
            if ($token !== '') {
                $request = $request->withToken($token);
            }
            $response = $request->get($path, $query)->throw();
        } catch (\Throwable $e) {
            Log::error('LVT Market search request failed.', [
                'component_number' => (string) ($query['componentNum'] ?? ''),
                'path' => $path,
                'message' => $e->getMessage(),
            ]);

            return [];
        }

        return $this->extractRows($response->json());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function extractRows(mixed $payload): array
    {
        if (is_array($payload) && isset($payload['data']) && is_array($payload['data'])) {
            return array_values(array_filter($payload['data'], 'is_array'));
        }
        if (is_array($payload) && array_is_list($payload)) {
            return array_values(array_filter($payload, 'is_array'));
        }

        return [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function stubResponse(string $componentNumber, int $amount): array
    {
        $baseUrl = rtrim((string) config('services.lvt_market.base_url', 'https://lvt.market'), '/');

        return [[
            'provider' => 'lvt.market',
            'supplier' => 'LVT Market',
            'partNumber' => $componentNumber,
            'brand' => null,
            'stock' => null,
            'minOrderQty' => null,
            'packSize' => null,
            'unitPrice' => null,
            'currency' => 'USD',
            'leadTimeDays' => null,
            'totalPrice' => null,
            'url' => $baseUrl.'/catalog/?q='.urlencode($componentNumber),
            'catalogUrl' => $baseUrl.'/catalog/?q='.urlencode($componentNumber),
            'note' => 'Поиск по каталогу: lvt.market',
            /* product card fields — заполняются реальным API */
            'imageUrl' => null,
            'imageUrls' => [],
            'productTitle' => null,
            'articleNumber' => null,
            'badges' => [],
            'brandName' => null,
            'brandLogoUrl' => null,
            'brandCatalogUrl' => null,
            'categoryCatalogUrl' => null,
            'specs' => [],
        ]];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildMouserFallbackRow(string $componentNumber, int $amount, string $baseUrl): ?array
    {
        $part = $this->mouserApiClient->searchPart($componentNumber);
        if (! is_array($part) || $part === []) {
            return null;
        }

        $priceBreaks = [];
        $unitPrice = null;
        $currency = 'USD';
        if (! empty($part['PriceBreaks']) && is_array($part['PriceBreaks'])) {
            foreach ($part['PriceBreaks'] as $pb) {
                if (! is_array($pb)) {
                    continue;
                }
                $qty = isset($pb['Quantity']) ? (int) $pb['Quantity'] : 0;
                $price = $this->sanitizeMouserPrice($pb['Price'] ?? null);
                if ($qty < 1 || $price === null) {
                    continue;
                }
                $ccy = trim((string) ($pb['Currency'] ?? ''));
                if ($ccy !== '') {
                    $currency = strtoupper($ccy);
                }
                if ($unitPrice === null) {
                    $unitPrice = $price;
                }
                $priceBreaks[] = [
                    'quantity' => $qty,
                    'price' => $price,
                ];
            }
        }

        $partNumber = trim((string) ($part['ManufacturerPartNumber'] ?? $part['MouserPartNumber'] ?? $componentNumber));
        $catalogUrl = $baseUrl.'/catalog/?q='.urlencode($partNumber);
        $productUrl = trim((string) ($part['ProductDetailUrl'] ?? ''));
        $imageUrl = trim((string) ($part['ImagePath'] ?? ''));

        return [
            'provider' => 'lvt.market',
            'supplier' => 'Mouser',
            'partNumber' => $partNumber,
            'brand' => trim((string) ($part['Manufacturer'] ?? '')) ?: null,
            'stock' => $this->parseMouserStock($part['Availability'] ?? null),
            'minOrderQty' => isset($part['Min']) ? max(1, (int) $part['Min']) : null,
            'packSize' => isset($part['Mult']) ? max(1, (int) $part['Mult']) : null,
            'packaging' => isset($part['Mult']) ? 'x'.(int) $part['Mult'] : null,
            'unitPrice' => $unitPrice,
            'currency' => $currency,
            'totalPrice' => $unitPrice !== null ? $unitPrice * max(1, $amount) : null,
            'leadTimeDays' => $this->extractLeadTimeDays($part['LeadTime'] ?? null),
            'url' => $catalogUrl,
            'catalogUrl' => $catalogUrl,
            'productDetailUrl' => $productUrl !== '' ? $productUrl : null,
            'productTitle' => trim((string) ($part['Description'] ?? $partNumber)),
            'articleNumber' => $partNumber,
            'imageUrl' => $imageUrl !== '' ? $imageUrl : null,
            'imageUrls' => $imageUrl !== '' ? [$imageUrl] : [],
            'brandName' => trim((string) ($part['Manufacturer'] ?? '')) ?: null,
            'specs' => $this->mapMouserAttributesToSpecs($part['ProductAttributes'] ?? null),
            'note' => 'Карточка добавлена из Mouser fallback',
            'priceBreak' => $priceBreaks,
        ];
    }

    private function sanitizeMouserPrice(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        $cleaned = preg_replace('/[^0-9\.\,\-]/', '', (string) $value);
        if ($cleaned === null || $cleaned === '') {
            return null;
        }
        $normalized = str_replace(',', '.', $cleaned);
        if (! is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
    }

    private function parseMouserStock(mixed $availability): ?int
    {
        if ($availability === null || $availability === '') {
            return null;
        }
        if (is_numeric($availability)) {
            return (int) $availability;
        }
        if (preg_match('/([0-9\s]+)/', (string) $availability, $m) === 1) {
            $n = (int) preg_replace('/\s+/', '', (string) $m[1]);
            return $n > 0 ? $n : null;
        }

        return null;
    }

    private function extractLeadTimeDays(mixed $leadTime): ?int
    {
        if ($leadTime === null || $leadTime === '') {
            return null;
        }
        if (is_numeric($leadTime)) {
            return (int) $leadTime;
        }
        if (preg_match('/(\d+)/', (string) $leadTime, $m) === 1) {
            return (int) $m[1];
        }

        return null;
    }

    /**
     * @return array<string, string>
     */
    private function mapMouserAttributesToSpecs(mixed $attributes): array
    {
        if (! is_array($attributes) || $attributes === []) {
            return [];
        }
        $specs = [];
        foreach ($attributes as $attr) {
            if (! is_array($attr)) {
                continue;
            }
            $name = trim((string) ($attr['AttributeName'] ?? ''));
            $value = trim((string) ($attr['AttributeValue'] ?? ''));
            if ($name === '' || $value === '') {
                continue;
            }
            $specs[$name] = $value;
        }

        return $specs;
    }
}
