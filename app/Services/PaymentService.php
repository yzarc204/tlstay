<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Confirm payment for a booking
     *
     * @param Booking $booking
     * @param User $user
     * @return Booking
     * @throws \Exception
     */
    public function confirmPayment(Booking $booking, User $user): Booking
    {
        // Validate user owns the booking
        if ($booking->user_id != $user->id) {
            throw new \Exception('Bạn không có quyền thanh toán đơn đặt phòng này.');
        }

        // Check if already paid
        if ($booking->payment_status === 'paid') {
            return $booking; // Already paid, return as is
        }

        // Use transaction to ensure atomicity
        return DB::transaction(function () use ($booking, $user) {
            // Lock booking to prevent race conditions
            $lockedBooking = Booking::lockForUpdate()->findOrFail($booking->id);
            
            // Double-check payment status
            if ($lockedBooking->payment_status === 'paid') {
                return $lockedBooking;
            }
            
            // Update booking payment status
            // Note: Room status will be updated by cronjob when check-in date arrives
            $lockedBooking->update([
                'payment_status' => 'paid',
                'paid_at' => now(),
                'payment_method' => 'bank_transfer',
            ]);
            
            Log::info('Booking payment confirmed', [
                'booking_id' => $lockedBooking->id,
                'user_id' => $user->id,
                'amount' => $lockedBooking->total_price,
                'check_in_date' => $lockedBooking->start_date->format('Y-m-d'),
            ]);
            
            return $lockedBooking->fresh();
        });
    }

    /**
     * Validate payment can be processed
     *
     * @param Booking $booking
     * @param User $user
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validatePayment(Booking $booking, User $user): array
    {
        $errors = [];

        // Check user owns the booking
        if ($booking->user_id != $user->id) {
            $errors['authorization'] = 'Bạn không có quyền thanh toán đơn đặt phòng này.';
        }

        // Check if already paid
        if ($booking->payment_status === 'paid') {
            $errors['status'] = 'Đơn đặt phòng này đã được thanh toán.';
        }

        // Check if booking is cancelled or invalid
        if ($booking->status !== 'active') {
            $errors['status'] = 'Đơn đặt phòng không hợp lệ hoặc đã bị hủy.';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
