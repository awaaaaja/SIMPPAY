<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gaji_pokok_scale', function (Blueprint $table) {
            $table->id();
            $table->foreignId('golongan_ruang_id')->constrained('golongan_ruang');
            $table->integer('mkg'); // 0, 2, 4, ..., 30
            $table->decimal('nominal', 15, 2);
            $table->timestamps();

            $table->unique(['golongan_ruang_id', 'mkg']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gaji_pokok_scale');
    }
};
