<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            // Add new three-line overlay fields
            $table->string('text_line1')->nullable()->after('text_position')->comment('Dòng 1 (chữ nhỏ)');
            $table->string('text_line2')->nullable()->after('text_line1')->comment('Dòng 2 (chữ to)');
            $table->string('text_line3')->nullable()->after('text_line2')->comment('Dòng 3 (chữ nhỏ)');
        });
        
        // Migrate existing data: text_subtitle -> text_line1, text_title -> text_line2, description -> text_line3
        DB::table('banners')->whereNotNull('text_subtitle')->orWhereNotNull('text_title')->orWhereNotNull('description')->chunkById(100, function ($banners) {
            foreach ($banners as $banner) {
                $updates = [];
                if ($banner->text_subtitle && !$banner->text_line1) {
                    $updates['text_line1'] = $banner->text_subtitle;
                }
                if ($banner->text_title && !$banner->text_line2) {
                    $updates['text_line2'] = $banner->text_title;
                }
                if ($banner->description && !$banner->text_line3) {
                    $updates['text_line3'] = $banner->description;
                }
                if (!empty($updates)) {
                    DB::table('banners')->where('id', $banner->id)->update($updates);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn(['text_line1', 'text_line2', 'text_line3']);
        });
    }
};
