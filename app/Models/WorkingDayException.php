<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkingDayException extends Model
{
    protected $fillable = [
        'date',
        'starts_on',
        'ends_on',
        'is_working',
        'starts_at',
        'ends_at',
        'note',
    ];

    protected $casts = [
        'date' => 'date',
        'starts_on' => 'date',
        'ends_on' => 'date',
        'is_working' => 'boolean',
    ];
}
