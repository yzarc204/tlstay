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
        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('booking_status', ['upcoming', 'active', 'past'])->nullable()->after('status')->comment('Trạng thái đặt phòng dựa trên ngày tháng: upcoming (sắp tới), active (đang ở), past (đã ở)');
            $table->index('booking_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['booking_status']);
            $table->dropColumn('booking_status');
        });
    }
};
