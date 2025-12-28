<?php

namespace App\Http\Controllers;

use App\Models\House;
use App\Models\Room;
use App\Models\Booking;
use App\Services\BookingService;
use App\Services\RoomAvailabilityService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    public function show(Request $request, $id)
    {
        $house = House::with(['owner', 'rooms.bookings'])
            ->withCount([
                'rooms as total_rooms_count',
            ])
            ->findOrFail($id);

        // Get selected dates from request (if user has selected dates)
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        // Use RoomAvailabilityService to check room status
        $roomAvailabilityService = app(RoomAvailabilityService::class);

        $rooms = $house->rooms()
            ->with('bookings') // Load bookings for status calculation
            ->orderBy('floor')
            ->orderBy('room_number')
            ->get()
            ->map(function ($room) use ($startDate, $endDate, $roomAvailabilityService) {
                // If dates are provided, check status for that date range
                // Otherwise, default to 'available' (all rooms show as empty)
                if ($startDate && $endDate) {
                    $status = $roomAvailabilityService->getStatusForDates($room, $startDate, $endDate);
                } else {
                    // Mặc định tất cả phòng ở trạng thái trống khi không có ngày
                    $status = 'available';
                }

                return [
                    'id' => $room->id,
                    'roomNumber' => $room->room_number,
                    'floor' => $room->floor,
                    'pricePerDay' => (float) $room->price_per_day,
                    'pricePerWeek' => $room->price_per_week ? (float) $room->price_per_week : null,
                    'pricePerMonth' => $room->price_per_month ? (float) $room->price_per_month : null,
                    'area' => (float) ($room->area ?? 0),
                    'status' => $status,
                    'amenities' => $room->amenities ?? [],
                    'images' => $room->images ?? [],
                ];
            })
            ->values();

        // Calculate available rooms count based on status (only 'available' status)
        $availableRoomsCount = $rooms->filter(function ($room) {
            return $room['status'] === 'available';
        })->count();

        // Calculate rooms by status for display (only available and active)
        $roomsByStatus = [
            'available' => $rooms->filter(fn($r) => $r['status'] === 'available')->count(),
            'active' => $rooms->filter(fn($r) => $r['status'] === 'active')->count(),
        ];

        $houseData = [
            'id' => $house->id,
            'name' => $house->name,
            'address' => $house->address,
            'description' => $house->description,
            'image' => $house->image ?? (is_array($house->images) && count($house->images) > 0 ? $house->images[0] : 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800'),
            'images' => $house->images ?? [],
            'pricePerDay' => (float) $house->price_per_day,
            'floors' => $house->floors,
            'totalRooms' => $house->total_rooms_count ?? $house->total_rooms ?? 0,
            'availableRooms' => $availableRoomsCount,
            'rating' => (float) ($house->rating ?? 0),
            'reviews' => $house->reviews ?? 0,
            'amenities' => $house->amenities ?? [],
        ];

        return Inertia::render('BookingRoom', [
            'house' => $houseData,
            'rooms' => $rooms,
        ]);
    }

    public function store(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'house_id' => 'required|exists:houses,id',
            'room_id' => 'required|exists:rooms,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'total_price' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check authentication
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để đặt phòng');
        }

        // Load models
        $house = House::findOrFail($request->house_id);
        $room = Room::findOrFail($request->room_id);

        // Use BookingService to handle booking creation
        $bookingService = app(BookingService::class);

        // Validate booking data
        $validation = $bookingService->validateBookingData($user, $request->all());
        if (!$validation['valid']) {
            return back()->withErrors($validation['errors'])->withInput();
        }

        // Calculate price breakdown
        $breakdown = $this->calculatePriceBreakdown($room, $request->start_date, $request->end_date);

        // Create booking using service
        try {
            $booking = $bookingService->createBooking(
                $user,
                $house,
                $room,
                $request->start_date,
                $request->end_date,
                $request->total_price,
                $request->discount_amount ?? 0,
                $request->notes,
                $breakdown
            );
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error creating booking', [
                'user_id' => $user->id,
                'room_id' => $request->room_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'error' => $e->getMessage(),
                'sql_state' => $e->errorInfo[0] ?? null,
                'error_code' => $e->errorInfo[1] ?? null,
                'trace' => $e->getTraceAsString(),
            ]);

            // Handle duplicate entry error (SQLSTATE 23000)
            if (isset($e->errorInfo[0]) && $e->errorInfo[0] == '23000') {
                return back()->withErrors([
                    'message' => 'Có lỗi xảy ra khi tạo đơn đặt phòng. Vui lòng thử lại.'
                ])->withInput();
            }

            return back()->withErrors([
                'message' => 'Có lỗi xảy ra khi tạo đơn đặt phòng. Vui lòng thử lại.'
            ])->withInput();

        } catch (\Exception $e) {
            \Log::error('Error creating booking', [
                'user_id' => $user->id,
                'room_id' => $request->room_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withErrors([
                'room_id' => $e->getMessage()
            ])->withInput();
        }

        // Success - redirect to payment page
        // Flow: Chọn phòng → Đặt phòng → Thanh toán → Ký hợp đồng
        if ($request->header('X-Inertia')) {
            return Inertia::location(route('payment.create', ['bookingId' => $booking->id]));
        }

        return redirect()->route('payment.create', ['bookingId' => $booking->id])
            ->with('success', 'Đặt phòng thành công! Vui lòng thanh toán để hoàn tất.');
    }

    public function success(Request $request, $id)
    {
        $booking = Booking::with(['house', 'room', 'user'])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Only show success page if payment is completed
        if ($booking->payment_status !== 'paid') {
            return redirect()->route('history.index')
                ->with('error', 'Đơn đặt phòng này chưa được thanh toán.');
        }

        // Get invoices for this booking
        $invoices = $booking->invoices()->orderBy('start_date', 'desc')->orderBy('end_date', 'desc')->get()->map(function ($invoice) {
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
                'notes' => $invoice->notes,
            ];
        });

        $bookingData = [
            'id' => $booking->id,
            'booking_code' => $booking->booking_code,
            'house_name' => $booking->house->name,
            'house_address' => $booking->house->address,
            'room_number' => $booking->room->room_number,
            'floor' => $booking->room->floor,
            'start_date' => $booking->start_date->format('Y-m-d'),
            'end_date' => $booking->end_date->format('Y-m-d'),
            'status' => $booking->status,
            'booking_status' => $booking->booking_status,
            'total_price' => (float) $booking->total_price,
            'discount_amount' => (float) $booking->discount_amount,
            'payment_method' => $booking->payment_method,
            'paid_at' => $booking->paid_at ? $booking->paid_at->format('Y-m-d H:i:s') : null,
            'vnpay_transaction_id' => $booking->vnpay_transaction_id,
            'notes' => $booking->notes,
            'contract_signed' => $booking->contract_signed ?? false,
            'signed_at' => $booking->signed_at ? $booking->signed_at->format('Y-m-d H:i:s') : null,
            'invoices' => $invoices,
            // Price breakdown data
            'full_months' => $booking->full_months ?? null,
            'full_weeks' => $booking->full_weeks ?? null,
            'remaining_days' => $booking->remaining_days ?? null,
            'months_price' => $booking->months_price ? (float) $booking->months_price : null,
            'weeks_price' => $booking->weeks_price ? (float) $booking->weeks_price : null,
            'remaining_price' => $booking->remaining_price ? (float) $booking->remaining_price : null,
            // Room data for fallback calculation
            'room' => [
                'price_per_day' => $booking->room->price_per_day ? (float) $booking->room->price_per_day : null,
                'price_per_week' => $booking->room->price_per_week ? (float) $booking->room->price_per_week : null,
                'price_per_month' => $booking->room->price_per_month ? (float) $booking->room->price_per_month : null,
            ],
        ];

        return Inertia::render('BookingSuccess', [
            'booking' => $bookingData,
        ]);
    }

    /**
     * Calculate price breakdown for booking
     */
    private function calculatePriceBreakdown($room, $startDate, $endDate)
    {
        $start = new \DateTime($startDate);
        $end = new \DateTime($endDate);
        $diff = $start->diff($end);
        $days = $diff->days + 1; // Include both start and end date

        $pricePerDay = $room->price_per_day ?? 0;
        $pricePerWeek = $room->price_per_week ?? null;
        $pricePerMonth = $room->price_per_month ?? null;

        // Calculate breakdown: months -> weeks -> days
        $remaining = $days;
        $fullMonths = floor($remaining / 30);
        $remaining = $remaining % 30;
        $fullWeeks = floor($remaining / 7);
        $remainingDays = $remaining % 7;

        // Calculate prices
        $monthsPrice = 0;
        if ($fullMonths > 0) {
            if ($pricePerMonth !== null && $pricePerMonth > 0) {
                $monthsPrice = $fullMonths * $pricePerMonth;
            } else {
                $monthsPrice = $fullMonths * $pricePerDay * 30;
            }
        }

        $weeksPrice = 0;
        if ($fullWeeks > 0) {
            if ($pricePerWeek !== null && $pricePerWeek > 0) {
                $weeksPrice = $fullWeeks * $pricePerWeek;
            } else {
                $weeksPrice = $fullWeeks * $pricePerDay * 7;
            }
        }

        $remainingPrice = $remainingDays * $pricePerDay;

        return [
            'full_months' => $fullMonths,
            'full_weeks' => $fullWeeks,
            'remaining_days' => $remainingDays,
            'month_unit_price' => $pricePerMonth,
            'week_unit_price' => $pricePerWeek,
            'day_unit_price' => $pricePerDay,
            'months_price' => round($monthsPrice, 2),
            'weeks_price' => round($weeksPrice, 2),
            'remaining_price' => round($remainingPrice, 2),
        ];
    }
}
