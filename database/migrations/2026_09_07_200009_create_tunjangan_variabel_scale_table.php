<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tunjangan_variabel_scale', function (Blueprint $table) {
            $table->id();
            $table->foreignId('golongan_ruang_id')->constrained('golongan_ruang');
            $table->decimal('nominal_maksimum', 15, 2); // 100% kinerja
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tunjangan_variabel_scale');
    }
};
