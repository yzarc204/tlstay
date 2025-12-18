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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('wallets')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['credit', 'debit']); // credit = nạp tiền, debit = rút tiền
            $table->decimal('amount', 15, 2); // Số tiền
            $table->decimal('balance_after', 15, 2); // Số dư sau giao dịch
            $table->string('description')->nullable(); // Mô tả giao dịch
            $table->string('reference_type')->nullable(); // Loại tham chiếu (Booking, Invoice, etc.)
            $table->unsignedBigInteger('reference_id')->nullable(); // ID của tham chiếu
            $table->timestamps();
            
            $table->index(['wallet_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
