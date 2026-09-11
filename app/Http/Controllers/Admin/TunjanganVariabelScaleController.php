<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GolonganRuang;
use App\Models\TunjanganVariabelScale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TunjanganVariabelScaleController extends Controller
{
    public function index(Request $request): Response
    {
        $items = TunjanganVariabelScale::with('golonganRuang')
            ->when($request->search, fn ($q, $s) => $q->whereHas('golonganRuang', fn ($gq) => $gq->where('kode', 'like', "%{$s}%")->orWhere('nama', 'like', "%{$s}%")))
            ->orderBy('golongan_ruang_id')
            ->paginate(20)
            ->withQueryString();

        $golongans = GolonganRuang::orderBy('urutan')->get();

        return Inertia::render('Admin/TunjanganVariabelScale/Index', [
            'items' => $items,
            'golongans' => $golongans,
            'can' => [
                'create' => $request->user()->can('create', TunjanganVariabelScale::class),
                'update' => $request->user()->hasRole('admin'),
                'delete' => $request->user()->hasRole('admin'),
            ],
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', TunjanganVariabelScale::class);

        $validated = $request->validate([
            'golongan_ruang_id' => ['required', 'exists:golongan_ruang,id'],
            'nominal_maksimum' => ['required', 'numeric', 'min:0'],
        ]);

        TunjanganVariabelScale::create($validated);

        return redirect()->route('admin.tunjangan-variabel-scale.index')->with('success', 'Data berhasil ditambahkan.');
    }

    public function edit(Request $request, TunjanganVariabelScale $tunjangan_variabel_scale): Response
    {
        $this->authorize('update', $tunjangan_variabel_scale);

        return Inertia::render('Admin/TunjanganVariabelScale/Edit', [
            'item' => $tunjangan_variabel_scale->load('golonganRuang'),
            'golongans' => GolonganRuang::orderBy('urutan')->get(),
        ]);
    }

    public function update(Request $request, TunjanganVariabelScale $tunjangan_variabel_scale): RedirectResponse
    {
        $validated = $request->validate([
            'golongan_ruang_id' => ['required', 'exists:golongan_ruang,id'],
            'nominal_maksimum' => ['required', 'numeric', 'min:0'],
        ]);

        $tunjangan_variabel_scale->update($validated);

        return redirect()->route('admin.tunjangan-variabel-scale.index')->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(Request $request, TunjanganVariabelScale $tunjangan_variabel_scale): RedirectResponse
    {
        $this->authorize('delete', $tunjangan_variabel_scale);
        $tunjangan_variabel_scale->delete();

        return redirect()->route('admin.tunjangan-variabel-scale.index')->with('success', 'Data berhasil dihapus.');
    }
}
