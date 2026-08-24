<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Make extends Model
{
    use HasSlug;

    protected $fillable = [
        'name',
        'slug',
        'logo_url',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'display_order' => 'integer',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate(); // Slug changes are handled by MakeObserver to log redirects
    }

    // ── Relationships ────────────────────────────────────────

    public function models(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CarModel::class);
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeActive($query)
    {
        // Qualified with the table name — see CarModel::scopeActive for why
        // an unqualified column here breaks once this scope is used in a
        // query joined against another table that also has is_active.
        return $query->where('makes.is_active', true);
    }
}
