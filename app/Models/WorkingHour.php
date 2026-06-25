<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkingHour extends Model
{
    protected $fillable = [
        'weekday',
        'is_working',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'is_working' => 'boolean',
    ];
}
