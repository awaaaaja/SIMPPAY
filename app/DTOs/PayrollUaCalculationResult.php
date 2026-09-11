<?php

namespace App\DTOs;

readonly class PayrollUaCalculationResult
{
    public function __construct(
        public int $pegawaiId,
        public string $periode,
        // ─── Pendapatan ────────────────────────────────────
        public float $gajiPokok,
        public float $tunjanganJabatan,
        public float $tunjanganFungsional,
        public float $tunjanganStruktural,
        public float $tunjanganVariabel,
        public float $tunjanganIstri,
        public float $tunjanganAnak,
        public float $tunjanganMakan,
        public float $bpjsTkIncome,
        public float $tunjanganTransportasi,
        public float $penyesuaian,
        public float $lembur,
        public float $honorKelebihanSks,
        public float $rapel,
        public float $jumlah,
        // ─── Potongan ──────────────────────────────────────
        public float $potonganMakan,
        public float $potonganBpjs,
        public float $potonganBpjsTk,
        public float $potonganPendidikanAnak,
        public float $potonganSosial,
        public float $potonganUjks,
        public float $potonganKkb,
        public float $potonganBtnBns,
        public float $potonganLainLain,
        public float $jumlahPotongan,
        // ─── Final ─────────────────────────────────────────
        public float $thp,
        // ─── Breakdown metadata ────────────────────────────
        public ?array $breakdown = null,
    ) {}

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public function toBreakdownJson(): array
    {
        return $this->breakdown ?? [];
    }
}
