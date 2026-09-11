<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LevelStruktur;
use App\Models\TunjanganTransportasiScale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TunjanganTransportasiScaleController extends Controller
{
    public function index(Request $request): Response
    {
        $items = TunjanganTransportasiScale::with('levelStruktur')
            ->when($request->search, fn ($q, $s) => $q->whereHas('levelStruktur', fn ($lq) => $lq->where('kode', 'like', "%{$s}%")->orWhere('nama', 'like', "%{$s}%")))
            ->orderBy('level_struktur_id')
            ->paginate(20)
            ->withQueryString();

        $levels = LevelStruktur::orderBy('urutan')->get();

        return Inertia::render('Admin/TunjanganTransportasiScale/Index', [
            'items' => $items,
            'levels' => $levels,
            'can' => [
                'create' => $request->user()->can('create', TunjanganTransportasiScale::class),
                'update' => $request->user()->hasRole('admin'),
                'delete' => $request->user()->hasRole('admin'),
            ],
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', TunjanganTransportasiScale::class);

        $validated = $request->validate([
            'level_struktur_id' => ['required', 'exists:level_struktur,id'],
            'keterangan' => ['required', 'string', 'max:100'],
            'golongan_range' => ['nullable', 'string', 'max:50'],
            'nominal' => ['required', 'numeric', 'min:0'],
        ]);

        TunjanganTransportasiScale::create($validated);

        return redirect()->route('admin.tunjangan-transportasi-scale.index')->with('success', 'Data berhasil ditambahkan.');
    }

    public function edit(Request $request, TunjanganTransportasiScale $tunjangan_transportasi_scale): Response
    {
        $this->authorize('update', $tunjangan_transportasi_scale);

        return Inertia::render('Admin/TunjanganTransportasiScale/Edit', [
            'item' => $tunjangan_transportasi_scale->load('levelStruktur'),
            'levels' => LevelStruktur::orderBy('urutan')->get(),
        ]);
    }

    public function update(Request $request, TunjanganTransportasiScale $tunjangan_transportasi_scale): RedirectResponse
    {
        $validated = $request->validate([
            'level_struktur_id' => ['required', 'exists:level_struktur,id'],
            'keterangan' => ['required', 'string', 'max:100'],
            'golongan_range' => ['nullable', 'string', 'max:50'],
            'nominal' => ['required', 'numeric', 'min:0'],
        ]);

        $tunjangan_transportasi_scale->update($validated);

        return redirect()->route('admin.tunjangan-transportasi-scale.index')->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(Request $request, TunjanganTransportasiScale $tunjangan_transportasi_scale): RedirectResponse
    {
        $this->authorize('delete', $tunjangan_transportasi_scale);
        $tunjangan_transportasi_scale->delete();

        return redirect()->route('admin.tunjangan-transportasi-scale.index')->with('success', 'Data berhasil dihapus.');
    }
}
