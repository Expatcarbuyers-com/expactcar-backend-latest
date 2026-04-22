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
use Illuminate\Database\Connectors\PostgresConnector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Neon SNI workaround for hosts with old libpq (e.g. Hostinger shared hosting).
        // If DB_NEON_ENDPOINT is set, appends options='endpoint=...' to the PostgreSQL DSN
        // so Neon can identify the endpoint without needing SNI support.
        if ($endpoint = env('DB_NEON_ENDPOINT')) {
            $this->app->bind('db.connector.pgsql', function () use ($endpoint) {
                return new class($endpoint) extends PostgresConnector {
                    public function __construct(private readonly string $neonEndpoint) {}

                    protected function getDsn(array $config): string
                    {
                        $dsn = parent::getDsn($config);
                        return $dsn . ";options='endpoint={$this->neonEndpoint}'";
                    }
                };
            });
        }
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
