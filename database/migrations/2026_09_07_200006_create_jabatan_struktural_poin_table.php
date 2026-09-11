<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jabatan_struktural_poin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_struktur_id')->constrained('level_struktur');
            $table->string('nama_jabatan', 100);
            $table->integer('total_poin');
            $table->foreignId('klasifikasi_id')->nullable()->constrained('klasifikasi_jabatan_struktural');
            $table->decimal('tunjangan_baru', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jabatan_struktural_poin');
    }
};
