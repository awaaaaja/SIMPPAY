<?php

namespace App\Exports;

use App\Models\Pegawai;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PegawaiExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Pegawai::with(['jabatan', 'struktural', 'fungsional'])
            ->orderBy('nama_pegawai')
            ->get();
    }

    public function headings(): array
    {
        return [
            'NIK',
            'Nama Pegawai',
            'Jenis Kelamin',
            'Jabatan',
            'Struktural',
            'Fungsional',
            'Status',
            'Tanggal Masuk',
            'Email',
            'No. HP',
            'Status Dosen',
            'Ikatan Kerja',
            'Status Kawin',
        ];
    }

    public function map($pegawai): array
    {
        return [
            $pegawai->nik,
            $pegawai->nama_pegawai,
            $pegawai->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan',
            $pegawai->jabatan?->nama_jabatan ?? '',
            $pegawai->struktural?->nama_struktural ?? '',
            $pegawai->fungsional?->nama_fungsional ?? '',
            $pegawai->status_pegawai,
            $pegawai->tanggal_masuk?->format('d/m/Y') ?? '',
            $pegawai->email ?? '',
            $pegawai->no_hp ?? '',
            $pegawai->status_dosen ?? '',
            $pegawai->ikatan_kerja ?? '',
            $pegawai->status_kawin ?? '',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
