<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('houses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->text('address');
            $table->text('description')->nullable();
            $table->json('amenities')->nullable(); // Tiện nghi: wifi, parking, etc.
            $table->json('images')->nullable(); // Mảng các URL ảnh
            $table->string('image')->nullable(); // Ảnh chính
            $table->decimal('price_per_day', 12, 2); // Giá thuê theo ngày
            $table->integer('floors')->default(1); // Số tầng
            $table->integer('total_rooms')->default(0); // Tổng số phòng
            $table->decimal('rating', 3, 2)->default(0); // Đánh giá (0-5)
            $table->integer('reviews')->default(0); // Số lượt đánh giá
            $table->decimal('latitude', 10, 8)->nullable(); // Vĩ độ
            $table->decimal('longitude', 11, 8)->nullable(); // Kinh độ
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('houses');
    }
};
