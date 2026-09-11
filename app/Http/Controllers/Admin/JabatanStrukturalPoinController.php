<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JabatanStrukturalPoin;
use App\Models\KlasifikasiJabatanStruktural;
use App\Models\LevelStruktur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JabatanStrukturalPoinController extends Controller
{
    public function index(Request $request): Response
    {
        $items = JabatanStrukturalPoin::with(['levelStruktur', 'klasifikasi'])
            ->when($request->search, fn ($q, $s) => $q->where('nama_jabatan', 'like', "%{$s}%"))
            ->orderBy('level_struktur_id')
            ->orderByDesc('total_poin')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('Admin/JabatanStrukturalPoin/Index', [
            'items' => $items,
            'can' => [
                'create' => $request->user()->can('create', JabatanStrukturalPoin::class),
                'update' => $request->user()->hasRole('admin'),
                'delete' => $request->user()->hasRole('admin'),
            ],
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', JabatanStrukturalPoin::class);

        $validated = $request->validate([
            'level_struktur_id' => ['required', 'exists:level_struktur,id'],
            'nama_jabatan' => ['required', 'string', 'max:100'],
            'total_poin' => ['required', 'integer', 'min:0'],
            'klasifikasi_id' => ['nullable', 'exists:klasifikasi_jabatan_struktural,id'],
            'tunjangan_baru' => ['required', 'numeric', 'min:0'],
        ]);

        JabatanStrukturalPoin::create($validated);

        return redirect()->route('admin.jabatan-struktural-poin.index')->with('success', 'Data berhasil ditambahkan.');
    }

    public function edit(Request $request, JabatanStrukturalPoin $jabatan_struktural_poin): Response
    {
        $this->authorize('update', $jabatan_struktural_poin);

        return Inertia::render('Admin/JabatanStrukturalPoin/Edit', [
            'item' => $jabatan_struktural_poin->load(['levelStruktur', 'klasifikasi']),
            'levels' => LevelStruktur::orderBy('urutan')->get(),
            'klasifikasis' => KlasifikasiJabatanStruktural::orderBy('klasifikasi')->get(),
        ]);
    }

    public function update(Request $request, JabatanStrukturalPoin $jabatan_struktural_poin): RedirectResponse
    {
        $validated = $request->validate([
            'level_struktur_id' => ['required', 'exists:level_struktur,id'],
            'nama_jabatan' => ['required', 'string', 'max:100'],
            'total_poin' => ['required', 'integer', 'min:0'],
            'klasifikasi_id' => ['nullable', 'exists:klasifikasi_jabatan_struktural,id'],
            'tunjangan_baru' => ['required', 'numeric', 'min:0'],
        ]);

        $jabatan_struktural_poin->update($validated);

        return redirect()->route('admin.jabatan-struktural-poin.index')->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(Request $request, JabatanStrukturalPoin $jabatan_struktural_poin): RedirectResponse
    {
        $this->authorize('delete', $jabatan_struktural_poin);
        $jabatan_struktural_poin->delete();

        return redirect()->route('admin.jabatan-struktural-poin.index')->with('success', 'Data berhasil dihapus.');
    }
}
