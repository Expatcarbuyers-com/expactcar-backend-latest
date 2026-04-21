<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\CarModel;
use App\Models\Make;
use App\Models\Variant;
use App\Observers\BookingObserver;
use App\Observers\CarModelObserver;
use App\Observers\CatalogCacheObserver;
use App\Observers\MakeObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Status history tracking on bookings
        Booking::observe(BookingObserver::class);

        // Slug redirect tracking (logs old → new slug to url_redirects table)
        Make::observe(MakeObserver::class);
        CarModel::observe(CarModelObserver::class);

        // Catalog cache busting (increments catalog_version on any catalog change)
        Make::observe(CatalogCacheObserver::class);
        CarModel::observe(CatalogCacheObserver::class);
        Variant::observe(CatalogCacheObserver::class);
    }
}
