<?php

namespace App\Services;

use App\Models\Room;
use Carbon\Carbon;

class RoomAvailabilityService
{
    /**
     * Check if room is available for booking in a date range
     * Room is NOT available if:
     * 1. There are PAID bookings that overlap with the requested date range
     * 2. There is a tenant currently staying (rental_start_date and rental_end_date) that overlaps with the requested date range
     * Pending or failed bookings are ignored
     *
     * @param Room $room
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @return bool
     */
    public function isAvailableForDates(Room $room, $startDate, $endDate): bool
    {
        // Convert to Carbon if string
        $startDate = $this->parseDate($startDate);
        $endDate = $this->parseDate($endDate);

        // Check for overlapping PAID bookings only
        // Ignore pending/failed bookings
        $overlappingBookings = $room->bookings()
            ->where('payment_status', 'paid')
            ->where(function ($query) use ($startDate, $endDate) {
                // Booking overlaps if:
                // - Booking starts before or on requested end date
                // - AND booking ends after or on requested start date
                $query->where('start_date', '<=', $endDate)
                      ->where('end_date', '>=', $startDate);
            })
            ->exists();

        // Check if there is a tenant currently staying that overlaps with the requested date range
        $hasOverlappingTenant = $this->hasOverlappingTenant($room, $startDate, $endDate);

        // Room is available only if there are no overlapping bookings AND no overlapping tenant
        return !$overlappingBookings && !$hasOverlappingTenant;
    }

    /**
     * Get status for a specific date range
     * Returns 'occupied' if:
     * 1. There are PAID bookings in that date range, OR
     * 2. There is a tenant currently staying (rental_start_date and rental_end_date) that overlaps with the date range
     * Returns 'available' otherwise
     * Pending or failed bookings are ignored
     *
     * @param Room $room
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @return string 'available' or 'occupied'
     */
    public function getStatusForDates(Room $room, $startDate, $endDate): string
    {
        // Convert to Carbon if string
        $startDate = $this->parseDate($startDate);
        $endDate = $this->parseDate($endDate);

        $hasBooking = $room->bookings()
            ->where('payment_status', 'paid')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate)
                      ->where('end_date', '>=', $startDate);
            })
            ->exists();

        // Check if there is a tenant currently staying that overlaps with the date range
        $hasOverlappingTenant = $this->hasOverlappingTenant($room, $startDate, $endDate);

        return ($hasBooking || $hasOverlappingTenant) ? 'occupied' : 'available';
    }

    /**
     * Get effective status based on bookings and current tenant
     * Room is occupied if:
     * 1. There's a paid booking that has reached check-in date and hasn't ended, OR
     * 2. There's a tenant currently staying (rental_start_date <= today <= rental_end_date)
     *
     * @param Room $room
     * @return string 'available' or 'occupied'
     */
    public function getEffectiveStatus(Room $room): string
    {
        $today = now()->toDateString();
        
        $activeBooking = $room->bookings()
            ->where('payment_status', 'paid')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();

        // Check if there is a tenant currently staying
        $hasActiveTenant = $this->hasActiveTenant($room, $today);

        return ($activeBooking || $hasActiveTenant) ? 'occupied' : 'available';
    }

    /**
     * Check if room has overlapping tenant for the given date range
     *
     * @param Room $room
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return bool
     */
    protected function hasOverlappingTenant(Room $room, $startDate, $endDate): bool
    {
        if (!$room->tenant_id || !$room->rental_start_date || !$room->rental_end_date) {
            return false;
        }

        // Tenant rental overlaps if:
        // - Tenant rental starts before or on requested end date
        // - AND tenant rental ends after or on requested start date
        return $room->rental_start_date <= $endDate 
            && $room->rental_end_date >= $startDate;
    }

    /**
     * Check if room has active tenant for the given date
     *
     * @param Room $room
     * @param string $date
     * @return bool
     */
    protected function hasActiveTenant(Room $room, string $date): bool
    {
        if (!$room->tenant_id || !$room->rental_start_date || !$room->rental_end_date) {
            return false;
        }

        return $room->rental_start_date <= $date 
            && $room->rental_end_date >= $date;
    }

    /**
     * Parse date string to Carbon instance
     *
     * @param string|Carbon $date
     * @return Carbon
     */
    protected function parseDate($date): Carbon
    {
        if ($date instanceof Carbon) {
            return $date;
        }

        return Carbon::parse($date);
    }
}
