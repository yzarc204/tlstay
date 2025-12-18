<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class House extends Model
{
    protected $fillable = [
        'owner_id',
        'name',
        'address',
        'street_detail',
        'description',
        'amenities',
        'images',
        'featured_images',
        'image',
        'price_per_day',
        'floors',
        'total_rooms',
        'rating',
        'reviews',
        'latitude',
        'longitude',
        'contact_phone',
        'contact_email',
        'ward_id',
        'street_id',
    ];

    protected function casts(): array
    {
        return [
            'amenities' => 'array',
            'images' => 'array',
            'featured_images' => 'array',
            'price_per_day' => 'decimal:2',
            'rating' => 'decimal:2',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
        ];
    }

    /**
     * Get the owner of the house.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the rooms for the house.
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    /**
     * Get the bookings for the house.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get available rooms count.
     */
    public function getAvailableRoomsAttribute(): int
    {
        return $this->rooms()->where('status', 'available')->count();
    }

    /**
     * Get the ward address.
     */
    public function wardAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'ward_id');
    }

    /**
     * Get the street address.
     */
    public function streetAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'street_id');
    }
}
