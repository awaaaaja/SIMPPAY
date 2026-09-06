<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StrukturalRequest;
use App\Models\Struktural;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StrukturalController extends Controller
{
    public function index(Request $request): Response
    {
        $strukturals = Struktural::query()
            ->withCount('pegawai')
            ->when($request->search, fn ($q, $s) => $q->where('nama_struktural', 'like', "%{$s}%"))
            ->orderBy('nama_struktural')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Struktural/Index', [
            'strukturals' => $strukturals,
            'can' => [
                'create' => $request->user()->can('create', Struktural::class),
                'update' => $request->user()->hasRole('admin'),
                'delete' => $request->user()->hasRole('admin'),
            ],
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Struktural::class);

        return Inertia::render('Admin/Struktural/Create');
    }

    public function store(StrukturalRequest $request): RedirectResponse
    {
        Struktural::create($request->validated());

        return redirect()->route('admin.struktural.index')->with('success', 'Struktural berhasil ditambahkan.');
    }

    public function edit(Request $request, Struktural $struktural): Response
    {
        $this->authorize('update', $struktural);

        return Inertia::render('Admin/Struktural/Edit', [
            'struktural' => $struktural,
        ]);
    }

    public function update(StrukturalRequest $request, Struktural $struktural): RedirectResponse
    {
        $struktural->update($request->validated());

        return redirect()->route('admin.struktural.index')->with('success', 'Struktural berhasil diperbarui.');
    }

    public function destroy(Request $request, Struktural $struktural): RedirectResponse
    {
        $this->authorize('delete', $struktural);

        $pegawaiCount = $struktural->pegawai()->count();
        if ($pegawaiCount > 0) {
            return back()->withErrors(['struktural' => "Struktural masih dipakai oleh {$pegawaiCount} pegawai."]);
        }

        $struktural->delete();

        return redirect()->route('admin.struktural.index')->with('success', 'Struktural berhasil dihapus.');
    }
}
