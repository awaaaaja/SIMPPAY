<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fungsional', function (Blueprint $table) {
            $table->id();
            $table->string('nama_fungsional');
            $table->decimal('angka_kredit', 8, 2)->default(0);
            $table->string('pangkat')->nullable();
            $table->string('golongan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fungsional');
    }
};
