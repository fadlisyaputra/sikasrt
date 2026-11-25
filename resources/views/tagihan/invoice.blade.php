<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuitansi Tagihan - {{ $bill->bill_code }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: #f1f5f9; color: #1e293b; padding: 40px 20px; }
        .invoice-card {
            max-width: 650px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            padding: 40px;
            border: 1px solid #e2e8f0;
            position: relative;
        }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e2e8f0; padding-bottom: 20px; margin-bottom: 24px; }
        .brand h2 { font-size: 20px; font-weight: 800; color: #4338ca; }
        .brand p { font-size: 12px; color: #64748b; margin-top: 2px; }
        .stamp {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .stamp-paid { background: #ecfdf5; color: #059669; border: 2px solid #10b981; }
        .stamp-unpaid { background: #fef2f2; color: #dc2626; border: 2px solid #ef4444; }
        .stamp-pending { background: #fffbeb; color: #d97706; border: 2px solid #f59e0b; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; font-size: 13px; }
        .info-box h4 { font-size: 11px; text-transform: uppercase; color: #94a3b8; font-weight: 700; margin-bottom: 4px; }
        .info-box p { font-size: 14px; font-weight: 700; color: #1e293b; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th { background: #f8fafc; padding: 12px 14px; text-align: left; font-size: 12px; color: #64748b; text-transform: uppercase; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; }
        td { padding: 14px; font-size: 13px; border-bottom: 1px solid #f1f5f9; }
        .total-row { background: #f8fafc; font-weight: 800; font-size: 16px; }
        .footer { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 40px; padding-top: 20px; border-top: 1px dashed #cbd5e1; }
        .btn-print { background: #4f46e5; color: #fff; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 13px; }
        @media print {
            body { background: #fff; padding: 0; }
            .invoice-card { box-shadow: none; border: none; padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div style="max-width: 650px; margin: 0 auto 16px; display: flex; justify-content: space-between; align-items: center;" class="no-print">
        <a href="{{ route('tagihan.index') }}" style="color: #64748b; text-decoration: none; font-size: 13px; font-weight: 600;">&larr; Kembali ke Daftar Tagihan</a>
        <button onclick="window.print()" class="btn-print">&#128438; Cetak / Simpan PDF</button>
    </div>

    <div class="invoice-card">
        <div class="header">
            <div class="brand">
                <h2>SIKAS-RT 01 / RW 05</h2>
                <p>Sistem Informasi Kas & Lingkungan RT Terpadu</p>
                <p style="font-size: 11px; margin-top: 2px;">Kelurahan Cipinang, Kec. Pulogadung, Jakarta Timur</p>
            </div>
            <div style="text-align: right;">
                @if($bill->status === 'paid')
                    <div class="stamp stamp-paid">&#10004; LUNAS</div>
                @elseif($bill->status === 'pending')
                    <div class="stamp stamp-pending">&#9200; VERIFIKASI</div>
                @else
                    <div class="stamp stamp-unpaid">&#10008; BELUM LUNAS</div>
                @endif
                <div style="font-family: monospace; font-size: 12px; color: #64748b; margin-top: 6px;">{{ $bill->bill_code }}</div>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-box">
                <h4>Ditagihkan Kepada:</h4>
                <p>{{ $bill->user->name }}</p>
                <div style="color: #64748b; font-size: 12px; margin-top: 2px;">
                    Alamat: {{ $bill->user->address ?? 'RT 01 / RW 05' }}<br>
                    Status: Warga {{ ucfirst($bill->user->status_warga) }} &bull; {{ $bill->user->phone ?? '-' }}
                </div>
            </div>
            <div class="info-box" style="text-align: right;">
                <h4>Detail Tagihan:</h4>
                <p>{{ \Carbon\Carbon::create()->month($bill->period_month)->translatedFormat('F') }} {{ $bill->period_year }}</p>
                <div style="color: #64748b; font-size: 12px; margin-top: 2px;">
                    Jatuh Tempo: {{ \Carbon\Carbon::parse($bill->due_date)->translatedFormat('d F Y') }}<br>
                    @if($bill->paid_at)
                        Tanggal Bayar: {{ \Carbon\Carbon::parse($bill->paid_at)->translatedFormat('d F Y H:i') }} ({{ strtoupper($bill->payment_method ?? 'TUNAI') }})
                    @endif
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Deskripsi Rincian Iuran</th>
                    <th style="text-align: right;">Nominal</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>{{ $bill->iuranType->name }}</strong>
                        <div style="font-size: 12px; color: #64748b; margin-top: 2px;">{{ $bill->notes ?? 'Iuran operasional dan pemeliharaan lingkungan RT' }}</div>
                    </td>
                    <td style="text-align: right; font-weight: 700;">Rp {{ number_format($bill->amount, 0, ',', '.') }}</td>
                </tr>
                <tr class="total-row">
                    <td>TOTAL TAGIHAN</td>
                    <td style="text-align: right; color: #4338ca;">Rp {{ number_format($bill->amount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            <div style="font-size: 11px; color: #94a3b8; max-width: 320px;">
                Dokumen ini merupakan bukti tagihan dan kuitansi pembayaran resmi yang diterbitkan secara otomatis oleh sistem aplikasi SIKAS-RT.
            </div>
            <div style="text-align: center;">
                <div style="font-size: 11px; color: #64748b; margin-bottom: 45px;">Pengurus Bendahara RT 01</div>
                <div style="font-weight: 700; font-size: 13px; text-decoration: underline;">Hj. Ratna</div>
                <div style="font-size: 11px; color: #94a3b8;">Bendahara RT 01 / RW 05</div>
            </div>
        </div>
    </div>

</body>
</html>
