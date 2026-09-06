<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Inter', 'Helvetica Neue', Arial, sans-serif; font-size: 12px; color: #1a1a1a; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 24px; border-bottom: 2px solid #176B5B; padding-bottom: 16px; }
        .header h1 { font-size: 18px; color: #176B5B; margin: 0 0 4px 0; font-weight: 700; }
        .header p { font-size: 11px; color: #666; margin: 0; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 4px 24px; margin-bottom: 20px; }
        .info-row { display: flex; }
        .info-label { width: 120px; font-weight: 600; color: #555; }
        .info-value { flex: 1; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 6px 10px; text-align: left; border-bottom: 1px solid #e5e7eb; font-size: 11px; }
        th { background: #f9fafb; font-weight: 600; color: #555; }
        .text-right { text-align: right; }
        .total-row { font-weight: 700; background: #f0fdf4; }
        .total-row td { border-top: 2px solid #176B5B; border-bottom: 2px solid #176B5B; }
        .footer { margin-top: 40px; font-size: 10px; color: #999; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>SIMPPAY</h1>
        <p>Universitas Adzkia — Slip Gaji</p>
    </div>

    <div class="info-grid">
        <div class="info-row">
            <span class="info-label">NIK</span>
            <span class="info-value">: {{ $pegawai->nik }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Periode</span>
            <span class="info-value">: {{ $periode->format('F Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Nama</span>
            <span class="info-value">: {{ $pegawai->nama_pegawai }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Status</span>
            <span class="info-value">: {{ ucfirst($detail->payrollRun->status) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Jabatan</span>
            <span class="info-value">: {{ $jabatan->nama_jabatan ?? '-' }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Komponen</th>
                <th class="text-right">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Gaji Pokok</td>
                <td class="text-right">{{ number_format($detail->gaji_pokok, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Tunjangan Transport</td>
                <td class="text-right">{{ number_format($detail->tj_transport, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Uang Makan</td>
                <td class="text-right">{{ number_format($detail->uang_makan, 0, ',', '.') }}</td>
            </tr>
            @if($detail->potongan_alpha > 0)
            <tr>
                <td>Potongan Alpha</td>
                <td class="text-right">-{{ number_format($detail->potongan_alpha, 0, ',', '.') }}</td>
            </tr>
            @endif

            @if(!empty($breakdown['tunjangan']))
            <tr><td colspan="2" style="background:#f0fdf4; font-weight:600;">Tunjangan Tambahan</td></tr>
            @foreach($breakdown['tunjangan'] as $t)
            <tr>
                <td>&nbsp;&nbsp;{{ $t['nama'] ?? 'Tunjangan' }}</td>
                <td class="text-right">{{ number_format($t['nominal'] ?? 0, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            @endif

            @if(!empty($breakdown['potongan']))
            <tr><td colspan="2" style="background:#fef2f2; font-weight:600;">Potongan Tambahan</td></tr>
            @foreach($breakdown['potongan'] as $p)
            <tr>
                <td>&nbsp;&nbsp;{{ $p['nama'] ?? 'Potongan' }}</td>
                <td class="text-right">-{{ number_format($p['nominal'] ?? 0, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            @endif

            @if(!empty($breakdown['honor_sks']) && $detail->honor_kelebihan_sks > 0)
            <tr><td colspan="2" style="background:#eff6ff; font-weight:600;">Honor Kelebihan SKS</td></tr>
            <tr>
                <td>&nbsp;&nbsp;Honor SKS</td>
                <td class="text-right">{{ number_format($detail->honor_kelebihan_sks, 0, ',', '.') }}</td>
            </tr>
            @endif

            <tr class="total-row">
                <td>TOTAL GAJI</td>
                <td class="text-right">{{ number_format($detail->total_gaji, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada {{ now()->format('d/m/Y H:i') }} — SIMPPAY Universitas Adzkia
    </div>
</body>
</html>
