<?php

namespace App\Imports;

use App\Models\Fungsional;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\Struktural;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PegawaiImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        $nik = trim($row['nik'] ?? '');
        if ($nik === '') {
            return null;
        }

        $jabatanId = null;
        if (! empty($row['jabatan'])) {
            $jabatan = Jabatan::firstOrCreate(
                ['nama_jabatan' => trim($row['jabatan'])],
                ['gaji_pokok' => 0, 'tj_transport' => 0, 'uang_makan' => 0]
            );
            $jabatanId = $jabatan->id;
        }

        $strukturalId = null;
        if (! empty($row['struktural'])) {
            $struktural = Struktural::firstOrCreate(
                ['nama_struktural' => trim($row['struktural'])]
            );
            $strukturalId = $struktural->id;
        }

        $fungsionalId = null;
        if (! empty($row['fungsional'])) {
            $fungsional = Fungsional::firstOrCreate(
                ['nama_fungsional' => trim($row['fungsional'])]
            );
            $fungsionalId = $fungsional->id;
        }

        return new Pegawai([
            'nik' => $nik,
            'nama_pegawai' => trim($row['nama_pegawai'] ?? ''),
            'jenis_kelamin' => Str::upper(substr(trim($row['jenis_kelamin'] ?? 'L'), 0, 1)),
            'jabatan_id' => $jabatanId,
            'struktural_id' => $strukturalId,
            'fungsional_id' => $fungsionalId,
            'status_pegawai' => strtolower(trim($row['status'] ?? 'aktif')),
            'tanggal_masuk' => $this->parseDate($row['tanggal_masuk'] ?? null),
            'email' => $row['email'] ?? null,
            'no_hp' => $row['no_hp'] ?? null,
            'status_dosen' => $row['status_dosen'] ?? null,
            'ikatan_kerja' => $row['ikatan_kerja'] ?? null,
            'status_kawin' => $row['status_kawin'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'nik' => ['required', 'string', 'max:255'],
            'nama_pegawai' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', 'string'],
            'status' => ['nullable', 'string'],
        ];
    }

    protected function parseDate($value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Exception) {
            return null;
        }
    }
}
