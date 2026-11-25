@extends('layouts.app')

@section('title', 'Smart IoT & ESP32 Hub')
@section('page_title', 'Smart IoT & ESP32 Hub')

@section('content')
<div style="display: flex; flex-direction: column; gap: 28px;">

    <!-- Active Emergency Alert Warning Banner -->
    @if($alertCount > 0)
    <div style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); border-radius: 16px; padding: 24px; color: #fff; box-shadow: 0 10px 30px rgba(239, 68, 68, 0.5); border: 2px solid #fca5a5;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 26px;">
                    <i class="fa-solid fa-bell fa-shake"></i>
                </div>
                <div>
                    <h2 style="font-size: 20px; font-weight: 800; letter-spacing: -0.5px;">PERINGATAN DARURAT AKTIF ({{ $alertCount }} Kejadian)</h2>
                    <p style="font-size: 13px; opacity: 0.95; margin-top: 4px;">
                        Sirene darurat pos keamanan aktif. Mohon petugas siskamling dan pengurus segera melakukan pengecekan ke lokasi.
                    </p>
                </div>
            </div>
            @foreach($activeAlerts as $alert)
            <div style="background: rgba(0,0,0,0.25); border-radius: 12px; padding: 12px 18px; display: flex; align-items: center; gap: 14px; width: 100%; justify-content: space-between;">
                <div>
                    <strong><i class="fa-solid fa-location-dot"></i> {{ $alert->location }}</strong> — 
                    <span style="font-size: 12px;">{{ $alert->description ?? 'Alarm panik ditekan' }} ({{ $alert->created_at->diffForHumans() }})</span>
                </div>
                @if(in_array(auth()->user()->role, ['superadmin', 'bendahara', 'rt']))
                <form action="{{ route('iot.emergency.resolve', $alert->id) }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn btn-success" style="padding: 6px 14px; font-size: 12px;">
                        <i class="fa-solid fa-check-double"></i> Tandai Aman / Selesai
                    </button>
                </form>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Metric Overview Cards -->
    <div class="grid grid-4">
        <div class="card metric-card">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <span class="metric-sub">Total Node ESP32</span>
                    <h3 class="metric-value">{{ $totalDevices }} <span style="font-size: 14px; font-weight: 500; color: #94a3b8;">Unit</span></h3>
                    <span class="badge badge-info"><i class="fa-solid fa-microchip"></i> Gateway RT</span>
                </div>
                <div class="metric-icon" style="background: var(--gradient-sky);">
                    <i class="fa-solid fa-tower-broadcast"></i>
                </div>
            </div>
        </div>

        <div class="card metric-card">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <span class="metric-sub">Perangkat Online</span>
                    <h3 class="metric-value" style="color: #34d399;">{{ $onlineCount }} <span style="font-size: 14px; font-weight: 500; color: #94a3b8;">/ {{ $totalDevices }}</span></h3>
                    <span class="badge badge-success"><i class="fa-solid fa-wifi"></i> WiFi Aktif</span>
                </div>
                <div class="metric-icon" style="background: var(--gradient-emerald);">
                    <i class="fa-solid fa-signal"></i>
                </div>
            </div>
        </div>

        <div class="card metric-card">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <span class="metric-sub">Kartu RFID Terdaftar</span>
                    <h3 class="metric-value">{{ $rfidCards->count() }} <span style="font-size: 14px; font-weight: 500; color: #94a3b8;">Warga</span></h3>
                    <span class="badge badge-pending"><i class="fa-solid fa-id-card"></i> Tap-to-Pay Ready</span>
                </div>
                <div class="metric-icon" style="background: var(--gradient-amber);">
                    <i class="fa-solid fa-credit-card"></i>
                </div>
            </div>
        </div>

        <div class="card metric-card">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <span class="metric-sub">Status Keamanan RT</span>
                    <h3 class="metric-value" style="{{ $alertCount > 0 ? 'color: #f87171;' : 'color: #34d399;' }}">
                        {{ $alertCount > 0 ? 'WASPADA' : 'KONDUSIF' }}
                    </h3>
                    <span class="badge {{ $alertCount > 0 ? 'badge-danger' : 'badge-success' }}">
                        <i class="fa-solid {{ $alertCount > 0 ? 'fa-triangle-exclamation' : 'fa-shield-halved' }}"></i> 
                        {{ $alertCount > 0 ? $alertCount . ' Alarm Aktif' : 'Pos Ronda Siaga' }}
                    </span>
                </div>
                <div class="metric-icon" style="background: {{ $alertCount > 0 ? 'var(--gradient-danger)' : 'var(--gradient-accent)' }};">
                    <i class="fa-solid {{ $alertCount > 0 ? 'fa-triangle-exclamation' : 'fa-shield-check' }}"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Interactive ESP32 Hardware Simulator -->
    <div class="card" style="border: 2px solid rgba(6, 182, 212, 0.4); background: linear-gradient(180deg, rgba(30, 41, 59, 0.9) 0%, rgba(15, 23, 42, 0.95) 100%);">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 16px;">
            <div>
                <h3 class="card-title" style="color: #38bdf8;">
                    <i class="fa-solid fa-gamepad"></i> Simulator Hardware ESP32 & Pengujian Interaktif
                </h3>
                <p style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">
                    Uji coba langsung semua fitur IoT (Tap Kartu RFID Masuk Portal, Tap-to-Pay Iuran Kas RT, Setoran Uang Smart Box, dan Alarm Darurat) secara live tanpa perlu menyolder hardware terlebih dahulu!
                </p>
            </div>
            <span class="badge badge-info" style="font-size: 12px; padding: 6px 14px;">
                <i class="fa-solid fa-bolt"></i> Realtime API Tester
            </span>
        </div>

        <div class="grid grid-3" style="margin-top: 20px;">
            <!-- Sim 1: Portal Gate RFID Tap -->
            <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid var(--border-color); border-radius: 14px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                        <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(14, 165, 233, 0.2); color: #38bdf8; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                            <i class="fa-solid fa-door-open"></i>
                        </div>
                        <div>
                            <h4 style="font-size: 14px; color: #fff; font-weight: 700;">1. Tap RFID Gerbang Portal</h4>
                            <span style="font-size: 11px; color: var(--text-muted);">ESP32-GATE-01</span>
                        </div>
                    </div>
                    <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 14px;">
                        Simulasi warga menempelkan kartu RFID di portal perumahan. Relay palang pintu otomatis membuka jika kartu valid.
                    </p>
                    <div class="form-group">
                        <label>Pilih Warga / Kartu RFID</label>
                        <select id="sim_gate_uid" class="form-control" style="font-size: 13px;">
                            @foreach($rfidCards as $card)
                            <option value="{{ $card->rfid_uid }}">{{ $card->user->name }} ({{ $card->rfid_uid }})</option>
                            @endforeach
                            <option value="UNKNOWN:99:88:77">Kartu Tidak Terdaftar (Tolak)</option>
                        </select>
                    </div>
                </div>
                <button type="button" class="btn btn-primary" onclick="simulateGateTap()" style="width: 100%; justify-content: center; margin-top: 10px;">
                    <i class="fa-solid fa-id-badge"></i> Tap Kartu Masuk Gerbang
                </button>
            </div>

            <!-- Sim 2: Tap-to-Pay Iuran Kas RT -->
            <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid var(--border-color); border-radius: 14px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                        <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(16, 185, 129, 0.2); color: #34d399; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                            <i class="fa-solid fa-money-bill-transfer"></i>
                        </div>
                        <div>
                            <h4 style="font-size: 14px; color: #fff; font-weight: 700;">2. Tap-to-Pay Iuran Kas RT</h4>
                            <span style="font-size: 11px; color: var(--text-muted);">ESP32-RONDA-POS</span>
                        </div>
                    </div>
                    <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 14px;">
                        Warga tap kartu di Pos RT untuk melunasi iuran. Status otomatis "Approved" & uang tercatat di Buku Kas RT.
                    </p>
                    <div class="form-group">
                        <label>Pilih Warga Pembayar</label>
                        <select id="sim_pay_uid" class="form-control" style="font-size: 13px;">
                            @foreach($rfidCards as $card)
                            <option value="{{ $card->rfid_uid }}">{{ $card->user->name }} ({{ $card->rfid_uid }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="button" class="btn btn-success" onclick="simulatePayTap()" style="width: 100%; justify-content: center; margin-top: 10px;">
                    <i class="fa-solid fa-receipt"></i> Tap Bayar Iuran Rp 50.000
                </button>
            </div>

            <!-- Sim 3: Smart Kas Box & Panic Button -->
            <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid var(--border-color); border-radius: 14px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                        <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(245, 158, 11, 0.2); color: #fbbf24; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                            <i class="fa-solid fa-box-archive"></i>
                        </div>
                        <div>
                            <h4 style="font-size: 14px; color: #fff; font-weight: 700;">3. Smart Kotak Kas & Panic</h4>
                            <span style="font-size: 11px; color: var(--text-muted);">ESP32-KASBOX & PANIC</span>
                        </div>
                    </div>
                    <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 14px;">
                        Deteksi koin/uang kertas masuk ke kotak kas RT otomatis, atau tekan sakelar panik darurat pos keamanan.
                    </p>
                    <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                        <button type="button" class="btn btn-secondary" onclick="simulateKasDrop(20000)" style="flex: 1; font-size: 12px; padding: 10px;">
                            <i class="fa-solid fa-coins"></i> Drop Rp 20.000
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="simulateKasDrop(50000)" style="flex: 1; font-size: 12px; padding: 10px;">
                            <i class="fa-solid fa-money-bill-wave"></i> Drop Rp 50.000
                        </button>
                    </div>
                </div>
                <button type="button" class="btn btn-danger" onclick="simulatePanic()" style="width: 100%; justify-content: center; margin-top: 10px;">
                    <i class="fa-solid fa-triangle-exclamation"></i> Uji Tombol Panik ESP32
                </button>
            </div>
        </div>

        <!-- Simulated OLED Display Screen -->
        <div style="margin-top: 24px; background: #000000; border: 3px solid #334155; border-radius: 14px; padding: 18px 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 44px; height: 44px; border-radius: 10px; background: #1e293b; display: flex; align-items: center; justify-content: center; color: #06b6d4; font-size: 20px;">
                    <i class="fa-solid fa-tv"></i>
                </div>
                <div>
                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #06b6d4; font-weight: 700;">Simulasi Layar OLED ESP32 (SSD1306 128x64)</span>
                    <div id="sim-oled-screen" style="font-family: 'Courier New', Courier, monospace; color: #38bdf8; font-weight: bold; font-size: 15px; margin-top: 4px; line-height: 1.4;">
                        [ SIKAS-RT IOT STANDBY ]<br>
                        SILAKAN TAP KARTU RFID ANDA...<br>
                        STATUS: ONLINE (192.168.1.101)
                    </div>
                </div>
            </div>
            <div id="sim-gate-badge" style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 20px; background: rgba(51, 65, 85, 0.4); color: #94a3b8; font-weight: 700; font-size: 13px;">
                <i class="fa-solid fa-lock"></i> Portal Tertutup
            </div>
        </div>
    </div>

    <!-- Active ESP32 Device Nodes -->
    <div class="card">
        <div class="card-header">
            <div>
                <h3 class="card-title"><i class="fa-solid fa-network-wired"></i> Daftar Node Perangkat ESP32 Terhubung</h3>
                <p style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Kelola dan pantau seluruh modul mikrokontroler di wilayah RT 01</p>
            </div>
            @if(in_array(auth()->user()->role, ['superadmin', 'bendahara']))
            <button type="button" class="btn btn-primary" onclick="openAddDeviceModal()">
                <i class="fa-solid fa-plus"></i> Tambah Node ESP32
            </button>
            @endif
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Device ID</th>
                        <th>Nama Perangkat</th>
                        <th>Tipe / Fungsi</th>
                        <th>Lokasi Fisik</th>
                        <th>IP / Sinyal WiFi</th>
                        <th>Status</th>
                        <th>Aksi Kontrol Jarak Jauh</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($devices as $dev)
                    <tr>
                        <td>
                            <strong style="color: #38bdf8; font-family: monospace;">{{ $dev->device_id }}</strong>
                            <div style="font-size: 10px; color: var(--text-muted);">v{{ $dev->firmware_version }}</div>
                        </td>
                        <td>
                            <strong>{{ $dev->name }}</strong>
                            <div style="font-size: 11px; color: var(--text-muted);">
                                Key: <span style="font-family: monospace;">{{ substr($dev->api_key, 0, 10) }}...</span>
                            </div>
                        </td>
                        <td>
                            @if($dev->type === 'portal_gate')
                                <span class="badge badge-info"><i class="fa-solid fa-door-open"></i> Portal Gerbang</span>
                            @elseif($dev->type === 'pos_ronda')
                                <span class="badge badge-success"><i class="fa-solid fa-shield-halved"></i> Pos Ronda</span>
                            @elseif($dev->type === 'smart_kasbox')
                                <span class="badge badge-pending"><i class="fa-solid fa-box"></i> Kotak Kas</span>
                            @else
                                <span class="badge badge-danger"><i class="fa-solid fa-triangle-exclamation"></i> Tombol Panik</span>
                            @endif
                        </td>
                        <td>{{ $dev->location }}</td>
                        <td>
                            <div>{{ $dev->ip_address ?? '127.0.0.1' }}</div>
                            <div style="font-size: 11px; color: #34d399;">
                                <i class="fa-solid fa-wifi"></i> {{ $dev->rssi_signal ?? -60 }} dBm
                            </div>
                        </td>
                        <td>
                            @if($dev->status === 'online')
                                <span class="badge badge-success"><span class="dot-pulse" style="display:inline-block;"></span> Online</span>
                            @elseif($dev->status === 'alert')
                                <span class="badge badge-danger"><i class="fa-solid fa-bell"></i> ALERT</span>
                            @else
                                <span class="badge badge-danger">Offline</span>
                            @endif
                            <div style="font-size: 10px; color: var(--text-muted); margin-top: 2px;">
                                {{ $dev->last_seen_at ? $dev->last_seen_at->diffForHumans() : 'Belum aktif' }}
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                                @if($dev->type === 'portal_gate')
                                <form action="{{ route('iot.command.send') }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <input type="hidden" name="device_id" value="{{ $dev->device_id }}">
                                    <input type="hidden" name="command" value="open_gate">
                                    <button type="submit" class="btn btn-secondary" style="padding: 6px 10px; font-size: 11px;" title="Buka Palang Pintu">
                                        <i class="fa-solid fa-arrow-up-from-bracket"></i> Buka Portal
                                    </button>
                                </form>
                                @endif

                                @if($dev->status === 'alert')
                                <form action="{{ route('iot.command.send') }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <input type="hidden" name="device_id" value="{{ $dev->device_id }}">
                                    <input type="hidden" name="command" value="reset_alarm">
                                    <button type="submit" class="btn btn-success" style="padding: 6px 10px; font-size: 11px;" title="Matikan Sirene">
                                        <i class="fa-solid fa-volume-xmark"></i> Mute Alarm
                                    </button>
                                </form>
                                @else
                                <form action="{{ route('iot.command.send') }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <input type="hidden" name="device_id" value="{{ $dev->device_id }}">
                                    <input type="hidden" name="command" value="trigger_siren">
                                    <button type="submit" class="btn btn-secondary" style="padding: 6px 10px; font-size: 11px;" title="Test Sirene Pos">
                                        <i class="fa-solid fa-bullhorn"></i> Test Buzzer
                                    </button>
                                </form>
                                @endif

                                @if(in_array(auth()->user()->role, ['superadmin']))
                                <form action="{{ route('iot.device.destroy', $dev->id) }}" method="POST" onsubmit="return confirm('Hapus perangkat ini?');" style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-secondary" style="padding: 6px 8px; font-size: 11px; color: #f87171;" title="Hapus">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- RFID Cards Registration & Realtime Telemetry Grid -->
    <div class="grid grid-2">
        <!-- RFID Cards Card -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><i class="fa-solid fa-id-card-clip"></i> Registrasi Kartu RFID Warga</h3>
                    <p style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Tautkan kartu pintar ke warga untuk akses & pembayaran</p>
                </div>
                @if(in_array(auth()->user()->role, ['superadmin', 'bendahara']))
                <button type="button" class="btn btn-success" onclick="openAddRfidModal()" style="font-size: 12px; padding: 6px 14px;">
                    <i class="fa-solid fa-plus"></i> Tambah Kartu
                </button>
                @endif
            </div>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Warga</th>
                            <th>UID Kartu</th>
                            <th>Label Kartu</th>
                            <th>Terakhir Tap</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rfidCards as $card)
                        <tr>
                            <td>
                                <strong>{{ $card->user->name }}</strong>
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $card->user->address }}</div>
                            </td>
                            <td>
                                <span style="font-family: monospace; font-weight: 700; color: #38bdf8; background: rgba(6,182,212,0.1); padding: 2px 8px; border-radius: 6px;">
                                    {{ $card->rfid_uid }}
                                </span>
                            </td>
                            <td>{{ $card->card_label }}</td>
                            <td>
                                <span style="font-size: 12px;">{{ $card->last_tapped_at ? $card->last_tapped_at->diffForHumans() : 'Belum pernah' }}</span>
                            </td>
                            <td>
                                @if(in_array(auth()->user()->role, ['superadmin', 'bendahara']))
                                <form action="{{ route('iot.rfid.destroy', $card->id) }}" method="POST" onsubmit="return confirm('Hapus kartu RFID ini?');" style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-secondary" style="padding: 4px 8px; font-size: 11px; color: #f87171;">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Telemetry & Event Feed -->
        <div class="card">
            <div class="card-header">
                <div>
                    <h3 class="card-title"><i class="fa-solid fa-clock-rotate-left"></i> Feed Aktivitas & Telemetri Real-Time</h3>
                    <p style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Log otomatis dari seluruh sensor dan pembaca RFID</p>
                </div>
                <span class="badge badge-info"><i class="fa-solid fa-circle-dot fa-fade"></i> Live Stream</span>
            </div>

            <div style="max-height: 380px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; padding-right: 6px;">
                @forelse($telemetryLogs as $log)
                <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-color); border-radius: 10px; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 14px;
                            @if($log->event_type === 'gate_access') background: rgba(14, 165, 233, 0.2); color: #38bdf8;
                            @elseif($log->event_type === 'kas_drop') background: rgba(16, 185, 129, 0.2); color: #34d399;
                            @elseif($log->event_type === 'panic_alert') background: rgba(239, 68, 68, 0.2); color: #f87171;
                            @elseif($log->event_type === 'pos_ronda') background: rgba(245, 158, 11, 0.2); color: #fbbf24;
                            @else background: rgba(99, 102, 241, 0.2); color: #818cf8; @endif
                        ">
                            <i class="fa-solid 
                                @if($log->event_type === 'gate_access') fa-door-open
                                @elseif($log->event_type === 'kas_drop') fa-money-bill-wave
                                @elseif($log->event_type === 'panic_alert') fa-triangle-exclamation
                                @elseif($log->event_type === 'pos_ronda') fa-shield-halved
                                @else fa-microchip @endif
                            "></i>
                        </div>
                        <div>
                            <div style="font-size: 13px; font-weight: 600; color: #fff;">{{ $log->message }}</div>
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                                Node: <span style="color: #38bdf8;">{{ $log->device_id }}</span> 
                                @if($log->rfid_uid) &bull; UID: {{ $log->rfid_uid }} @endif
                            </div>
                        </div>
                    </div>
                    <div style="text-align: right; font-size: 11px; color: var(--text-muted);">
                        {{ $log->logged_at->format('H:i:s') }}<br>
                        {{ $log->logged_at->format('d M') }}
                    </div>
                </div>
                @empty
                <p style="text-align: center; color: var(--text-muted); padding: 30px 0;">Belum ada data telemetri yang masuk.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- ESP32 Arduino C++ Firmware & Wiring Pinout Section -->
    <div class="card" style="background: rgba(15, 23, 42, 0.8);">
        <div class="card-header">
            <div>
                <h3 class="card-title"><i class="fa-brands fa-usb"></i> Panduan Koneksi Fisik & Firmware ESP32 SIKAS-RT</h3>
                <p style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Kode sumber Arduino C++ (.ino) lengkap tersedia di folder project: <code>sikasrt/firmware_esp32/sikasrt_esp32.ino</code></p>
            </div>
            <span class="badge badge-success"><i class="fa-solid fa-code"></i> Ready to Flash</span>
        </div>

        <div class="grid grid-3" style="font-size: 13px;">
            <div style="background: #1e293b; padding: 16px; border-radius: 12px; border: 1px solid var(--border-color);">
                <h4 style="color: #38bdf8; margin-bottom: 8px;"><i class="fa-solid fa-microchip"></i> Pinout RFID RC522 (SPI)</h4>
                <ul style="list-style: none; padding-left: 0; line-height: 1.8; color: #cbd5e1;">
                    <li>SDA (SS) &rarr; <strong>GPIO 5</strong></li>
                    <li>SCK &rarr; <strong>GPIO 18</strong></li>
                    <li>MOSI &rarr; <strong>GPIO 23</strong></li>
                    <li>MISO &rarr; <strong>GPIO 19</strong></li>
                    <li>RST &rarr; <strong>GPIO 22</strong></li>
                    <li>VCC &rarr; <strong>3.3V</strong> & GND &rarr; <strong>GND</strong></li>
                </ul>
            </div>

            <div style="background: #1e293b; padding: 16px; border-radius: 12px; border: 1px solid var(--border-color);">
                <h4 style="color: #34d399; margin-bottom: 8px;"><i class="fa-solid fa-tv"></i> Pinout OLED SSD1306 (I2C)</h4>
                <ul style="list-style: none; padding-left: 0; line-height: 1.8; color: #cbd5e1;">
                    <li>SDA &rarr; <strong>GPIO 21</strong></li>
                    <li>SCL &rarr; <strong>GPIO 22</strong> (shared)</li>
                    <li>VCC &rarr; <strong>3.3V</strong> / 5V</li>
                    <li>GND &rarr; <strong>GND</strong></li>
                    <li>Resolusi: <strong>128x64 Pixel I2C</strong></li>
                    <li>Address: <strong>0x3C</strong></li>
                </ul>
            </div>

            <div style="background: #1e293b; padding: 16px; border-radius: 12px; border: 1px solid var(--border-color);">
                <h4 style="color: #fbbf24; margin-bottom: 8px;"><i class="fa-solid fa-bolt"></i> Aktuator & Sensor</h4>
                <ul style="list-style: none; padding-left: 0; line-height: 1.8; color: #cbd5e1;">
                    <li>Relay Palang Pintu &rarr; <strong>GPIO 26</strong></li>
                    <li>Active Buzzer / Sirene &rarr; <strong>GPIO 27</strong></li>
                    <li>Tombol Darurat (Panic) &rarr; <strong>GPIO 4</strong></li>
                    <li>Drop Sensor Kas &rarr; <strong>GPIO 14</strong></li>
                    <li>LED Status WiFi &rarr; <strong>GPIO 2</strong></li>
                </ul>
            </div>
        </div>
    </div>

