@extends('layouts.app')

@section('title', 'Manajemen Iuran Warga')
@section('page_title', 'Data & Riwayat Pembayaran Iuran')

@section('content')
<!-- Header & Action Row -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 style="font-size: 18px; font-weight: 700; color: #fff;">Daftar Transaksi Iuran RT 01</h2>
        <p style="font-size: 13px; color: var(--text-muted);">Monitor dan kelola pembayaran iuran wajib maupun insidental warga.</p>
    </div>

    @if(in_array(auth()->user()->role, ['superadmin', 'bendahara']))
    <button onclick="document.getElementById('modal-jenis-iuran').style.display='flex';" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Tambah Master Jenis Iuran
    </button>
    @endif
</div>

<!-- Active Master Iuran Types Cards -->
<div class="grid grid-3" style="margin-bottom: 30px;">
    @foreach($iuranTypes as $type)
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
            <div style="font-weight: 700; font-size: 16px; color: #fff;">{{ $type->name }}</div>
            <span class="badge {{ $type->period_type === 'monthly' ? 'badge-success' : 'badge-pending' }}">
                {{ strtoupper($type->period_type) }}
            </span>
        </div>
        <div style="font-size: 22px; font-weight: 800; color: #34d399; margin-bottom: 8px;">
            Rp {{ number_format($type->amount, 0, ',', '.') }}
        </div>
        <p style="font-size: 12px; color: var(--text-muted); line-height: 1.5;">{{ $type->description }}</p>
    </div>
    @endforeach
</div>

<!-- Payment History Table -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-list-check" style="color: var(--primary);"></i>
            <span>Tabel Catatan Pembayaran Iuran Warga</span>
        </div>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>#ID</th>
                    <th>Nama Warga</th>
                    <th>Jenis Iuran</th>
                    <th>Periode</th>
                    <th>Nominal</th>
                    <th>Metode</th>
                    <th>Status</th>
                    <th>Tgl Verifikasi</th>
                    @if(in_array(auth()->user()->role, ['superadmin', 'bendahara']))
                    <th>Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $p)
                <tr>
                    <td>#{{ $p->id }}</td>
                    <td>
                        <strong>{{ $p->user->name }}</strong><br>
                        <small style="color: var(--text-muted);">{{ $p->user->address }}</small>
                    </td>
                    <td>{{ $p->iuranType->name }}</td>
                    <td>{{ $p->month_name }} {{ $p->period_year }}</td>
                    <td style="font-weight: 700; color: #34d399;">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                    <td><span class="badge badge-pending">{{ strtoupper($p->payment_method) }}</span></td>
                    <td>
                        @if($p->status === 'approved')
                            <span class="badge badge-success">Disetujui</span>
                        @elseif($p->status === 'pending')
                            <span class="badge badge-pending">Pending</span>
                        @else
                            <span class="badge badge-danger">Ditolak</span>
                        @endif
                    </td>
                    <td>
                        {{ $p->verified_at ? $p->verified_at->format('d/m/Y H:i') : '-' }}<br>
                        <small style="color: var(--text-muted);">{{ $p->verifier->name ?? '' }}</small>
                    </td>
                    @if(in_array(auth()->user()->role, ['superadmin', 'bendahara']))
                    <td>
                        @if($p->status === 'pending')
                        <form action="{{ route('iuran.verify', $p->id) }}" method="POST" style="display: flex; gap: 4px;">
                            @csrf
                            <button type="submit" name="action" value="approve" class="btn btn-success" style="padding: 4px 8px; font-size: 11px;">Setujui</button>
                            <button type="submit" name="action" value="reject" class="btn btn-danger" style="padding: 4px 8px; font-size: 11px;">Tolak</button>
                        </form>
                        @else
                        <small style="color: var(--text-muted);">Selesai</small>
                        @endif
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 30px;">Belum ada data transaksi iuran.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 20px;">
        {{ $payments->links() }}
    </div>
</div>

<!-- Modal Create Iuran Type -->
@if(in_array(auth()->user()->role, ['superadmin', 'bendahara']))
<div id="modal-jenis-iuran" style="display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.7); backdrop-filter: blur(5px); z-index: 1000; align-items:center; justify-content:center;">
    <div class="card" style="width: 100%; max-width: 500px;">
        <div class="card-header">
            <div class="card-title">Tambah Master Jenis Iuran Baru</div>
            <button onclick="document.getElementById('modal-jenis-iuran').style.display='none';" class="btn btn-secondary" style="padding: 4px 8px;">&times;</button>
        </div>

        <form action="{{ route('iuran.store_type') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Nama Iuran</label>
                <input type="text" name="name" class="form-control" placeholder="Contoh: Iuran Kebersihan" required>
            </div>
            <div class="form-group">
                <label>Nominal Standar (Rp)</label>
                <input type="number" name="amount" class="form-control" placeholder="50000" required>
            </div>
            <div class="form-group">
                <label>Sifat Periode</label>
                <select name="period_type" class="form-control" required>
                    <option value="monthly">Wajib Bulanan</option>
                    <option value="incident">Insidental / Sekali Bayar</option>
                </select>
            </div>
            <div class="form-group">
                <label>Deskripsi Peruntukan</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Keterangan peruntukan dana iuran..."></textarea>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" onclick="document.getElementById('modal-jenis-iuran').style.display='none';" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Master Iuran</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
