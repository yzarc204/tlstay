<?php

namespace Database\Seeders;

use App\Models\Address;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo các phường và đường theo yêu cầu
        
        // Hà Đông - Xa La
        $haDong = Address::create([
            'type' => 'ward',
            'name' => 'Hà Đông',
            'parent_id' => null,
        ]);
        
        Address::create([
            'type' => 'street',
            'name' => 'Xa La',
            'parent_id' => $haDong->id,
        ]);

        // Thanh Liệt - Tân Triều, Triều Khúc
        $thanhLiet = Address::create([
            'type' => 'ward',
            'name' => 'Thanh Liệt',
            'parent_id' => null,
        ]);
        
        Address::create([
            'type' => 'street',
            'name' => 'Tân Triều',
            'parent_id' => $thanhLiet->id,
        ]);
        
        Address::create([
            'type' => 'street',
            'name' => 'Triều Khúc',
            'parent_id' => $thanhLiet->id,
        ]);

        // Hạ Đình - Nguyễn Xiển, Kim Giang, Hạ Đình
        $haDinh = Address::create([
            'type' => 'ward',
            'name' => 'Hạ Đình',
            'parent_id' => null,
        ]);
        
        Address::create([
            'type' => 'street',
            'name' => 'Nguyễn Xiển',
            'parent_id' => $haDinh->id,
        ]);
        
        Address::create([
            'type' => 'street',
            'name' => 'Kim Giang',
            'parent_id' => $haDinh->id,
        ]);
        
        Address::create([
            'type' => 'street',
            'name' => 'Hạ Đình',
            'parent_id' => $haDinh->id,
        ]);

        // Thanh Xuân - Nguyễn Trãi, Khuất Duy Tiến
        $thanhXuan = Address::create([
            'type' => 'ward',
            'name' => 'Thanh Xuân',
            'parent_id' => null,
        ]);
        
        Address::create([
            'type' => 'street',
            'name' => 'Nguyễn Trãi',
            'parent_id' => $thanhXuan->id,
        ]);
        
        Address::create([
            'type' => 'street',
            'name' => 'Khuất Duy Tiến',
            'parent_id' => $thanhXuan->id,
        ]);

        // Định Công - Kim Văn - Kim Lũ
        $dinhCong = Address::create([
            'type' => 'ward',
            'name' => 'Định Công',
            'parent_id' => null,
        ]);
        
        Address::create([
            'type' => 'street',
            'name' => 'Kim Văn - Kim Lũ',
            'parent_id' => $dinhCong->id,
        ]);

        // Khương Đình - Khương Đình
        $khuongDinh = Address::create([
            'type' => 'ward',
            'name' => 'Khương Đình',
            'parent_id' => null,
        ]);
        
        Address::create([
            'type' => 'street',
            'name' => 'Khương Đình',
            'parent_id' => $khuongDinh->id,
        ]);
    }
}