</div>

<!-- Modal Tambah Perangkat ESP32 -->
<div id="modal-add-device" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#1e293b; border:1px solid var(--border-color); border-radius:18px; max-width:500px; width:90%; padding:28px;">
        <h3 style="font-size:18px; color:#fff; font-weight:700; margin-bottom:16px;">
            <i class="fa-solid fa-plus-circle" style="color: #38bdf8;"></i> Daftarkan Perangkat Node ESP32
        </h3>
        <form action="{{ route('iot.device.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Device ID (Unik)</label>
                <input type="text" name="device_id" class="form-control" placeholder="Contoh: ESP32-GATE-02" required>
            </div>
            <div class="form-group">
                <label>Nama Perangkat</label>
                <input type="text" name="name" class="form-control" placeholder="Contoh: Portal Otomatis Belakang Blok D" required>
            </div>
            <div class="form-group">
                <label>Fungsi / Tipe Node</label>
                <select name="type" class="form-control" required>
                    <option value="portal_gate">Portal Gerbang Masuk (Relay Solenoid)</option>
                    <option value="pos_ronda">Terminal Pos Ronda (Absensi & Tap Kasir)</option>
                    <option value="smart_kasbox">Smart Kotak Kas (Deteksi Uang Drop)</option>
                    <option value="panic_button">Tombol Darurat Siskamling (Sirene)</option>
                    <option value="environment_sensor">Sensor Cuaca / Lingkungan</option>
                </select>
            </div>
            <div class="form-group">
                <label>Lokasi Pemasangan</label>
                <input type="text" name="location" class="form-control" placeholder="Contoh: Pos Gerbang Belakang Gang 4" required>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                <button type="button" class="btn btn-secondary" onclick="closeAddDeviceModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Node ESP32</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Kartu RFID Warga -->
