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
        Schema::table('company_information', function (Blueprint $table) {
            $table->string('bank_code')->nullable()->after('bank_name')->comment('Mã ngân hàng (ví dụ: TCB, VIB)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_information', function (Blueprint $table) {
            $table->dropColumn('bank_code');
        });
    }
};
