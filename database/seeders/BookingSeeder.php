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
     * Danh sách các bình luận tích cực về nhà trọ (4-5 sao)
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
     * Danh sách các bình luận trung bình về nhà trọ (3 sao)
     */
    private array $averageComments = [
        'Phòng ổn, giá cả hợp lý. Có một số điểm cần cải thiện nhưng nhìn chung chấp nhận được.',
        'Vị trí tốt, giá rẻ. Phòng hơi nhỏ và thiếu một số tiện nghi nhưng vẫn ổn.',
        'Phòng sạch sẽ, chủ nhà dễ tính. Wifi đôi khi chậm và nước nóng không ổn định.',
        'Giá cả phải chăng, gần trường. Phòng hơi cũ nhưng vẫn sử dụng được.',
        'Phòng đủ tiện nghi cơ bản. Một số thiết bị cần bảo trì nhưng không ảnh hưởng nhiều.',
        'Vị trí thuận tiện, giá hợp lý. Phòng hơi ồn vào buổi tối nhưng vẫn chấp nhận được.',
        'Chủ nhà nhiệt tình, phòng sạch sẽ. Một số tiện nghi cần nâng cấp nhưng nhìn chung ổn.',
        'Phòng đủ rộng, giá cả hợp lý. Điều hòa đôi khi không mát lắm nhưng vẫn dùng được.',
        'Khu vực yên tĩnh, phù hợp để học tập. Phòng hơi cũ nhưng vẫn ổn.',
        'Giá rẻ, gần các tiện ích. Phòng thiếu một số đồ dùng nhưng có thể tự mua thêm.',
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

            // === TẠO LỊCH SỬ THUÊ TRONG QUÁ KHỨ (7-12 tháng trước) ===
            // Mỗi nhà có 3-8 booking đã hoàn thành
            $pastBookingsToCreate = rand(3, 8);

            for ($i = 0; $i < $pastBookingsToCreate; $i++) {
                // Chọn customer
                $customer = $availableCustomers[$customerIndex % $availableCustomers->count()];
                $customerIndex++;

                // Chọn ngẫu nhiên một phòng
                $room = $rooms->random();

                // Tạo ngày thuê trong quá khứ (từ 7-12 tháng trước)
                $monthsAgo = rand(7, 12);
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

                // Đặt created_at trùng với thời gian ở (start_date) để tính doanh thu chính xác
                // created_at có thể là start_date hoặc trước đó 1-3 ngày (thời điểm đặt phòng)
                $createdAt = $startDate->copy()->subDays(rand(0, 3));
                // updated_at là sau khi kết thúc thuê
                $updatedAt = $endDate->copy()->addDays(rand(0, 2));

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
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ]);

                $pastBookingCount++;
            }

            // === TẠO LỊCH SỬ ĐẶT PHÒNG TRONG 6 THÁNG GẦN ĐÂY NHẤT ===
            // Mỗi nhà có 10-20 booking trong 6 tháng gần đây để có đủ dữ liệu cho biểu đồ
            $recentBookingsToCreate = rand(10, 20);
            $recentBookingCount = 0;

            for ($i = 0; $i < $recentBookingsToCreate; $i++) {
                // Chọn customer
                $customer = $availableCustomers[$customerIndex % $availableCustomers->count()];
                $customerIndex++;

                // Chọn ngẫu nhiên một phòng
                $room = $rooms->random();

                // Phân bổ đều trong 6 tháng gần đây (từ tháng hiện tại đến 5 tháng trước)
                $monthOffset = rand(0, 5); // 0 = tháng hiện tại, 5 = 5 tháng trước
                $targetMonth = $today->copy()->subMonths($monthOffset);

                // Chọn ngẫu nhiên một ngày trong tháng đó
                $daysInMonth = $targetMonth->daysInMonth;
                $dayInMonth = rand(1, $daysInMonth);

                // Tạo ngày kết thúc trong tháng đó
                $endDate = $targetMonth->copy()->day($dayInMonth);

                // Thời gian thuê từ 7-60 ngày
                $rentalDuration = rand(7, 60);
                $startDate = $endDate->copy()->subDays($rentalDuration);

                // Đảm bảo booking đã kết thúc (end_date < today)
                if ($endDate->gte($today)) {
                    $endDate = $today->copy()->subDays(rand(1, 7));
                    $startDate = $endDate->copy()->subDays($rentalDuration);
                }

                // Kiểm tra không trùng booking
                $existingBooking = Booking::where('room_id', $room->id)
                    ->where(function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('start_date', [$startDate, $endDate])
                            ->orWhereBetween('end_date', [$startDate, $endDate])
                            ->orWhere(function ($q) use ($startDate, $endDate) {
                                $q->where('start_date', '<=', $startDate)
                                    ->where('end_date', '>=', $endDate);
                            });
                    })
                    ->first();

                if ($existingBooking) {
                    continue;
                }

                // Tính giá tiền
                $totalPrice = round($room->price_per_day * $rentalDuration, 2);
                $paidAt = $startDate->copy()->subDays(rand(0, 3));

                // Đặt created_at trùng với thời gian ở (start_date) để tính doanh thu chính xác
                // created_at có thể là start_date hoặc trước đó 1-3 ngày (thời điểm đặt phòng)
                $createdAt = $startDate->copy()->subDays(rand(0, 3));
                // updated_at là sau khi kết thúc thuê
                $updatedAt = $endDate->copy()->addDays(rand(0, 2));

                // Tạo booking đã hoàn thành trong 6 tháng gần đây
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
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ]);

                $recentBookingCount++;
                $pastBookingCount++;
            }

            // === TẠO ĐÁNH GIÁ CHO NHÀ TRỌ ===
            // Mỗi nhà chỉ có 5-10 đánh giá từ các booking đã hoàn thành
            $reviewsToCreate = rand(5, 10);

            // Lấy tất cả booking đã hoàn thành của nhà này
            $completedBookings = Booking::where('house_id', $house->id)
                ->where('status', 'completed')
                ->where('booking_status', 'past')
                ->whereDoesntHave('review') // Chỉ lấy booking chưa có đánh giá
                ->get()
                ->shuffle()
                ->take($reviewsToCreate);

            foreach ($completedBookings as $booking) {
                // Đánh giá từ 3-5 sao
                $rating = rand(3, 5);

                // Chọn bình luận phù hợp với rating
                if ($rating >= 4) {
                    // 4-5 sao: dùng bình luận tích cực
                    $comment = $this->positiveComments[array_rand($this->positiveComments)];
                } else {
                    // 3 sao: dùng bình luận trung bình
                    $comment = $this->averageComments[array_rand($this->averageComments)];
                }

                // 30% có phản hồi từ quản lý (ưu tiên cho đánh giá 4-5 sao)
                $hasManagerResponse = ($rating >= 4 && rand(1, 100) <= 30) || ($rating === 3 && rand(1, 100) <= 20);
                $managerResponses = [
                    'Cảm ơn bạn đã đánh giá! Rất vui vì bạn hài lòng với dịch vụ của chúng tôi.',
                    'Cảm ơn bạn nhiều! Hẹn gặp lại bạn lần sau nhé!',
                    'Cảm ơn bạn đã ở với chúng tôi. Chúc bạn mọi điều tốt đẹp!',
                    'Rất vui khi được phục vụ bạn. Hy vọng sẽ gặp lại bạn sớm!',
                    'Cảm ơn những lời khen của bạn! Chúng tôi sẽ tiếp tục cố gắng.',
                    'Cảm ơn bạn đã phản hồi. Chúng tôi sẽ cải thiện dịch vụ dựa trên góp ý của bạn.',
                ];

                Review::create([
                    'booking_id' => $booking->id,
                    'user_id' => $booking->user_id,
                    'house_id' => $house->id,
                    'rating' => $rating,
                    'comment' => $comment,
                    'images' => null,
                    'manager_response' => $hasManagerResponse ? $managerResponses[array_rand($managerResponses)] : null,
                    'manager_response_at' => $hasManagerResponse ? Carbon::parse($booking->end_date)->addDays(rand(1, 5)) : null,
                ]);

                $reviewCount++;
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
        $this->command->info("Đã tạo booking trong 6 tháng gần đây để hiển thị biểu đồ thống kê");
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
