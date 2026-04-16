<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'location',
        'address',
        'phone',
        'latitude',
        'longitude',
        'is_active',
    ];
}
