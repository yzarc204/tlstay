<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\House;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class HouseSeeder extends Seeder
{
    /**
     * Lấy danh sách ảnh mẫu từ thư mục storage
     * Đường dẫn trả về dạng /storage/sample-images/filename.jpg
     */
    private function getSampleImages(): array
    {
        $sampleImagesPath = storage_path('app/public/sample-images');

        if (!File::isDirectory($sampleImagesPath)) {
            return [];
        }

        $files = File::files($sampleImagesPath);
        $images = [];

        foreach ($files as $file) {
            $extension = strtolower($file->getExtension());
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                // Lưu đường dẫn public để sử dụng trực tiếp trên frontend
                // /storage/app/public/sample-images/abc.jpg -> /storage/sample-images/abc.jpg
                $images[] = '/storage/sample-images/' . $file->getFilename();
            }
        }

        return $images;
    }

    /**
     * Lấy ngẫu nhiên một số ảnh từ danh sách
     */
    private function getRandomImages(array $images, int $count): array
    {
        if (empty($images)) {
            return [];
        }

        $count = min($count, count($images));
        $keys = array_rand($images, $count);

        if (!is_array($keys)) {
            $keys = [$keys];
        }

        $result = [];
        foreach ($keys as $key) {
            $result[] = $images[$key];
        }

        return $result;
    }

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

        // Lấy danh sách ảnh mẫu
        $sampleImages = $this->getSampleImages();

        if (empty($sampleImages)) {
            $this->command->warn('Không tìm thấy ảnh mẫu trong storage/app/public/sample-images/');
            $this->command->info('Tiếp tục tạo nhà trọ không có ảnh...');
        } else {
            $this->command->info('Tìm thấy ' . count($sampleImages) . ' ảnh mẫu.');
        }

        // Danh sách tiện nghi (sử dụng key tiếng Anh để khớp với frontend)
        $amenitiesList = [
            ['Wifi', 'AirConditioning', 'HotWater', 'Refrigerator'],
            ['Wifi', 'AirConditioning', 'HotWater', 'Refrigerator', 'SharedWashingMachine'],
            ['Wifi', 'AirConditioning', 'HotWater', 'Refrigerator', 'SharedWashingMachine', 'SharedKitchen'],
            ['Wifi', 'AirConditioning', 'HotWater'],
            ['Wifi', 'AirConditioning', 'HotWater', 'Refrigerator', 'PrivateKitchen'],
            ['Wifi', 'AirConditioning', 'HotWater', 'PrivateBathroom', 'Balcony'],
            ['Wifi', 'AirConditioning', 'HotWater', 'Refrigerator', 'Bed', 'Wardrobe'],
        ];

        // Mảng giá ngày từ 180k - 250k, mỗi giá cách nhau 10k (đơn vị nghìn)
        $dayPriceArray = range(180, 250, 10);

        // Mảng giá tuần từ 1m - 2m, mỗi giá cách nhau 100k (đơn vị nghìn)
        $weekPriceArray = range(1000, 2000, 100);

        // Mảng giá tháng từ 3m - 5m, mỗi giá cách nhau 200k (đơn vị nghìn)
        $monthPriceArray = range(3000, 5000, 200);

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

            // Chọn giá ngày ngẫu nhiên từ mảng
            $basePriceInK = $dayPriceArray[array_rand($dayPriceArray)];
            $basePrice = $basePriceInK * 1000;

            // Lọc giá tuần: chỉ lấy các giá tuần sao cho giá tuần/7 >= giá ngày
            // Để đảm bảo giá tuần/ngày >= giá ngày (tiết kiệm khi đặt nhiều)
            $validWeekPrices = array_filter($weekPriceArray, function ($weekPrice) use ($basePriceInK) {
                return ($weekPrice / 7) >= $basePriceInK;
            });
            $validWeekPrices = array_values($validWeekPrices);
            if (empty($validWeekPrices)) {
                // Nếu không có giá tuần hợp lệ, chọn giá tuần nhỏ nhất thỏa mãn điều kiện
                $minWeekPrice = ceil($basePriceInK * 7 / 100) * 100; // Làm tròn lên đến bội số 100
                $validWeekPrices = [max($minWeekPrice, 1000)]; // Tối thiểu 1000k
            }
            $pricePerWeek = $validWeekPrices[array_rand($validWeekPrices)] * 1000;

            // Lọc giá tháng: chỉ lấy các giá tháng sao cho giá tháng/30 >= giá ngày
            // Để đảm bảo giá tháng/ngày >= giá ngày (tiết kiệm khi đặt nhiều)
            $validMonthPrices = array_filter($monthPriceArray, function ($monthPrice) use ($basePriceInK) {
                return ($monthPrice / 30) >= $basePriceInK;
            });
            $validMonthPrices = array_values($validMonthPrices);
            if (empty($validMonthPrices)) {
                // Nếu không có giá tháng hợp lệ, chọn giá tháng nhỏ nhất thỏa mãn điều kiện
                $minMonthPrice = ceil($basePriceInK * 30 / 200) * 200; // Làm tròn lên đến bội số 200
                $validMonthPrices = [max($minMonthPrice, 3000)]; // Tối thiểu 3000k
            }
            $pricePerMonth = $validMonthPrices[array_rand($validMonthPrices)] * 1000;

            // Lọc mảng giá phòng: chỉ lấy các giá >= giá nhà (để đảm bảo giá phòng >= giá nhà)
            $roomPriceArray = array_filter($dayPriceArray, function ($price) use ($basePriceInK) {
                return $price >= $basePriceInK;
            });
            // Reset lại key của mảng để array_rand hoạt động đúng
            $roomPriceArray = array_values($roomPriceArray);
            // Đảm bảo mảng không rỗng
            if (empty($roomPriceArray)) {
                $roomPriceArray = [$basePriceInK];
            }

            // Lấy ảnh ngẫu nhiên cho nhà trọ
            $houseImages = $this->getRandomImages($sampleImages, rand(3, 6));
            $featuredImages = $this->getRandomImages($sampleImages, rand(2, 4));
            $mainImage = !empty($sampleImages) ? $sampleImages[array_rand($sampleImages)] : null;

            // Tạo nhà trọ (không gán rating và reviews, sẽ được tính từ BookingSeeder)
            $house = House::create([
                'owner_id' => $owner->id,
                'name' => $houseName,
                'address' => $fullAddress,
                'street_detail' => $streetDetail,
                'ward_id' => $ward ? $ward->id : null,
                'street_id' => $street->id,
                'description' => 'Nhà trọ sạch sẽ, thoáng mát, gần trường học và các tiện ích công cộng. Phù hợp cho sinh viên và người đi làm.',
                'amenities' => $amenitiesList[array_rand($amenitiesList)],
                'images' => $houseImages,
                'featured_images' => $featuredImages,
                'image' => $mainImage,
                'price_per_day' => $basePrice,
                'price_per_week' => $pricePerWeek,
                'price_per_month' => $pricePerMonth,
                'floors' => $floors,
                'total_rooms' => $totalRooms,
                'rating' => 0,
                'reviews' => 0,
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

            // Tạo phòng cho mỗi tầng (tất cả phòng ban đầu đều trống)
            foreach ($roomsPerFloor as $floor => $roomCount) {
                for ($j = 1; $j <= $roomCount; $j++) {
                    // Tên phòng theo format: {tầng}{số thứ tự trong tầng}
                    // Ví dụ: Tầng 1 -> 101, 102, 103... | Tầng 2 -> 201, 202, 203...
                    $roomNumber = (string) ($floor * 100 + $j);

                    // Giá phòng từ mảng giá, đảm bảo >= giá nhà
                    $roomPriceInK = $roomPriceArray[array_rand($roomPriceArray)];
                    $roomPrice = $roomPriceInK * 1000;

                    // Lọc giá tuần cho phòng: chỉ lấy các giá tuần sao cho giá tuần/7 >= giá phòng ngày
                    $validRoomWeekPrices = array_filter($weekPriceArray, function ($weekPrice) use ($roomPriceInK) {
                        return ($weekPrice / 7) >= $roomPriceInK;
                    });
                    $validRoomWeekPrices = array_values($validRoomWeekPrices);
                    if (empty($validRoomWeekPrices)) {
                        $minRoomWeekPrice = ceil($roomPriceInK * 7 / 100) * 100;
                        $validRoomWeekPrices = [max($minRoomWeekPrice, 1000)];
                    }
                    $roomPricePerWeek = $validRoomWeekPrices[array_rand($validRoomWeekPrices)] * 1000;

                    // Lọc giá tháng cho phòng: chỉ lấy các giá tháng sao cho giá tháng/30 >= giá phòng ngày
                    $validRoomMonthPrices = array_filter($monthPriceArray, function ($monthPrice) use ($roomPriceInK) {
                        return ($monthPrice / 30) >= $roomPriceInK;
                    });
                    $validRoomMonthPrices = array_values($validRoomMonthPrices);
                    if (empty($validRoomMonthPrices)) {
                        $minRoomMonthPrice = ceil($roomPriceInK * 30 / 200) * 200;
                        $validRoomMonthPrices = [max($minRoomMonthPrice, 3000)];
                    }
                    $roomPricePerMonth = $validRoomMonthPrices[array_rand($validRoomMonthPrices)] * 1000;

                    // Diện tích từ 15-30 m²
                    $area = rand(15, 30);

                    // Tiện nghi phòng (sử dụng key tiếng Anh để khớp với frontend)
                    $roomAmenities = [];
                    if (rand(0, 1)) {
                        $roomAmenities[] = 'AirConditioning';
                    }
                    if (rand(0, 1)) {
                        $roomAmenities[] = 'HotWater';
                    }
                    if (rand(0, 1)) {
                        $roomAmenities[] = 'Refrigerator';
                    }
                    if (rand(0, 1)) {
                        $roomAmenities[] = 'PrivateBathroom';
                    }
                    if (rand(0, 1)) {
                        $roomAmenities[] = 'Balcony';
                    }
                    if (empty($roomAmenities)) {
                        $roomAmenities = ['HotWater'];
                    }

                    // Lấy ảnh ngẫu nhiên cho phòng (2-4 ảnh)
                    $roomImages = $this->getRandomImages($sampleImages, rand(2, 4));

                    // Tạo phòng với trạng thái available (không có người thuê)
                    // Thông tin người thuê sẽ được cập nhật từ BookingSeeder
                    Room::create([
                        'house_id' => $house->id,
                        'room_number' => $roomNumber,
                        'floor' => $floor,
                        'price_per_day' => round($roomPrice, 2),
                        'price_per_week' => $roomPricePerWeek,
                        'price_per_month' => $roomPricePerMonth,
                        'status' => 'available',
                        'area' => $area,
                        'amenities' => $roomAmenities,
                        'images' => $roomImages,
                        'tenant_id' => null,
                        'tenant_name' => null,
                        'rental_start_date' => null,
                        'rental_end_date' => null,
                    ]);
                }
            }

            $this->command->info("Đã tạo nhà trọ: {$house->name} với {$totalRooms} phòng trên {$floors} tầng");
        }

        $this->command->info('Đã tạo thành công 50 nhà trọ với phòng và tầng!');
    }
}
