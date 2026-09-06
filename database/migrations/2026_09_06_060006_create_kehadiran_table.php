<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kehadiran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawai')->cascadeOnDelete();
            $table->date('periode'); // always 1st of month (YYYY-MM-01)
            $table->unsignedSmallInteger('hadir')->default(0);
            $table->unsignedSmallInteger('sakit')->default(0);
            $table->unsignedSmallInteger('alpha')->default(0);
            $table->timestamps();
            $table->unique(['pegawai_id', 'periode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kehadiran');
    }
};
