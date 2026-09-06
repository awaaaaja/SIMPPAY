<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PegawaiExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PegawaiRequest;
use App\Imports\PegawaiImport;
use App\Models\Fungsional;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\Struktural;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class PegawaiController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Pegawai::class);

        $pegawais = Pegawai::query()
            ->with(['jabatan', 'struktural', 'fungsional'])
            ->when($request->search, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('nama_pegawai', 'like', "%{$s}%")
                    ->orWhere('nik', 'like', "%{$s}%");
            }))
            ->when($request->jabatan_id, fn ($q, $v) => $q->where('jabatan_id', $v))
            ->when($request->status_pegawai, fn ($q, $v) => $q->where('status_pegawai', $v))
            ->when($request->struktural_id, fn ($q, $v) => $q->where('struktural_id', $v))
            ->when($request->fungsional_id, fn ($q, $v) => $q->where('fungsional_id', $v))
            ->orderBy('nama_pegawai')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Pegawai/Index', [
            'pegawais' => $pegawais,
            'jabatans' => Jabatan::orderBy('nama_jabatan')->get(['id', 'nama_jabatan']),
            'strukturals' => Struktural::orderBy('nama_struktural')->get(['id', 'nama_struktural']),
            'fungsionals' => Fungsional::orderBy('nama_fungsional')->get(['id', 'nama_fungsional']),
            'can' => [
                'create' => $request->user()->can('create', Pegawai::class),
                'update' => $request->user()->hasRole('admin'),
                'delete' => $request->user()->hasRole('admin'),
            ],
            'filters' => $request->only(['search', 'jabatan_id', 'status_pegawai', 'struktural_id', 'fungsional_id']),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Pegawai::class);

        return Inertia::render('Admin/Pegawai/Create', [
            'jabatans' => Jabatan::orderBy('nama_jabatan')->get(['id', 'nama_jabatan']),
            'strukturals' => Struktural::orderBy('nama_struktural')->get(['id', 'nama_struktural']),
            'fungsionals' => Fungsional::orderBy('nama_fungsional')->get(['id', 'nama_fungsional']),
        ]);
    }

    public function store(PegawaiRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('pegawai/photo', 'public');
        }
        if ($request->hasFile('foto_sk')) {
            $data['foto_sk'] = $request->file('foto_sk')->store('pegawai/foto-sk', 'public');
        }

        Pegawai::create($data);

        return redirect()->route('admin.pegawai.index')->with('success', 'Pegawai berhasil ditambahkan.');
    }

    public function show(Request $request, Pegawai $pegawai): Response
    {
        $this->authorize('view', $pegawai);

        $pegawai->load(['jabatan', 'struktural', 'fungsional', 'anak', 'user']);

        return Inertia::render('Admin/Pegawai/Show', [
            'pegawai' => $pegawai,
            'can' => [
                'update' => $request->user()->can('update', $pegawai),
                'delete' => $request->user()->can('delete', $pegawai),
            ],
        ]);
    }

    public function edit(Request $request, Pegawai $pegawai): Response
    {
        $this->authorize('update', $pegawai);

        $pegawai->load('anak');

        return Inertia::render('Admin/Pegawai/Edit', [
            'pegawai' => $pegawai,
            'jabatans' => Jabatan::orderBy('nama_jabatan')->get(['id', 'nama_jabatan']),
            'strukturals' => Struktural::orderBy('nama_struktural')->get(['id', 'nama_struktural']),
            'fungsionals' => Fungsional::orderBy('nama_fungsional')->get(['id', 'nama_fungsional']),
        ]);
    }

    public function update(PegawaiRequest $request, Pegawai $pegawai): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            if ($pegawai->photo) {
                Storage::disk('public')->delete($pegawai->photo);
            }
            $data['photo'] = $request->file('photo')->store('pegawai/photo', 'public');
        }
        if ($request->hasFile('foto_sk')) {
            if ($pegawai->foto_sk) {
                Storage::disk('public')->delete($pegawai->foto_sk);
            }
            $data['foto_sk'] = $request->file('foto_sk')->store('pegawai/foto-sk', 'public');
        }

        $pegawai->update($data);

        return redirect()->route('admin.pegawai.show', $pegawai)->with('success', 'Data pegawai berhasil diperbarui.');
    }

    public function destroy(Request $request, Pegawai $pegawai): RedirectResponse
    {
        $this->authorize('delete', $pegawai);

        if ($pegawai->photo) {
            Storage::disk('public')->delete($pegawai->photo);
        }
        if ($pegawai->foto_sk) {
            Storage::disk('public')->delete($pegawai->foto_sk);
        }

        $pegawai->delete();

        return redirect()->route('admin.pegawai.index')->with('success', 'Pegawai berhasil dihapus.');
    }

    public function export()
    {
        return Excel::download(new PegawaiExport, 'data-pegawai.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
        ]);

        Excel::import(new PegawaiImport, $request->file('file'));

        return redirect()->route('admin.pegawai.index')->with('success', 'Import pegawai berhasil.');
    }
}
