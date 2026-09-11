<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beban_sks_jabatan', function (Blueprint $table) {
            $table->foreignId('jabatan_struktural_poin_id')->nullable()->after('nama_jabatan')->constrained('jabatan_struktural_poin')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('beban_sks_jabatan', function (Blueprint $table) {
            $table->dropForeign(['jabatan_struktural_poin_id']);
            $table->dropColumn('jabatan_struktural_poin_id');
        });
    }
};
