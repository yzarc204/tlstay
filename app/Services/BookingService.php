<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\House;
use App\Models\Room;
use App\Models\User;
use App\Helpers\CodeGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingService
{
    protected $roomAvailabilityService;

    public function __construct(RoomAvailabilityService $roomAvailabilityService)
    {
        $this->roomAvailabilityService = $roomAvailabilityService;
    }

    /**
     * Create a new booking
     *
     * @param User $user
     * @param House $house
     * @param Room $room
     * @param string $startDate
     * @param string $endDate
     * @param float $totalPrice
     * @param float $discountAmount
     * @param string|null $notes
     * @return Booking
     * @throws \Exception
     */
    public function createBooking(
        User $user,
        House $house,
        Room $room,
        string $startDate,
        string $endDate,
        float $totalPrice,
        float $discountAmount = 0,
        ?string $notes = null
    ): Booking {
        // Validate room belongs to house
        if ($room->house_id != $house->id) {
            throw new \Exception('Phòng không thuộc nhà trọ này');
        }

        // Use database transaction to ensure atomicity
        try {
            return DB::transaction(function () use ($user, $house, $room, $startDate, $endDate, $totalPrice, $discountAmount, $notes) {
                // Lock room to prevent race conditions
                $lockedRoom = Room::lockForUpdate()->findOrFail($room->id);
                
                // Check room availability
                if (!$this->roomAvailabilityService->isAvailableForDates($lockedRoom, $startDate, $endDate)) {
                    throw new \Exception('Phòng này đã được đặt hoặc đang có người ở trong khoảng thời gian bạn chọn. Vui lòng chọn khoảng thời gian khác.');
                }
                
                // Generate unique booking code
                $bookingCode = CodeGenerator::generateBookingCode();
                
                // Create booking with booking code
                $booking = Booking::create([
                    'user_id' => $user->id,
                    'house_id' => $house->id,
                    'room_id' => $room->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'total_price' => $totalPrice,
                    'discount_amount' => $discountAmount,
                    'tenant_name' => $user->name,
                    'notes' => $notes,
                    'status' => 'active',
                    'payment_status' => 'pending',
                    'booking_code' => $bookingCode,
                ]);
                
                return $booking;
            });
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error in BookingService::createBooking', [
                'error' => $e->getMessage(),
                'sql_state' => $e->errorInfo[0] ?? null,
                'error_code' => $e->errorInfo[1] ?? null,
            ]);
            throw $e;
        }
    }


    /**
     * Validate booking data before creation
     *
     * @param User $user
     * @param array $data
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validateBookingData(User $user, array $data): array
    {
        $errors = [];

        // Check personal info completeness
        if (!$user->hasCompletePersonalInfo()) {
            $errors['personal_info'] = 'Vui lòng cập nhật đầy đủ thông tin cá nhân (Căn cước công dân, Ngày cấp CCCD, Nơi cấp CCCD, Địa chỉ thường trú, Ngày sinh, Giới tính) trước khi đặt phòng.';
        }

        // Validate dates
        if (isset($data['start_date']) && isset($data['end_date'])) {
            $startDate = \Carbon\Carbon::parse($data['start_date']);
            $endDate = \Carbon\Carbon::parse($data['end_date']);
            $today = \Carbon\Carbon::today();

            if ($startDate->lt($today)) {
                $errors['start_date'] = 'Ngày bắt đầu phải từ hôm nay trở đi.';
            }

            if ($endDate->lte($startDate)) {
                $errors['end_date'] = 'Ngày kết thúc phải sau ngày bắt đầu.';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
