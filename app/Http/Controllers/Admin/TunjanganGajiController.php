<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTunjanganGajiRequest;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\TunjanganGaji;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TunjanganGajiController extends Controller
{
    public function index(Request $request): Response
    {
        $tunjangans = TunjanganGaji::query()
            ->with(['jabatan', 'pegawai'])
            ->when($request->target_tipe, fn ($q, $t) => $q->where('target_tipe', $t))
            ->orderBy('nama_tunjangan')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/TunjanganGaji/Index', [
            'tunjangans' => $tunjangans,
            'jabatans' => Jabatan::orderBy('nama_jabatan')->get(['id', 'nama_jabatan']),
            'pegawais' => Pegawai::orderBy('nama_pegawai')->get(['id', 'nik', 'nama_pegawai']),
            'filters' => $request->only(['target_tipe']),
            'can' => [
                'create' => $request->user()->can('create', TunjanganGaji::class),
                'update' => $request->user()->hasRole('admin'),
                'delete' => $request->user()->hasRole('admin'),
            ],
        ]);
    }

    public function store(StoreTunjanganGajiRequest $request): RedirectResponse
    {
        TunjanganGaji::create($request->validated());

        return redirect()->route('admin.tunjangan-gaji.index')->with('success', 'Tunjangan gaji berhasil ditambahkan.');
    }

    public function update(StoreTunjanganGajiRequest $request, TunjanganGaji $tunjangan_gaji): RedirectResponse
    {
        $tunjangan_gaji->update($request->validated());

        return redirect()->route('admin.tunjangan-gaji.index')->with('success', 'Tunjangan gaji berhasil diperbarui.');
    }

    public function toggle(TunjanganGaji $tunjangan_gaji): RedirectResponse
    {
        $tunjangan_gaji->update(['aktif' => ! $tunjangan_gaji->aktif]);

        return redirect()->route('admin.tunjangan-gaji.index')->with('success', 'Status tunjangan gaji berhasil diubah.');
    }

    public function destroy(TunjanganGaji $tunjangan_gaji): RedirectResponse
    {
        $tunjangan_gaji->delete();

        return redirect()->route('admin.tunjangan-gaji.index')->with('success', 'Tunjangan gaji berhasil dihapus.');
    }
}
