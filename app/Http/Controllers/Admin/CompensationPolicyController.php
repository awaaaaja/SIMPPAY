<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GolonganRuang;
use App\Models\PayrollSetting;
use App\Models\PayrollSettingLog;
use App\Models\TunjanganJabatanKaryawanScale;
use App\Models\TunjanganJabatanKaryawanScaleLog;
use App\Models\TunjanganVariabelScale;
use App\Models\TunjanganVariabelScaleLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompensationPolicyController extends Controller
{
    public function index(): Response
    {
        $settings = PayrollSetting::instance();
        $scalesJabatan = TunjanganJabatanKaryawanScale::with('golonganRuang')->orderBy('golongan_ruang_id')->get();
        $scalesVariabel = TunjanganVariabelScale::with('golonganRuang')->orderBy('golongan_ruang_id')->get();

        $logs = collect();
        $logs = $logs->concat(
            PayrollSettingLog::with('updatedBy')->orderByDesc('created_at')->limit(20)->get()
                ->map(fn ($l) => ['type' => 'payroll_setting', 'field' => $l->field_name, 'lama' => $l->nilai_lama, 'baru' => $l->nilai_baru, 'role' => $l->role, 'user' => $l->updatedBy?->name, 'at' => $l->created_at])
        );
        $logs = $logs->concat(
            TunjanganJabatanKaryawanScaleLog::with(['updatedBy', 'golonganRuang'])->orderByDesc('created_at')->limit(20)->get()
                ->map(fn ($l) => ['type' => 'tj_karyawan', 'field' => $l->golonganRuang?->kode ?? '-', 'lama' => $l->nominal_lama, 'baru' => $l->nominal_baru, 'role' => $l->role, 'user' => $l->updatedBy?->name, 'at' => $l->created_at])
        );
        $logs = $logs->concat(
            TunjanganVariabelScaleLog::with(['updatedBy', 'golonganRuang'])->orderByDesc('created_at')->limit(20)->get()
                ->map(fn ($l) => ['type' => 'tj_variabel', 'field' => $l->golonganRuang?->kode ?? '-', 'lama' => $l->nominal_lama, 'baru' => $l->nominal_baru, 'role' => $l->role, 'user' => $l->updatedBy?->name, 'at' => $l->created_at])
        );
        $logs = $logs->sortByDesc('at')->values()->take(50);

        return Inertia::render('Admin/Kebijakan/Index', [
            'settings' => $settings,
            'scalesJabatan' => $scalesJabatan,
            'scalesVariabel' => $scalesVariabel,
            'logs' => $logs,
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $this->authorize('update', PayrollSetting::class);

        $validated = $request->validate([
            'tunjangan_makan_per_hari' => ['nullable', 'numeric', 'min:0'],
            'lembur_basis_gaji_dasar' => ['required', 'in:gaji_pokok_saja,gaji_pokok_plus_tunjangan_tetap'],
            'lembur_jam_kerja_sebulan' => ['required', 'integer', 'min:1'],
            'tunjangan_variabel_default_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'bpjs_kesehatan_aktif' => ['required', 'boolean'],
            'bpjs_tk_aktif' => ['required', 'boolean'],
            'bpjs_tk_kategori_risiko_jkk' => ['nullable', 'in:sangat_rendah,rendah,sedang,tinggi,sangat_tinggi'],
        ]);

        $settings = PayrollSetting::instance();
        $userId = $request->user()->id;
        $userRole = $request->user()->getRoleNames()->first();

        $fieldToConfirmedKey = [
            'tunjangan_makan_per_hari' => 'is_confirmed_tunjangan_makan',
            'lembur_basis_gaji_dasar' => 'is_confirmed_lembur_basis',
            'lembur_jam_kerja_sebulan' => 'is_confirmed_lembur_jam',
            'tunjangan_variabel_default_percentage' => 'is_confirmed_tunjangan_variabel',
            'bpjs_kesehatan_aktif' => 'is_confirmed_bpjs_kes',
            'bpjs_tk_aktif' => 'is_confirmed_bpjs_tk',
            'bpjs_tk_kategori_risiko_jkk' => 'is_confirmed_bpjs_tk_risiko',
        ];

        foreach ($validated as $field => $newValue) {
            $oldValue = $settings->{$field};

            if ((string) $oldValue === (string) $newValue) {
                continue;
            }

            PayrollSettingLog::create([
                'field_name' => $field,
                'nilai_lama' => $oldValue,
                'nilai_baru' => $newValue,
                'updated_by' => $userId,
                'role' => $userRole,
            ]);

            $settings->{$field} = $newValue;

            if (isset($fieldToConfirmedKey[$field])) {
                $settings->{$fieldToConfirmedKey[$field]} = true;
            }
        }

        $settings->updated_by = $userId;
        $settings->save();

        PayrollSetting::clearCache();

        return redirect()->route($this->getRoutePrefix($request) . '.kebijakan-kompensasi.index')
            ->with('success', 'Pengaturan payroll berhasil diperbarui.');
    }

    public function updateScalesJabatan(Request $request): RedirectResponse
    {
        abort_unless($request->user()->hasAnyRole(['admin', 'bpsdm']), 403);

        $validated = $request->validate([
            'scales' => ['required', 'array'],
            'scales.*.id' => ['required', 'exists:tunjangan_jabatan_karyawan_scale,id'],
            'scales.*.nominal' => ['required', 'numeric', 'min:0'],
        ]);

        $userId = $request->user()->id;
        $userRole = $request->user()->getRoleNames()->first();

        foreach ($validated['scales'] as $item) {
            $scale = TunjanganJabatanKaryawanScale::find($item['id']);
            $oldNominal = (float) $scale->nominal;
            $newNominal = (float) $item['nominal'];

            if ($oldNominal === $newNominal) {
                continue;
            }

            TunjanganJabatanKaryawanScaleLog::create([
                'scale_id' => $scale->id,
                'golongan_ruang_id' => $scale->golongan_ruang_id,
                'nominal_lama' => $oldNominal,
                'nominal_baru' => $newNominal,
                'updated_by' => $userId,
                'role' => $userRole,
            ]);

            $scale->update(['nominal' => $newNominal]);
        }

        return redirect()->route($this->getRoutePrefix($request) . '.kebijakan-kompensasi.index')
            ->with('success', 'Tunjangan Jabatan Karyawan berhasil diperbarui.');
    }

    public function updateScalesVariabel(Request $request): RedirectResponse
    {
        abort_unless($request->user()->hasAnyRole(['admin', 'bpsdm']), 403);

        $validated = $request->validate([
            'scales' => ['required', 'array'],
            'scales.*.id' => ['required', 'exists:tunjangan_variabel_scale,id'],
            'scales.*.nominal_maksimum' => ['required', 'numeric', 'min:0'],
        ]);

        $userId = $request->user()->id;
        $userRole = $request->user()->getRoleNames()->first();

        foreach ($validated['scales'] as $item) {
            $scale = TunjanganVariabelScale::find($item['id']);
            $oldNominal = (float) $scale->nominal_maksimum;
            $newNominal = (float) $item['nominal_maksimum'];

            if ($oldNominal === $newNominal) {
                continue;
            }

            TunjanganVariabelScaleLog::create([
                'scale_id' => $scale->id,
                'golongan_ruang_id' => $scale->golongan_ruang_id,
                'nominal_lama' => $oldNominal,
                'nominal_baru' => $newNominal,
                'updated_by' => $userId,
                'role' => $userRole,
            ]);

            $scale->update(['nominal_maksimum' => $newNominal]);
        }

        return redirect()->route($this->getRoutePrefix($request) . '.kebijakan-kompensasi.index')
            ->with('success', 'Tunjangan Variabel berhasil diperbarui.');
    }

    private function getRoutePrefix(Request $request): string
    {
        return $request->user()->hasRole('admin') ? 'admin' : 'bpsdm';
    }
}
