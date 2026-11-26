@extends('layouts.app')

@section('title', 'Dashboard Ketua RT')
@section('page_title', 'Ringkasan Eksekutif & Monitoring RT')

@section('content')
<!-- Financial Summary Cards -->
<div class="grid grid-4" style="margin-bottom: 30px;">
    <div class="card metric-card">
        <div class="metric-icon" style="background: var(--gradient-accent);">
            <i class="fa-solid fa-scale-balanced"></i>
        </div>
        <div class="metric-value">Rp {{ number_format($totalKas, 0, ',', '.') }}</div>
        <div class="metric-sub">Total Saldo Kas RT 01</div>
    </div>

    <div class="card metric-card">
        <div class="metric-icon" style="background: var(--gradient-emerald);">
            <i class="fa-solid fa-file-invoice-dollar"></i>
        </div>
        <div class="metric-value" style="color: #34d399;">Rp {{ number_format($pemasukanBulanIni, 0, ',', '.') }}</div>
        <div class="metric-sub">Pemasukan Bulan Ini</div>
    </div>

    <div class="card metric-card">
        <div class="metric-icon" style="background: var(--gradient-amber);">
            <i class="fa-solid fa-hand-holding-dollar"></i>
        </div>
        <div class="metric-value" style="color: #f87171;">Rp {{ number_format($pengeluaranBulanIni, 0, ',', '.') }}</div>
        <div class="metric-sub">Pengeluaran Bulan Ini</div>
    </div>

    <div class="card metric-card">
        <div class="metric-icon" style="background: var(--gradient-sky);">
            <i class="fa-solid fa-house-chimney-user"></i>
        </div>
        <div class="metric-value">{{ $totalWarga }} KK</div>
        <div class="metric-sub">Total Kepala Keluarga Warga</div>
    </div>
</div>

<!-- IoT Pos Ronda & Security Monitoring Banner -->
<div class="card" style="margin-bottom: 26px; border: 1px solid rgba(14, 165, 233, 0.4); background: linear-gradient(135deg, rgba(14, 165, 233, 0.08) 0%, rgba(30, 41, 59, 0.9) 100%);">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(14, 165, 233, 0.2); color: #38bdf8; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <h4 style="font-size: 15px; font-weight: 700; color: #fff;">Sistem Keamanan & Pos Ronda IoT ESP32</h4>
                <p style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                    Portal Gerbang: <span style="color: #34d399; font-weight: 600;">Otomatis RFID Aktif</span> &bull; 
                    Pos Siskamling: <span style="color: #38bdf8;">{{ $iotOnlineCount }} Node Terhubung</span> &bull; 
                    Status: <span style="font-weight: 700; color: {{ $iotAlertCount > 0 ? '#f87171' : '#34d399' }}">{{ $iotAlertCount > 0 ? 'WASPADA (Alarm Bunyi)' : 'Lingkungan Aman & Kondusif' }}</span>
                </p>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="{{ route('iot.index') }}" class="btn btn-primary" style="background: linear-gradient(135deg, #0284c7, #2563eb); font-size: 12px;">
                <i class="fa-solid fa-tower-broadcast"></i> Pantau Portal & Pos Ronda
            </a>
        </div>
    </div>
</div>

<div class="grid grid-2">
    <!-- Payment Compliance per Warga -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-user-check" style="color: #34d399;"></i>
                <span>Status Pembayaran Iuran Bulan {{ \Carbon\Carbon::now()->translatedFormat('F Y') }}</span>
            </div>
            <a href="{{ route('warga.index') }}" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">Direktori Warga</a>
        </div>

        <div class="desktop-only-table table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Nama Warga</th>
                        <th>Alamat Rumah</th>
                        <th>Status Iuran</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($wargaStatusList as $warga)
                    <tr>
                        <td>
                            <strong>{{ $warga->name }}</strong><br>
                            <small style="color: var(--text-muted);">{{ $warga->phone ?? '-' }}</small>
                        </td>
                        <td>{{ $warga->address }}</td>
                        <td>
                            @if($warga->has_paid)
                                <span class="badge badge-success"><i class="fa-solid fa-check"></i> Sudah Bayar</span>
                            @else
                                <span class="badge badge-danger"><i class="fa-solid fa-clock"></i> Belum Bayar</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Mobile-Only Warga Status Cards -->
        <div class="mobile-only-cards">
            @foreach($wargaStatusList as $warga)
            <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-color); border-radius: 12px; padding: 12px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong style="color: #fff; font-size: 13.5px;">{{ $warga->name }}</strong>
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">{{ $warga->address }}</div>
                    @if($warga->phone)
                    <div style="font-size: 10px; color: #38bdf8; margin-top: 2px;"><i class="fa-solid fa-phone"></i> {{ $warga->phone }}</div>
                    @endif
                </div>
                <div style="text-align: right; flex-shrink: 0; margin-left: 8px;">
                    @if($warga->has_paid)
                        <span class="badge badge-success" style="font-size: 10px; padding: 3px 8px;"><i class="fa-solid fa-check"></i> Lunas</span>
                    @else
                        <span class="badge badge-danger" style="font-size: 10px; padding: 3px 8px;"><i class="fa-solid fa-clock"></i> Belum</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Right Column: Announcements & Ledger -->
    <div>
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-solid fa-bullhorn" style="color: #fbbf24;"></i>
                    <span>Pengumuman RT Terbaru</span>
                </div>
                <a href="{{ route('announcements.index') }}" class="btn btn-primary" style="padding: 6px 12px; font-size: 12px;">Buat Pengumuman</a>
            </div>

            <div style="display: flex; flex-direction: column; gap: 16px;">
                @foreach($recentAnnouncements as $ann)
                <div style="background: rgba(15, 23, 42, 0.6); padding: 14px; border-radius: 12px; border-left: 3px solid var(--primary);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <strong style="color: #fff; font-size: 14px;">{{ $ann->title }}</strong>
                        <span class="badge badge-pending">{{ $ann->category }}</span>
                    </div>
                    <p style="font-size: 13px; color: var(--text-muted); line-height: 1.5;">{{ Str::limit($ann->content, 120) }}</p>
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 8px;">
                        Diposting oleh: {{ $ann->author->name }} &bull; {{ $ann->created_at->diffForHumans() }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-solid fa-list" style="color: var(--secondary);"></i>
                    <span>Pengeluaran RT Terakhir</span>
                </div>
                <a href="{{ route('kas.index') }}" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">Buka Kas</a>
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Keterangan</th>
                            <th>Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentTransactions->where('type', 'pengeluaran')->take(4) as $tx)
                        <tr>
                            <td>
                                <strong>{{ $tx->description }}</strong><br>
                                <small style="color: var(--text-muted);">{{ \Carbon\Carbon::parse($tx->transaction_date)->format('d M Y') }}</small>
                            </td>
                            <td style="font-weight: 700; color: #f87171;">
                                - Rp {{ number_format($tx->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
