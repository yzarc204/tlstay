<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Tạo user quản lý mẫu
        User::factory()->create([
            'name' => 'Nguyễn Thùy Dung',
            'email' => 'manager@tlstay.com',
            'role' => 'manager',
            'phone' => '036975008',
            'avatar' => null
        ]);

        // Tạo user khách hàng mẫu
        User::factory()->create([
            'name' => 'Nguyễn Văn A',
            'email' => 'customer@tlstay.com',
            'role' => 'customer',
            'phone' => '0987654321',
        ]);

        // Tạo thêm một số user khách hàng mẫu
        // User::factory(10)->create(['role' => 'customer']);
    }
}
