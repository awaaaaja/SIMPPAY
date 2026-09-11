<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tunjangan_fungsional_dosen_scale', function (Blueprint $table) {
            $table->id();
            $table->string('jabatan_fungsional', 50); // Asisten Ahli, Lektor, Lektor Kepala, Guru Besar
            $table->integer('angka_kredit');
            $table->string('pangkat', 50);
            $table->string('golongan_ruang', 10); // III.a, III.b, etc.
            $table->decimal('nominal', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tunjangan_fungsional_dosen_scale');
    }
};
