<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GajiPokokScale;
use App\Models\GolonganRuang;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GajiPokokScaleController extends Controller
{
    public function index(Request $request): Response
    {
        $items = GajiPokokScale::with('golonganRuang')
            ->when($request->golongan, fn ($q, $s) => $q->whereHas('golonganRuang', fn ($q2) => $q2->where('kode', $s)))
            ->orderBy('golongan_ruang_id')
            ->orderBy('mkg')
            ->paginate(50)
            ->withQueryString();

        $golongans = GolonganRuang::orderBy('urutan')->get();

        return Inertia::render('Admin/GajiPokokScale/Index', [
            'items' => $items,
            'golongans' => $golongans,
            'can' => [
                'create' => $request->user()->can('create', GajiPokokScale::class),
                'update' => $request->user()->hasRole('admin'),
                'delete' => $request->user()->hasRole('admin'),
            ],
            'filters' => $request->only(['golongan']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', GajiPokokScale::class);

        $validated = $request->validate([
            'golongan_ruang_id' => ['required', 'exists:golongan_ruang,id'],
            'mkg' => ['required', 'integer', 'min:0', 'max:30'],
            'nominal' => ['required', 'numeric', 'min:0'],
        ]);

        GajiPokokScale::create($validated);

        return redirect()->route('admin.gaji-pokok-scale.index')->with('success', 'Data berhasil ditambahkan.');
    }

    public function edit(Request $request, GajiPokokScale $gaji_pokok_scale): Response
    {
        $this->authorize('update', $gaji_pokok_scale);

        return Inertia::render('Admin/GajiPokokScale/Edit', [
            'item' => $gaji_pokok_scale->load('golonganRuang'),
            'golongans' => GolonganRuang::orderBy('urutan')->get(),
        ]);
    }

    public function update(Request $request, GajiPokokScale $gaji_pokok_scale): RedirectResponse
    {
        $validated = $request->validate([
            'golongan_ruang_id' => ['required', 'exists:golongan_ruang,id'],
            'mkg' => ['required', 'integer', 'min:0', 'max:30'],
            'nominal' => ['required', 'numeric', 'min:0'],
        ]);

        $gaji_pokok_scale->update($validated);

        return redirect()->route('admin.gaji-pokok-scale.index')->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(Request $request, GajiPokokScale $gaji_pokok_scale): RedirectResponse
    {
        $this->authorize('delete', $gaji_pokok_scale);
        $gaji_pokok_scale->delete();

        return redirect()->route('admin.gaji-pokok-scale.index')->with('success', 'Data berhasil dihapus.');
    }
}
