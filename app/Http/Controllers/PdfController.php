<?php

namespace App\Http\Controllers;

use App\Exports\LaporanAbsensiExport;
use App\Exports\LaporanGajiExport;
use App\Models\Kehadiran;
use App\Models\PayrollDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PdfController extends Controller
{
    public function slipGaji(PayrollDetail $detail)
    {
        $this->authorize('cetak', $detail);

        $detail->load(['pegawai.jabatan', 'payrollRun']);

        $pdf = Pdf::loadView('pdf.slip-gaji', [
            'detail' => $detail,
            'pegawai' => $detail->pegawai,
            'jabatan' => $detail->pegawai->jabatan,
            'periode' => $detail->payrollRun->periode,
            'breakdown' => $detail->breakdown_json ?? [],
        ]);

        return $pdf->download("slip-gaji-{$detail->pegawai->nik}-{$detail->payrollRun->periode->format('Y-m')}.pdf");
    }

    public function laporanGaji(Request $request)
    {
        $request->validate([
            'periode' => ['required', 'date_format:Y-m'],
            'jabatan_id' => ['nullable', 'exists:jabatan,id'],
            'pegawai_id' => ['nullable', 'exists:pegawai,id'],
        ]);

        $periode = Carbon::parse($request->input('periode'));

        $query = PayrollDetail::query()
            ->whereHas('payrollRun', fn ($q) => $q->where('periode', $periode))
            ->with(['pegawai.jabatan', 'payrollRun']);

        if ($request->filled('jabatan_id')) {
            $query->whereHas('pegawai', fn ($q) => $q->where('jabatan_id', $request->input('jabatan_id')));
        }

        if ($request->filled('pegawai_id')) {
            $query->where('pegawai_id', $request->input('pegawai_id'));
        }

        $details = $query->get();

        $pdf = Pdf::loadView('pdf.laporan-gaji', [
            'details' => $details,
            'periode' => $periode,
        ]);

        return $pdf->download("laporan-gaji-{$periode->format('Y-m')}.pdf");
    }

    public function laporanAbsensi(Request $request)
    {
        $request->validate([
            'periode' => ['required', 'date_format:Y-m'],
        ]);

        $periode = Carbon::parse($request->input('periode'));

        $kehadiran = Kehadiran::where('periode', $periode)
            ->with('pegawai.jabatan')
            ->get();

        $pdf = Pdf::loadView('pdf.laporan-absensi', [
            'kehadiran' => $kehadiran,
            'periode' => $periode,
        ]);

        return $pdf->download("laporan-absensi-{$periode->format('Y-m')}.pdf");
    }

    public function exportLaporanGaji(Request $request)
    {
        $request->validate([
            'periode' => ['required', 'date_format:Y-m'],
            'jabatan_id' => ['nullable', 'exists:jabatan,id'],
            'pegawai_id' => ['nullable', 'exists:pegawai,id'],
        ]);

        return Excel::download(
            new LaporanGajiExport($request->input('periode'), $request->input('jabatan_id'), $request->input('pegawai_id')),
            "laporan-gaji-{$request->input('periode')}.xlsx"
        );
    }

    public function exportLaporanAbsensi(Request $request)
    {
        $request->validate([
            'periode' => ['required', 'date_format:Y-m'],
        ]);

        return Excel::download(
            new LaporanAbsensiExport($request->input('periode')),
            "laporan-absensi-{$request->input('periode')}.xlsx"
        );
    }
}
