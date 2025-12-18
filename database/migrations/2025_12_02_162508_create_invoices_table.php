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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->onDelete('set null');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('month'); // Tháng (1-12)
            $table->integer('year'); // Năm
            $table->decimal('amount', 12, 2); // Tổng tiền
            $table->decimal('electricity_amount', 12, 2)->default(0); // Tiền điện
            $table->decimal('water_amount', 12, 2)->default(0); // Tiền nước
            $table->decimal('other_fees', 12, 2)->default(0); // Phí khác
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->date('due_date'); // Ngày đến hạn
            $table->timestamp('paid_at')->nullable(); // Ngày thanh toán
            $table->text('notes')->nullable(); // Ghi chú
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['month', 'year']);
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
