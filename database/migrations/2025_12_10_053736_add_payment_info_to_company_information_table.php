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
            $table->string('bank_name')->nullable()->after('company_email')->comment('Tên ngân hàng');
            $table->string('bank_account_number')->nullable()->after('bank_name')->comment('Số tài khoản');
            $table->string('bank_account_holder')->nullable()->after('bank_account_number')->comment('Tên chủ tài khoản');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_information', function (Blueprint $table) {
            $table->dropColumn(['bank_name', 'bank_account_number', 'bank_account_holder']);
        });
    }
};
