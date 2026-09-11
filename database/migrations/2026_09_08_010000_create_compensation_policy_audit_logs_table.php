<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add role column to existing payroll_settings_log
        Schema::table('payroll_settings_log', function (Blueprint $table) {
            $table->string('role')->after('updated_by')->nullable();
        });

        // Tunjangan Jabatan Karyawan Scale log
        Schema::create('tunjangan_jabatan_karyawan_scale_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scale_id')->constrained('tunjangan_jabatan_karyawan_scale')->cascadeOnDelete();
            $table->foreignId('golongan_ruang_id')->constrained('golongan_ruang');
            $table->decimal('nominal_lama', 15, 2);
            $table->decimal('nominal_baru', 15, 2);
            $table->foreignId('updated_by')->constrained('users');
            $table->string('role'); // admin | bpsdm
            $table->timestamps();
        });

        // Tunjangan Variabel Scale log
        Schema::create('tunjangan_variabel_scale_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scale_id')->constrained('tunjangan_variabel_scale')->cascadeOnDelete();
            $table->foreignId('golongan_ruang_id')->constrained('golongan_ruang');
            $table->decimal('nominal_lama', 15, 2);
            $table->decimal('nominal_baru', 15, 2);
            $table->foreignId('updated_by')->constrained('users');
            $table->string('role'); // admin | bpsdm
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tunjangan_variabel_scale_log');
        Schema::dropIfExists('tunjangan_jabatan_karyawan_scale_log');

        Schema::table('payroll_settings_log', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};