<div id="modal-add-rfid" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:#1e293b; border:1px solid var(--border-color); border-radius:18px; max-width:480px; width:90%; padding:28px;">
        <h3 style="font-size:18px; color:#fff; font-weight:700; margin-bottom:16px;">
            <i class="fa-solid fa-id-card" style="color: #10b981;"></i> Daftarkan Kartu RFID Warga
        </h3>
        <form action="{{ route('iot.rfid.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Pilih Warga</label>
                <select name="user_id" class="form-control" required>
                    @foreach($wargaList as $w)
                    <option value="{{ $w->id }}">{{ $w->name }} ({{ $w->address }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>UID Kartu RFID (Hexadecimal)</label>
                <input type="text" name="rfid_uid" class="form-control" placeholder="Contoh: 93:B4:72:A1" required style="font-family: monospace;">
                <small style="color: var(--text-muted); font-size: 11px;">Dapat dibaca via Serial Monitor Arduino saat kartu ditempel ke reader.</small>
            </div>
            <div class="form-group">
                <label>Label Kartu</label>
                <input type="text" name="card_label" class="form-control" value="Kartu Warga Utama" required>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:20px;">
                <button type="button" class="btn btn-secondary" onclick="closeAddRfidModal()">Batal</button>
                <button type="submit" class="btn btn-success">Daftarkan Kartu</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddDeviceModal() { document.getElementById('modal-add-device').style.display = 'flex'; }
    function closeAddDeviceModal() { document.getElementById('modal-add-device').style.display = 'none'; }
    function openAddRfidModal() { document.getElementById('modal-add-rfid').style.display = 'flex'; }
    function closeAddRfidModal() { document.getElementById('modal-add-rfid').style.display = 'none'; }

    // Live Simulator Functions
    async function simulateGateTap() {
        const uid = document.getElementById('sim_gate_uid').value;
        const oled = document.getElementById('sim-oled-screen');
        const badge = document.getElementById('sim-gate-badge');

        oled.innerHTML = `[ MEMBACA KARTU... ]<br>UID: ${uid}<br>VERIFIKASI SISTEM...`;

        try {
            const res = await fetch('/api/esp32/rfid-tap', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ device_id: 'ESP32-GATE-01', rfid_uid: uid })
            });
            const data = await res.json();

            if (data.authorized) {
                oled.innerHTML = `[ ${data.display.line1} ]<br>${data.display.line2}<br>${data.display.line3}`;
                badge.innerHTML = `<i class="fa-solid fa-lock-open" style="color: #34d399;"></i> <span style="color: #34d399;">Portal Terbuka (5 detik)</span>`;
                setTimeout(() => {
                    badge.innerHTML = `<i class="fa-solid fa-lock"></i> Portal Tertutup`;
                }, 5000);
            } else {
                oled.innerHTML = `<span style="color: #ef4444;">[ ${data.display.line1} ]<br>${data.display.line2}<br>${data.display.line3}</span>`;
            }
        } catch (e) {
            oled.innerHTML = `<span style="color:#ef4444;">Error koneksi API simulator</span>`;
        }
    }

    async function simulatePayTap() {
        const uid = document.getElementById('sim_pay_uid').value;
        const oled = document.getElementById('sim-oled-screen');

        oled.innerHTML = `[ PROSES PEMBAYARAN IURAN ]<br>UID: ${uid}<br>MENGHUBUNGI KAS RT...`;

        try {
            const res = await fetch('/api/esp32/rfid-tap', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ device_id: 'ESP32-RONDA-POS', rfid_uid: uid, mode: 'pay', amount: 50000 })
            });
            const data = await res.json();

            if (data.authorized) {
                oled.innerHTML = `<span style="color: #34d399;">[ ${data.display.line1} ]<br>${data.display.line2}<br>${data.display.line3}</span>`;
                alert('Berhasil! Pembayaran Iuran Kas RT sebesar Rp 50.000 telah dicatat secara otomatis via Tap RFID.');
                location.reload();
            } else {
                oled.innerHTML = `<span style="color: #ef4444;">Gagal: ${data.message}</span>`;
            }
        } catch (e) {
            oled.innerHTML = `<span style="color:#ef4444;">Error koneksi API simulator</span>`;
        }
    }

    async function simulateKasDrop(amount) {
        const oled = document.getElementById('sim-oled-screen');
        oled.innerHTML = `[ DETEKSI UANG FISIK ]<br>SENSOR IR AKTIF...<br>NOMINAL: Rp ${amount.toLocaleString()}`;

        try {
            const res = await fetch('/api/esp32/kas-drop', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ device_id: 'ESP32-KASBOX-01', amount: amount })
            });
            const data = await res.json();

            oled.innerHTML = `<span style="color: #34d399;">[ ${data.display.line1} ]<br>${data.display.line2}<br>${data.display.line3}</span>`;
            alert(`Berhasil! Setoran fisik uang kas sebesar Rp ${amount.toLocaleString()} telah masuk ke Buku Kas RT.`);
            location.reload();
        } catch (e) {
            oled.innerHTML = `<span style="color:#ef4444;">Error koneksi API simulator</span>`;
        }
    }

    async function simulatePanic() {
        if (!confirm('Picu sinyal darurat dari hardware ESP32?')) return;
        try {
            const res = await fetch('/api/esp32/panic-alert', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ device_id: 'ESP32-PANIC-01', alert_type: 'panic_button' })
            });
            const data = await res.json();
            alert('ALARM DARURAT AKTIF: Sirene berbunyi dan notifikasi bahaya telah disebarkan!');
            location.reload();
        } catch (e) {
            alert('Error komunikasi simulator');
        }
    }
</script>
@endsection
