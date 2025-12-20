<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Room;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class OccupiedRoomsController extends Controller
{
    /**
     * Display a listing of occupied rooms.
     */
    public function index(Request $request): Response
    {
        $today = now()->toDateString();

        // Get rooms with active paid bookings OR active tenant rentals
        $query = Room::with(['house', 'bookings' => function ($query) use ($today) {
            $query->where('payment_status', 'paid')
                  ->where('start_date', '<=', $today)
                  ->where('end_date', '>=', $today)
                  ->with('user');
        }])
        ->where(function ($query) use ($today) {
            // Rooms with active paid bookings
            $query->whereHas('bookings', function ($q) use ($today) {
                $q->where('payment_status', 'paid')
                  ->where('start_date', '<=', $today)
                  ->where('end_date', '>=', $today);
            })
            // OR rooms with active tenant (rental_start_date and rental_end_date)
            ->orWhere(function ($q) use ($today) {
                $q->whereNotNull('tenant_id')
                  ->whereNotNull('rental_start_date')
                  ->whereNotNull('rental_end_date')
                  ->where('rental_start_date', '<=', $today)
                  ->where('rental_end_date', '>=', $today);
            });
        });

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('room_number', 'like', "%{$search}%")
                  ->orWhereHas('house', function ($houseQuery) use ($search) {
                      $houseQuery->where('name', 'like', "%{$search}%")
                                 ->orWhere('address', 'like', "%{$search}%");
                  })
                  ->orWhereHas('bookings.user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by house
        if ($request->filled('house_id')) {
            $query->where('house_id', $request->house_id);
        }

        $rooms = $query->orderBy('house_id')
                      ->orderBy('floor')
                      ->orderBy('room_number')
                      ->paginate(15)
                      ->withQueryString();

        // Transform rooms data
        $rooms->getCollection()->transform(function ($room) use ($today) {
            // Get active booking
            $activeBooking = $room->bookings()
                ->where('payment_status', 'paid')
                ->where('start_date', '<=', $today)
                ->where('end_date', '>=', $today)
                ->first();

            // Check if it's a long-term rental (monthly rental)
            $isLongTerm = false;
            $rentalDays = 0;
            if ($activeBooking) {
                $rentalDays = Carbon::parse($activeBooking->start_date)
                    ->diffInDays(Carbon::parse($activeBooking->end_date));
                // Consider monthly if rental period is 30 days or more
                $isLongTerm = $rentalDays >= 30;
            } elseif ($room->tenant_id && $room->rental_start_date && $room->rental_end_date) {
                $rentalDays = Carbon::parse($room->rental_start_date)
                    ->diffInDays(Carbon::parse($room->rental_end_date));
                $isLongTerm = $rentalDays >= 30;
            }

            // Get existing invoices for the active booking or tenant
            $invoices = [];
            if ($activeBooking) {
                $invoices = $activeBooking->invoices()
                    ->orderBy('start_date', 'desc')
                    ->orderBy('end_date', 'desc')
                    ->get()
                    ->map(function ($invoice) {
                        return [
                            'id' => $invoice->id,
                            'month' => $invoice->month,
                            'year' => $invoice->year,
                            'start_date' => $invoice->start_date ? $invoice->start_date->format('Y-m-d') : null,
                            'end_date' => $invoice->end_date ? $invoice->end_date->format('Y-m-d') : null,
                            'amount' => (float) $invoice->amount,
                            'electricity_amount' => (float) $invoice->electricity_amount,
                            'water_amount' => (float) $invoice->water_amount,
                            'other_fees' => (float) ($invoice->other_fees ?? 0),
                            'status' => $invoice->status,
                            'due_date' => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : null,
                            'paid_at' => $invoice->paid_at ? $invoice->paid_at->format('Y-m-d H:i:s') : null,
                        ];
                    });
            } elseif ($room->tenant_id) {
                // Get invoices for tenant (where booking_id is null and user_id matches tenant_id)
                $invoices = Invoice::where('user_id', $room->tenant_id)
                    ->whereNull('booking_id')
                    ->orderBy('start_date', 'desc')
                    ->orderBy('end_date', 'desc')
                    ->get()
                    ->map(function ($invoice) {
                        return [
                            'id' => $invoice->id,
                            'month' => $invoice->month,
                            'year' => $invoice->year,
                            'start_date' => $invoice->start_date ? $invoice->start_date->format('Y-m-d') : null,
                            'end_date' => $invoice->end_date ? $invoice->end_date->format('Y-m-d') : null,
                            'amount' => (float) $invoice->amount,
                            'electricity_amount' => (float) $invoice->electricity_amount,
                            'water_amount' => (float) $invoice->water_amount,
                            'other_fees' => (float) ($invoice->other_fees ?? 0),
                            'status' => $invoice->status,
                            'due_date' => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : null,
                            'paid_at' => $invoice->paid_at ? $invoice->paid_at->format('Y-m-d H:i:s') : null,
                        ];
                    });
            }

            return [
                'id' => $room->id,
                'room_number' => $room->room_number,
                'floor' => $room->floor,
                'house' => [
                    'id' => $room->house->id,
                    'name' => $room->house->name,
                    'address' => $room->house->address,
                ],
                'active_booking' => $activeBooking ? [
                    'id' => $activeBooking->id,
                    'booking_code' => $activeBooking->booking_code,
                    'start_date' => $activeBooking->start_date->format('Y-m-d'),
                    'end_date' => $activeBooking->end_date->format('Y-m-d'),
                    'user' => [
                        'id' => $activeBooking->user->id,
                        'name' => $activeBooking->user->name,
                        'email' => $activeBooking->user->email,
                        'phone' => $activeBooking->user->phone,
                    ],
                ] : null,
                'tenant' => $room->tenant_id ? [
                    'id' => $room->tenant_id,
                    'name' => $room->tenant_name,
                    'rental_start_date' => $room->rental_start_date ? $room->rental_start_date->format('Y-m-d') : null,
                    'rental_end_date' => $room->rental_end_date ? $room->rental_end_date->format('Y-m-d') : null,
                ] : null,
                'has_tenant' => (bool) $room->tenant_id,
                'is_long_term' => $isLongTerm,
                'rental_days' => $rentalDays,
                'invoices' => $invoices,
            ];
        });

        // Get all houses for filter
        $houses = \App\Models\House::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/OccupiedRooms/Index', [
            'rooms' => $rooms,
            'filters' => $request->only(['search', 'house_id']),
            'houses' => $houses,
        ]);
    }

    /**
     * Create invoice for a booking or tenant.
     */
    public function createInvoice(Request $request, $roomId)
    {
        $validator = Validator::make($request->all(), [
            'booking_id' => 'nullable|exists:bookings,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'electricity_amount' => 'required|numeric|min:0',
            'water_amount' => 'required|numeric|min:0',
            'other_fees' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $room = Room::with('tenant')->findOrFail($roomId);
        $userId = null;
        $booking = null;
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        // If booking_id is provided, verify it belongs to room
        if ($request->booking_id) {
            $booking = Booking::findOrFail($request->booking_id);
            if ($booking->room_id != $roomId) {
                return back()->withErrors(['booking_id' => 'Đơn đặt phòng không thuộc về phòng này.'])->withInput();
            }
            $userId = $booking->user_id;
            
            // Verify date range is within booking period
            if ($startDate->lt($booking->start_date) || $endDate->gt($booking->end_date)) {
                return back()->withErrors(['start_date' => 'Khoảng thời gian tính hóa đơn phải nằm trong thời gian thuê phòng.'])->withInput();
            }
        } else {
            // No booking_id, must have tenant
            if (!$room->tenant_id) {
                return back()->withErrors(['booking_id' => 'Phòng này không có người thuê.'])->withInput();
            }
            $userId = $room->tenant_id;
            
            // Verify date range is within tenant rental period
            if ($room->rental_start_date && $room->rental_end_date) {
                if ($startDate->lt($room->rental_start_date) || $endDate->gt($room->rental_end_date)) {
                    return back()->withErrors(['start_date' => 'Khoảng thời gian tính hóa đơn phải nằm trong thời gian thuê phòng.'])->withInput();
                }
            }
        }

        // Check if invoice already exists with overlapping date range
        $query = Invoice::where('user_id', $userId)
            ->where(function ($q) use ($startDate, $endDate) {
                // Check for overlap: invoice overlaps if start_date <= end_date AND end_date >= start_date
                $q->where(function ($overlapQuery) use ($startDate, $endDate) {
                    $overlapQuery->where(function ($q1) use ($startDate, $endDate) {
                        $q1->whereNotNull('start_date')
                           ->whereNotNull('end_date')
                           ->where('start_date', '<=', $endDate)
                           ->where('end_date', '>=', $startDate);
                    });
                });
            });
        
        if ($booking) {
            $query->where('booking_id', $booking->id);
        } else {
            // For tenant invoices, check invoices with null booking_id for this user
            $query->whereNull('booking_id');
        }

        $existingInvoice = $query->first();

        if ($existingInvoice) {
            return back()->withErrors(['start_date' => 'Đã tồn tại hóa đơn trong khoảng thời gian này.'])->withInput();
        }

        // Calculate total amount
        $totalAmount = (float) $request->electricity_amount 
                     + (float) $request->water_amount 
                     + (float) ($request->other_fees ?? 0);

        // Create invoice
        $invoice = Invoice::create([
            'booking_id' => $request->booking_id,
            'user_id' => $userId,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'month' => $startDate->month, // Keep for backward compatibility
            'year' => $startDate->year, // Keep for backward compatibility
            'electricity_amount' => $request->electricity_amount,
            'water_amount' => $request->water_amount,
            'other_fees' => $request->other_fees ?? 0,
            'amount' => $totalAmount,
            'status' => 'pending',
            'due_date' => $request->due_date ? Carbon::parse($request->due_date) : Carbon::now()->addDays(7),
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Tạo hóa đơn thành công!');
    }
}
