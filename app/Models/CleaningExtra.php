<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CleaningExtra extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'duration_minutes',
        'is_available',
    ];

    protected $casts = [
        'price' => 'float',
        'duration_minutes' => 'integer',
        'is_available' => 'boolean',
    ];

    public function bookings(): BelongsToMany
    {
        return $this->belongsToMany(Booking::class, 'booking_extras');
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(CleaningPackage::class);
    }
}
