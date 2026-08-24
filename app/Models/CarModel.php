<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class CarModel extends Model
{
    use HasSlug;

    protected $fillable = [
        'make_id',
        'name',
        'slug',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate(); // Slug changes handled by CarModelObserver to log redirects
    }

    // ── Relationships ────────────────────────────────────────

    public function make(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Make::class);
    }

    public function variants(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Variant::class, 'model_id');
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeActive($query)
    {
        // Qualified with the table name: unqualified 'is_active' is ambiguous
        // once this scope is used on a query joined to variants (which also
        // has an is_active column), and Postgres rejects it outright.
        return $query->where('car_models.is_active', true);
    }
}
