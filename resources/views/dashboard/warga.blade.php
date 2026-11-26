@extends('layouts.app')

@section('title', 'Portal Warga')
@section('page_title', 'Portal Keuangan & Tagihan Iuran Warga')

@section('content')
<!-- Warga Welcome Banner -->
<div class="card" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.2) 0%, rgba(14, 165, 233, 0.2) 100%); margin-bottom: 24px;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2 style="font-size: 20px; font-weight: 800; color: #fff; margin-bottom: 6px;">Halo, {{ auth()->user()->name }} 👋</h2>
            <p style="color: var(--text-muted); font-size: 14px;">Alamat: <strong>{{ auth()->user()->address ?? 'RT 01 / RW 05' }}</strong> &bull; Status Warga: <strong>{{ strtoupper(auth()->user()->status_warga) }}</strong></p>
        </div>

        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            @if($myRfidCard || auth()->user()->rfid_uid)
                <a href="{{ route('ktp.index') }}" style="text-decoration: none;">
                    <div style="background: rgba(6, 182, 212, 0.2); border: 1px solid #06b6d4; border-radius: 20px; padding: 6px 14px; font-size: 12px; color: #38bdf8; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-id-card"></i> e-KTP: <strong style="font-family: monospace;">{{ $myRfidCard ? $myRfidCard->rfid_uid : auth()->user()->rfid_uid }}</strong> (Akses Portal Aktif) &rarr;
                    </div>
                </a>
            @else
                <a href="{{ route('ktp.index') }}" class="btn btn-primary" style="padding: 6px 14px; font-size: 12px; border-radius: 20px; text-decoration: none;">
                    <i class="fa-solid fa-qrcode"></i> Daftarkan e-KTP Anda &rarr;
                </a>
            @endif

            @if($hasPaidThisMonth)
                <a href="{{ route('tagihan.index') }}" style="text-decoration: none;">
                    <div class="badge badge-success" style="padding: 10px 18px; font-size: 13px;">
                        <i class="fa-solid fa-circle-check"></i> Iuran Bulan Ini ({{ \Carbon\Carbon::now()->translatedFormat('F Y') }}) Lunas &bull; Lihat Tagihan &rarr;
                    </div>
                </a>
            @else
                <a href="{{ route('tagihan.index') }}" style="text-decoration: none;">
                    <div class="badge badge-pending" style="padding: 10px 18px; font-size: 13px;">
                        <i class="fa-solid fa-clock"></i> Ada Tagihan Iuran Bulan Ini &bull; Bayar Sekarang &rarr;
                    </div>
                </a>
            @endif
        </div>
    </div>
</div>

<!-- Shortcut Layanan Cerdas Warga: Tagihan, Ronda & Posyandu -->
<div class="grid grid-3" style="margin-bottom: 24px; gap: 14px;">
    <a href="{{ route('tagihan.index') }}" style="text-decoration: none;">
        <div class="card" style="display: flex; align-items: center; gap: 14px; padding: 16px; border: 1px solid rgba(56, 189, 248, 0.3); transition: transform 0.2s;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(56, 189, 248, 0.2); color: #38bdf8; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
            <div>
                <h4 style="font-size: 14px; font-weight: 700; color: #fff;">Tagihan Warga</h4>
                <p style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">Cek rincian tagihan & kuitansi resmi</p>
            </div>
        </div>
    </a>

    <a href="{{ route('ronda.index') }}" style="text-decoration: none;">
        <div class="card" style="display: flex; align-items: center; gap: 14px; padding: 16px; border: 1px solid rgba(52, 211, 153, 0.3); transition: transform 0.2s;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(52, 211, 153, 0.2); color: #34d399; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <h4 style="font-size: 14px; font-weight: 700; color: #fff;">Jadwal Ronda Siskamling</h4>
                <p style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">Cek giliran jaga malam & absensi</p>
            </div>
        </div>
    </a>

    <a href="{{ route('posyandu.index') }}" style="text-decoration: none;">
        <div class="card" style="display: flex; align-items: center; gap: 14px; padding: 16px; border: 1px solid rgba(244, 114, 182, 0.3); transition: transform 0.2s;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(244, 114, 182, 0.2); color: #f472b6; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                <i class="fa-solid fa-heart-pulse"></i>
            </div>
            <div>
                <h4 style="font-size: 14px; font-weight: 700; color: #fff;">Layanan Posyandu</h4>
                <p style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">Kesehatan balita, lansia & imunisasi</p>
            </div>
        </div>
    </a>
</div>

<!-- Shortcut Layanan Warga: Surat, UMKM, Bank Sampah -->
<div class="grid grid-3" style="margin-bottom: 24px; gap: 14px;">
    <a href="{{ route('surat.index') }}" style="text-decoration: none;">
        <div class="card" style="display: flex; align-items: center; gap: 14px; padding: 16px; border: 1px solid rgba(167, 139, 250, 0.3); transition: transform 0.2s;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(167, 139, 250, 0.2); color: #a78bfa; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                <i class="fa-solid fa-envelope-open-text"></i>
            </div>
            <div>
                <h4 style="font-size: 14px; font-weight: 700; color: #fff;">Surat Pengantar RT</h4>
                <p style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">Ajukan surat online & cetak resmi</p>
            </div>
        </div>
    </a>

    <a href="{{ route('umkm.index') }}" style="text-decoration: none;">
        <div class="card" style="display: flex; align-items: center; gap: 14px; padding: 16px; border: 1px solid rgba(251, 191, 36, 0.3); transition: transform 0.2s;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(251, 191, 36, 0.2); color: #fbbf24; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                <i class="fa-solid fa-store"></i>
            </div>
            <div>
                <h4 style="font-size: 14px; font-weight: 700; color: #fff;">Etalase UMKM Warga</h4>
                <p style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">Promosikan usaha & belanja tetangga</p>
            </div>
        </div>
    </a>

    <a href="{{ route('bank_sampah.index') }}" style="text-decoration: none;">
        <div class="card" style="display: flex; align-items: center; gap: 14px; padding: 16px; border: 1px solid rgba(45, 212, 191, 0.3); transition: transform 0.2s;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(45, 212, 191, 0.2); color: #2dd4bf; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                <i class="fa-solid fa-recycle"></i>
            </div>
            <div>
                <h4 style="font-size: 14px; font-weight: 700; color: #fff;">Bank Sampah RT</h4>
                <p style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">Tabungan daur ulang & cek saldo</p>
            </div>
        </div>
    </a>
