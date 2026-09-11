<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('klasifikasi_jabatan_struktural', function (Blueprint $table) {
            $table->id();
            $table->integer('klasifikasi'); // 1-6
            $table->integer('poin_min');
            $table->integer('poin_max');
            $table->string('level_jabatan', 100); // e.g. "Sekprod/Kaprod"
            $table->decimal('tunjangan_min', 15, 2);
            $table->decimal('tunjangan_max', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('klasifikasi_jabatan_struktural');
    }
};
