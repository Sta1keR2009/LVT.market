<?php

use App\Http\Controllers\CatalogOffersController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('search.index');
});

Route::get('/search', [SearchController::class, 'index'])->name('search.index');

/** Верстка Aspro Lite (тест); рабочая страница — {@see search.index} */
Route::get('/search-v2', [SearchController::class, 'indexV2'])->name('search.index.v2');

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'app' => config('app.name'),
        'time' => now()->toIso8601String(),
    ]);
});

/** Bitrix lazy-load карточки: {@see \GetchipsCatalogOffersHelper::httpFetch} */
Route::get('/api/catalog-offers', [CatalogOffersController::class, 'show']);

Route::get('/health/getchips', function () {
    $component = request('component', 'IO-BP176015-T3MBK');
    $amount = (int) request('amount', 1);

    $endpoint = (string) config('services.getchips.endpoint');
    $token = (string) config('services.getchips.token');

    try {
        $response = Http::acceptJson()
            ->withOptions([
                'curl' => [
                    \CURLOPT_IPRESOLVE => \CURL_IPRESOLVE_V4,
                ],
            ])
            ->timeout((int) config('services.getchips.timeout', 10))
            ->get($endpoint, [
                'input' => $component,
                'qty' => $amount,
                'token' => $token,
            ]);

        return response()->json([
            'ok' => $response->successful(),
            'status' => $response->status(),
            'endpoint' => $endpoint,
            'component' => $component,
            'amount' => $amount,
            'token_present' => $token !== '',
            'data_sample' => $response->json('data.0') ?? null,
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'ok' => false,
            'status' => null,
            'endpoint' => $endpoint,
            'component' => $component,
            'amount' => $amount,
            'token_present' => $token !== '',
            'error' => $e->getMessage(),
        ], 500);
    }
});
