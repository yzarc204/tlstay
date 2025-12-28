<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo 1 tài khoản manager
        User::factory()->create([
            'name' => 'Nguyễn Thùy Dung',
            'email' => 'manager@tlstay.com',
            'password' => Hash::make('123456'),
            'phone' => '036975008',
            'role' => 'manager',
            'avatar' => null,
            'permanent_address' => '789 Giải Phóng, Quận Hoàng Mai, Thành phố Hà Nội',
            'date_of_birth' => '2005-09-09',
            'gender' => 'female',
        ]);

        // Tạo 1 tài khoản customer
        User::factory()->create([
            'name' => 'Hà Kỳ Anh',
            'email' => 'customer@tlstay.com',
            'password' => Hash::make('123456'),
            'phone' => '0987654321',
            'role' => 'customer',
            'permanent_address' => 'Ngõ 160 Cầu Giấy, Quận Cầu Giấy, Thành phố Hà Nội',
            'date_of_birth' => '2004-03-03',
            'gender' => 'male'
        ]);

        // Tạo 50 tài khoản customer mẫu
        $vietnameseNames = [
            'Nguyễn Văn B',
            'Trần Thị C',
            'Lê Văn D',
            'Phạm Thị E',
            'Hoàng Văn F',
            'Vũ Thị G',
            'Đặng Văn H',
            'Bùi Thị I',
            'Đỗ Văn J',
            'Hồ Thị K',
            'Ngô Văn L',
            'Dương Thị M',
            'Đinh Văn N',
            'Lý Thị O',
            'Võ Văn P',
            'Phan Thị Q',
            'Trương Văn R',
            'Đào Thị S',
            'Lương Văn T',
            'Tạ Thị U',
            'Chu Văn V',
            'Mai Thị W',
            'Lâm Văn X',
            'Hà Thị Y',
            'Đinh Văn Z',
            'Nguyễn Thị An',
            'Trần Văn Bình',
            'Lê Thị Cẩm',
            'Phạm Văn Dũng',
            'Hoàng Thị Em',
            'Vũ Văn Phong',
            'Đặng Thị Giang',
            'Bùi Văn Hùng',
            'Đỗ Thị Hương',
            'Hồ Văn Khoa',
            'Ngô Thị Lan',
            'Dương Văn Minh',
            'Đinh Thị Nga',
            'Lý Văn Oanh',
            'Võ Thị Phượng',
            'Phan Văn Quang',
            'Trương Thị Rạng',
            'Đào Văn Sơn',
            'Lương Thị Tâm',
            'Tạ Văn Tuấn',
            'Chu Thị Vân',
            'Mai Văn Xuân',
            'Lâm Thị Yến',
            'Hà Văn Đức',
            'Đinh Thị Hạnh',
        ];

        $phonePrefixes = ['090', '091', '092', '093', '094', '096', '097', '098', '032', '033', '034', '035', '036', '037', '038', '039'];
        $usedPhones = ['036975008', '0987654321']; // Tránh trùng với phone đã tạo

        for ($i = 0; $i < 50; $i++) {
            $name = $vietnameseNames[$i] ?? fake()->name();

            // Tạo số điện thoại không trùng lặp
            do {
                $phonePrefix = $phonePrefixes[array_rand($phonePrefixes)];
                $phoneSuffix = str_pad((string) rand(1000000, 9999999), 7, '0', STR_PAD_LEFT);
                $phone = $phonePrefix . $phoneSuffix;
            } while (in_array($phone, $usedPhones));
            $usedPhones[] = $phone;

            User::create([
                'name' => $name,
                'email' => 'customer' . ($i + 1) . '@tlstay.com',
                'password' => Hash::make('123456'),
                'phone' => $phone,
                'role' => 'customer',
                // Thêm một số thông tin cá nhân mẫu cho một số user
                'id_card_number' => fake()->boolean(70) ? '0012' . str_pad((string) rand(10000000, 99999999), 8, '0', STR_PAD_LEFT) : null,
                'id_card_issue_date' => fake()->boolean(70) ? fake()->date('Y-m-d', '-5 years') : null,
                'id_card_issue_place' => fake()->boolean(70) ? 'Cục Cảnh sát Quản lý hành chính về trật tự xã hội' : null,
                'permanent_address' => fake()->boolean(70) ? fake()->address() : null,
                'date_of_birth' => fake()->boolean(70) ? fake()->date('Y-m-d', '-18 years') : null,
                'gender' => fake()->boolean(70) ? fake()->randomElement(['male', 'female']) : null,
            ]);
        }
    }
}
