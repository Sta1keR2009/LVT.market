<?php

namespace App\Services\Suppliers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MouserApiClient
{
    /**
     * @return array<string, mixed>|null
     */
    public function searchPart(string $componentNumber): ?array
    {
        $apiKey = trim((string) config('services.mouser.api_key', ''));
        $baseUrl = rtrim((string) config('services.mouser.base_url', 'https://api.mouser.com/api/v1'), '/');
        $path = '/'.ltrim((string) config('services.mouser.search_part_path', '/search/partnumber'), '/');

        if ($apiKey === '' || $componentNumber === '') {
            return null;
        }

        try {
            $response = Http::acceptJson()
                ->withOptions([
                    'curl' => [
                        \CURLOPT_IPRESOLVE => \CURL_IPRESOLVE_V4,
                    ],
                ])
                ->timeout((int) config('services.mouser.timeout', 12))
                ->retry((int) config('services.mouser.retries', 1), 200)
                ->post($baseUrl.$path.'?apiKey='.urlencode($apiKey), [
                    'SearchByPartRequest' => [
                        'mouserPartNumber' => $componentNumber,
                    ],
                ])
                ->throw();
        } catch (\Throwable $e) {
            Log::warning('Mouser fallback request failed.', [
                'component_number' => $componentNumber,
                'message' => $e->getMessage(),
            ]);

            return null;
        }

        $parts = $response->json('SearchResults.Parts', []);
        if (! is_array($parts) || $parts === []) {
            return null;
        }

        foreach ($parts as $part) {
            if (is_array($part)) {
                return $part;
            }
        }

        return null;
    }
}
