<?php

namespace App\Http\Controllers;

use App\Models\House;
use App\Models\Room;
use App\Models\Booking;
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

        $rooms = $house->rooms()
            ->with('bookings') // Load bookings for status calculation
            ->orderBy('floor')
            ->orderBy('room_number')
            ->get()
            ->map(function ($room) use ($startDate, $endDate) {
                // If dates are provided, check status for that date range
                // Otherwise, use current effective status
                if ($startDate && $endDate) {
                    $status = $room->getStatusForDates($startDate, $endDate);
                } else {
                    $status = $room->getEffectiveStatus();
                }
                
                return [
                    'id' => $room->id,
                    'roomNumber' => $room->room_number,
                    'floor' => $room->floor,
                    'pricePerDay' => (float) $room->price_per_day,
                    'area' => (float) ($room->area ?? 0),
                    'status' => $status,
                    'amenities' => $room->amenities ?? [],
                    'images' => $room->images ?? [],
                ];
            })
            ->values();
        
        // Calculate available rooms count based on status
        $availableRoomsCount = $rooms->filter(function ($room) {
            return $room['status'] === 'available';
        })->count();

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

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để đặt phòng');
        }

        // Kiểm tra thông tin cá nhân đầy đủ
        if (!$user->hasCompletePersonalInfo()) {
            return back()->withErrors([
                'personal_info' => 'Vui lòng cập nhật đầy đủ thông tin cá nhân (Căn cước công dân, Ngày cấp CCCD, Nơi cấp CCCD, Địa chỉ thường trú, Ngày sinh, Giới tính) trước khi đặt phòng.'
            ])->withInput();
        }

        // Check if room belongs to the house
        $room = Room::findOrFail($request->room_id);
        if ($room->house_id != $request->house_id) {
            return back()->withErrors(['room_id' => 'Phòng không thuộc nhà trọ này'])->withInput();
        }

        // Check if room is available for the requested dates
        // Room is available if:
        // 1. No paid bookings overlap with the requested date range
        // 2. No tenant is currently staying during the requested date range
        if (!$room->isAvailableForDates($request->start_date, $request->end_date)) {
            return back()->withErrors([
                'room_id' => 'Phòng này đã được đặt hoặc đang có người ở trong khoảng thời gian bạn chọn. Vui lòng chọn khoảng thời gian khác.'
            ])->withInput();
        }

        // Load house to generate booking code
        $house = \App\Models\House::findOrFail($request->house_id);
        
        // Generate booking code
        $bookingCode = Booking::generateBookingCode($house, $user->id);
        
        // Create booking with pending payment
        $booking = Booking::create([
            'user_id' => $user->id,
            'house_id' => $request->house_id,
            'room_id' => $request->room_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_price' => $request->total_price,
            'discount_amount' => $request->discount_amount ?? 0,
            'tenant_name' => $user->name,
            'notes' => $request->notes,
            'status' => 'active',
            'payment_status' => 'pending',
            'booking_code' => $bookingCode,
        ]);

        // Don't update room status yet - wait for payment confirmation
        // Room will be updated after successful payment

        // Redirect to payment page
        return redirect()->route('payment.create', ['bookingId' => $booking->id])
            ->with('info', 'Vui lòng thanh toán để hoàn tất đặt phòng.');
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
        $invoices = $booking->invoices()->orderBy('year')->orderBy('month')->get()->map(function ($invoice) {
            return [
                'id' => $invoice->id,
                'month' => $invoice->month,
                'year' => $invoice->year,
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
            'total_price' => (float) $booking->total_price,
            'discount_amount' => (float) $booking->discount_amount,
            'payment_method' => $booking->payment_method,
            'paid_at' => $booking->paid_at ? $booking->paid_at->format('Y-m-d H:i:s') : null,
            'vnpay_transaction_id' => $booking->vnpay_transaction_id,
            'notes' => $booking->notes,
            'contract_signed' => $booking->contract_signed ?? false,
            'signed_at' => $booking->signed_at ? $booking->signed_at->format('Y-m-d H:i:s') : null,
            'invoices' => $invoices,
        ];

        return Inertia::render('BookingSuccess', [
            'booking' => $bookingData,
        ]);
    }
}
