<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // Key của setting (ví dụ: 'site_name', 'site_address')
            $table->text('value')->nullable(); // Giá trị của setting
            $table->string('type')->default('text'); // Loại: text, textarea, email, phone, etc.
            $table->string('group')->default('general'); // Nhóm: general, contact, etc.
            $table->string('label'); // Nhãn hiển thị
            $table->text('description')->nullable(); // Mô tả
            $table->timestamps();
        });

        // Insert default settings
        DB::table('settings')->insert([
            [
                'key' => 'site_name',
                'value' => 'THANG LONG STAY',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Tên website',
                'description' => 'Tên hiển thị của website',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'site_address',
                'value' => 'Thang Long University, Nội Bài, Hà Nội',
                'type' => 'textarea',
                'group' => 'general',
                'label' => 'Địa chỉ',
                'description' => 'Địa chỉ của website/công ty',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'contact_phone',
                'value' => '+84 123 456 789',
                'type' => 'text',
                'group' => 'contact',
                'label' => 'Số điện thoại',
                'description' => 'Số điện thoại liên hệ',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'contact_email',
                'value' => 'contact@tlstay.com',
                'type' => 'email',
                'group' => 'contact',
                'label' => 'Email liên hệ',
                'description' => 'Email liên hệ chính',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
