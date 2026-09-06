<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('honor_sks_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('honor_sks_id')->constrained('honor_sks')->cascadeOnDelete();
            $table->decimal('honor_lama', 15, 2);
            $table->decimal('honor_baru', 15, 2);
            $table->foreignId('updated_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('honor_sks_log');
    }
};
