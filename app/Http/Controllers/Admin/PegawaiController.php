<?php

namespace App\Http\Controllers\Admin;

use App\Exports\PegawaiExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PegawaiRequest;
use App\Imports\PegawaiImport;
use App\Models\GolonganRuang;
use App\Models\JabatanStrukturalPoin;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class PegawaiController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Pegawai::class);

        $pegawais = Pegawai::query()
            ->with(['jabatanStrukturalPoin', 'golonganRuang'])
            ->when($request->search, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('nama_pegawai', 'like', "%{$s}%")
                    ->orWhere('nik', 'like', "%{$s}%");
            }))
            ->when($request->status_pegawai, fn ($q, $v) => $q->where('status_pegawai', $v))
            ->orderBy('nama_pegawai')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Pegawai/Index', [
            'pegawais' => $pegawais,
            'can' => [
                'create' => $request->user()->can('create', Pegawai::class),
                'update' => $request->user()->hasRole('admin'),
                'delete' => $request->user()->hasRole('admin'),
            ],
            'filters' => $request->only(['search', 'status_pegawai']),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Pegawai::class);

        return Inertia::render('Admin/Pegawai/Create', [
            'golongans' => GolonganRuang::orderBy('urutan')->get(['id', 'kode']),
            'jabatanStrukturalPoin' => JabatanStrukturalPoin::orderBy('nama_jabatan')->get(['id', 'nama_jabatan']),
        ]);
    }

    public function store(PegawaiRequest $request): RedirectResponse
    {
        $this->authorize('create', Pegawai::class);

        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('pegawai/photo', 'public');
        }
        if ($request->hasFile('foto_sk')) {
            $data['foto_sk'] = $request->file('foto_sk')->store('pegawai/foto-sk', 'public');
        }

        $pegawai = Pegawai::create($data);

        if ($request->boolean('create_user')) {
            // Require explicit password — no more default 'password123'
            $password = $request->input('user_password');
            if (empty($password)) {
                $password = \Illuminate\Support\Str::random(12);
            }

            $user = User::create([
                'name' => $pegawai->nama_pegawai,
                'username' => $request->input('user_username', $pegawai->nik),
                'email' => $pegawai->email,
                'password' => Hash::make($password),
            ]);
            $user->assignRole($request->input('user_role', 'pegawai'));
            $pegawai->update(['user_id' => $user->id]);
        }

        return redirect()->route('admin.pegawai.index')->with('success', 'Pegawai berhasil ditambahkan.');
    }

    public function show(Request $request, Pegawai $pegawai): Response
    {
        $this->authorize('view', $pegawai);

        $pegawai->load(['anak', 'user', 'golonganRuang', 'jabatanStrukturalPoin']);

        return Inertia::render('Admin/Pegawai/Show', [
            'pegawai' => $pegawai,
            'can' => [
                'update' => $request->user()->can('update', $pegawai),
                'delete' => $request->user()->can('delete', $pegawai),
            ],
            'roles' => ['pegawai', 'tendik', 'bpsdm'],
        ]);
    }

    public function edit(Request $request, Pegawai $pegawai): Response
    {
        $this->authorize('update', $pegawai);

        $pegawai->load('anak');

        return Inertia::render('Admin/Pegawai/Edit', [
            'pegawai' => $pegawai,
            'golongans' => GolonganRuang::orderBy('urutan')->get(['id', 'kode']),
            'jabatanStrukturalPoin' => JabatanStrukturalPoin::orderBy('nama_jabatan')->get(['id', 'nama_jabatan']),
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

    public function createAccount(Request $request, Pegawai $pegawai): RedirectResponse
    {
        $this->authorize('update', $pegawai);

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'in:pegawai,tendik,bpsdm'],
        ]);

        if ($pegawai->user) {
            return back()->withErrors(['username' => 'Pegawai ini sudah memiliki akun.']);
        }

        $user = User::create([
            'name' => $pegawai->nama_pegawai,
            'username' => $validated['username'],
            'email' => $pegawai->email,
            'password' => Hash::make($validated['password'] ?: 'password123'),
        ]);

        $user->assignRole($validated['role']);
        $pegawai->update(['user_id' => $user->id]);

        return back()->with('success', 'Akun berhasil dibuat untuk pegawai.');
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
