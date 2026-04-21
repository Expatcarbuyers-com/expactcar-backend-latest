<?php

namespace App\Observers;

use App\Models\Make;
use App\Models\UrlRedirect;

class MakeObserver
{
    public function updating(Make $make): void
    {
        // When the name changes, Spatie Sluggable won't auto-update (doNotGenerateSlugsOnUpdate).
        // We manually regenerate the slug and log a redirect for the old one.
        if ($make->isDirty('name')) {
            $oldSlug = $make->getOriginal('slug');
            $newSlug = \Str::slug($make->name);

            if ($oldSlug && $oldSlug !== $newSlug) {
                $make->slug = $newSlug;

                UrlRedirect::updateOrCreate(
                    ['old_slug' => $oldSlug],
                    ['new_slug' => $newSlug, 'is_permanent' => true]
                );
            }
        }
    }
}
