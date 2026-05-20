<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Langsung isi nilai awal (default) agar tidak kosong saat pertama kali dijalankan
        DB::table('settings')->insert([
            ['key' => 'center_latitude', 'value' => '-7.6647513', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'center_longitude', 'value' => '114.0710803', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'max_radius', 'value' => '100', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
