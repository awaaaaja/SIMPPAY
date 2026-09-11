<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tunjangan_jabatan_karyawan_scale', function (Blueprint $table) {
            $table->id();
            $table->foreignId('golongan_ruang_id')->constrained('golongan_ruang');
            $table->decimal('nominal', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tunjangan_jabatan_karyawan_scale');
    }
};
