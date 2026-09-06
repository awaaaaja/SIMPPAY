<?php

namespace App\Exports;

use App\Models\Kehadiran;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanAbsensiExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private string $periode,
    ) {}

    public function query()
    {
        $periode = Carbon::parse($this->periode);

        return Kehadiran::where('periode', $periode)
            ->with('pegawai.jabatan');
    }

    public function headings(): array
    {
        return [
            'No',
            'NIK',
            'Nama Pegawai',
            'Jabatan',
            'Hadir',
            'Sakit',
            'Alpha',
        ];
    }

    public function map($kehadiran): array
    {
        static $no = 0;

        return [
            ++$no,
            $kehadiran->pegawai->nik,
            $kehadiran->pegawai->nama_pegawai,
            $kehadiran->pegawai->jabatan->nama_jabatan ?? '-',
            $kehadiran->hadir,
            $kehadiran->sakit,
            $kehadiran->alpha,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
