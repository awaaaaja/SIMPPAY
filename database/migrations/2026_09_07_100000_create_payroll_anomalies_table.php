<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_anomalies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_detail_id')->constrained('payroll_details')->cascadeOnDelete();
            $table->string('tipe'); // 'gaji_deviasi', 'absensi_deviasi'
            $table->decimal('nilai_sebelumnya', 15, 2)->nullable();
            $table->decimal('nilai_sekarang', 15, 2)->nullable();
            $table->decimal('persentase_deviasi', 8, 2)->nullable();
            $table->text('catatan_ai')->nullable();
            $table->enum('status_review', ['pending', 'dikonfirmasi', 'diabaikan'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_anomalies');
    }
};