</div>

<div class="grid grid-3" style="margin-bottom: 30px;">
    <div class="card metric-card">
        <div class="metric-icon" style="background: var(--gradient-accent);">
            <i class="fa-solid fa-wallet"></i>
        </div>
        <div class="metric-value">Rp {{ number_format($totalKas, 0, ',', '.') }}</div>
        <div class="metric-sub">Total Kas Terbuka RT 01</div>
    </div>

    <div class="card metric-card">
        <div class="metric-icon" style="background: var(--gradient-emerald);">
            <i class="fa-solid fa-arrow-down"></i>
        </div>
        <div class="metric-value" style="color: #34d399;">Rp {{ number_format($pemasukanBulanIni, 0, ',', '.') }}</div>
        <div class="metric-sub">Total Pemasukan RT Bulan Ini</div>
    </div>

    <div class="card metric-card">
        <div class="metric-icon" style="background: var(--gradient-amber);">
            <i class="fa-solid fa-arrow-up"></i>
        </div>
        <div class="metric-value" style="color: #f87171;">Rp {{ number_format($pengeluaranBulanIni, 0, ',', '.') }}</div>
        <div class="metric-sub">Total Pengeluaran RT Bulan Ini</div>
    </div>
</div>

<div class="grid grid-2">
    <!-- Form Upload Bukti Transfer -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-cloud-arrow-up" style="color: var(--primary);"></i>
                <span>Form Bayar / Upload Bukti Transfer Iuran</span>
            </div>
        </div>

        <form action="{{ route('iuran.pay') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label>Pilih Jenis Iuran</label>
                <select name="iuran_type_id" class="form-control" required>
                    @foreach($activeIuranTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }} - Rp {{ number_format($type->amount, 0, ',', '.') }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label>Bulan Iuran</label>
                    <select name="period_month" class="form-control" required>
                        @for($m=1; $m<=12; $m++)
                            <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>
                                {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div class="form-group">
                    <label>Tahun</label>
                    <input type="number" name="period_year" class="form-control" value="{{ date('Y') }}" required>
                </div>
            </div>

            <div class="grid grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label>Nominal Pembayaran (Rp)</label>
                    <input type="number" name="amount" class="form-control" placeholder="50000" required>
                </div>

                <div class="form-group">
                    <label>Metode Pembayaran</label>
                    <select name="payment_method" class="form-control" required>
                        <option value="transfer">Transfer Bank / QRIS</option>
                        <option value="cash">Titip Tunai ke Bendahara</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Upload Foto/Scan Bukti Transfer (Opsional)</label>
                <input type="file" name="proof_image" class="form-control" accept="image/*">
            </div>

            <div class="form-group">
                <label>Catatan Tambahan</label>
                <input type="text" name="notes" class="form-control" placeholder="Contoh: Titip di pos ronda / Transfer dari BCA a.n Budi">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <i class="fa-solid fa-paper-plane"></i> Kirim Bukti Pembayaran
            </button>
        </form>
    </div>

    <!-- Payment History Table -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-history" style="color: var(--secondary);"></i>
                <span>Riwayat Pembayaran Saya</span>
            </div>
        </div>

        <div class="desktop-only-table table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th>Jenis Iuran</th>
                        <th>Nominal</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($myPayments as $payment)
                    <tr>
                        <td><strong>{{ $payment->month_name }} {{ $payment->period_year }}</strong></td>
                        <td>{{ $payment->iuranType->name }}</td>
                        <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                        <td>
                            @if($payment->status === 'approved')
                                <span class="badge badge-success">Disetujui</span>
                            @elseif($payment->status === 'pending')
                                <span class="badge badge-pending">Menunggu Verifikasi</span>
                            @else
                                <span class="badge badge-danger">Ditolak</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: var(--text-muted);">Belum ada riwayat pembayaran.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile-Only Payment History Cards -->
        <div class="mobile-only-cards">
            @forelse($myPayments as $payment)
            <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong style="color: #fff; font-size: 13.5px;">{{ $payment->iuranType->name }}</strong>
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                        <i class="fa-regular fa-calendar"></i> {{ $payment->month_name }} {{ $payment->period_year }}
                    </div>
                </div>
                <div style="text-align: right; flex-shrink: 0; margin-left: 8px;">
                    <div style="font-weight: 800; color: #34d399; font-size: 14px;">
                        Rp {{ number_format($payment->amount, 0, ',', '.') }}
                    </div>
                    <div style="margin-top: 4px;">
                        @if($payment->status === 'approved')
                            <span class="badge badge-success" style="font-size: 9.5px; padding: 2px 7px;">Disetujui</span>
                        @elseif($payment->status === 'pending')
                            <span class="badge badge-pending" style="font-size: 9.5px; padding: 2px 7px;">Verifikasi</span>
                        @else
                            <span class="badge badge-danger" style="font-size: 9.5px; padding: 2px 7px;">Ditolak</span>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div style="text-align: center; padding: 20px; color: var(--text-muted); font-size: 12px;">
                Belum ada riwayat pembayaran.
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
