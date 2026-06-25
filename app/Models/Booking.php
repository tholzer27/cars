<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'cleaning_package_id',
        'booking_mode',
        'booking_date',
        'booking_time',
        'pickup_address',
        'vehicle_info',
        'notes',
        'custom_configuration',
        'total_price',
        'duration_minutes',
        'status',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'custom_configuration' => 'array',
        'total_price' => 'float',
        'duration_minutes' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cleaningPackage(): BelongsTo
    {
        return $this->belongsTo(CleaningPackage::class);
    }

    public function extras(): BelongsToMany
    {
        return $this->belongsToMany(CleaningExtra::class, 'booking_extras');
    }
}
