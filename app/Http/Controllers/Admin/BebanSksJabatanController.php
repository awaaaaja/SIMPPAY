<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BebanSksJabatan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BebanSksJabatanController extends Controller
{
    public function index(Request $request): Response
    {
        $items = BebanSksJabatan::query()
            ->when($request->search, fn ($q, $s) => $q->where('nama_jabatan', 'like', "%{$s}%"))
            ->orderBy('nama_jabatan')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/BebanSksJabatan/Index', [
            'items' => $items,
            'can' => [
                'create' => $request->user()->can('create', BebanSksJabatan::class),
                'update' => $request->user()->hasRole('admin'),
                'delete' => $request->user()->hasRole('admin'),
            ],
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', BebanSksJabatan::class);

        $validated = $request->validate([
            'nama_jabatan' => ['required', 'string', 'max:100'],
            'sks_perkuliahan' => ['required', 'integer', 'min:0'],
            'sks_penelitian' => ['required', 'integer', 'min:0'],
            'sks_adm' => ['required', 'integer', 'min:0'],
            'sks_jabatan' => ['required', 'integer', 'min:0'],
        ]);

        $validated['total'] = $validated['sks_perkuliahan'] + $validated['sks_penelitian'] + $validated['sks_adm'] + $validated['sks_jabatan'];

        BebanSksJabatan::create($validated);

        return redirect()->route('admin.beban-sks-jabatan.index')->with('success', 'Data berhasil ditambahkan.');
    }

    public function edit(Request $request, BebanSksJabatan $beban_sks_jabatan): Response
    {
        $this->authorize('update', $beban_sks_jabatan);

        return Inertia::render('Admin/BebanSksJabatan/Edit', ['item' => $beban_sks_jabatan]);
    }

    public function update(Request $request, BebanSksJabatan $beban_sks_jabatan): RedirectResponse
    {
        $validated = $request->validate([
            'nama_jabatan' => ['required', 'string', 'max:100'],
            'sks_perkuliahan' => ['required', 'integer', 'min:0'],
            'sks_penelitian' => ['required', 'integer', 'min:0'],
            'sks_adm' => ['required', 'integer', 'min:0'],
            'sks_jabatan' => ['required', 'integer', 'min:0'],
        ]);

        $validated['total'] = $validated['sks_perkuliahan'] + $validated['sks_penelitian'] + $validated['sks_adm'] + $validated['sks_jabatan'];

        $beban_sks_jabatan->update($validated);

        return redirect()->route('admin.beban-sks-jabatan.index')->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(Request $request, BebanSksJabatan $beban_sks_jabatan): RedirectResponse
    {
        $this->authorize('delete', $beban_sks_jabatan);
        $beban_sks_jabatan->delete();

        return redirect()->route('admin.beban-sks-jabatan.index')->with('success', 'Data berhasil dihapus.');
    }
}
