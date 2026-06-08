<?php

namespace App\Providers;

use App\Services\Suppliers\GetchipsProvider;
use App\Services\Suppliers\LvtMarketProvider;
use App\Services\Suppliers\SearchAggregator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SearchAggregator::class, function ($app): SearchAggregator {
            return new SearchAggregator([
                $app->make(LvtMarketProvider::class),
                $app->make(GetchipsProvider::class),
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
