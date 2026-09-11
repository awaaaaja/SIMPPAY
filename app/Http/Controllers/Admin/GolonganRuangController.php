<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GolonganRuang;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GolonganRuangController extends Controller
{
    public function index(Request $request): Response
    {
        $items = GolonganRuang::query()
            ->when($request->search, fn ($q, $s) => $q->where('kode', 'like', "%{$s}%")->orWhere('nama', 'like', "%{$s}%"))
            ->orderBy('urutan')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/GolonganRuang/Index', [
            'items' => $items,
            'can' => [
                'create' => $request->user()->can('create', GolonganRuang::class),
                'update' => $request->user()->hasRole('admin'),
                'delete' => $request->user()->hasRole('admin'),
            ],
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', GolonganRuang::class);

        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:10', 'unique:golongan_ruang,kode'],
            'nama' => ['required', 'string', 'max:50'],
            'urutan' => ['required', 'integer', 'min:0'],
        ]);

        GolonganRuang::create($validated);

        return redirect()->route('admin.golongan-ruang.index')->with('success', 'Golongan Ruang berhasil ditambahkan.');
    }

    public function edit(Request $request, GolonganRuang $golongan_ruang): Response
    {
        $this->authorize('update', $golongan_ruang);

        return Inertia::render('Admin/GolonganRuang/Edit', ['item' => $golongan_ruang]);
    }

    public function update(Request $request, GolonganRuang $golongan_ruang): RedirectResponse
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:10', "unique:golongan_ruang,kode,{$golongan_ruang->id}"],
            'nama' => ['required', 'string', 'max:50'],
            'urutan' => ['required', 'integer', 'min:0'],
        ]);

        $golongan_ruang->update($validated);

        return redirect()->route('admin.golongan-ruang.index')->with('success', 'Golongan Ruang berhasil diperbarui.');
    }

    public function destroy(Request $request, GolonganRuang $golongan_ruang): RedirectResponse
    {
        $this->authorize('delete', $golongan_ruang);
        $golongan_ruang->delete();

        return redirect()->route('admin.golongan-ruang.index')->with('success', 'Golongan Ruang berhasil dihapus.');
    }
}
