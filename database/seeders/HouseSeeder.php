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
                    $roomPrice = $roomPriceArray[array_rand($roomPriceArray)] * 1000;

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
