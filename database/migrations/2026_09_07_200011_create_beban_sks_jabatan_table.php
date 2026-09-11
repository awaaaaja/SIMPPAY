<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beban_sks_jabatan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_jabatan', 100);
            $table->integer('sks_perkuliahan')->default(0);
            $table->integer('sks_penelitian')->default(0);
            $table->integer('sks_adm')->default(0);
            $table->integer('sks_jabatan')->default(0);
            $table->integer('total')->default(12);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beban_sks_jabatan');
    }
};
