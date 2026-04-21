<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Busts the catalog version counter whenever a Make, CarModel, or Variant
 * is created, updated, or deleted. All catalog cache keys embed this version
 * number, so stale entries are effectively orphaned and will expire naturally.
 *
 * This pattern works with any cache driver (database, file, Redis) without
 * needing cache tags.
 */
class CatalogCacheObserver
{
    public function created(Model $model): void
    {
        $this->bustCatalog();
    }

    public function updated(Model $model): void
    {
        $this->bustCatalog();
    }

    public function deleted(Model $model): void
    {
        $this->bustCatalog();
    }

    private function bustCatalog(): void
    {
        $current = (int) Cache::get('catalog_version', 1);
        Cache::forever('catalog_version', $current + 1);
    }
}
