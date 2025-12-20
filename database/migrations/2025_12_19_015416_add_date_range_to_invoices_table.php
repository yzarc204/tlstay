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
        Schema::table('invoices', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('year');
            $table->date('end_date')->nullable()->after('start_date');
            // Make month and year nullable for backward compatibility
            $table->integer('month')->nullable()->change();
            $table->integer('year')->nullable()->change();
            // Add index for date range queries
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['start_date', 'end_date']);
            $table->dropColumn(['start_date', 'end_date']);
            // Restore month and year to not nullable
            $table->integer('month')->nullable(false)->change();
            $table->integer('year')->nullable(false)->change();
        });
    }
};
