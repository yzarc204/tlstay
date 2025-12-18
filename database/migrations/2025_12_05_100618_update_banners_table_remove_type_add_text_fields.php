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
        Schema::table('banners', function (Blueprint $table) {
            // Remove type column
            $table->dropColumn('type');
            
            // Add text overlay fields
            $table->boolean('show_text')->default(false)->after('description');
            $table->string('text_title')->nullable()->after('show_text');
            $table->string('text_subtitle')->nullable()->after('text_title');
            $table->enum('text_position', ['left', 'center', 'right'])->default('center')->after('text_subtitle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            // Restore type column
            $table->enum('type', ['banner', 'slider', 'popup'])->default('banner');
            
            // Remove text overlay fields
            $table->dropColumn(['show_text', 'text_title', 'text_subtitle', 'text_position']);
        });
    }
};
