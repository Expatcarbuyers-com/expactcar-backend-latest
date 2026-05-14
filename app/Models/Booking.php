<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Booking extends Model
{
    protected $fillable = [
        'reference_number',
        'variant_id',
        'make_name',
        'model_name',
        'variant_name',
        'year',
        'mileage',
        'specs',
        'car_option',
        'paint_condition',
        'name',
        'phone',
        'email',
        'utm_data',
        'status',
        'status_history',
        'internal_notes',
        'assigned_to',
        'ip_address',
        'user_agent',
        'legacy_source_id',
        'source',
        'date',
        'time',
    ];

    protected $casts = [
        'utm_data'       => 'array',
        'status_history' => 'array',
        'year'           => 'integer',
    ];

    // ── Relationships ────────────────────────────────────────

    public function variant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Variant::class);
    }

    public function assignedAgent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // ── Scopes ───────────────────────────────────────────────

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    // ── Boot ─────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Booking $booking) {
            if (empty($booking->reference_number)) {
                $booking->reference_number = 'ECB-' . strtoupper(Str::random(8));
            }
        });
    }
}
