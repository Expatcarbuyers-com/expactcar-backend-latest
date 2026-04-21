<?php

namespace App\Observers;

use App\Models\CarModel;
use App\Models\UrlRedirect;

class CarModelObserver
{
    public function updating(CarModel $carModel): void
    {
        // When the name changes, regenerate slug and log a redirect.
        if ($carModel->isDirty('name')) {
            $oldSlug = $carModel->getOriginal('slug');
            $newSlug = \Str::slug($carModel->name);

            if ($oldSlug && $oldSlug !== $newSlug) {
                $carModel->slug = $newSlug;

                UrlRedirect::updateOrCreate(
                    ['old_slug' => $oldSlug],
                    ['new_slug' => $newSlug, 'is_permanent' => true]
                );
            }
        }
    }
}
