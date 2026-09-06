<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Inter', 'Helvetica Neue', Arial, sans-serif; font-size: 11px; color: #1a1a1a; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #176B5B; padding-bottom: 12px; }
        .header h1 { font-size: 16px; color: #176B5B; margin: 0 0 4px 0; font-weight: 700; }
        .header p { font-size: 11px; color: #666; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { padding: 5px 6px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
        th { background: #f9fafb; font-weight: 600; color: #555; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .grand-total { font-weight: 700; background: #f0fdf4; }
        .grand-total td { border-top: 2px solid #176B5B; border-bottom: 2px solid #176B5B; }
        .footer { margin-top: 24px; font-size: 9px; color: #999; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Gaji</h1>
        <p>Universitas Adzkia — Periode {{ $periode->format('F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>NIK</th>
                <th>Nama Pegawai</th>
                <th>Jabatan</th>
                <th class="text-right">Gaji Pokok</th>
                <th class="text-right">Tj. Transport</th>
                <th class="text-right">Uang Makan</th>
                <th class="text-right">Pot. Alpha</th>
                <th class="text-right">Tunj. Tambahan</th>
                <th class="text-right">Pot. Tambahan</th>
                <th class="text-right">Honor SKS</th>
                <th class="text-right">Total Gaji</th>
            </tr>
        </thead>
        <tbody>
            @forelse($details as $i => $d)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ $d->pegawai->nik }}</td>
                <td>{{ $d->pegawai->nama_pegawai }}</td>
                <td>{{ $d->pegawai->jabatan->nama_jabatan ?? '-' }}</td>
                <td class="text-right">{{ number_format($d->gaji_pokok, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($d->tj_transport, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($d->uang_makan, 0, ',', '.') }}</td>
                <td class="text-right">{{ $d->potongan_alpha > 0 ? number_format($d->potongan_alpha, 0, ',', '.') : '-' }}</td>
                <td class="text-right">{{ $d->total_tunjangan_tambahan > 0 ? number_format($d->total_tunjangan_tambahan, 0, ',', '.') : '-' }}</td>
                <td class="text-right">{{ $d->total_potongan_tambahan > 0 ? number_format($d->total_potongan_tambahan, 0, ',', '.') : '-' }}</td>
                <td class="text-right">{{ $d->honor_kelebihan_sks > 0 ? number_format($d->honor_kelebihan_sks, 0, ',', '.') : '-' }}</td>
                <td class="text-right">{{ number_format($d->total_gaji, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="12" class="text-center">Tidak ada data untuk periode ini.</td>
            </tr>
            @endforelse
            @if($details->count() > 0)
            <tr class="grand-total">
                <td colspan="4">TOTAL ({{ $details->count() }} Pegawai)</td>
                <td class="text-right">{{ number_format($details->sum('gaji_pokok'), 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($details->sum('tj_transport'), 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($details->sum('uang_makan'), 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($details->sum('potongan_alpha'), 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($details->sum('total_tunjangan_tambahan'), 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($details->sum('total_potongan_tambahan'), 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($details->sum('honor_kelebihan_sks'), 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($details->sum('total_gaji'), 0, ',', '.') }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada {{ now()->format('d/m/Y H:i') }} — SIMPPAY Universitas Adzkia
    </div>
</body>
</html>
