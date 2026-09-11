<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BebanSksJabatan;
use App\Models\GajiPokokScale;
use App\Models\GolonganRuang;
use App\Models\HonorSksScale;
use App\Models\JabatanStrukturalPoin;
use App\Models\KlasifikasiJabatanStruktural;
use App\Models\LevelStruktur;
use App\Models\TunjanganFungsionalDosenScale;
use App\Models\TunjanganJabatanKaryawanScale;
use App\Models\TunjanganTransportasiScale;
use App\Models\TunjanganVariabelScale;
use Inertia\Inertia;
use Inertia\Response;

class ReferensiController extends Controller
{
    public function golonganGajiPokok(): Response
    {
        return Inertia::render('Admin/Referensi/GolonganGajiPokok', [
            'golongans' => GolonganRuang::orderBy('urutan')->get(),
            'scales' => GajiPokokScale::with('golonganRuang')->orderBy('golongan_ruang_id')->orderBy('mkg')->get(),
        ]);
    }

    public function jabatanStruktural(): Response
    {
        return Inertia::render('Admin/Referensi/JabatanStruktural', [
            'levels' => LevelStruktur::orderBy('urutan')->get(),
            'klasifikasi' => KlasifikasiJabatanStruktural::orderBy('klasifikasi')->get(),
            'jabatan' => JabatanStrukturalPoin::with(['levelStruktur', 'klasifikasi'])->orderBy('level_struktur_id')->orderBy('total_poin', 'desc')->get(),
        ]);
    }

    public function tunjanganKomponen(): Response
    {
        return Inertia::render('Admin/Referensi/TunjanganKomponen', [
            'tjKaryawan' => TunjanganJabatanKaryawanScale::with('golonganRuang')->orderBy('golongan_ruang_id')->get(),
            'tjFungsional' => TunjanganFungsionalDosenScale::orderBy('jabatan_fungsional')->get(),
            'tjVariabel' => TunjanganVariabelScale::with('golonganRuang')->orderBy('golongan_ruang_id')->get(),
            'tjTransport' => TunjanganTransportasiScale::with('levelStruktur')->orderBy('level_struktur_id')->get(),
        ]);
    }

    public function dosenSks(): Response
    {
        return Inertia::render('Admin/Referensi/DosenSks', [
            'honorSks' => HonorSksScale::orderBy('program')->orderBy('status_dosen')->orderBy('strata')->get(),
            'bebanSks' => BebanSksJabatan::with('jabatanStrukturalPoin')->get(),
        ]);
    }
}
