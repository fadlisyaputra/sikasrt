@extends('layouts.app')

@section('title', 'Dashboard Bendahara')
@section('page_title', 'Keuangan & Pembukuan Kas (Bendahara)')

@section('content')
<!-- Financial Metrics -->
<div class="grid grid-4" style="margin-bottom: 30px;">
    <div class="card metric-card">
        <div class="metric-icon" style="background: var(--gradient-accent);">
            <i class="fa-solid fa-vault"></i>
        </div>
        <div class="metric-value">Rp {{ number_format($totalKas, 0, ',', '.') }}</div>
        <div class="metric-sub">Saldo Kas RT Terkini</div>
    </div>

    <div class="card metric-card">
        <div class="metric-icon" style="background: var(--gradient-emerald);">
            <i class="fa-solid fa-money-bill-trend-up"></i>
        </div>
        <div class="metric-value" style="color: #34d399;">Rp {{ number_format($pemasukanBulanIni, 0, ',', '.') }}</div>
        <div class="metric-sub">Pemasukan Bulan Ini</div>
    </div>

    <div class="card metric-card">
        <div class="metric-icon" style="background: var(--gradient-sky);">
            <i class="fa-solid fa-microchip"></i>
        </div>
        <div class="metric-value" style="color: #38bdf8;">Rp {{ number_format($iotKasMasuk, 0, ',', '.') }}</div>
        <div class="metric-sub">Kas Masuk Otomatis via IoT</div>
    </div>

    <div class="card metric-card">
        <div class="metric-icon" style="background: var(--gradient-amber);">
            <i class="fa-solid fa-receipt"></i>
        </div>
        <div class="metric-value" style="color: #fbbf24;">{{ count($pendingPayments) }}</div>
        <div class="metric-sub">Antrean Bukti Iuran Pending</div>
    </div>
</div>

<!-- Pending Verification Queue -->
<div class="card" style="margin-bottom: 30px;">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-hourglass-half" style="color: #fbbf24;"></i>
            <span>Verifikasi Pembayaran Iuran Warga (Pending)</span>
        </div>
    </div>

    <div class="table-responsive">
        @if(count($pendingPayments) > 0)
        <table>
            <thead>
                <tr>
                    <th>Nama Warga</th>
                    <th>Jenis Iuran</th>
                    <th>Periode</th>
                    <th>Nominal</th>
                    <th>Metode</th>
                    <th>Bukti Transfer</th>
                    <th>Aksi Verifikasi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendingPayments as $payment)
                <tr>
                    <td>
                        <strong>{{ $payment->user->name }}</strong><br>
                        <small style="color: var(--text-muted);">{{ $payment->user->address }}</small>
                    </td>
                    <td>{{ $payment->iuranType->name }}</td>
                    <td>{{ $payment->month_name }} {{ $payment->period_year }}</td>
                    <td style="font-weight: 700; color: #34d399;">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                    <td>
                        <span class="badge badge-pending">{{ strtoupper($payment->payment_method) }}</span>
                    </td>
                    <td>
                        @if($payment->proof_image)
                            <a href="{{ asset($payment->proof_image) }}" target="_blank" class="btn btn-secondary" style="padding: 4px 10px; font-size: 11px;">
                                <i class="fa-solid fa-image"></i> Lihat Bukti
                            </a>
                        @else
                            <small style="color: var(--text-muted);">Tidak ada lampiran</small>
                        @endif
                    </td>
                    <td>
                        <form action="{{ route('iuran.verify', $payment->id) }}" method="POST" style="display: inline-flex; gap: 6px;">
                            @csrf
                            <button type="submit" name="action" value="approve" class="btn btn-success" style="padding: 6px 12px; font-size: 12px;">
                                <i class="fa-solid fa-check"></i> Setujui
                            </button>
                            <button type="submit" name="action" value="reject" class="btn btn-danger" style="padding: 6px 12px; font-size: 12px;">
                                <i class="fa-solid fa-xmark"></i> Tolak
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div style="text-align: center; padding: 30px; color: var(--text-muted);">
            <i class="fa-solid fa-circle-check" style="font-size: 32px; color: #34d399; margin-bottom: 10px;"></i>
            <p>Semua pembayaran iuran warga telah diverifikasi.</p>
        </div>
        @endif
    </div>
</div>

<div class="grid grid-2">
    <!-- Quick Transaction Form -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-circle-plus" style="color: var(--primary);"></i>
                <span>Catat Pemasukan / Pengeluaran Langsung</span>
            </div>
        </div>

        <form action="{{ route('kas.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label>Jenis Transaksi</label>
                <select name="type" class="form-control" required>
                    <option value="pemasukan">Pemasukan (+)</option>
                    <option value="pengeluaran">Pengeluaran (-)</option>
                </select>
            </div>

            <div class="grid grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label>Kategori</label>
                    <input type="text" name="category" class="form-control" placeholder="Contoh: Kebersihan, Keamanan, Donasi" required>
                </div>
                <div class="form-group">
                    <label>Nominal (Rp)</label>
                    <input type="number" name="amount" class="form-control" placeholder="50000" required>
                </div>
            </div>

            <div class="form-group">
                <label>Tanggal Transaksi</label>
                <input type="date" name="transaction_date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>

            <div class="form-group">
                <label>Uraian / Keterangan</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Deskripsi rincian pengeluaran/pemasukan..." required></textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <i class="fa-solid fa-floppy-disk"></i> Simpan Transaksi Kas
            </button>
        </form>
    </div>

    <!-- Recent Transactions -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-clock-rotate-left" style="color: var(--secondary);"></i>
                <span>Buku Kas Terakhir</span>
            </div>
            <a href="{{ route('kas.index') }}" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">Buka Ledger</a>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th>Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentTransactions as $tx)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($tx->transaction_date)->format('d/m/Y') }}</td>
                        <td>
                            <strong>{{ $tx->category }}</strong><br>
                            <small style="color: var(--text-muted);">{{ Str::limit($tx->description, 30) }}</small>
                        </td>
                        <td style="font-weight: 700; color: {{ $tx->type === 'pemasukan' ? '#34d399' : '#f87171' }}">
                            {{ $tx->type === 'pemasukan' ? '+' : '-' }} Rp {{ number_format($tx->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
