<?php

namespace App\Http\Controllers;

use App\Http\Requests\ComponentSearchRequest;
use App\Services\Search\SearchResultsPresenter;
use App\Services\Suppliers\Data\ComponentSearchQuery;
use App\Services\Suppliers\Data\SupplierOffer;
use App\Services\Suppliers\SearchAggregator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SearchController extends Controller
{
    public function index(ComponentSearchRequest $request, SearchAggregator $aggregator)
    {
        return view('search.index', $this->buildSearchPayload($request, $aggregator, withFilters: false));
    }

    /**
     * Тестовая страница с версткой в стиле Aspro Lite; логика поиска та же, что у /search.
     */
    public function indexV2(ComponentSearchRequest $request, SearchAggregator $aggregator)
    {
        return view('search.index-aspro', $this->buildSearchPayload($request, $aggregator, withFilters: true));
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSearchPayload(ComponentSearchRequest $request, SearchAggregator $aggregator, bool $withFilters): array
    {
        $componentNumber = (string) $request->input('componentNum', '');
        $amount = (int) $request->input('amount', 1);
        $sort = (string) $request->input('sort', 'price_asc');
        $perPage = (int) $request->input('per_page', 25);
        $displayCurrency = (string) $request->input('display_currency', 'rub');
        if (! in_array($displayCurrency, ['rub', 'usd'], true)) {
            $displayCurrency = 'rub';
        }

        // Part-number mode is disabled: keep only site search flow.
        $filterPartNumbers = [];
        $filterSuppliers = $withFilters ? array_values((array) $request->input('suppliers', [])) : [];
        $filterBrands = $withFilters ? array_values((array) $request->input('brands', [])) : [];
        $priceMin = $withFilters && $request->filled('price_min') ? (float) $request->input('price_min') : null;
        $priceMax = $withFilters && $request->filled('price_max') ? (float) $request->input('price_max') : null;

        $offers = collect();
        $providerErrors = [];
        $timingsMs = [];
        $presenter = SearchResultsPresenter::fromConfig();
        $pricing = config('services.getchips.pricing', []);

        if ($componentNumber !== '') {
            $query = new ComponentSearchQuery(componentNumber: $componentNumber, amount: max(1, $amount));
            $result = $aggregator->search($query);
            $offers = collect($result->offers)
                ->filter(fn ($o): bool => $o instanceof SupplierOffer)
                ->map(fn (SupplierOffer $o): array => $o->toArray());
            $providerErrors = $result->errors;
            $timingsMs = $result->timingsMs;
        }

        $enriched = $offers->map(fn (array $o): array => $presenter->enrich($o));

        $filterPartNumbersAll = [];
        $filterSuppliersAll = $withFilters && $enriched->isNotEmpty()
            ? $presenter->uniqueSuppliers($enriched)
            : [];
        $filterBrandsAll = $withFilters && $enriched->isNotEmpty()
            ? $presenter->uniqueBrands($enriched)
            : [];

        $filtered = $withFilters
            ? $presenter->filter($enriched, $filterPartNumbers, $filterSuppliers, $filterBrands, $priceMin, $priceMax, $displayCurrency)
            : $enriched;

        $sortedOffers = $this->sortOffers($filtered, $sort, $displayCurrency, $presenter);
        $sortedOffers = $this->floatLvtMarketFirst($sortedOffers);
        $paginated = $this->paginate($sortedOffers, max(10, min($perPage, 100)), $request);

        $totalUnfiltered = $enriched->count();
        $totalFiltered = $filtered->count();

        // Первое предложение lvt.market (без пагинации, из всех отфильтрованных)
        $lvtMarketCard = $enriched
            ->first(fn (array $o): bool => ($o['provider'] ?? '') === 'lvt.market');

        return [
            'offers' => $paginated,
            'lvtMarketCard' => $lvtMarketCard,
            'componentNum' => $componentNumber,
            'amount' => max(1, $amount),
            'sort' => $sort,
            'perPage' => $perPage,
            'provider_errors' => $providerErrors,
            'timingsMs' => $timingsMs,
            'displayCurrency' => $displayCurrency,
            'filterPartNumbers' => $filterPartNumbers,
            'filterSuppliers' => $filterSuppliers,
            'filterBrands' => $filterBrands,
            'priceMin' => $request->input('price_min'),
            'priceMax' => $request->input('price_max'),
            'filterPartNumbersAll' => $filterPartNumbersAll,
            'filterSuppliersAll' => $filterSuppliersAll,
            'filterBrandsAll' => $filterBrandsAll,
            'markupPercent' => (float) ($pricing['markup_percent'] ?? 0),
            'usdToRub' => (float) ($pricing['usd_to_rub'] ?? 1),
            'withFilters' => $withFilters,
            'totalUnfiltered' => $totalUnfiltered,
            'totalFiltered' => $totalFiltered,
        ];
    }

    private function paginate(Collection $offers, int $perPage, ComponentSearchRequest $request): LengthAwarePaginator
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $items = $offers->forPage($currentPage, $perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $offers->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    /**
     * Предложения lvt.market всегда идут первыми независимо от остальной сортировки.
     */
    private function floatLvtMarketFirst(Collection $offers): Collection
    {
        $lvt = $offers->filter(fn (array $o): bool => ($o['provider'] ?? '') === 'lvt.market')->values();
        $rest = $offers->filter(fn (array $o): bool => ($o['provider'] ?? '') !== 'lvt.market')->values();

        return $lvt->concat($rest);
    }

    private function sortOffers(Collection $offers, string $sort, string $displayCurrency, SearchResultsPresenter $presenter): Collection
    {
        $priceAsc = function (array $o) use ($presenter, $displayCurrency): float {
            $p = $presenter->displayUnitPrice($o, $displayCurrency);

            return $p === null ? INF : $p;
        };
        $priceDesc = function (array $o) use ($presenter, $displayCurrency): float {
            $p = $presenter->displayUnitPrice($o, $displayCurrency);

            return $p === null ? -INF : $p;
        };

        return match ($sort) {
            'price_desc' => $offers->sortByDesc($priceDesc)->values(),
            'lead_asc' => $offers->sortBy(fn (array $o): int => (int) ($o['lead_time_days'] ?? PHP_INT_MAX))->values(),
            'stock_desc' => $offers->sortByDesc(fn (array $o): int => (int) ($o['stock'] ?? -1))->values(),
            default => $offers->sortBy($priceAsc)->values(),
        };
    }
}
