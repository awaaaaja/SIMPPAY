<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TunjanganFungsionalDosenScale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TunjanganFungsionalDosenScaleController extends Controller
{
    public function index(Request $request): Response
    {
        $items = TunjanganFungsionalDosenScale::query()
            ->when($request->search, fn ($q, $s) => $q->where('jabatan_fungsional', 'like', "%{$s}%"))
            ->orderBy('jabatan_fungsional')
            ->orderBy('angka_kredit')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/TunjanganFungsionalDosenScale/Index', [
            'items' => $items,
            'can' => [
                'create' => $request->user()->can('create', TunjanganFungsionalDosenScale::class),
                'update' => $request->user()->hasRole('admin'),
                'delete' => $request->user()->hasRole('admin'),
            ],
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', TunjanganFungsionalDosenScale::class);

        $validated = $request->validate([
            'jabatan_fungsional' => ['required', 'string', 'max:50'],
            'angka_kredit' => ['required', 'integer', 'min:0'],
            'pangkat' => ['required', 'string', 'max:50'],
            'golongan_ruang' => ['required', 'string', 'max:10'],
            'nominal' => ['required', 'numeric', 'min:0'],
        ]);

        TunjanganFungsionalDosenScale::create($validated);

        return redirect()->route('admin.tunjangan-fungsional-dosen-scale.index')->with('success', 'Data berhasil ditambahkan.');
    }

    public function edit(Request $request, TunjanganFungsionalDosenScale $tunjangan_fungsional_dosen_scale): Response
    {
        $this->authorize('update', $tunjangan_fungsional_dosen_scale);

        return Inertia::render('Admin/TunjanganFungsionalDosenScale/Edit', ['item' => $tunjangan_fungsional_dosen_scale]);
    }

    public function update(Request $request, TunjanganFungsionalDosenScale $tunjangan_fungsional_dosen_scale): RedirectResponse
    {
        $validated = $request->validate([
            'jabatan_fungsional' => ['required', 'string', 'max:50'],
            'angka_kredit' => ['required', 'integer', 'min:0'],
            'pangkat' => ['required', 'string', 'max:50'],
            'golongan_ruang' => ['required', 'string', 'max:10'],
            'nominal' => ['required', 'numeric', 'min:0'],
        ]);

        $tunjangan_fungsional_dosen_scale->update($validated);

        return redirect()->route('admin.tunjangan-fungsional-dosen-scale.index')->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(Request $request, TunjanganFungsionalDosenScale $tunjangan_fungsional_dosen_scale): RedirectResponse
    {
        $this->authorize('delete', $tunjangan_fungsional_dosen_scale);
        $tunjangan_fungsional_dosen_scale->delete();

        return redirect()->route('admin.tunjangan-fungsional-dosen-scale.index')->with('success', 'Data berhasil dihapus.');
    }
}
