<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General Settings
            [
                'key' => 'site_name',
                'value' => 'THANG LONG STAY',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Tên website',
                'description' => 'Tên hiển thị của website',
            ],
            [
                'key' => 'site_address',
                'value' => 'Trường Đại học Thăng Long, Nghiêm Xuân Yêm, Hoàng Mai, Hà Nội',
                'type' => 'textarea',
                'group' => 'general',
                'label' => 'Địa chỉ',
                'description' => 'Địa chỉ của website/công ty',
            ],
            [
                'key' => 'site_logo',
                'value' => null,
                'type' => 'file',
                'group' => 'general',
                'label' => 'Logo website',
                'description' => 'Logo của website. Nếu không có logo, sẽ hiển thị tên website dạng text.',
            ],
            [
                'key' => 'site_logo_show_text',
                'value' => '1',
                'type' => 'checkbox',
                'group' => 'general',
                'label' => 'Hiển thị tên website cùng logo',
                'description' => 'Khi bật, tên website sẽ hiển thị bên cạnh logo. Khi tắt, chỉ hiển thị logo.',
            ],

            // Contact Settings
            [
                'key' => 'contact_phone',
                'value' => '+84 123 456 789',
                'type' => 'text',
                'group' => 'contact',
                'label' => 'Số điện thoại',
                'description' => 'Số điện thoại liên hệ',
            ],
            [
                'key' => 'contact_email',
                'value' => 'contact@tlstay.com',
                'type' => 'email',
                'group' => 'contact',
                'label' => 'Email liên hệ',
                'description' => 'Email liên hệ chính',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
