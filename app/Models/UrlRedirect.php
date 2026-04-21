<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UrlRedirect extends Model
{
    protected $fillable = [
        'old_slug',
        'new_slug',
        'is_permanent',
    ];

    protected $casts = [
        'is_permanent' => 'boolean',
    ];
}
