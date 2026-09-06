<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dosen_sks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawai')->cascadeOnDelete();
            $table->foreignId('tahun_akademik_id')->constrained('tahun_akademik')->cascadeOnDelete();
            $table->unsignedSmallInteger('sks_maksimal')->default(0);
            $table->unsignedSmallInteger('sks_terpakai')->default(0);
            $table->unsignedSmallInteger('sks_beban')->default(0);
            $table->timestamps();
            $table->unique(['pegawai_id', 'tahun_akademik_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dosen_sks');
    }
};
