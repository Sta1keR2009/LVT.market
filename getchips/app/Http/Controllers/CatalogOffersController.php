<?php

namespace App\Http\Controllers;

use App\Services\Suppliers\Data\ComponentSearchQuery;
use App\Services\Suppliers\Data\SupplierOffer;
use App\Services\Suppliers\SearchAggregator;
use Illuminate\Http\Request;

/**
 * Внутренний JSON для Bitrix: {@see GetchipsCatalogOffersHelper::httpFetch}.
 */
class CatalogOffersController extends Controller
{
    public function show(Request $request, SearchAggregator $aggregator)
    {
        $expectedKey = (string) config('services.getchips.internal_api_key', '');
        if ($expectedKey !== '') {
            $provided = (string) $request->header('X-Getchips-Internal-Key', '');
            if (! hash_equals($expectedKey, $provided)) {
                return response()->json(['ok' => false, 'error' => 'Unauthorized'], 403);
            }
        }

        $componentNum = trim((string) $request->query('componentNum', ''));
        $amount = max(1, (int) $request->query('amount', 1));

        if (mb_strlen($componentNum) < 3) {
            return response()->json(['ok' => true, 'offers' => []]);
        }

        $result = $aggregator->search(new ComponentSearchQuery(
            componentNumber: $componentNum,
            amount: $amount,
        ));

        $offers = array_map(
            static fn (SupplierOffer $o): array => $o->toArray(),
            $result->offers
        );

        return response()->json([
            'ok' => true,
            'offers' => $offers,
        ]);
    }
}
