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
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Tiêu đề banner
            $table->text('description')->nullable(); // Mô tả
            $table->string('image'); // Đường dẫn hình ảnh
            $table->string('link')->nullable(); // Link khi click vào banner
            $table->enum('type', ['banner', 'slider', 'popup'])->default('banner'); // Loại banner
            $table->integer('order')->default(0); // Thứ tự hiển thị
            $table->boolean('is_active')->default(true); // Trạng thái hoạt động
            $table->dateTime('start_date')->nullable(); // Ngày bắt đầu hiển thị
            $table->dateTime('end_date')->nullable(); // Ngày kết thúc hiển thị
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
