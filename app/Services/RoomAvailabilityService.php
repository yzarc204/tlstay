<?php

namespace App\Services;

use App\Models\Room;
use App\Services\SystemTimeService;
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
     * Returns: 'available' or 'active'
     * - available: No bookings or tenants in the date range
     * - active: Has paid booking that overlaps with the date range OR has tenant overlapping
     * Note: Room only has 2 states: available (no one staying) or active (someone staying)
     * 'upcoming' and 'past' are booking statuses, not room statuses
     * Pending or failed bookings are ignored
     *
     * @param Room $room
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @return string 'available' or 'active'
     */
    public function getStatusForDates(Room $room, $startDate, $endDate): string
    {
        // Convert to Carbon if string
        $startDate = $this->parseDate($startDate);
        $endDate = $this->parseDate($endDate);

        // Check for overlapping bookings in the date range (only active bookings with booking_status = 'active')
        $overlappingBooking = $room->bookings()
            ->where('payment_status', 'paid')
            ->where('status', 'active')
            ->where('booking_status', 'active') // Only check active bookings (currently staying)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate)
                      ->where('end_date', '>=', $startDate);
            })
            ->first();

        // Check if there is a tenant currently staying that overlaps with the date range
        $hasOverlappingTenant = $this->hasOverlappingTenant($room, $startDate, $endDate);

        // Room is active if there's an overlapping active booking or tenant
        if ($overlappingBooking || $hasOverlappingTenant) {
            return 'active';
        }

        return 'available';
    }

    /**
     * Get effective status based on bookings and current tenant
     * Returns: 'available' or 'active'
     * - available: No active bookings or tenants (room is empty)
     * - active: Has active booking (booking_status = 'active') OR has active tenant
     * Note: Room only has 2 states: available (no one staying) or active (someone staying)
     * 'upcoming' and 'past' are booking statuses, not room statuses
     *
     * @param Room $room
     * @return string 'available' or 'active'
     */
    public function getEffectiveStatus(Room $room): string
    {
        // Use system time (real or manual) instead of real time
        $today = SystemTimeService::today();
        $todayString = $today->toDateString();

        // Check for active bookings (booking_status = 'active' means someone is currently staying)
        $activeBooking = $room->bookings()
            ->where('payment_status', 'paid')
            ->where('status', 'active')
            ->where('booking_status', 'active') // Only check bookings with active status (currently staying)
            ->where('start_date', '<=', $todayString)
            ->where('end_date', '>=', $todayString)
            ->first();

        // Check if there is a tenant currently staying
        $hasActiveTenant = $this->hasActiveTenant($room, $todayString);

        // Room is active if there's an active booking or tenant
        if ($activeBooking || $hasActiveTenant) {
            return 'active';
        }

        return 'available';
    }

    /**
     * Get detailed status info for a room
     *
     * @param Room $room
     * @return array
     */
    public function getDetailedStatus(Room $room): array
    {
        $today = SystemTimeService::today();
        $todayString = $today->toDateString();
        
        $status = $this->getEffectiveStatus($room);
        
        $activeBooking = null;

        if ($status === 'active') {
            $activeBooking = $room->bookings()
                ->where('payment_status', 'paid')
                ->where('status', 'active')
                ->where('booking_status', 'active') // Only active bookings (currently staying)
                ->where('start_date', '<=', $todayString)
                ->where('end_date', '>=', $todayString)
                ->first();
        }

        return [
            'status' => $status,
            'active_booking' => $activeBooking,
            'has_active_tenant' => $this->hasActiveTenant($room, $todayString),
        ];
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
