<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Booking;
use App\Models\House;
use App\Models\Room;
use App\Models\User;
use App\Helpers\CodeGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy các đường từ database
        $streets = Address::where('type', 'street')->with('parent')->get();
        
        if ($streets->isEmpty()) {
            $this->command->warn('Không có địa chỉ đường nào trong database. Vui lòng chạy AddressSeeder trước.');
            return;
        }

        // Lấy hoặc tạo owner (manager)
        $owner = User::where('role', 'manager')->first();
        
        if (!$owner) {
            // Tạo owner mới nếu chưa có
            $owner = User::create([
                'name' => 'Quản lý hệ thống',
                'email' => 'manager@tlstay.com',
                'password' => bcrypt('password'),
                'role' => 'manager',
                'phone' => '0123456789',
            ]);
        }

        // Lấy danh sách customer để gán người thuê
        $customers = User::where('role', 'customer')->get();
        
        if ($customers->isEmpty()) {
            $this->command->warn('Không có customer nào trong database. Vui lòng chạy UserSeeder trước.');
            // Tạo một vài customer mẫu nếu chưa có
            for ($i = 0; $i < 20; $i++) {
                User::create([
                    'name' => 'Khách hàng ' . ($i + 1),
                    'email' => 'tenant' . ($i + 1) . '@tlstay.com',
                    'password' => bcrypt('123456'),
                    'role' => 'customer',
                    'phone' => '0' . rand(100000000, 999999999),
                ]);
            }
            $customers = User::where('role', 'customer')->get();
        }

        // Danh sách tên nhà trọ mẫu
        $houseNames = [
            'Nhà trọ An Bình', 'Nhà trọ Hạnh Phúc', 'Nhà trọ Thành Công',
            'Nhà trọ Minh Anh', 'Nhà trọ Thanh Bình', 'Nhà trọ Phúc Lộc',
            'Nhà trọ Đức Anh', 'Nhà trọ Quang Minh', 'Nhà trọ Hồng Phúc',
            'Nhà trọ Bình An', 'Nhà trọ Tân Phú', 'Nhà trọ Đông Anh',
            'Nhà trọ Tây Hồ', 'Nhà trọ Nam Sơn', 'Nhà trọ Bắc Giang',
            'Nhà trọ Trung Tâm', 'Nhà trọ Cổ Nhuế', 'Nhà trọ Mỹ Đình',
            'Nhà trọ Cầu Giấy', 'Nhà trọ Ba Đình', 'Nhà trọ Hoàn Kiếm',
            'Nhà trọ Hai Bà Trưng', 'Nhà trọ Đống Đa', 'Nhà trọ Tây Hồ',
            'Nhà trọ Cầu Giấy', 'Nhà trọ Thanh Xuân', 'Nhà trọ Hoàng Mai',
            'Nhà trọ Long Biên', 'Nhà trọ Nam Từ Liêm', 'Nhà trọ Bắc Từ Liêm',
            'Nhà trọ Mỹ Đức', 'Nhà trọ Ứng Hòa', 'Nhà trọ Phú Xuyên',
            'Nhà trọ Thường Tín', 'Nhà trọ Phúc Thọ', 'Nhà trọ Đan Phượng',
            'Nhà trọ Hoài Đức', 'Nhà trọ Quốc Oai', 'Nhà trọ Thạch Thất',
            'Nhà trọ Chương Mỹ', 'Nhà trọ Thanh Oai', 'Nhà trọ Mỹ Đức',
            'Nhà trọ Ứng Hòa', 'Nhà trọ Phú Xuyên', 'Nhà trọ Thường Tín',
            'Nhà trọ Phúc Thọ', 'Nhà trọ Đan Phượng', 'Nhà trọ Hoài Đức',
            'Nhà trọ Quốc Oai', 'Nhà trọ Thạch Thất', 'Nhà trọ Chương Mỹ',
            'Nhà trọ Thanh Oai', 'Nhà trọ Mỹ Đức', 'Nhà trọ Ứng Hòa',
        ];

        // Danh sách tiện nghi
        $amenitiesList = [
            ['WiFi', 'Điều hòa', 'Nóng lạnh', 'Tủ lạnh'],
            ['WiFi', 'Điều hòa', 'Nóng lạnh', 'Tủ lạnh', 'Máy giặt'],
            ['WiFi', 'Điều hòa', 'Nóng lạnh', 'Tủ lạnh', 'Máy giặt', 'Bếp'],
            ['WiFi', 'Điều hòa', 'Nóng lạnh'],
            ['WiFi', 'Điều hòa', 'Nóng lạnh', 'Tủ lạnh', 'Bếp'],
        ];

        // Tạo 50 nhà trọ
        for ($i = 0; $i < 50; $i++) {
            // Chọn ngẫu nhiên một đường
            $street = $streets->random();
            $ward = $street->parent;

            // Tạo số nhà ngẫu nhiên
            $streetDetail = 'Số ' . rand(1, 999) . ', Ngõ ' . rand(1, 50);

            // Xây dựng địa chỉ đầy đủ
            $addressParts = [$streetDetail, $street->name];
            if ($ward) {
                $addressParts[] = $ward->name;
            }
            $fullAddress = implode(', ', $addressParts);

            // Số phòng từ 10-15
            $totalRooms = rand(10, 15);
            
            // Số tầng từ 2-4 (tùy số phòng)
            $floors = min(4, max(2, ceil($totalRooms / 5)));

            // Giá cơ bản từ 500k - 2 triệu/ngày
            $basePrice = rand(500000, 2000000);

            // Tạo nhà trọ
            $house = House::create([
                'owner_id' => $owner->id,
                'name' => $houseNames[$i % count($houseNames)] . ' ' . ($i + 1),
                'address' => $fullAddress,
                'street_detail' => $streetDetail,
                'ward_id' => $ward ? $ward->id : null,
                'street_id' => $street->id,
                'description' => 'Nhà trọ sạch sẽ, thoáng mát, gần trường học và các tiện ích công cộng. Phù hợp cho sinh viên và người đi làm.',
                'amenities' => $amenitiesList[array_rand($amenitiesList)],
                'price_per_day' => $basePrice,
                'floors' => $floors,
                'total_rooms' => $totalRooms,
                'rating' => round(rand(35, 50) / 10, 1), // 3.5 - 5.0
                'reviews' => rand(5, 50),
                'latitude' => 21.0 + (rand(0, 1000) / 10000), // Khoảng Hà Nội
                'longitude' => 105.7 + (rand(0, 1000) / 10000),
                'contact_phone' => '0' . rand(100000000, 999999999),
                'contact_email' => 'house' . ($i + 1) . '@tlstay.com',
            ]);

            // Tạo các phòng và chia vào các tầng
            $roomsPerFloor = [];
            $remainingRooms = $totalRooms;
            
            // Phân bổ phòng vào các tầng
            for ($floor = 1; $floor <= $floors; $floor++) {
                if ($floor === $floors) {
                    // Tầng cuối cùng nhận tất cả phòng còn lại
                    $roomsPerFloor[$floor] = $remainingRooms;
                } else {
                    // Các tầng khác nhận 3-5 phòng
                    $roomsOnFloor = rand(3, min(5, $remainingRooms));
                    $roomsPerFloor[$floor] = $roomsOnFloor;
                    $remainingRooms -= $roomsOnFloor;
                }
            }

            // Tạo phòng cho mỗi tầng
            foreach ($roomsPerFloor as $floor => $roomCount) {
                for ($j = 1; $j <= $roomCount; $j++) {
                    // Tên phòng theo format: {tầng}{số thứ tự trong tầng}
                    // Ví dụ: Tầng 1 -> 101, 102, 103... | Tầng 2 -> 201, 202, 203...
                    $roomNumber = (string)($floor * 100 + $j);
                    
                    // Giá phòng có thể khác nhau ±20% so với giá cơ bản
                    $roomPrice = $basePrice * (1 + (rand(-20, 20) / 100));
                    
                    // Diện tích từ 15-30 m²
                    $area = rand(15, 30);

                    // Tiện nghi phòng (có thể khác với nhà)
                    $roomAmenities = [];
                    if (rand(0, 1)) {
                        $roomAmenities[] = 'Điều hòa';
                    }
                    if (rand(0, 1)) {
                        $roomAmenities[] = 'Nóng lạnh';
                    }
                    if (rand(0, 1)) {
                        $roomAmenities[] = 'Tủ lạnh';
                    }
                    if (empty($roomAmenities)) {
                        $roomAmenities = ['Nóng lạnh'];
                    }

                    $room = Room::create([
                        'house_id' => $house->id,
                        'room_number' => $roomNumber,
                        'floor' => $floor,
                        'price_per_day' => round($roomPrice, 2),
                        'status' => 'available', // Mặc định là available, sẽ cập nhật sau nếu có người thuê
                        'area' => $area,
                        'amenities' => $roomAmenities,
                    ]);

                    // 30% khả năng phòng có người thuê
                    if (rand(0, 10) < 3) {
                        $tenant = $customers->random();
                        
                        // Tạo ngày thuê: có thể đã bắt đầu hoặc sắp bắt đầu
                        $daysAgo = rand(0, 60); // Có thể đã thuê từ 0-60 ngày trước
                        $rentalStartDate = now()->subDays($daysAgo)->format('Y-m-d');
                        
                        // Thời gian thuê: từ 30-180 ngày
                        $rentalDays = rand(30, 180);
                        $rentalEndDate = now()->addDays($rentalDays)->format('Y-m-d');
                        
                        // Cập nhật thông tin người thuê vào phòng
                        $room->update([
                            'tenant_id' => $tenant->id,
                            'tenant_name' => $tenant->name,
                            'rental_start_date' => $rentalStartDate,
                            'rental_end_date' => $rentalEndDate,
                            'status' => 'active',
                        ]);

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

                        // 70% khả năng có hợp đồng đã ký
                        $hasContract = rand(0, 10) < 7;
                        
                        // Tạo booking
                        $booking = Booking::create([
                            'user_id' => $tenant->id,
                            'house_id' => $house->id,
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
                    }
                }
            }

            $this->command->info("Đã tạo nhà trọ: {$house->name} với {$totalRooms} phòng trên {$floors} tầng");
        }

        $this->command->info('Đã tạo thành công 50 nhà trọ với phòng và tầng!');
    }
}
