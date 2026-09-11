<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_settings', function (Blueprint $table) {
            $table->id();

            // §6 Tunjangan Makan
            $table->decimal('tunjangan_makan_per_hari', 15, 2)->nullable();
            $table->boolean('is_confirmed_tunjangan_makan')->default(false);

            // §16 Lembur
            $table->enum('lembur_basis_gaji_dasar', ['gaji_pokok_saja', 'gaji_pokok_plus_tunjangan_tetap'])->default('gaji_pokok_saja');
            $table->boolean('is_confirmed_lembur_basis')->default(false);
            $table->integer('lembur_jam_kerja_sebulan')->default(120);
            $table->boolean('is_confirmed_lembur_jam')->default(true);

            // §9 Tunjangan Variabel
            $table->decimal('tunjangan_variabel_default_percentage', 5, 2)->default(100);
            $table->boolean('is_confirmed_tunjangan_variabel')->default(false);

            // §13 BPJS Kesehatan
            $table->boolean('bpjs_kesehatan_aktif')->default(true);
            $table->boolean('is_confirmed_bpjs_kes')->default(true);

            // §14 BPJS Ketenagakerjaan
            $table->boolean('bpjs_tk_aktif')->default(false);
            $table->boolean('is_confirmed_bpjs_tk')->default(false);
            $table->enum('bpjs_tk_kategori_risiko_jkk', [
                'sangat_rendah', 'rendah', 'sedang', 'tinggi', 'sangat_tinggi',
            ])->nullable();
            $table->boolean('is_confirmed_bpjs_tk_risiko')->default(false);

            // Audit
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_settings');
    }
};
