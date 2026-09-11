<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayrollSetting;
use App\Models\PayrollSettingLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PayrollSettingsController extends Controller
{
    private const FIELD_LABELS = [
        'tunjangan_makan_per_hari' => 'Tunjangan Makan per Hari',
        'lembur_basis_gaji_dasar' => 'Basis Perhitungan Lembur',
        'lembur_jam_kerja_sebulan' => 'Jam Kerja per Bulan',
        'tunjangan_variabel_default_percentage' => 'Persentase Tunjangan Variabel',
        'bpjs_kesehatan_aktif' => 'BPJS Kesehatan Aktif',
        'bpjs_tk_aktif' => 'BPJS Ketenagakerjaan Aktif',
        'bpjs_tk_kategori_risiko_jkk' => 'Kategori Risiko JKK',
        'potongan_bpjs_kes_pct' => 'Potongan BPJS Kesehatan (%)',
        'potongan_bpjs_tk_jkk_pct' => 'Potongan BPJS TK JKK (%)',
        'potongan_bpjs_tk_jkm_pct' => 'Potongan BPJS TK JKM (%)',
        'potongan_bpjs_tk_jht_pct' => 'Potongan BPJS TK JHT (%)',
        'potongan_bpjs_tk_jp_pct' => 'Potongan BPJS TK JP (%)',
        'potongan_sosial_pct' => 'Potongan Iuran Sosial (%)',
        'potongan_pendidikan_anak_pct' => 'Potongan Iuran Pendidikan Anak (%)',
    ];

    public function index(): Response
    {
        $settings = PayrollSetting::instance();

        return Inertia::render('Admin/PayrollSettings/Index', [
            'settings' => $settings,
            'logs' => PayrollSettingLog::with('updatedBy')
                ->orderByDesc('created_at')
                ->limit(50)
                ->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
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
            // UA-2025 Potongan validation
            'potongan_bpjs_kes_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'potongan_bpjs_tk_jkk_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'potongan_bpjs_tk_jkm_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'potongan_bpjs_tk_jht_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'potongan_bpjs_tk_jp_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'potongan_sosial_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'potongan_pendidikan_anak_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $settings = PayrollSetting::instance();
        $userId = $request->user()->id;

        $fieldToConfirmedKey = [
            'tunjangan_makan_per_hari' => 'is_confirmed_tunjangan_makan',
            'lembur_basis_gaji_dasar' => 'is_confirmed_lembur_basis',
            'lembur_jam_kerja_sebulan' => 'is_confirmed_lembur_jam',
            'tunjangan_variabel_default_percentage' => 'is_confirmed_tunjangan_variabel',
            'bpjs_kesehatan_aktif' => 'is_confirmed_bpjs_kes',
            'bpjs_tk_aktif' => 'is_confirmed_bpjs_tk',
            'bpjs_tk_kategori_risiko_jkk' => 'is_confirmed_bpjs_tk_risiko',
            'potongan_bpjs_kes_pct' => 'is_confirmed_ua_potongan',
            'potongan_bpjs_tk_jkk_pct' => 'is_confirmed_ua_potongan',
            'potongan_bpjs_tk_jkm_pct' => 'is_confirmed_ua_potongan',
            'potongan_bpjs_tk_jht_pct' => 'is_confirmed_ua_potongan',
            'potongan_bpjs_tk_jp_pct' => 'is_confirmed_ua_potongan',
            'potongan_sosial_pct' => 'is_confirmed_ua_potongan',
            'potongan_pendidikan_anak_pct' => 'is_confirmed_ua_potongan',
        ];

        foreach ($validated as $field => $newValue) {
            $oldValue = $settings->{$field};

            if ((string) $oldValue === (string) $newValue) {
                continue;
            }

            // Log the change
            PayrollSettingLog::create([
                'field_name' => $field,
                'nilai_lama' => $oldValue,
                'nilai_baru' => $newValue,
                'updated_by' => $userId,
            ]);

            $settings->{$field} = $newValue;

            // Auto-confirm when admin explicitly sets a value
            if (isset($fieldToConfirmedKey[$field])) {
                $settings->{$fieldToConfirmedKey[$field]} = true;
            }
        }

        $settings->updated_by = $userId;
        $settings->save();

        PayrollSetting::clearCache();

        return redirect()->route('admin.payroll-settings.index')
            ->with('success', 'Pengaturan payroll berhasil diperbarui.');
    }
}
