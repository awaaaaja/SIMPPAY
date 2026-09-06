<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FungsionalRequest;
use App\Models\Fungsional;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FungsionalController extends Controller
{
    public function index(Request $request): Response
    {
        $fungsionals = Fungsional::query()
            ->withCount('pegawai')
            ->when($request->search, fn ($q, $s) => $q->where('nama_fungsional', 'like', "%{$s}%"))
            ->orderBy('nama_fungsional')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Fungsional/Index', [
            'fungsionals' => $fungsionals,
            'can' => [
                'create' => $request->user()->can('create', Fungsional::class),
                'update' => $request->user()->hasRole('admin'),
                'delete' => $request->user()->hasRole('admin'),
            ],
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Fungsional::class);

        return Inertia::render('Admin/Fungsional/Create');
    }

    public function store(FungsionalRequest $request): RedirectResponse
    {
        Fungsional::create($request->validated());

        return redirect()->route('admin.fungsional.index')->with('success', 'Fungsional berhasil ditambahkan.');
    }

    public function edit(Request $request, Fungsional $fungsional): Response
    {
        $this->authorize('update', $fungsional);

        return Inertia::render('Admin/Fungsional/Edit', [
            'fungsional' => $fungsional,
        ]);
    }

    public function update(FungsionalRequest $request, Fungsional $fungsional): RedirectResponse
    {
        $fungsional->update($request->validated());

        return redirect()->route('admin.fungsional.index')->with('success', 'Fungsional berhasil diperbarui.');
    }

    public function destroy(Request $request, Fungsional $fungsional): RedirectResponse
    {
        $this->authorize('delete', $fungsional);

        $pegawaiCount = $fungsional->pegawai()->count();
        if ($pegawaiCount > 0) {
            return back()->withErrors(['fungsional' => "Fungsional masih dipakai oleh {$pegawaiCount} pegawai."]);
        }

        $fungsional->delete();

        return redirect()->route('admin.fungsional.index')->with('success', 'Fungsional berhasil dihapus.');
    }
}
