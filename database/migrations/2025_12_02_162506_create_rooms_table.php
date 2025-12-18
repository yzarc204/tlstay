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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('house_id')->constrained('houses')->onDelete('cascade');
            $table->string('room_number'); // Số phòng
            $table->integer('floor'); // Tầng
            $table->decimal('price_per_day', 12, 2); // Giá thuê theo ngày
            $table->enum('status', ['available', 'occupied'])->default('available');
            $table->decimal('area', 8, 2)->nullable(); // Diện tích (m²)
            $table->json('amenities')->nullable(); // Tiện nghi của phòng
            $table->json('images')->nullable(); // Ảnh phòng
            $table->string('tenant_name')->nullable(); // Tên người thuê hiện tại
            $table->timestamps();
            
            $table->index(['house_id', 'status']);
            $table->unique(['house_id', 'room_number', 'floor']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
