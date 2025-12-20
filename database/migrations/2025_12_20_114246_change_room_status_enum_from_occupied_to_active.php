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
        // Step 1: Add 'active' to the enum (keeping 'occupied' temporarily)
        DB::statement("ALTER TABLE rooms MODIFY COLUMN status ENUM('available', 'occupied', 'active') NOT NULL DEFAULT 'available'");
        
        // Step 2: Update existing 'occupied' values to 'active'
        DB::statement("UPDATE rooms SET status = 'active' WHERE status = 'occupied'");
        
        // Step 3: Remove 'occupied' from enum, keeping only 'available' and 'active'
        DB::statement("ALTER TABLE rooms MODIFY COLUMN status ENUM('available', 'active') NOT NULL DEFAULT 'available'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Add 'occupied' back to the enum
        DB::statement("ALTER TABLE rooms MODIFY COLUMN status ENUM('available', 'active', 'occupied') NOT NULL DEFAULT 'available'");
        
        // Step 2: Update existing 'active' values back to 'occupied'
        DB::statement("UPDATE rooms SET status = 'occupied' WHERE status = 'active'");
        
        // Step 3: Remove 'active' from enum, keeping only 'available' and 'occupied'
        DB::statement("ALTER TABLE rooms MODIFY COLUMN status ENUM('available', 'occupied') NOT NULL DEFAULT 'available'");
    }
};
