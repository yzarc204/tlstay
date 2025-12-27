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
            ->with(['house', 'room', 'review'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($booking) {
                $canReview = $booking->payment_status === 'paid' 
                    && $booking->status !== 'cancelled' 
                    && !$booking->review;
                
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
                    'has_review' => $booking->review !== null,
                    'can_review' => $canReview,
                    'review' => $booking->review ? [
                        'id' => $booking->review->id,
                        'rating' => $booking->review->rating,
                        'comment' => $booking->review->comment,
                        'created_at' => $booking->review->created_at->format('Y-m-d H:i:s'),
                    ] : null,
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
     * Show booking details
     */
    public function show($id)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $booking = Booking::where('id', $id)
            ->where('user_id', $user->id)
            ->with(['house.owner', 'room', 'review.user', 'invoices'])
            ->firstOrFail();

        // Get invoices for this booking
        $invoices = $booking->invoices()
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
                    'invoice_code' => $invoice->invoice_code,
                    'month' => $invoice->month,
                    'year' => $invoice->year,
                    'start_date' => $invoice->start_date ? $invoice->start_date->format('Y-m-d') : null,
                    'end_date' => $invoice->end_date ? $invoice->end_date->format('Y-m-d') : null,
                    'month_year' => $monthYear,
                    'electricity_amount' => (float) ($invoice->electricity_amount ?? 0),
                    'water_amount' => (float) ($invoice->water_amount ?? 0),
                    'other_fees' => (float) ($invoice->other_fees ?? 0),
                    'total' => (float) (
                        ($invoice->electricity_amount ?? 0) +
                        ($invoice->water_amount ?? 0) +
                        ($invoice->other_fees ?? 0)
                    ),
                    'status' => $invoice->status,
                    'due_date' => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : null,
                    'paid_at' => $invoice->paid_at ? $invoice->paid_at->format('Y-m-d H:i:s') : null,
                    'notes' => $invoice->notes,
                ];
            });

        // Check if user can review
        $canReview = false;
        $reviewableBooking = null;
        if ($booking->payment_status === 'paid' 
            && $booking->status !== 'cancelled' 
            && !$booking->review) {
            $today = SystemTimeService::today();
            
            // Check if booking has ended and within 14 days
            if ($booking->end_date->lte($today)) {
                $reviewDeadline = $booking->end_date->copy()->addDays(14);
                if ($today->lte($reviewDeadline)) {
                    $canReview = true;
                    $reviewableBooking = $booking;
                }
            }
        }

        $bookingData = [
            'id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'house' => [
                'id' => $booking->house->id,
                'name' => $booking->house->name,
                'address' => $booking->house->address,
                'latitude' => $booking->house->latitude ? (float) $booking->house->latitude : null,
                'longitude' => $booking->house->longitude ? (float) $booking->house->longitude : null,
            ],
            'owner' => $booking->house->owner ? [
                'id' => $booking->house->owner->id,
                'name' => $booking->house->owner->name,
                'email' => $booking->house->owner->email,
                'phone' => $booking->house->owner->phone,
            ] : null,
            'room' => [
                'id' => $booking->room->id,
                'room_number' => $booking->room->room_number,
                'floor' => $booking->room->floor,
                'area' => $booking->room->area ? (float) $booking->room->area : null,
                'amenities' => $booking->room->amenities ?? [],
                'images' => $booking->room->images ?? [],
            ],
            'start_date' => $booking->start_date->format('Y-m-d'),
            'end_date' => $booking->end_date->format('Y-m-d'),
            'status' => $booking->status,
            'booking_status' => $booking->booking_status ?? $this->getBookingStatus($booking),
            'total_price' => (float) $booking->total_price,
            'discount_amount' => (float) ($booking->discount_amount ?? 0),
            'payment_status' => $booking->payment_status,
            'payment_method' => $booking->payment_method,
            'paid_at' => $booking->paid_at ? $booking->paid_at->format('Y-m-d H:i:s') : null,
            'vnpay_transaction_id' => $booking->vnpay_transaction_id,
            'notes' => $booking->notes,
            'contract_signed' => $booking->contract_signed ?? false,
            'signed_at' => $booking->signed_at ? $booking->signed_at->format('Y-m-d H:i:s') : null,
            'user_signature' => $booking->user_signature,
            'created_at' => $booking->created_at->format('Y-m-d H:i:s'),
            'invoices' => $invoices,
            'has_review' => $booking->review !== null,
            'can_review' => $canReview,
            'review' => $booking->review ? [
                'id' => $booking->review->id,
                'user' => [
                    'id' => $booking->review->user->id,
                    'name' => $booking->review->user->name,
                    'avatar' => $booking->review->user->avatar,
                ],
                'rating' => $booking->review->rating,
                'comment' => $booking->review->comment,
                'images' => $booking->review->images ?? [],
                'manager_response' => $booking->review->manager_response,
                'manager_response_at' => $booking->review->manager_response_at ? $booking->review->manager_response_at->format('Y-m-d H:i:s') : null,
                'created_at' => $booking->review->created_at->format('Y-m-d H:i:s'),
            ] : null,
        ];

        return Inertia::render('BookingDetail', [
            'booking' => $bookingData,
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
