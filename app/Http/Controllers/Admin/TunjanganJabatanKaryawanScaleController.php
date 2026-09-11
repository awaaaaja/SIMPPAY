<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GolonganRuang;
use App\Models\TunjanganJabatanKaryawanScale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TunjanganJabatanKaryawanScaleController extends Controller
{
    public function index(Request $request): Response
    {
        $items = TunjanganJabatanKaryawanScale::with('golonganRuang')
            ->when($request->search, fn ($q, $s) => $q->whereHas('golonganRuang', fn ($gq) => $gq->where('kode', 'like', "%{$s}%")->orWhere('nama', 'like', "%{$s}%")))
            ->orderBy('golongan_ruang_id')
            ->paginate(20)
            ->withQueryString();

        $golongans = GolonganRuang::orderBy('urutan')->get();

        return Inertia::render('Admin/TunjanganJabatanKaryawanScale/Index', [
            'items' => $items,
            'golongans' => $golongans,
            'can' => [
                'create' => $request->user()->can('create', TunjanganJabatanKaryawanScale::class),
                'update' => $request->user()->hasRole('admin'),
                'delete' => $request->user()->hasRole('admin'),
            ],
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', TunjanganJabatanKaryawanScale::class);

        $validated = $request->validate([
            'golongan_ruang_id' => ['required', 'exists:golongan_ruang,id'],
            'nominal' => ['required', 'numeric', 'min:0'],
        ]);

        TunjanganJabatanKaryawanScale::create($validated);

        return redirect()->route('admin.tunjangan-jabatan-karyawan-scale.index')->with('success', 'Data berhasil ditambahkan.');
    }

    public function edit(Request $request, TunjanganJabatanKaryawanScale $tunjangan_jabatan_karyawan_scale): Response
    {
        $this->authorize('update', $tunjangan_jabatan_karyawan_scale);

        return Inertia::render('Admin/TunjanganJabatanKaryawanScale/Edit', [
            'item' => $tunjangan_jabatan_karyawan_scale->load('golonganRuang'),
            'golongans' => GolonganRuang::orderBy('urutan')->get(),
        ]);
    }

    public function update(Request $request, TunjanganJabatanKaryawanScale $tunjangan_jabatan_karyawan_scale): RedirectResponse
    {
        $validated = $request->validate([
            'golongan_ruang_id' => ['required', 'exists:golongan_ruang,id'],
            'nominal' => ['required', 'numeric', 'min:0'],
        ]);

        $tunjangan_jabatan_karyawan_scale->update($validated);

        return redirect()->route('admin.tunjangan-jabatan-karyawan-scale.index')->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(Request $request, TunjanganJabatanKaryawanScale $tunjangan_jabatan_karyawan_scale): RedirectResponse
    {
        $this->authorize('delete', $tunjangan_jabatan_karyawan_scale);
        $tunjangan_jabatan_karyawan_scale->delete();

        return redirect()->route('admin.tunjangan-jabatan-karyawan-scale.index')->with('success', 'Data berhasil dihapus.');
    }
}
