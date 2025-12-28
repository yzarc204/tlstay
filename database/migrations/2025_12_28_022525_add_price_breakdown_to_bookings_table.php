<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Số lượng
            $table->integer('full_months')->default(0)->after('discount_amount');
            $table->integer('full_weeks')->default(0)->after('full_months');
            $table->integer('remaining_days')->default(0)->after('full_weeks');

            // Giá đơn vị
            $table->decimal('month_unit_price', 12, 2)->nullable()->after('remaining_days');
            $table->decimal('week_unit_price', 12, 2)->nullable()->after('month_unit_price');
            $table->decimal('day_unit_price', 12, 2)->nullable()->after('week_unit_price');

            // Thành tiền
            $table->decimal('months_price', 12, 2)->default(0)->after('day_unit_price');
            $table->decimal('weeks_price', 12, 2)->default(0)->after('months_price');
            $table->decimal('remaining_price', 12, 2)->default(0)->after('weeks_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'full_months',
                'full_weeks',
                'remaining_days',
                'month_unit_price',
                'week_unit_price',
                'day_unit_price',
                'months_price',
                'weeks_price',
                'remaining_price',
            ]);
        });
    }
};
