<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Inter', 'Helvetica Neue', Arial, sans-serif; font-size: 11px; color: #1a1a1a; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #D40C14; padding-bottom: 12px; }
        .header h1 { font-size: 16px; color: #D40C14; margin: 0 0 4px 0; font-weight: 700; }
        .header p { font-size: 11px; color: #666; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { padding: 5px 6px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
        th { background: #f9fafb; font-weight: 600; color: #555; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .grand-total { font-weight: 700; background: #f0fdf4; }
        .grand-total td { border-top: 2px solid #D40C14; border-bottom: 2px solid #D40C14; }
        .footer { margin-top: 24px; font-size: 9px; color: #999; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Absensi</h1>
        <p>Universitas Adzkia — Periode {{ $periode->format('F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>NIK</th>
                <th>Nama Pegawai</th>
                <th>Jabatan</th>
                <th class="text-right">Hadir</th>
                <th class="text-right">Sakit</th>
                <th class="text-right">Alpha</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kehadiran as $i => $k)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $k->pegawai->nik }}</td>
                <td>{{ $k->pegawai->nama_pegawai }}</td>
                <td>{{ $k->pegawai->jabatan->nama_jabatan ?? '-' }}</td>
                <td class="text-right">{{ $k->hadir }}</td>
                <td class="text-right">{{ $k->sakit }}</td>
                <td class="text-right">{{ $k->alpha }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada data kehadiran untuk periode ini.</td>
            </tr>
            @endforelse
            @if($kehadiran->count() > 0)
            <tr class="grand-total">
                <td colspan="4">TOTAL ({{ $kehadiran->count() }} Pegawai)</td>
                <td class="text-right">{{ $kehadiran->sum('hadir') }}</td>
                <td class="text-right">{{ $kehadiran->sum('sakit') }}</td>
                <td class="text-right">{{ $kehadiran->sum('alpha') }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada {{ now()->format('d/m/Y H:i') }} — SIMPPAY Universitas Adzkia
    </div>
</body>
</html>
