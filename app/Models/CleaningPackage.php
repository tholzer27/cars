<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CleaningPackage extends Model
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

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function includedServices(): BelongsToMany
    {
        return $this->belongsToMany(CleaningExtra::class);
    }
}
