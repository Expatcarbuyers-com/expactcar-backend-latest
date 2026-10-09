<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Busts the blog version counter whenever a Blog or BlogCategory is created,
 * updated, or deleted. All blog API cache keys embed this version number, so a
 * published or edited post is served fresh immediately instead of after the
 * cache TTL. Stale entries are orphaned and expire naturally.
 *
 * Same tag-free pattern as CatalogCacheObserver (works with any cache driver).
 */
class BlogCacheObserver
{
    public function created(Model $model): void
    {
        $this->bustBlogs();
    }

    public function updated(Model $model): void
    {
        $this->bustBlogs();
    }

    public function deleted(Model $model): void
    {
        $this->bustBlogs();
    }

    private function bustBlogs(): void
    {
        $current = (int) Cache::get('blog_version', 1);
        Cache::forever('blog_version', $current + 1);
    }
}
