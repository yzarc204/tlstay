<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\House;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BookingController extends Controller
{
    /**
     * Display a listing of bookings.
     */
    public function index(Request $request): Response
    {
        $query = Booking::with(['user', 'house', 'room'])
            ->orderBy('created_at', 'desc');

        // Filter by booking status (upcoming, active, past)
        if ($request->filled('booking_status')) {
            $query->where('booking_status', $request->booking_status);
        }

        // Filter by payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Filter by contract signed status
        if ($request->filled('contract_signed')) {
            if ($request->contract_signed === '1') {
                $query->where('contract_signed', true);
            } else {
                $query->where('contract_signed', false);
            }
        }

        // Filter by house
        if ($request->filled('house_id')) {
            $query->where('house_id', $request->house_id);
        }

        // Search by booking code, user name, house name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('booking_code', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    })
                    ->orWhereHas('house', function ($houseQuery) use ($search) {
                        $houseQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $bookings = $query->paginate(15)->withQueryString()->through(function ($booking) {
            return [
                'id' => $booking->id,
                'booking_code' => $booking->booking_code,
                'user' => [
                    'id' => $booking->user->id,
                    'name' => $booking->user->name,
                    'email' => $booking->user->email,
                    'phone' => $booking->user->phone,
                ],
                'house' => [
                    'id' => $booking->house->id,
                    'name' => $booking->house->name,
                ],
                'room' => [
                    'id' => $booking->room->id,
                    'room_number' => $booking->room->room_number,
                    'floor' => $booking->room->floor,
                ],
                'start_date' => $booking->start_date->format('Y-m-d'),
                'end_date' => $booking->end_date->format('Y-m-d'),
                'status' => $booking->status,
                'booking_status' => $booking->booking_status,
                'total_price' => (float) $booking->total_price,
                'discount_amount' => (float) $booking->discount_amount,
                'payment_status' => $booking->payment_status,
                'payment_method' => $booking->payment_method,
                'contract_signed' => $booking->contract_signed ?? false,
                'signed_at' => $booking->signed_at?->format('Y-m-d H:i:s'),
                'paid_at' => $booking->paid_at?->format('Y-m-d H:i:s'),
                'created_at' => $booking->created_at->format('Y-m-d H:i:s'),
            ];
        });

        // Get houses owned by manager for filter
        $user = auth()->user();
        $houses = House::where('owner_id', $user->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Admin/Bookings/Index', [
            'bookings' => $bookings,
            'houses' => $houses,
            'filters' => $request->only(['search', 'booking_status', 'payment_status', 'contract_signed', 'house_id']),
        ]);
    }

    /**
     * Display the specified booking.
     */
    public function show(Booking $booking): Response
    {
        $user = auth()->user();

        // Check if booking belongs to a house owned by the manager
        if ($booking->house->owner_id !== $user->id) {
            abort(403, 'Bạn không có quyền xem đặt phòng này');
        }

        $booking->load(['user', 'house', 'room', 'invoices']);

        $bookingData = [
            'id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'user' => [
                'id' => $booking->user->id,
                'name' => $booking->user->name,
                'email' => $booking->user->email,
                'phone' => $booking->user->phone,
                'id_card_number' => $booking->user->id_card_number,
                'permanent_address' => $booking->user->permanent_address,
                'date_of_birth' => $booking->user->date_of_birth?->format('Y-m-d'),
                'gender' => $booking->user->gender,
            ],
            'house' => [
                'id' => $booking->house->id,
                'name' => $booking->house->name,
                'address' => $booking->house->address,
            ],
            'room' => [
                'id' => $booking->room->id,
                'room_number' => $booking->room->room_number,
                'floor' => $booking->room->floor,
                'price_per_day' => (float) $booking->room->price_per_day,
                'area' => (float) ($booking->room->area ?? 0),
            ],
            'start_date' => $booking->start_date->format('Y-m-d'),
            'end_date' => $booking->end_date->format('Y-m-d'),
            'status' => $booking->status,
            'booking_status' => $booking->booking_status,
            'total_price' => (float) $booking->total_price,
            'discount_amount' => (float) $booking->discount_amount,
            'payment_status' => $booking->payment_status,
            'payment_method' => $booking->payment_method,
            'vnpay_transaction_id' => $booking->vnpay_transaction_id,
            'contract_signed' => $booking->contract_signed ?? false,
            'signed_at' => $booking->signed_at?->format('Y-m-d H:i:s'),
            'paid_at' => $booking->paid_at?->format('Y-m-d H:i:s'),
            'notes' => $booking->notes,
            'created_at' => $booking->created_at->format('Y-m-d H:i:s'),
            'invoices' => $booking->invoices->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'month' => $invoice->month,
                    'year' => $invoice->year,
                    'start_date' => $invoice->start_date?->format('Y-m-d'),
                    'end_date' => $invoice->end_date?->format('Y-m-d'),
                    'amount' => (float) $invoice->amount,
                    'electricity_amount' => (float) $invoice->electricity_amount,
                    'water_amount' => (float) $invoice->water_amount,
                    'other_fees' => (float) ($invoice->other_fees ?? 0),
                    'status' => $invoice->status,
                    'due_date' => $invoice->due_date?->format('Y-m-d'),
                    'paid_at' => $invoice->paid_at?->format('Y-m-d H:i:s'),
                    'notes' => $invoice->notes,
                ];
            }),
        ];

        return Inertia::render('Admin/Bookings/Show', [
            'booking' => $bookingData,
        ]);
    }
}
