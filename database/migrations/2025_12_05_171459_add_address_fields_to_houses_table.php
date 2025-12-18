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
        Schema::table('houses', function (Blueprint $table) {
            $table->foreignId('ward_id')->nullable()->after('address')->constrained('addresses')->onDelete('set null')->comment('Phường/Xã');
            $table->foreignId('street_id')->nullable()->after('ward_id')->constrained('addresses')->onDelete('set null')->comment('Đường');
            $table->string('street_detail')->nullable()->after('street_id')->comment('Địa chỉ cụ thể (số nhà, ngõ ngách)');
            
            $table->index('ward_id');
            $table->index('street_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('houses', function (Blueprint $table) {
            $table->dropForeign(['ward_id']);
            $table->dropForeign(['street_id']);
            
            $table->dropColumn(['ward_id', 'street_id', 'street_detail']);
        });
    }
};
