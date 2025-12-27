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
        Schema::table('rooms', function (Blueprint $table) {
            $table->decimal('price_per_week', 12, 2)->nullable()->after('price_per_day'); // Giá thuê theo tuần
            $table->decimal('price_per_month', 12, 2)->nullable()->after('price_per_week'); // Giá thuê theo tháng
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['price_per_week', 'price_per_month']);
        });
    }
};
