<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\House;
use App\Models\Room;
use App\Models\User;
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

        // Lấy danh sách customer để gán người thuê, loại trừ customer@tlstay.com và manager@tlstay.com
        $customers = User::where('role', 'customer')
            ->whereNotIn('email', ['customer@tlstay.com', 'manager@tlstay.com'])
            ->get();

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
            $houseNumber = rand(1, 999);
            $alleyNumber = rand(1, 50);
            $streetDetail = 'Số ' . $houseNumber . ', Ngõ ' . $alleyNumber;

            // Xây dựng địa chỉ đầy đủ
            $addressParts = [$streetDetail, $street->name];
            if ($ward) {
                $addressParts[] = $ward->name;
            }
            $fullAddress = implode(', ', $addressParts);

            // Tạo tên nhà trọ theo format: "Tên đường + Số"
            $houseName = $street->name . ' ' . $houseNumber;

            // Số phòng từ 10-15
            $totalRooms = rand(10, 15);

            // Số tầng từ 2-4 (tùy số phòng)
            $floors = min(4, max(2, ceil($totalRooms / 5)));

            // Tạo mảng giá từ 100 đến 300 (bước 10)
            $priceArray = range(100, 300, 10);

            // Giá cơ bản từ 100k - 300k/ngày (cho nhà trọ) - random từ mảng
            $basePrice = $priceArray[array_rand($priceArray)] * 1000;
            
            // Lọc mảng giá phòng: chỉ lấy các giá >= giá nhà (để đảm bảo giá phòng >= giá nhà)
            $basePriceInK = $basePrice / 1000; // Chuyển về đơn vị nghìn
            $roomPriceArray = array_filter($priceArray, function($price) use ($basePriceInK) {
                return $price >= $basePriceInK;
            });
            // Reset lại key của mảng để array_rand hoạt động đúng
            $roomPriceArray = array_values($roomPriceArray);
            // Đảm bảo mảng không rỗng (nếu basePrice = 300, thì roomPriceArray sẽ có ít nhất [300])
            if (empty($roomPriceArray)) {
                $roomPriceArray = [$basePriceInK];
            }

            // Tạo nhà trọ
            $house = House::create([
                'owner_id' => $owner->id,
                'name' => $houseName,
                'address' => $fullAddress,
                'street_detail' => $streetDetail,
                'ward_id' => $ward ? $ward->id : null,
                'street_id' => $street->id,
                'description' => 'Nhà trọ sạch sẽ, thoáng mát, gần trường học và các tiện ích công cộng. Phù hợp cho sinh viên và người đi làm.',
                'amenities' => $amenitiesList[array_rand($amenitiesList)],
                'price_per_day' => $basePrice,
                'floors' => $floors,
                'total_rooms' => $totalRooms,
                'rating' => round(rand(40, 50) / 10, 1), // 3.5 - 5.0
                'reviews' => rand(5, 50),
                'latitude' => 21.0 + (rand(0, 1000) / 10000), // Khoảng Hà Nội
                'longitude' => 105.7 + (rand(0, 1000) / 10000),
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
                    $roomNumber = (string) ($floor * 100 + $j);

                    // Giá phòng từ mảng giá, đảm bảo >= giá nhà
                    $roomPrice = $roomPriceArray[array_rand($roomPriceArray)] * 1000;

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
                        // Booking sẽ được tạo trong BookingSeeder
                        $room->update([
                            'tenant_id' => $tenant->id,
                            'tenant_name' => $tenant->name,
                            'rental_start_date' => $rentalStartDate,
                            'rental_end_date' => $rentalEndDate,
                            'status' => 'active',
                        ]);
                    }
                }
            }

            $this->command->info("Đã tạo nhà trọ: {$house->name} với {$totalRooms} phòng trên {$floors} tầng");
        }

        $this->command->info('Đã tạo thành công 50 nhà trọ với phòng và tầng!');
    }
}
