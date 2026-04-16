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
        'name',
        'phone',
        'email',
        'utm_data',
        'status',
        'notes',
        'zoho_lead_id',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'utm_data' => 'array',
    ];

    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->reference_number)) {
                $booking->reference_number = 'ECB-' . strtoupper(Str::random(8));
            }
        });
    }
}
