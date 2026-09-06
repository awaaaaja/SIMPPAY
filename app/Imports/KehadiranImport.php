<?php

namespace App\Imports;

use App\Models\Kehadiran;
use App\Models\Pegawai;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class KehadiranImport implements ToModel, WithHeadingRow, WithValidation
{
    protected string $periode;

    public function __construct(string $periode)
    {
        $this->periode = $periode;
    }

    public function model(array $row)
    {
        $nik = trim($row['nik'] ?? '');
        if ($nik === '') {
            return null;
        }

        $pegawai = Pegawai::where('nik', $nik)->first();
        if (! $pegawai) {
            return null;
        }

        return new Kehadiran([
            'pegawai_id' => $pegawai->id,
            'periode' => $this->periode,
            'hadir' => max(0, (int) ($row['hadir'] ?? 0)),
            'sakit' => max(0, (int) ($row['sakit'] ?? 0)),
            'alpha' => max(0, (int) ($row['alpha'] ?? 0)),
        ]);
    }

    public function rules(): array
    {
        return [
            'nik' => ['required', 'string'],
            'hadir' => ['required', 'integer', 'min:0'],
            'sakit' => ['required', 'integer', 'min:0'],
            'alpha' => ['required', 'integer', 'min:0'],
        ];
    }
}
