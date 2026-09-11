<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tunjangan_transportasi_scale', function (Blueprint $table) {
            $table->id();
            $table->foreignId('level_struktur_id')->nullable()->constrained('level_struktur');
            $table->string('keterangan', 100); // e.g. "Gol IIA-IIIB", "Rektor & Wakil Rektor"
            $table->string('golongan_range', 50)->nullable(); // nullable for level_struktur-based
            $table->decimal('nominal', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tunjangan_transportasi_scale');
    }
};
