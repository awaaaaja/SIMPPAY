<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('honor_sks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_honor_sks_id')->constrained('kategori_honor_sks')->cascadeOnDelete();
            $table->decimal('honor', 15, 2);
            $table->foreignId('last_updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('honor_sks');
    }
};
