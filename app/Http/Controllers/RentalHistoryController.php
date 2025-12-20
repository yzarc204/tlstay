<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Invoice;
use App\Services\SystemTimeService;
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
                    'room_name' => $booking->room->name ?? ('Phòng ' . ($booking->room->room_number ?? $booking->room_id)),
                    'room_number' => $booking->room->room_number ?? null,
                    'start_date' => $booking->start_date->format('Y-m-d'),
                    'end_date' => $booking->end_date->format('Y-m-d'),
                    'status' => $booking->status, // active, completed, cancelled
                    'booking_status' => $booking->booking_status ?? $this->getBookingStatus($booking), // upcoming, active, past
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
            ->orderBy('start_date', 'desc')
            ->orderBy('end_date', 'desc')
            ->get()
            ->map(function ($invoice) {
                $monthYear = '';
                if ($invoice->start_date && $invoice->end_date) {
                    $monthYear = $invoice->start_date->format('d/m/Y') . ' - ' . $invoice->end_date->format('d/m/Y');
                } elseif ($invoice->month && $invoice->year) {
                    $monthYear = "{$invoice->month}/{$invoice->year}";
                }
                
                return [
                    'id' => $invoice->id,
                    'booking_id' => $invoice->booking_id,
                    'month' => $invoice->month,
                    'year' => $invoice->year,
                    'start_date' => $invoice->start_date ? $invoice->start_date->format('Y-m-d') : null,
                    'end_date' => $invoice->end_date ? $invoice->end_date->format('Y-m-d') : null,
                    'month_year' => $monthYear,
                    'room_rent' => 0, // Room rent is not included in invoices (already paid during booking)
                    'electricity' => (float) $invoice->electricity_amount,
                    'water' => (float) $invoice->water_amount,
                    'internet' => (float) ($invoice->other_fees ?? 0),
                    'total' => (float) (
                        $invoice->electricity_amount +
                        $invoice->water_amount +
                        ($invoice->other_fees ?? 0)
                    ),
                    'status' => $invoice->status,
                    'due_date' => $invoice->due_date?->format('Y-m-d'),
                    'paid_at' => $invoice->paid_at?->format('Y-m-d H:i:s'),
                ];
            });

        return Inertia::render('RentalHistory', [
            'bookings' => $bookings,
            'invoices' => $invoices,
        ]);
    }

    /**
     * Determine booking status based on dates and payment
     * Returns: 'upcoming', 'active', or 'past'
     */
    private function getBookingStatus($booking): string
    {
        // Only calculate for paid bookings
        if ($booking->payment_status !== 'paid') {
            return 'upcoming'; // Default for unpaid bookings
        }

        $today = SystemTimeService::today();
        $startDate = $booking->start_date;
        $endDate = $booking->end_date;

        // If start date is in the future, it's upcoming
        if ($startDate->gt($today)) {
            return 'upcoming';
        }

        // If end date has passed, it's past
        if ($endDate->lt($today)) {
            return 'past';
        }

        // If start_date <= today <= end_date, it's active
        return 'active';
    }
}
