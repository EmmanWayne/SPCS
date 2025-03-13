<?php

namespace App\Providers;

use App\Models\Sale;
use App\Observers\SaleObserver;
use App\Models\Purchase;
use App\Observers\PurchaseObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Sale::observe(SaleObserver::class);
        Purchase::observe(PurchaseObserver::class);
    }
}
