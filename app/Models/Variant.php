<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Variant extends Model
{
    use Searchable;

    protected $fillable = [
        'model_id',
        'year',
        'name',
        'body_type',
        'engine',
        'transmission',
        'gcc_specs',
        'specs',
        'is_active',
    ];

    protected $casts = [
        'gcc_specs' => 'boolean',
        'is_active' => 'boolean',
        'specs'     => 'array',
        'year'      => 'integer',
    ];

    // ── Relationships ────────────────────────────────────────

    public function model(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CarModel::class, 'model_id');
    }

    public function bookings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Booking::class);
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ── Scout ────────────────────────────────────────────────

    public function toSearchableArray(): array
    {
        $this->loadMissing('model.make');

        return [
            'id'           => (int) $this->id,
            'name'         => $this->name,
            'year'         => (int) $this->year,
            'make'         => $this->model->make->name ?? '',
            'make_slug'    => $this->model->make->slug ?? '',
            'model'        => $this->model->name ?? '',
            'model_slug'   => $this->model->slug ?? '',
            'body_type'    => $this->body_type,
            'engine'       => $this->engine,
            'transmission' => $this->transmission,
            'gcc_specs'    => $this->gcc_specs,
            'is_active'    => $this->is_active,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->is_active;
    }
}
