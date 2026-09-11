<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HonorSksScale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HonorSksScaleController extends Controller
{
    public function index(Request $request): Response
    {
        $items = HonorSksScale::query()
            ->when($request->search, fn ($q, $s) => $q->where('strata', 'like', "%{$s}%"))
            ->orderBy('program')
            ->orderBy('status_dosen')
            ->orderBy('strata')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/HonorSksScale/Index', [
            'items' => $items,
            'can' => [
                'create' => $request->user()->can('create', HonorSksScale::class),
                'update' => $request->user()->hasRole('admin'),
                'delete' => $request->user()->hasRole('admin'),
            ],
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', HonorSksScale::class);

        $validated = $request->validate([
            'program' => ['required', 'in:s1,s2'],
            'status_dosen' => ['required', 'in:tetap,tidak_tetap'],
            'strata' => ['required', 'string', 'max:20'],
            'nilai_sks' => ['required', 'numeric', 'min:0'],
        ]);

        HonorSksScale::create($validated);

        return redirect()->route('admin.honor-sks-scale.index')->with('success', 'Data berhasil ditambahkan.');
    }

    public function edit(Request $request, HonorSksScale $honor_sks_scale): Response
    {
        $this->authorize('update', $honor_sks_scale);

        return Inertia::render('Admin/HonorSksScale/Edit', ['item' => $honor_sks_scale]);
    }

    public function update(Request $request, HonorSksScale $honor_sks_scale): RedirectResponse
    {
        $validated = $request->validate([
            'program' => ['required', 'in:s1,s2'],
            'status_dosen' => ['required', 'in:tetap,tidak_tetap'],
            'strata' => ['required', 'string', 'max:20'],
            'nilai_sks' => ['required', 'numeric', 'min:0'],
        ]);

        $honor_sks_scale->update($validated);

        return redirect()->route('admin.honor-sks-scale.index')->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy(Request $request, HonorSksScale $honor_sks_scale): RedirectResponse
    {
        $this->authorize('delete', $honor_sks_scale);
        $honor_sks_scale->delete();

        return redirect()->route('admin.honor-sks-scale.index')->with('success', 'Data berhasil dihapus.');
    }
}
