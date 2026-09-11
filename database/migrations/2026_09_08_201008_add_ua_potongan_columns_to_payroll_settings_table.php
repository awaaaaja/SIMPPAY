<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_settings', function (Blueprint $table) {
            // UA-2025 Potongan columns (§6 formula)
            $table->decimal('potongan_bpjs_kes_pct', 5, 2)->default(0)->after('bpjs_tk_kategori_risiko_jkk')->comment('BPJS Kesehatan % from gaji pokok');
            $table->decimal('potongan_bpjs_tk_jkk_pct', 5, 2)->default(0)->after('potongan_bpjs_kes_pct')->comment('BPJS TK JKK % from total income');
            $table->decimal('potongan_bpjs_tk_jkm_pct', 5, 2)->default(0)->after('potongan_bpjs_tk_jkk_pct')->comment('BPJS TK JKM % from total income');
            $table->decimal('potongan_bpjs_tk_jht_pct', 5, 2)->default(0)->after('potongan_bpjs_tk_jkm_pct')->comment('BPJS TK JHT % from total income');
            $table->decimal('potongan_bpjs_tk_jp_pct', 5, 2)->default(0)->after('potongan_bpjs_tk_jht_pct')->comment('BPJS TK JP % from total income');
            $table->decimal('potongan_sosial_pct', 5, 2)->default(0)->after('potongan_bpjs_tk_jp_pct')->comment('Iuran sosial % from gaji pokok');
            $table->decimal('potongan_pendidikan_anak_pct', 5, 2)->default(0)->after('potongan_sosial_pct')->comment('Iuran pendidikan anak % from gaji pokok');
            $table->boolean('is_confirmed_ua_potongan')->default(false)->after('potongan_pendidikan_anak_pct')->comment('Admin confirmation flag');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_settings', function (Blueprint $table) {
            $table->dropColumn([
                'potongan_bpjs_kes_pct',
                'potongan_bpjs_tk_jkk_pct',
                'potongan_bpjs_tk_jkm_pct',
                'potongan_bpjs_tk_jht_pct',
                'potongan_bpjs_tk_jp_pct',
                'potongan_sosial_pct',
                'potongan_pendidikan_anak_pct',
                'is_confirmed_ua_potongan',
            ]);
        });
    }
};
