<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePotonganGajiRequest;
use App\Models\PotonganGaji;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PotonganGajiController extends Controller
{
    public function index(Request $request): Response
    {
        $potongans = PotonganGaji::query()
            ->orderBy('is_alpha_penalty', 'desc')
            ->orderBy('nama_potongan')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/PotonganGaji/Index', [
            'potongans' => $potongans,
            'can' => [
                'create' => $request->user()->can('create', PotonganGaji::class),
                'update' => $request->user()->hasRole('admin'),
                'delete' => $request->user()->hasRole('admin'),
            ],
        ]);
    }

    public function store(StorePotonganGajiRequest $request): RedirectResponse
    {
        $this->authorize('create', PotonganGaji::class);

        PotonganGaji::create($request->validated());

        return redirect()->route('admin.potongan-gaji.index')->with('success', 'Potongan gaji berhasil ditambahkan.');
    }

    public function update(StorePotonganGajiRequest $request, PotonganGaji $potongan_gaji): RedirectResponse
    {
        $this->authorize('update', $potongan_gaji);

        $potongan_gaji->update($request->validated());

        return redirect()->route('admin.potongan-gaji.index')->with('success', 'Potongan gaji berhasil diperbarui.');
    }

    public function toggle(PotonganGaji $potongan_gaji): RedirectResponse
    {
        $this->authorize('update', $potongan_gaji);

        $potongan_gaji->update(['aktif' => ! $potongan_gaji->aktif]);

        return redirect()->route('admin.potongan-gaji.index')->with('success', 'Status potongan gaji berhasil diubah.');
    }

    public function destroy(PotonganGaji $potongan_gaji): RedirectResponse
    {
        $this->authorize('delete', $potongan_gaji);

        $potongan_gaji->delete();

        return redirect()->route('admin.potongan-gaji.index')->with('success', 'Potongan gaji berhasil dihapus.');
    }
}
