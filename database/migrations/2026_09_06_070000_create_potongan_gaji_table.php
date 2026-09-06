<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('potongan_gaji', function (Blueprint $table) {
            $table->id();
            $table->string('nama_potongan');
            $table->enum('tipe', ['nominal', 'persentase'])->default('nominal');
            $table->decimal('nilai', 15, 2);
            $table->boolean('is_alpha_penalty')->default(false);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('potongan_gaji');
    }
};
