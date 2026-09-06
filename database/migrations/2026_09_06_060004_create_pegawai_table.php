<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pegawai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('jabatan_id')->nullable()->constrained('jabatan')->nullOnDelete();
            $table->foreignId('struktural_id')->nullable()->constrained('struktural')->nullOnDelete();
            $table->foreignId('fungsional_id')->nullable()->constrained('fungsional')->nullOnDelete();
            $table->string('nik')->unique();
            $table->string('nama_pegawai');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->date('tanggal_masuk')->nullable();
            $table->enum('status_pegawai', ['aktif', 'nonaktif', 'pensiun'])->default('aktif');
            $table->string('photo')->nullable();
            $table->string('ktp')->nullable();
            $table->string('nidn')->nullable();
            $table->string('id_ptk')->nullable();
            $table->string('nuptk')->nullable();
            $table->string('email')->nullable();
            $table->string('agama')->nullable();
            $table->string('kewarganegaraan')->nullable();
            $table->string('suku')->nullable();
            $table->text('alamat')->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('jurusan')->nullable();
            $table->string('bidang_keahlian')->nullable();
            $table->string('no_sk')->nullable();
            $table->date('tgl_sk')->nullable();
            $table->string('foto_sk')->nullable();
            $table->enum('status_dosen', ['dosen', 'bukan_dosen'])->nullable();
            $table->string('ikatan_kerja')->nullable();
            $table->enum('status_kawin', ['belum_kawin', 'kawin', 'cerai'])->nullable();
            $table->string('nama_sm')->nullable();
            $table->string('nip_sm')->nullable();
            $table->string('nohp_sm')->nullable();
            $table->string('pekerjaan_sm')->nullable();
            $table->string('nama_ibu')->nullable();
            $table->string('masa_jabatan')->nullable();
            $table->date('tgl_sk_jabatan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pegawai');
    }
};
