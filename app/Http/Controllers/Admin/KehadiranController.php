<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreKehadiranRequest;
use App\Imports\KehadiranImport;
use App\Models\Jabatan;
use App\Models\Kehadiran;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class KehadiranController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Kehadiran::query()
            ->with(['pegawai.jabatan'])
            ->when($request->periode, fn ($q, $p) => $q->where('periode', Carbon::parse($p)->startOfMonth()))
            ->when($request->jabatan_id, fn ($q, $j) => $q->whereHas('pegawai', fn ($pq) => $pq->where('jabatan_id', $j)))
            ->when($request->search, fn ($q, $s) => $q->whereHas('pegawai', fn ($pq) => $pq->where(function ($pq2) use ($s) {
                $pq2->where('nama_pegawai', 'like', "%{$s}%")
                    ->orWhere('nik', 'like', "%{$s}%");
            })));

        $kehadirans = $query->orderBy('periode', 'desc')
            ->orderBy('pegawai_id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Kehadiran/Index', [
            'kehadirans' => $kehadirans,
            'jabatans' => Jabatan::orderBy('nama_jabatan')->get(['id', 'nama_jabatan']),
            'filters' => $request->only(['periode', 'jabatan_id', 'search']),
            'can' => [
                'create' => $request->user()->can('create', Kehadiran::class),
                'update' => $request->user()->hasRole('admin'),
                'delete' => $request->user()->hasRole('admin'),
            ],
        ]);
    }

    public function store(StoreKehadiranRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['periode'] = Carbon::parse($data['periode'])->startOfMonth();

        $existing = Kehadiran::where('pegawai_id', $data['pegawai_id'])
            ->where('periode', $data['periode'])
            ->first();

        if ($existing) {
            return back()->withErrors([
                'periode' => 'Data kehadiran untuk pegawai ini pada periode tersebut sudah ada.',
            ]);
        }

        Kehadiran::create($data);

        return redirect()->route('admin.kehadiran.index')->with('success', 'Data kehadiran berhasil ditambahkan.');
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
            'periode' => ['required', 'date_format:Y-m-d'],
        ]);

        $periode = Carbon::parse($request->periode)->startOfMonth()->toDateString();

        Excel::import(new KehadiranImport($periode), $request->file('file'));

        return redirect()->route('admin.kehadiran.index')->with('success', 'Import kehadiran berhasil.');
    }

    public function destroy(Kehadiran $kehadiran): RedirectResponse
    {
        $kehadiran->delete();

        return redirect()->route('admin.kehadiran.index')->with('success', 'Data kehadiran berhasil dihapus.');
    }
}
