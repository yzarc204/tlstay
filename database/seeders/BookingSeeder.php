<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\House;
use App\Models\Review;
use App\Models\Room;
use App\Models\User;
use App\Helpers\CodeGenerator;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Danh sách các bình luận tích cực về nhà trọ
     */
    private array $positiveComments = [
        'Phòng rất sạch sẽ và thoáng mát. Chủ nhà rất thân thiện!',
        'Vị trí rất thuận tiện, gần trường học và chợ. Giá cả hợp lý.',
        'Phòng đầy đủ tiện nghi, wifi mạnh. Rất hài lòng!',
        'An ninh tốt, bãi đỗ xe rộng rãi. Sẽ giới thiệu cho bạn bè.',
        'Chủ nhà nhiệt tình, hỗ trợ nhanh khi có vấn đề. Rất tuyệt vời!',
        'Phòng mới, sạch sẽ, điều hòa mát. Giá cả phải chăng.',
        'Khu vực yên tĩnh, phù hợp để học tập và nghỉ ngơi.',
        'Tiện nghi đầy đủ, phòng rộng rãi. Rất đáng đồng tiền!',
        'Gần siêu thị và các tiện ích. Di chuyển rất thuận tiện.',
        'Phòng đẹp, view đẹp. Chủ nhà dễ tính và hỗ trợ nhiệt tình.',
        'Điện nước giá dân, không lo bị chặt chém. Rất hài lòng!',
        'Phòng có ban công thoáng mát, ánh sáng tự nhiên tốt.',
        'Bếp nấu ăn tiện nghi, phù hợp cho sinh viên tự nấu ăn.',
        'An ninh 24/7, cổng vào bằng vân tay rất an toàn.',
        'Phòng cách âm tốt, không bị ồn từ bên ngoài.',
        'Chỗ để xe rộng, không lo mất xe. Rất yên tâm!',
        'Wifi siêu nhanh, chơi game và học online đều tốt.',
        'Phòng tắm riêng, nước nóng lạnh đầy đủ. Rất tiện nghi.',
        'Khu vực nhiều quán ăn ngon, giá sinh viên. Rất thuận tiện.',
        'Phòng được dọn dẹp thường xuyên. Rất sạch sẽ và gọn gàng.',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $today = Carbon::today();

        // Lấy danh sách customer, loại trừ customer@tlstay.com và manager@tlstay.com
        $customers = User::where('role', 'customer')
            ->whereNotIn('email', ['customer@tlstay.com', 'manager@tlstay.com'])
            ->get();

        if ($customers->isEmpty()) {
            $this->command->warn('Không có customer nào để tạo booking. Vui lòng chạy UserSeeder trước.');
            return;
        }

        // Lấy tất cả các nhà với phòng
        $houses = House::with('rooms')->get();

        if ($houses->isEmpty()) {
            $this->command->warn('Không có nhà nào để tạo booking. Vui lòng chạy HouseSeeder trước.');
            return;
        }

        $activeBookingCount = 0;
        $upcomingBookingCount = 0;
        $pastBookingCount = 0;
        $reviewCount = 0;
        $roomsUpdated = 0;

        $availableCustomers = $customers->shuffle();
        $customerIndex = 0;

        foreach ($houses as $house) {
            $rooms = $house->rooms;

            if ($rooms->isEmpty()) {
                continue;
            }

            // === TẠO BOOKING ĐANG Ở VÀ SẮP TỚI ===
            // Mỗi nhà có 20-40% phòng đang có người ở hoặc sắp có người
            $currentBookingsCount = (int) ceil($rooms->count() * (rand(20, 40) / 100));
            $usedRoomIds = [];

            for ($i = 0; $i < $currentBookingsCount; $i++) {
                // Chọn phòng chưa được sử dụng
                $availableRooms = $rooms->filter(fn($r) => !in_array($r->id, $usedRoomIds));
                if ($availableRooms->isEmpty()) {
                    break;
                }

                $room = $availableRooms->random();
                $usedRoomIds[] = $room->id;

                // Chọn customer
                $customer = $availableCustomers[$customerIndex % $availableCustomers->count()];
                $customerIndex++;

                // 70% là đang ở, 30% là sắp tới
                $isActive = rand(1, 100) <= 70;

                if ($isActive) {
                    // Booking đang ở: start_date <= today <= end_date
                    $daysAgo = rand(1, 60); // Đã bắt đầu từ 1-60 ngày trước
                    $startDate = $today->copy()->subDays($daysAgo);
                    $rentalDuration = rand(30, 180); // Thuê từ 1-6 tháng
                    $endDate = $startDate->copy()->addDays($rentalDuration);

                    // Đảm bảo end_date > today
                    if ($endDate->lte($today)) {
                        $endDate = $today->copy()->addDays(rand(10, 90));
                    }

                    $bookingStatus = 'active';
                    $status = 'active';
                } else {
                    // Booking sắp tới: start_date > today
                    $daysLater = rand(1, 30); // Bắt đầu trong 1-30 ngày tới
                    $startDate = $today->copy()->addDays($daysLater);
                    $rentalDuration = rand(30, 180);
                    $endDate = $startDate->copy()->addDays($rentalDuration);

                    $bookingStatus = 'upcoming';
                    $status = 'active';
                }

                // Tính giá tiền
                $days = $startDate->diffInDays($endDate);
                $totalPrice = round($room->price_per_day * $days, 2);

                // 70% khả năng có hợp đồng đã ký
                $hasContract = rand(0, 10) < 7;
                $paidAt = $startDate->copy()->subDays(rand(1, 7));

                // Tạo booking
                $booking = Booking::create([
                    'user_id' => $customer->id,
                    'house_id' => $house->id,
                    'room_id' => $room->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => $status,
                    'booking_status' => $bookingStatus,
                    'total_price' => $totalPrice,
                    'discount_amount' => 0,
                    'tenant_name' => $customer->name,
                    'payment_status' => 'paid',
                    'payment_method' => 'vnpay',
                    'paid_at' => $paidAt,
                    'contract_signed' => $hasContract,
                    'signed_at' => $hasContract ? $paidAt : null,
                    'user_signature' => $hasContract ? 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==' : null,
                    'booking_code' => CodeGenerator::generateBookingCode(),
                ]);

                if ($bookingStatus === 'active') {
                    $activeBookingCount++;

                    // === CẬP NHẬT THÔNG TIN NGƯỜI THUÊ VÀO PHÒNG ===
                    // Chỉ cập nhật cho booking đang ở (start_date <= today <= end_date)
                    $room->update([
                        'status' => 'active',
                        'tenant_id' => $customer->id,
                        'tenant_name' => $customer->name,
                        'rental_start_date' => $startDate,
                        'rental_end_date' => $endDate,
                    ]);
                    $roomsUpdated++;
                } else {
                    $upcomingBookingCount++;
                }
            }

            // === TẠO LỊCH SỬ THUÊ TRONG QUÁ KHỨ ===
            // Mỗi nhà có 3-8 booking đã hoàn thành
            $pastBookingsToCreate = rand(3, 8);

            for ($i = 0; $i < $pastBookingsToCreate; $i++) {
                // Chọn customer
                $customer = $availableCustomers[$customerIndex % $availableCustomers->count()];
                $customerIndex++;

                // Chọn ngẫu nhiên một phòng
                $room = $rooms->random();

                // Tạo ngày thuê trong quá khứ (từ 1-12 tháng trước)
                $monthsAgo = rand(1, 12);
                $rentalDuration = rand(7, 90); // Thuê từ 1 tuần đến 3 tháng

                $endDate = $today->copy()->subMonths($monthsAgo)->subDays(rand(1, 28));
                $startDate = $endDate->copy()->subDays($rentalDuration);

                // Kiểm tra không trùng booking
                $existingBooking = Booking::where('room_id', $room->id)
                    ->where('user_id', $customer->id)
                    ->where(function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('start_date', [$startDate, $endDate])
                            ->orWhereBetween('end_date', [$startDate, $endDate]);
                    })
                    ->first();

                if ($existingBooking) {
                    continue;
                }

                // Tính giá tiền
                $totalPrice = round($room->price_per_day * $rentalDuration, 2);
                $paidAt = $startDate->copy()->subDays(rand(0, 3));

                // Tạo booking đã hoàn thành
                $booking = Booking::create([
                    'user_id' => $customer->id,
                    'house_id' => $house->id,
                    'room_id' => $room->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => 'completed',
                    'booking_status' => 'past',
                    'total_price' => $totalPrice,
                    'discount_amount' => 0,
                    'tenant_name' => $customer->name,
                    'payment_status' => 'paid',
                    'payment_method' => rand(0, 1) ? 'vnpay' : 'cash',
                    'paid_at' => $paidAt,
                    'contract_signed' => true,
                    'signed_at' => $paidAt,
                    'user_signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
                    'booking_code' => CodeGenerator::generateBookingCode(),
                ]);

                $pastBookingCount++;

                // === TẠO ĐÁNH GIÁ CHO BOOKING NÀY ===
                // 80% người thuê sẽ để lại đánh giá
                if (rand(1, 100) <= 80) {
                    // Đánh giá từ 4-5 sao
                    $rating = rand(4, 5);

                    // Chọn ngẫu nhiên bình luận
                    $comment = $this->positiveComments[array_rand($this->positiveComments)];

                    // 30% có phản hồi từ quản lý
                    $hasManagerResponse = rand(1, 100) <= 30;
                    $managerResponses = [
                        'Cảm ơn bạn đã đánh giá! Rất vui vì bạn hài lòng với dịch vụ của chúng tôi.',
                        'Cảm ơn bạn nhiều! Hẹn gặp lại bạn lần sau nhé!',
                        'Cảm ơn bạn đã ở với chúng tôi. Chúc bạn mọi điều tốt đẹp!',
                        'Rất vui khi được phục vụ bạn. Hy vọng sẽ gặp lại bạn sớm!',
                        'Cảm ơn những lời khen của bạn! Chúng tôi sẽ tiếp tục cố gắng.',
                    ];

                    Review::create([
                        'booking_id' => $booking->id,
                        'user_id' => $customer->id,
                        'house_id' => $house->id,
                        'rating' => $rating,
                        'comment' => $comment,
                        'images' => null,
                        'manager_response' => $hasManagerResponse ? $managerResponses[array_rand($managerResponses)] : null,
                        'manager_response_at' => $hasManagerResponse ? $endDate->copy()->addDays(rand(1, 5)) : null,
                    ]);

                    $reviewCount++;
                }
            }
        }

        // Cập nhật rating trung bình cho mỗi nhà
        $this->updateHouseRatings();

        $this->command->info("=== KẾT QUẢ TẠO BOOKING ===");
        $this->command->info("Booking đang ở (active): {$activeBookingCount}");
        $this->command->info("Booking sắp tới (upcoming): {$upcomingBookingCount}");
        $this->command->info("Lịch sử thuê (past): {$pastBookingCount}");
        $this->command->info("Đánh giá: {$reviewCount}");
        $this->command->info("Phòng đã cập nhật người thuê: {$roomsUpdated}");
    }

    /**
     * Cập nhật rating trung bình cho các nhà
     */
    private function updateHouseRatings(): void
    {
        $houses = House::all();

        foreach ($houses as $house) {
            $reviews = Review::where('house_id', $house->id)->get();

            if ($reviews->count() > 0) {
                $averageRating = round($reviews->avg('rating'), 2);
                $house->update([
                    'rating' => $averageRating,
                    'reviews' => $reviews->count(),
                ]);
            }
        }
    }
}
