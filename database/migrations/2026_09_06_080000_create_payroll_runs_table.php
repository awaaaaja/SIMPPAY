<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->date('periode');
            $table->enum('status', ['draft', 'calculated', 'finalized', 'void'])->default('draft');
            $table->foreignId('calculated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('calculated_at')->nullable();
            $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('finalized_at')->nullable();
            $table->text('void_reason')->nullable();
            $table->timestamps();
            $table->unique('periode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_runs');
    }
};
