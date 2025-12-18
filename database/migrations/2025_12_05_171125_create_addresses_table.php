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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['ward', 'street'])->comment('Loại địa điểm');
            $table->string('name')->comment('Tên địa điểm');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('Địa điểm cha (ward cho street)');
            $table->timestamps();
            
            $table->index('type');
            $table->index(['type', 'parent_id']);
        });
        
        // Add foreign key constraint after table is created (self-referencing)
        Schema::table('addresses', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('addresses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
        });
        
        Schema::dropIfExists('addresses');
    }
};
