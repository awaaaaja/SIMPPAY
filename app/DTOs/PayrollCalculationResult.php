<?php

namespace App\DTOs;

readonly class PayrollCalculationResult
{
    public function __construct(
        public int $pegawaiId,
        public string $periode,
        public float $gajiPokok,
        public float $tjTransport,
        public float $uangMakan,
        public float $potonganAlpha,
        public float $totalTunjanganTambahan,
        public float $totalPotonganTambahan,
        public float $honorKelebihanSks,
        public float $totalGaji,
        public ?array $breakdownTunjangan,
        public ?array $breakdownPotongan,
        public ?array $breakdownSks = null,
    ) {}

    public function toArray(): array
    {
        return [
            'gaji_pokok' => $this->gajiPokok,
            'tj_transport' => $this->tjTransport,
            'uang_makan' => $this->uangMakan,
            'potongan_alpha' => $this->potonganAlpha,
            'total_tunjangan_tambahan' => $this->totalTunjanganTambahan,
            'total_potongan_tambahan' => $this->totalPotonganTambahan,
            'honor_kelebihan_sks' => $this->honorKelebihanSks,
            'total_gaji' => $this->totalGaji,
        ];
    }

    public function toBreakdownJson(): array
    {
        return [
            'tunjangan' => $this->breakdownTunjangan ?? [],
            'potongan' => $this->breakdownPotongan ?? [],
            'honor_sks' => $this->breakdownSks ?? [],
        ];
    }
}
