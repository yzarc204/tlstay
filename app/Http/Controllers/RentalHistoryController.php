<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RentalHistoryController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Fetch user bookings with relationships
        $bookings = Booking::where('user_id', $user->id)
            ->with(['house', 'room'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'house_id' => $booking->house_id,
                    'room_id' => $booking->room_id,
                    'house_name' => $booking->house->name ?? 'Nhà trọ',
                    'room_name' => $booking->room->name ?? 'Phòng ' . $booking->room_id,
                    'start_date' => $booking->start_date->format('Y-m-d'),
                    'end_date' => $booking->end_date->format('Y-m-d'),
                    'status' => $this->getBookingStatus($booking),
                    'total_price' => (float) $booking->total_price,
                    'payment_status' => $booking->payment_status,
                    'payment_method' => $booking->payment_method,
                    'paid_at' => $booking->paid_at?->format('Y-m-d H:i:s'),
                    'created_at' => $booking->created_at->format('Y-m-d H:i:s'),
                ];
            });

        // Fetch user invoices
        $invoices = Invoice::where('user_id', $user->id)
            ->with('booking')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'booking_id' => $invoice->booking_id,
                    'month' => $invoice->month,
                    'year' => $invoice->year,
                    'month_year' => "{$invoice->month}/{$invoice->year}",
                    'room_rent' => (float) $invoice->amount,
                    'electricity' => (float) $invoice->electricity_amount,
                    'water' => (float) $invoice->water_amount,
                    'internet' => (float) ($invoice->other_fees ?? 0),
                    'total' => (float) (
                        $invoice->amount +
                        $invoice->electricity_amount +
                        $invoice->water_amount +
                        ($invoice->other_fees ?? 0)
                    ),
                    'status' => $invoice->status,
                    'due_date' => $invoice->due_date?->format('Y-m-d'),
                    'paid_at' => $invoice->paid_at?->format('Y-m-d H:i:s'),
                ];
            });

        // Get wallet balance
        $wallet = \App\Models\Wallet::getOrCreateForUser($user->id);

        return Inertia::render('RentalHistory', [
            'bookings' => $bookings,
            'invoices' => $invoices,
            'wallet' => [
                'balance' => (float) $wallet->balance,
            ],
        ]);
    }

    /**
     * Determine booking status based on dates and payment
     */
    private function getBookingStatus($booking): string
    {
        $now = now();
        $endDate = $booking->end_date;

        // If payment is not completed, return pending
        if ($booking->payment_status !== 'paid') {
            return 'pending';
        }

        // If end date has passed, return completed
        if ($endDate->isPast()) {
            return 'completed';
        }

        // If start date hasn't arrived yet, return upcoming
        if ($booking->start_date->isFuture()) {
            return 'upcoming';
        }

        // Otherwise, it's active
        return 'active';
    }
}
