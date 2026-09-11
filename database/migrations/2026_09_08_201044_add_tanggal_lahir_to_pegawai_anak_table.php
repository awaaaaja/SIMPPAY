<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pegawai_anak', function (Blueprint $table) {
            $table->date('tanggal_lahir')->nullable()->after('tempat_tanggal_lahir')->comment('Tanggal lahir anak untuk validasi usia < 21');
        });
    }

    public function down(): void
    {
        Schema::table('pegawai_anak', function (Blueprint $table) {
            $table->dropColumn('tanggal_lahir');
        });
    }
};
