<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            $table->foreignId('golongan_ruang_id')->nullable()->after('fungsional_id')->constrained('golongan_ruang')->nullOnDelete();
            $table->date('tmt')->nullable()->after('tanggal_masuk');
            $table->foreignId('jabatan_struktural_poin_id')->nullable()->after('struktural_id')->constrained('jabatan_struktural_poin')->nullOnDelete();
            $table->enum('strata_pendidikan', ['profesor', 's3', 's2', 'lainnya'])->nullable()->after('status_dosen');
            $table->enum('program_mengajar', ['s1', 's2'])->nullable()->after('strata_pendidikan');
        });
    }

    public function down(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            $table->dropForeign(['golongan_ruang_id']);
            $table->dropForeign(['jabatan_struktural_poin_id']);
            $table->dropColumn([
                'golongan_ruang_id',
                'tmt',
                'jabatan_struktural_poin_id',
                'strata_pendidikan',
                'program_mengajar',
            ]);
        });
    }
};
