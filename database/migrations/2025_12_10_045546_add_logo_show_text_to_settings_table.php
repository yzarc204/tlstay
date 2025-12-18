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
        // Insert logo_show_text setting
        DB::table('settings')->insert([
            [
                'key' => 'site_logo_show_text',
                'value' => '1', // Default: show text with logo
                'type' => 'checkbox',
                'group' => 'general',
                'label' => 'Hiển thị tên website cùng logo',
                'description' => 'Khi bật, tên website sẽ hiển thị bên cạnh logo. Khi tắt, chỉ hiển thị logo.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')->where('key', 'site_logo_show_text')->delete();
    }
};
