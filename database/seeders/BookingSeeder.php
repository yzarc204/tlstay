<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use App\Helpers\CodeGenerator;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy danh sách customer, loại trừ customer@tlstay.com và manager@tlstay.com
        $customers = User::where('role', 'customer')
            ->whereNotIn('email', ['customer@tlstay.com', 'manager@tlstay.com'])
            ->get();
        
        if ($customers->isEmpty()) {
            $this->command->warn('Không có customer nào để tạo booking. Vui lòng chạy UserSeeder trước.');
            return;
        }

        // Lấy tất cả các phòng đã có tenant (đã được gán trong HouseSeeder)
        $roomsWithTenants = Room::whereNotNull('tenant_id')
            ->where('status', 'active')
            ->with(['house', 'tenant'])
            ->get();

        if ($roomsWithTenants->isEmpty()) {
            $this->command->warn('Không có phòng nào có người thuê. Vui lòng chạy HouseSeeder trước.');
            return;
        }

        $bookingCount = 0;

        foreach ($roomsWithTenants as $room) {
            // Đảm bảo tenant không phải là customer@tlstay.com hoặc manager@tlstay.com
            $tenant = $room->tenant;
            
            if (!$tenant || in_array($tenant->email, ['customer@tlstay.com', 'manager@tlstay.com'])) {
                // Nếu tenant là customer@tlstay.com hoặc manager@tlstay.com, 
                // chọn một customer khác ngẫu nhiên
                $tenant = $customers->random();
                
                // Cập nhật lại tenant cho phòng
                $room->update([
                    'tenant_id' => $tenant->id,
                    'tenant_name' => $tenant->name,
                ]);
            }

            // Lấy thông tin từ phòng
            $rentalStartDate = $room->rental_start_date;
            $rentalEndDate = $room->rental_end_date;
            $roomPrice = $room->price_per_day;

            // Tính tổng giá tiền
            $days = (strtotime($rentalEndDate) - strtotime($rentalStartDate)) / (60 * 60 * 24);
            $totalPrice = round($roomPrice * $days, 2);
            
            // Xác định booking_status dựa trên ngày
            $bookingStatus = 'active';
            if (strtotime($rentalEndDate) < strtotime('today')) {
                $bookingStatus = 'past';
            } elseif (strtotime($rentalStartDate) > strtotime('today')) {
                $bookingStatus = 'upcoming';
            }

            // Tính số ngày đã thuê
            $daysAgo = max(0, (strtotime('today') - strtotime($rentalStartDate)) / (60 * 60 * 24));

            // 70% khả năng có hợp đồng đã ký
            $hasContract = rand(0, 10) < 7;
            
            // Kiểm tra xem đã có booking cho phòng này chưa
            $existingBooking = Booking::where('room_id', $room->id)
                ->where('start_date', $rentalStartDate)
                ->where('end_date', $rentalEndDate)
                ->first();

            if (!$existingBooking) {
                // Tạo booking
                Booking::create([
                    'user_id' => $tenant->id,
                    'house_id' => $room->house_id,
                    'room_id' => $room->id,
                    'start_date' => $rentalStartDate,
                    'end_date' => $rentalEndDate,
                    'status' => $bookingStatus === 'past' ? 'completed' : 'active',
                    'booking_status' => $bookingStatus,
                    'total_price' => $totalPrice,
                    'discount_amount' => 0,
                    'tenant_name' => $tenant->name,
                    'payment_status' => 'paid', // Đã thanh toán vì đang ở
                    'payment_method' => 'vnpay',
                    'paid_at' => now()->subDays($daysAgo),
                    'contract_signed' => $hasContract,
                    'signed_at' => $hasContract ? now()->subDays($daysAgo) : null,
                    'user_signature' => $hasContract ? 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==' : null, // Base64 placeholder
                    'booking_code' => CodeGenerator::generateBookingCode(),
                ]);
                $bookingCount++;
            }
        }

        $this->command->info("Đã tạo thành công {$bookingCount} booking!");
    }
}
