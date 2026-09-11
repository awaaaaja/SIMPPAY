<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('golongan_ruang', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 10)->unique(); // I.a, I.b, ..., IV.e
            $table->string('nama', 50); // Golongan I Ruang a, etc.
            $table->integer('urutan'); // sort order
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('golongan_ruang');
    }
};
