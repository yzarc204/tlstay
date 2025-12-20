<?php

namespace App\Models;

use App\Services\RoomAvailabilityService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $fillable = [
        'house_id',
        'room_number',
        'floor',
        'price_per_day',
        'status',
        'area',
        'amenities',
        'images',
        'tenant_name',
        'tenant_id',
        'rental_start_date',
        'rental_end_date',
    ];

    protected function casts(): array
    {
        return [
            'price_per_day' => 'decimal:2',
            'area' => 'decimal:2',
            'amenities' => 'array',
            'images' => 'array',
            'rental_start_date' => 'date',
            'rental_end_date' => 'date',
        ];
    }

    /**
     * Get the house that owns the room.
     */
    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    /**
     * Get the bookings for the room.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get active booking for the room.
     */
    public function activeBooking(): HasMany
    {
        return $this->hasMany(Booking::class)->where('status', 'active');
    }

    /**
     * Get the tenant (user) currently renting the room.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    /**
     * Get the room availability service instance
     *
     * @return RoomAvailabilityService
     */
    protected function getAvailabilityService(): RoomAvailabilityService
    {
        return app(RoomAvailabilityService::class);
    }

    /**
     * Check if room is available for booking in a date range
     * Room is NOT available if:
     * 1. There are PAID bookings that overlap with the requested date range
     * 2. There is a tenant currently staying (rental_start_date and rental_end_date) that overlaps with the requested date range
     * Pending or failed bookings are ignored
     * 
     * @deprecated This method now delegates to RoomAvailabilityService for better maintainability
     */
    public function isAvailableForDates($startDate, $endDate): bool
    {
        return $this->getAvailabilityService()->isAvailableForDates($this, $startDate, $endDate);
    }

    /**
     * Get status for a specific date range
     * Returns 'active' if:
     * 1. There are PAID bookings with booking_status = 'active' in that date range, OR
     * 2. There is a tenant currently staying (rental_start_date and rental_end_date) that overlaps with the date range
     * Returns 'available' otherwise
     * Pending or failed bookings are ignored
     * 
     * @deprecated This method now delegates to RoomAvailabilityService for better maintainability
     */
    public function getStatusForDates($startDate, $endDate): string
    {
        return $this->getAvailabilityService()->getStatusForDates($this, $startDate, $endDate);
    }

    /**
     * Get effective status based on bookings and current tenant
     * Room is active if:
     * 1. There's a paid booking with booking_status = 'active' (currently staying), OR
     * 2. There's a tenant currently staying (rental_start_date <= today <= rental_end_date)
     * Returns 'available' otherwise
     * 
     * @deprecated This method now delegates to RoomAvailabilityService for better maintainability
     */
    public function getEffectiveStatus(): string
    {
        return $this->getAvailabilityService()->getEffectiveStatus($this);
    }
}
