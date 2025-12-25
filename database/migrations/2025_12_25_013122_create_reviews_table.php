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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('house_id')->constrained('houses')->onDelete('cascade');
            $table->tinyInteger('rating')->unsigned(); // 1-5 sao
            $table->text('comment')->nullable(); // Bình luận
            $table->json('images')->nullable(); // Ảnh đánh giá
            $table->text('manager_response')->nullable(); // Phản hồi của quản lý
            $table->timestamp('manager_response_at')->nullable(); // Thời gian phản hồi
            $table->timestamps();
            
            // Đảm bảo mỗi booking chỉ có 1 review
            $table->unique('booking_id');
            $table->index(['house_id', 'rating']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
