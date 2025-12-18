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
            $table->text('user_signature')->nullable()->after('notes'); // Base64 encoded signature image
            $table->timestamp('signed_at')->nullable()->after('user_signature'); // When user signed
            $table->boolean('contract_signed')->default(false)->after('signed_at'); // Whether contract is signed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['user_signature', 'signed_at', 'contract_signed']);
        });
    }
};
