<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('honor_sks_scale', function (Blueprint $table) {
            $table->id();
            $table->enum('program', ['s1', 's2']);
            $table->enum('status_dosen', ['tetap', 'tidak_tetap']);
            $table->string('strata', 20); // Profesor, S.3/Dr, S.2/Magister
            $table->decimal('nilai_sks', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('honor_sks_scale');
    }
};
