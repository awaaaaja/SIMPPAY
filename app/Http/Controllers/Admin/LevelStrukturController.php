<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LevelStruktur;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LevelStrukturController extends Controller
{
    public function index(Request $request): Response
    {
        $items = LevelStruktur::query()
            ->when($request->search, fn ($q, $s) => $q->where('kode', 'like', "%{$s}%"))
            ->orderBy('urutan')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/LevelStruktur/Index', [
            'items' => $items,
            'can' => [
                'create' => $request->user()->can('create', LevelStruktur::class),
                'update' => $request->user()->hasRole('admin'),
                'delete' => $request->user()->hasRole('admin'),
            ],
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', LevelStruktur::class);

        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:20', 'unique:level_struktur,kode'],
            'nama' => ['required', 'string', 'max:50'],
            'urutan' => ['required', 'integer', 'min:0'],
        ]);

        LevelStruktur::create($validated);

        return redirect()->route('admin.level-struktur.index')->with('success', 'Level Struktur berhasil ditambahkan.');
    }

    public function edit(Request $request, LevelStruktur $level_struktur): Response
    {
        $this->authorize('update', $level_struktur);

        return Inertia::render('Admin/LevelStruktur/Edit', ['item' => $level_struktur]);
    }

    public function update(Request $request, LevelStruktur $level_struktur): RedirectResponse
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:20', "unique:level_struktur,kode,{$level_struktur->id}"],
            'nama' => ['required', 'string', 'max:50'],
            'urutan' => ['required', 'integer', 'min:0'],
        ]);

        $level_struktur->update($validated);

        return redirect()->route('admin.level-struktur.index')->with('success', 'Level Struktur berhasil diperbarui.');
    }

    public function destroy(Request $request, LevelStruktur $level_struktur): RedirectResponse
    {
        $this->authorize('delete', $level_struktur);
        $level_struktur->delete();

        return redirect()->route('admin.level-struktur.index')->with('success', 'Level Struktur berhasil dihapus.');
    }
}
