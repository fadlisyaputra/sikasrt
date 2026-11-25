@extends('layouts.app')

@section('title', 'Dashboard Superadmin')
@section('page_title', 'Dashboard Control Center (Superadmin)')

@section('content')
<!-- Metric Grid -->
<div class="grid grid-4" style="margin-bottom: 30px;">
    <div class="card metric-card">
        <div class="metric-icon" style="background: var(--gradient-accent);">
            <i class="fa-solid fa-wallet"></i>
        </div>
        <div class="metric-value">Rp {{ number_format($totalKas, 0, ',', '.') }}</div>
        <div class="metric-sub">Total Saldo Kas RT Realtime</div>
    </div>

    <div class="card metric-card">
        <div class="metric-icon" style="background: var(--gradient-emerald);">
            <i class="fa-solid fa-arrow-down-left"></i>
        </div>
        <div class="metric-value" style="color: #34d399;">Rp {{ number_format($pemasukanBulanIni, 0, ',', '.') }}</div>
        <div class="metric-sub">Pemasukan Bulan Ini</div>
    </div>

    <div class="card metric-card">
        <div class="metric-icon" style="background: var(--gradient-amber);">
            <i class="fa-solid fa-arrow-up-right"></i>
        </div>
        <div class="metric-value" style="color: #f87171;">Rp {{ number_format($pengeluaranBulanIni, 0, ',', '.') }}</div>
        <div class="metric-sub">Pengeluaran Bulan Ini</div>
    </div>

    <div class="card metric-card">
        <div class="metric-icon" style="background: var(--gradient-sky);">
            <i class="fa-solid fa-users"></i>
        </div>
        <div class="metric-value">{{ $totalUsers }}</div>
        <div class="metric-sub">Total Pengguna Terdaftar</div>
    </div>
</div>

<!-- IoT & ESP32 Quick Status Bar -->
<div class="card" style="margin-bottom: 26px; border: 1px solid rgba(6, 182, 212, 0.4); background: linear-gradient(135deg, rgba(6, 182, 212, 0.08) 0%, rgba(30, 41, 59, 0.9) 100%);">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(6, 182, 212, 0.2); color: #38bdf8; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                <i class="fa-solid fa-microchip"></i>
            </div>
            <div>
                <h4 style="font-size: 15px; font-weight: 700; color: #fff;">Infrastruktur IoT ESP32 SIKAS-RT</h4>
                <p style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                    <span style="color: #34d399; font-weight: 600;"><i class="fa-solid fa-circle-check"></i> {{ $iotOnlineCount }} Node Online</span> &bull; 
                    <span style="color: #38bdf8;">{{ $iotDeviceCount }} Total Gateway</span> &bull; 
                    Status: <span style="font-weight: 700; color: {{ $iotAlertCount > 0 ? '#f87171' : '#34d399' }}">{{ $iotAlertCount > 0 ? 'ALERT / WASPADA' : 'Sistem Normal' }}</span>
                </p>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="{{ route('iot.index') }}" class="btn btn-primary" style="background: linear-gradient(135deg, #06b6d4, #3b82f6); font-size: 12px;">
                <i class="fa-solid fa-gamepad"></i> Buka IoT Hub & Simulator
            </a>
        </div>
    </div>
</div>

<div class="grid grid-2">
    <!-- User Breakdown & System Settings -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-user-gear" style="color: var(--primary);"></i>
                <span>Distribusi Pengguna berdasarkan Role</span>
            </div>
            <a href="{{ route('warga.index') }}" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">Kelola User</a>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Role Akses</th>
                        <th>Jumlah User</th>
                        <th>Keterangan Otoritas</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="user-role-badge role-superadmin">Superadmin</span></td>
                        <td><strong>{{ $userCountsByRole['superadmin'] }}</strong> Account</td>
                        <td>Akses Penuh Master Data & Sistem</td>
                    </tr>
                    <tr>
                        <td><span class="user-role-badge role-bendahara">Bendahara</span></td>
                        <td><strong>{{ $userCountsByRole['bendahara'] }}</strong> Account</td>
                        <td>Input Transaksi & Verifikasi Iuran</td>
                    </tr>
                    <tr>
                        <td><span class="user-role-badge role-rt">RT</span></td>
                        <td><strong>{{ $userCountsByRole['rt'] }}</strong> Account</td>
                        <td>Monitoring Kas & Approval Rekap</td>
                    </tr>
                    <tr>
                        <td><span class="user-role-badge role-warga">Warga</span></td>
                        <td><strong>{{ $userCountsByRole['warga'] }}</strong> Account</td>
                        <td>Portal Bayar Iuran & Transparansi</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Active Master Iuran Types -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-list-check" style="color: var(--secondary);"></i>
                <span>Tarif & Jenis Iuran Aktif</span>
            </div>
            <a href="{{ route('iuran.index') }}" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">Lihat Iuran</a>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Nama Iuran</th>
                        <th>Nominal Standard</th>
                        <th>Tipe Periode</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($iuranTypes as $type)
                    <tr>
                        <td><strong>{{ $type->name }}</strong></td>
                        <td>Rp {{ number_format($type->amount, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge {{ $type->period_type === 'monthly' ? 'badge-success' : 'badge-pending' }}">
                                {{ $type->period_type === 'monthly' ? 'Bulanan' : 'Insidental' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Recent Transactions Log -->
<div class="card" style="margin-top: 24px;">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-clock-rotate-left" style="color: var(--warning);"></i>
            <span>Log Transaksi Kas Terakhir</span>
        </div>
        <a href="{{ route('kas.index') }}" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">Lihat Seluruh Kas</a>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Tipe</th>
                    <th>Kategori</th>
                    <th>Keterangan</th>
                    <th>Jumlah</th>
                    <th>Oleh</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentTransactions as $tx)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($tx->transaction_date)->format('d M Y') }}</td>
                    <td>
                        <span class="badge {{ $tx->type === 'pemasukan' ? 'badge-success' : 'badge-danger' }}">
                            {{ strtoupper($tx->type) }}
                        </span>
                    </td>
                    <td>{{ $tx->category }}</td>
                    <td>{{ $tx->description }}</td>
                    <td style="font-weight: 700; color: {{ $tx->type === 'pemasukan' ? '#34d399' : '#f87171' }}">
                        {{ $tx->type === 'pemasukan' ? '+' : '-' }} Rp {{ number_format($tx->amount, 0, ',', '.') }}
                    </td>
                    <td>{{ $tx->creator->name ?? 'System' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
