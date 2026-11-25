@extends('layouts.app')

@section('title', 'Laporan Kas RT')
@section('page_title', 'Buku Kas & Jurnal Transaksi RT 01')

@section('content')
<!-- Metric Row -->
<div class="grid grid-3" style="margin-bottom: 30px;">
    <div class="card metric-card">
        <div class="metric-icon" style="background: var(--gradient-accent);">
            <i class="fa-solid fa-vault"></i>
        </div>
        <div class="metric-value">Rp {{ number_format($balance, 0, ',', '.') }}</div>
        <div class="metric-sub">Saldo Kas RT Saat Ini</div>
    </div>

    <div class="card metric-card">
        <div class="metric-icon" style="background: var(--gradient-emerald);">
            <i class="fa-solid fa-circle-arrow-down"></i>
        </div>
        <div class="metric-value" style="color: #34d399;">Rp {{ number_format($totalIn, 0, ',', '.') }}</div>
        <div class="metric-sub">Akumulasi Total Pemasukan</div>
    </div>

    <div class="card metric-card">
        <div class="metric-icon" style="background: var(--gradient-amber);">
            <i class="fa-solid fa-circle-arrow-up"></i>
        </div>
        <div class="metric-value" style="color: #f87171;">Rp {{ number_format($totalOut, 0, ',', '.') }}</div>
        <div class="metric-sub">Akumulasi Total Pengeluaran</div>
    </div>
</div>

<!-- Control Bar & Filter -->
<div class="card" style="margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <form action="{{ route('kas.index') }}" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
            <select name="type" class="form-control" style="width: auto;">
                <option value="">-- Semua Jenis --</option>
                <option value="pemasukan" {{ request('type') == 'pemasukan' ? 'selected' : '' }}>Pemasukan (+)</option>
                <option value="pengeluaran" {{ request('type') == 'pengeluaran' ? 'selected' : '' }}>Pengeluaran (-)</option>
            </select>

            <select name="month" class="form-control" style="width: auto;">
                <option value="">-- Semua Bulan --</option>
                @for($m=1; $m<=12; $m++)
                    <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                        {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                    </option>
                @endfor
            </select>

            <button type="submit" class="btn btn-secondary">
                <i class="fa-solid fa-filter"></i> Filter
            </button>
        </form>

        @if(in_array(auth()->user()->role, ['superadmin', 'bendahara']))
        <button onclick="document.getElementById('modal-tambah-kas').style.display='flex';" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Catat Transaksi Baru
        </button>
        @endif
    </div>
</div>

<!-- Ledger Table -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-book" style="color: var(--primary);"></i>
            <span>Jurnal Buku Kas RT</span>
        </div>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Tgl Transaksi</th>
                    <th>Tipe</th>
                    <th>Kategori</th>
                    <th>Uraian / Keterangan</th>
                    <th>Nominal</th>
                    <th>Nota/Bukti</th>
                    <th>Pencatat</th>
                    @if(in_array(auth()->user()->role, ['superadmin', 'bendahara']))
                    <th>Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $tx)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($tx->transaction_date)->format('d/m/Y') }}</td>
                    <td>
                        <span class="badge {{ $tx->type === 'pemasukan' ? 'badge-success' : 'badge-danger' }}">
                            {{ strtoupper($tx->type) }}
                        </span>
                    </td>
                    <td><strong>{{ $tx->category }}</strong></td>
                    <td>{{ $tx->description }}</td>
                    <td style="font-weight: 800; color: {{ $tx->type === 'pemasukan' ? '#34d399' : '#f87171' }}">
                        {{ $tx->type === 'pemasukan' ? '+' : '-' }} Rp {{ number_format($tx->amount, 0, ',', '.') }}
                    </td>
                    <td>
                        @if($tx->receipt_file)
                            <a href="{{ asset($tx->receipt_file) }}" target="_blank" class="btn btn-secondary" style="padding: 4px 8px; font-size: 11px;">
                                <i class="fa-solid fa-paperclip"></i> Lampiran
                            </a>
                        @else
                            <small style="color: var(--text-muted);">-</small>
                        @endif
                    </td>
                    <td>{{ $tx->creator->name ?? 'System' }}</td>
                    @if(in_array(auth()->user()->role, ['superadmin', 'bendahara']))
                    <td>
                        <form action="{{ route('kas.destroy', $tx->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan kas ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" style="padding: 4px 8px; font-size: 11px;" title="Hapus">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 30px;">Tidak ada transaksi kas ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        {{ $transactions->links() }}
    </div>
</div>

<!-- Modal Tambah Transaksi Kas -->
@if(in_array(auth()->user()->role, ['superadmin', 'bendahara']))
<div id="modal-tambah-kas" style="display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.7); backdrop-filter: blur(5px); z-index: 1000; align-items:center; justify-content:center;">
    <div class="card" style="width: 100%; max-width: 500px;">
        <div class="card-header">
            <div class="card-title">Input Transaksi Kas RT Baru</div>
            <button onclick="document.getElementById('modal-tambah-kas').style.display='none';" class="btn btn-secondary" style="padding: 4px 8px;">&times;</button>
        </div>

        <form action="{{ route('kas.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label>Jenis Arus Kas</label>
                <select name="type" class="form-control" required>
                    <option value="pemasukan">Pemasukan (+)</option>
                    <option value="pengeluaran">Pengeluaran (-)</option>
                </select>
            </div>
            <div class="grid grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label>Kategori</label>
                    <input type="text" name="category" class="form-control" placeholder="Kebersihan, Perbaikan, Donasi..." required>
                </div>
                <div class="form-group">
                    <label>Nominal (Rp)</label>
                    <input type="number" name="amount" class="form-control" placeholder="100000" required>
                </div>
            </div>
            <div class="form-group">
                <label>Tanggal Transaksi</label>
                <input type="date" name="transaction_date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="form-group">
                <label>Rincian Keterangan</label>
                <textarea name="description" class="form-control" rows="3" required></textarea>
            </div>
            <div class="form-group">
                <label>Upload Nota/Kwitansi/Bukti Pembayaran (Opsional)</label>
                <input type="file" name="receipt_file" class="form-control">
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" onclick="document.getElementById('modal-tambah-kas').style.display='none';" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Kas</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
