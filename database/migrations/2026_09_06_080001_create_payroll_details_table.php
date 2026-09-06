<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained('payroll_runs')->cascadeOnDelete();
            $table->foreignId('pegawai_id')->constrained('pegawai')->cascadeOnDelete();
            $table->decimal('gaji_pokok', 15, 2);
            $table->decimal('tj_transport', 15, 2);
            $table->decimal('uang_makan', 15, 2);
            $table->decimal('potongan_alpha', 15, 2);
            $table->decimal('total_tunjangan_tambahan', 15, 2)->default(0);
            $table->decimal('total_potongan_tambahan', 15, 2)->default(0);
            $table->decimal('honor_kelebihan_sks', 15, 2)->default(0);
            $table->decimal('total_gaji', 15, 2);
            $table->json('breakdown_json')->nullable();
            $table->timestamps();
            $table->unique(['payroll_run_id', 'pegawai_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_details');
    }
};
