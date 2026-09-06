<?php

namespace App\Services;

use App\DTOs\PayrollCalculationResult;
use App\Events\PayrollFinalized;
use App\Models\DosenSks;
use App\Models\HonorSks;
use App\Models\Kehadiran;
use App\Models\PayrollDetail;
use App\Models\PayrollRun;
use App\Models\Pegawai;
use App\Models\PotonganGaji;
use App\Models\TahunAkademik;
use App\Models\TunjanganGaji;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class PayrollService
{
    private SksService $sksService;

    public function __construct(?SksService $sksService = null)
    {
        $this->sksService = $sksService ?? new SksService;
    }

    /**
     * Calculate gaji for ONE pegawai in ONE periode. Pure calculation —
     * does NOT persist to DB. Called by calculateBatch() and by preview.
     *
     * Formula (PRD §6):
     *   Gaji Pokok    = jabatan.gaji_pokok
     *   Tj. Transport = jabatan.tj_transport
     *   Uang Makan    = jabatan.uang_makan
     *   Potongan Alpha = alpha_days × active alpha penalty rate
     *   Total Dasar   = Gaji Pokok + Tj. Transport + Uang Makan − Potongan Alpha
     *   Total Tunjangan Tambahan = SUM(active tunjangan_gaji for this pegawai/jabatan)
     *   Total Potongan Tambahan  = SUM(active potongan_gaji except alpha for this pegawai)
     *   Honor Kelebihan SKS = MAX(0, sks_terpakai − sks_maksimal) × honor_per_kategori [via SksService]
     *   TOTAL GAJI = Total Dasar + Total Tunjangan Tambahan + Honor SKS − Total Potongan Tambahan
     *
     * Edge cases:
     *   - No kehadiran record → alpha = 0 (employee still gets paid)
     *   - No active alpha penalty → potongan_alpha = 0
     *   - Negative total → clamp to 0 (PRD §6 aturan wajib)
     */
    public function calculate(Pegawai $pegawai, Carbon $periode): PayrollCalculationResult
    {
        $jabatan = $pegawai->jabatan;

        // Base components from jabatan
        $gajiPokok = (float) $jabatan->gaji_pokok;
        $tjTransport = (float) $jabatan->tj_transport;
        $uangMakan = (float) $jabatan->uang_makan;

        // Alpha penalty: alpha_days × active alpha penalty rate
        $kehadiran = Kehadiran::where('pegawai_id', $pegawai->id)
            ->where('periode', $periode->copy()->startOfMonth())
            ->first();

        $alphaDays = $kehadiran?->alpha ?? 0;

        $alphaPenalty = PotonganGaji::where('is_alpha_penalty', true)
            ->where('aktif', true)
            ->first();

        $potonganAlpha = $alphaDays * (float) ($alphaPenalty?->nilai ?? 0);

        // Total Dasar
        $totalDasar = $gajiPokok + $tjTransport + $uangMakan - $potonganAlpha;

        // ─── Tunjangan Tambahan ─────────────────────────────────
        $tunjangans = TunjanganGaji::where('aktif', true)
            ->where(function ($q) use ($pegawai) {
                $q->where('target_tipe', 'semua')
                    ->orWhere(function ($q2) use ($pegawai) {
                        $q2->where('target_tipe', 'jabatan')
                            ->where('jabatan_id', $pegawai->jabatan_id);
                    })
                    ->orWhere(function ($q2) use ($pegawai) {
                        $q2->where('target_tipe', 'pegawai')
                            ->where('pegawai_id', $pegawai->id);
                    });
            })
            ->get();

        $totalTunjanganTambahan = (float) $tunjangans->sum('nominal');
        $breakdownTunjangan = $tunjangans->map(fn ($t) => [
            'nama' => $t->nama_tunjangan,
            'nominal' => (float) $t->nominal,
        ])->toArray();

        // ─── Potongan Tambahan (except alpha) ───────────────────
        $potongans = PotonganGaji::where('aktif', true)
            ->where('is_alpha_penalty', false)
            ->get();

        $totalPotonganTambahan = (float) $potongans->sum('nilai');
        $breakdownPotongan = $potongans->map(fn ($p) => [
            'nama' => $p->nama_potongan,
            'tipe' => $p->tipe,
            'nilai' => (float) $p->nilai,
        ])->toArray();

        // ─── Honor Kelebihan SKS (PRD FR-31) ──────────────────
        $honorKelebihanSks = $this->sksService->hitungHonorKelebihan($pegawai, $periode);
        $breakdownSks = $this->buildSksBreakdown($pegawai, $periode);

        // ─── TOTAL ─────────────────────────────────────────────
        $totalGaji = $totalDasar + $totalTunjanganTambahan + $honorKelebihanSks - $totalPotonganTambahan;

        // Clamp negative to 0 (PRD §6 aturan wajib)
        if ($totalGaji < 0) {
            Log::warning("PayrollService: negative total_gaji clamped to 0 for pegawai {$pegawai->id} periode {$periode->format('Y-m')}", [
                'total_gaji_before_clamp' => $totalGaji,
            ]);
            $totalGaji = 0;
        }

        return new PayrollCalculationResult(
            pegawaiId: $pegawai->id,
            periode: $periode->format('Y-m-d'),
            gajiPokok: $gajiPokok,
            tjTransport: $tjTransport,
            uangMakan: $uangMakan,
            potonganAlpha: $potonganAlpha,
            totalTunjanganTambahan: $totalTunjanganTambahan,
            totalPotonganTambahan: $totalPotonganTambahan,
            honorKelebihanSks: $honorKelebihanSks,
            totalGaji: $totalGaji,
            breakdownTunjangan: $breakdownTunjangan,
            breakdownPotongan: $breakdownPotongan,
            breakdownSks: $breakdownSks,
        );
    }

    private function buildSksBreakdown(Pegawai $pegawai, Carbon $periode): ?array
    {
        if ($pegawai->status_dosen !== 'dosen') {
            return null;
        }

        $ta = TahunAkademik::where('aktif', true)->first();
        if (! $ta) {
            return null;
        }

        $dosenSks = DosenSks::where('pegawai_id', $pegawai->id)
            ->where('tahun_akademik_id', $ta->id)
            ->first();

        if (! $dosenSks) {
            return null;
        }

        $kelebihan = max(0, $dosenSks->sks_terpakai - $dosenSks->sks_maksimal);
        $honorRecord = HonorSks::join('kategori_honor_sks', 'kategori_honor_sks.id', '=', 'honor_sks.kategori_honor_sks_id')
            ->select('honor_sks.honor')
            ->first();

        $honorPerSks = $honorRecord ? (float) $honorRecord->honor : 0;

        return [
            'sks_terpakai' => $dosenSks->sks_terpakai,
            'sks_maksimal' => $dosenSks->sks_maksimal,
            'kelebihan' => $kelebihan,
            'honor_per_sks' => $honorPerSks,
            'total' => $kelebihan * $honorPerSks,
        ];
    }

    /**
     * Calculate for ALL active pegawai in a periode, persist as
     * PayrollRun + PayrollDetails (status: calculated). Idempotent —
     * if a non-finalized run exists for this periode, replace it.
     */
    public function calculateBatch(Carbon $periode, User $calculatedBy): PayrollRun
    {
        $periodeStart = $periode->copy()->startOfMonth();

        return DB::transaction(function () use ($periodeStart, $calculatedBy) {
            // Idempotent: delete existing non-finalized run for this periode
            $existingRun = PayrollRun::where('periode', $periodeStart)->first();

            if ($existingRun && $existingRun->status === 'finalized') {
                throw new \RuntimeException("Payroll run for {$periodeStart->format('Y-m')} is already finalized. Void it first before recalculating.");
            }

            if ($existingRun) {
                $existingRun->details()->delete();
                $run = $existingRun;
                $run->update([
                    'status' => 'calculated',
                    'calculated_by' => $calculatedBy->id,
                    'calculated_at' => now(),
                ]);
            } else {
                $run = PayrollRun::create([
                    'periode' => $periodeStart,
                    'status' => 'calculated',
                    'calculated_by' => $calculatedBy->id,
                    'calculated_at' => now(),
                ]);
            }

            // Get all active pegawai
            $pegawais = Pegawai::where('status_pegawai', 'aktif')->get();

            foreach ($pegawais as $pegawai) {
                $result = $this->calculate($pegawai, $periodeStart);

                PayrollDetail::create([
                    'payroll_run_id' => $run->id,
                    'pegawai_id' => $result->pegawaiId,
                    'gaji_pokok' => $result->gajiPokok,
                    'tj_transport' => $result->tjTransport,
                    'uang_makan' => $result->uangMakan,
                    'potongan_alpha' => $result->potonganAlpha,
                    'total_tunjangan_tambahan' => $result->totalTunjanganTambahan,
                    'total_potongan_tambahan' => $result->totalPotonganTambahan,
                    'honor_kelebihan_sks' => $result->honorKelebihanSks,
                    'total_gaji' => $result->totalGaji,
                    'breakdown_json' => $result->toBreakdownJson(),
                ]);
            }

            return $run->fresh();
        });
    }

    /**
     * Finalize a run — locks it from further calculation.
     * Emits PayrollFinalized event.
     */
    public function finalize(PayrollRun $run, User $finalizedBy): PayrollRun
    {
        if ($run->status !== 'calculated') {
            throw new \RuntimeException("Only 'calculated' runs can be finalized. Current status: {$run->status}");
        }

        $run->update([
            'status' => 'finalized',
            'finalized_by' => $finalizedBy->id,
            'finalized_at' => now(),
        ]);

        event(new PayrollFinalized($run));

        return $run->fresh();
    }

    /**
     * Void a finalized run. WAJIB alasan. Run stays as 'void' (audit trail).
     */
    public function void(PayrollRun $run, string $reason, User $voidedBy): PayrollRun
    {
        if ($run->status !== 'finalized') {
            throw new \RuntimeException("Only 'finalized' runs can be voided. Current status: {$run->status}");
        }

        if (empty(trim($reason))) {
            throw new \RuntimeException('Void reason is required.');
        }

        $run->update([
            'status' => 'void',
            'void_reason' => $reason,
        ]);

        return $run->fresh();
    }
}
