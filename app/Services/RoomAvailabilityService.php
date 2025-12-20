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
     * Returns: 'available', 'upcoming', 'active', or 'past'
     * - available: No bookings or tenants in the date range
     * - upcoming: Has paid booking with start_date > endDate (booking starts after the range)
     * - active: Has paid booking that overlaps with the date range OR has tenant overlapping
     * - past: Has paid booking with end_date < startDate (booking ended before the range)
     * Pending or failed bookings are ignored
     *
     * @param Room $room
     * @param string|Carbon $startDate
     * @param string|Carbon $endDate
     * @return string 'available', 'upcoming', 'active', or 'past'
     */
    public function getStatusForDates(Room $room, $startDate, $endDate): string
    {
        // Convert to Carbon if string
        $startDate = $this->parseDate($startDate);
        $endDate = $this->parseDate($endDate);
        $today = SystemTimeService::today();

        // Check for overlapping bookings in the date range
        $overlappingBooking = $room->bookings()
            ->where('payment_status', 'paid')
            ->where('status', 'active')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate)
                      ->where('end_date', '>=', $startDate);
            })
            ->first();

        // Check for upcoming booking (starts after the end date)
        $upcomingBooking = $room->bookings()
            ->where('payment_status', 'paid')
            ->where('status', 'active')
            ->where('start_date', '>', $endDate)
            ->orderBy('start_date', 'asc')
            ->first();

        // Check for past booking (ended before the start date)
        $pastBooking = $room->bookings()
            ->where('payment_status', 'paid')
            ->where('status', 'active')
            ->where('end_date', '<', $startDate)
            ->orderBy('end_date', 'desc')
            ->first();

        // Check if there is a tenant currently staying that overlaps with the date range
        $hasOverlappingTenant = $this->hasOverlappingTenant($room, $startDate, $endDate);

        // Priority: active > upcoming > past > available
        if ($overlappingBooking || $hasOverlappingTenant) {
            return 'active';
        } elseif ($upcomingBooking) {
            return 'upcoming';
        } elseif ($pastBooking) {
            return 'past';
        }

        return 'available';
    }

    /**
     * Get effective status based on bookings and current tenant
     * Returns: 'available', 'upcoming', 'active', or 'past'
     * - available: No bookings or tenants
     * - upcoming: Has paid booking with start_date > today
     * - active: Has paid booking with start_date <= today <= end_date OR has active tenant
     * - past: Has paid booking with end_date < today OR tenant expired
     *
     * @param Room $room
     * @return string 'available', 'upcoming', 'active', or 'past'
     */
    public function getEffectiveStatus(Room $room): string
    {
        // Use system time (real or manual) instead of real time
        $today = SystemTimeService::today();
        $todayString = $today->toDateString();
        
        // Check for upcoming bookings (start_date > today)
        $upcomingBooking = $room->bookings()
            ->where('payment_status', 'paid')
            ->where('status', 'active')
            ->where('start_date', '>', $todayString)
            ->orderBy('start_date', 'asc')
            ->first();

        // Check for active bookings (start_date <= today <= end_date)
        $activeBooking = $room->bookings()
            ->where('payment_status', 'paid')
            ->where('status', 'active')
            ->where('start_date', '<=', $todayString)
            ->where('end_date', '>=', $todayString)
            ->first();

        // Check for past bookings (end_date < today)
        $pastBooking = $room->bookings()
            ->where('payment_status', 'paid')
            ->where('status', 'active')
            ->where('end_date', '<', $todayString)
            ->orderBy('end_date', 'desc')
            ->first();

        // Check if there is a tenant currently staying
        $hasActiveTenant = $this->hasActiveTenant($room, $todayString);

        // Priority: active > upcoming > past > available
        if ($activeBooking || $hasActiveTenant) {
            return 'active';
        } elseif ($upcomingBooking) {
            return 'upcoming';
        } elseif ($pastBooking) {
            return 'past';
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
        
        $upcomingBooking = null;
        $activeBooking = null;
        $pastBooking = null;

        if ($status === 'upcoming') {
            $upcomingBooking = $room->bookings()
                ->where('payment_status', 'paid')
                ->where('status', 'active')
                ->where('start_date', '>', $todayString)
                ->orderBy('start_date', 'asc')
                ->first();
        } elseif ($status === 'active') {
            $activeBooking = $room->bookings()
                ->where('payment_status', 'paid')
                ->where('status', 'active')
                ->where('start_date', '<=', $todayString)
                ->where('end_date', '>=', $todayString)
                ->first();
        } elseif ($status === 'past') {
            $pastBooking = $room->bookings()
                ->where('payment_status', 'paid')
                ->where('status', 'active')
                ->where('end_date', '<', $todayString)
                ->orderBy('end_date', 'desc')
                ->first();
        }

        return [
            'status' => $status,
            'upcoming_booking' => $upcomingBooking,
            'active_booking' => $activeBooking,
            'past_booking' => $pastBooking,
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
