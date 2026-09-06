<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\JabatanRequest;
use App\Models\Jabatan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JabatanController extends Controller
{
    public function index(Request $request): Response
    {
        $jabatans = Jabatan::query()
            ->withCount('activePegawai')
            ->when($request->search, fn ($q, $s) => $q->where('nama_jabatan', 'like', "%{$s}%"))
            ->orderBy('nama_jabatan')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Jabatan/Index', [
            'jabatans' => $jabatans,
            'can' => [
                'create' => $request->user()->can('create', Jabatan::class),
                'update' => $request->user()->hasRole('admin'),
                'delete' => $request->user()->hasRole('admin'),
            ],
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Jabatan::class);

        return Inertia::render('Admin/Jabatan/Create');
    }

    public function store(JabatanRequest $request): RedirectResponse
    {
        Jabatan::create($request->validated());

        return redirect()->route('admin.jabatan.index')->with('success', 'Jabatan berhasil ditambahkan.');
    }

    public function edit(Request $request, Jabatan $jabatan): Response
    {
        $this->authorize('update', $jabatan);

        return Inertia::render('Admin/Jabatan/Edit', [
            'jabatan' => $jabatan,
        ]);
    }

    public function update(JabatanRequest $request, Jabatan $jabatan): RedirectResponse
    {
        $jabatan->update($request->validated());

        return redirect()->route('admin.jabatan.index')->with('success', 'Jabatan berhasil diperbarui.');
    }

    public function destroy(Request $request, Jabatan $jabatan): RedirectResponse
    {
        $this->authorize('delete', $jabatan);

        $activeCount = $jabatan->activePegawai()->count();
        if ($activeCount > 0) {
            return back()->withErrors(['jabatan' => "Jabatan masih dipakai oleh {$activeCount} pegawai aktif."]);
        }

        $jabatan->delete();

        return redirect()->route('admin.jabatan.index')->with('success', 'Jabatan berhasil dihapus.');
    }
}
