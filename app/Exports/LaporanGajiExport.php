<?php

namespace App\Exports;

use App\Models\PayrollDetail;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanGajiExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        private string $periode,
        private ?int $jabatanId = null,
        private ?int $pegawaiId = null,
    ) {}

    public function query()
    {
        $periode = Carbon::parse($this->periode);

        $query = PayrollDetail::query()
            ->whereHas('payrollRun', fn ($q) => $q->where('periode', $periode))
            ->with(['pegawai.jabatan']);

        if ($this->jabatanId) {
            $query->whereHas('pegawai', fn ($q) => $q->where('jabatan_id', $this->jabatanId));
        }

        if ($this->pegawaiId) {
            $query->where('pegawai_id', $this->pegawaiId);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'No',
            'NIK',
            'Nama Pegawai',
            'Jabatan',
            'Gaji Pokok',
            'Tj. Transport',
            'Uang Makan',
            'Potongan Alpha',
            'Tunj. Tambahan',
            'Pot. Tambahan',
            'Honor SKS',
            'Total Gaji',
        ];
    }

    public function map($detail): array
    {
        static $no = 0;

        return [
            ++$no,
            $detail->pegawai->nik,
            $detail->pegawai->nama_pegawai,
            $detail->pegawai->jabatan->nama_jabatan ?? '-',
            $detail->gaji_pokok,
            $detail->tj_transport,
            $detail->uang_makan,
            $detail->potongan_alpha,
            $detail->total_tunjangan_tambahan,
            $detail->total_potongan_tambahan,
            $detail->honor_kelebihan_sks,
            $detail->total_gaji,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
