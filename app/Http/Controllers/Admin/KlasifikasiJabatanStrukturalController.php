<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KlasifikasiJabatanStruktural;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KlasifikasiJabatanStrukturalController extends Controller
{
    public function index(Request $request): Response
    {
        $items = KlasifikasiJabatanStruktural::query()
            ->when($request->search, fn ($q, $s) => $q->where('klasifikasi', 'like', "%{$s}%")->orWhere('level_jabatan', 'like', "%{$s}%"))
            ->orderBy('klasifikasi')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/KlasifikasiJabatanStruktural/Index', [
            'items' => $items,
            'can' => [
                'create' => $request->user()->can('create', KlasifikasiJabatanStruktural::class),
                'update' => $request->user()->hasRole('admin'),
                'delete' => $request->user()->hasRole('admin'),
            ],
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', KlasifikasiJabatanStruktural::class);

        $validated = $request->validate([
            'klasifikasi' => ['required', 'integer', 'min:1', 'max:6', 'unique:klasifikasi_jabatan_struktural,klasifikasi'],
            'poin_min' => ['required', 'integer', 'min:0'],
            'poin_max' => ['required', 'integer', 'min:0'],
            'level_jabatan' => ['required', 'string', 'max:100'],
            'tunjangan_min' => ['required', 'numeric', 'min:0'],
            'tunjangan_max' => ['required', 'numeric', 'min:0'],
        ]);

        KlasifikasiJabatanStruktural::create($validated);

        return redirect()->route('admin.klasifikasi-jabatan-struktural.index')->with('success', 'Data berhasil ditambahkan.');
    }

    public function edit(Request $request, KlasifikasiJabatanStruktural $klasifikasi_jabatan_struktural): Response
    {
        $this->authorize('update', $klasifikasi_jabatan_struktural);

        return Inertia::render('Admin/KlasifikasiJabatanStruktural/Edit', ['item' => $klasifikasi_jabatan_struktural]);
    }

    public function update(Request $request, KlasifikasiJabatanStruktural $klasifikasi_jabatan_struktural): RedirectResponse
    {
        $validated = $request->validate([
            'klasifikasi' => ['required', 'integer', "unique:klasifikasi_jabatan_struktural,klasifikasi,{$klasifikasi_jabatan_struktural->id}"],
            'poin_min' => ['required', 'integer', 'min:0'],
            'poin_max' => ['required', 'integer', 'min:0'],
            'level_jabatan' => ['required', 'string', 'max:100'],
            'tunjangan_min' => ['required', 'numeric', 'min:0'],
            'tunjangan_max' => ['required', 'numeric', 'min:0'],
        ]);

        $klasifikasi_jabatan_struktural->update($validated);

        return redirect()->route('admin.klasifikasi-jabatan-struktural.index')->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(Request $request, KlasifikasiJabatanStruktural $klasifikasi_jabatan_struktural): RedirectResponse
    {
        $this->authorize('delete', $klasifikasi_jabatan_struktural);
        $klasifikasi_jabatan_struktural->delete();

        return redirect()->route('admin.klasifikasi-jabatan-struktural.index')->with('success', 'Data berhasil dihapus.');
    }
}
