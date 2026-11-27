@extends('layouts.app')

@section('title', 'Data Warga & Pengguna')
@section('page_title', 'Direktori Warga & Manajemen Akun RT 01')

@section('content')
<!-- Header & Action Row -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 style="font-size: 18px; font-weight: 700; color: #fff;">Daftar Pengguna Terdaftar</h2>
        <p style="font-size: 13px; color: var(--text-muted);">Kelola data warga, role pengurus RT, dan akun login sistem.</p>
    </div>

    @if(in_array(auth()->user()->role, ['superadmin', 'bendahara']))
    <button type="button" onclick="openTambahWargaModal()" class="btn btn-primary" id="btnTambahWarga">
        <i class="fa-solid fa-user-plus"></i> Tambah Akun / Warga Baru
    </button>
    @endif
</div>

<!-- Search & Filter Bar -->
<div class="card" style="margin-bottom: 24px;">
    <form action="{{ route('warga.index') }}" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap;">
        <input type="text" name="search" class="form-control" style="max-width: 300px;" placeholder="Cari nama, email, alamat..." value="{{ request('search') }}">

        <select name="role" class="form-control" style="width: auto;">
            <option value="">-- Semua Role --</option>
            <option value="superadmin" {{ request('role') == 'superadmin' ? 'selected' : '' }}>Superadmin</option>
            <option value="bendahara" {{ request('role') == 'bendahara' ? 'selected' : '' }}>Bendahara</option>
            <option value="rt" {{ request('role') == 'rt' ? 'selected' : '' }}>Ketua RT</option>
            <option value="warga" {{ request('role') == 'warga' ? 'selected' : '' }}>Warga</option>
        </select>

        <button type="submit" class="btn btn-secondary">
            <i class="fa-solid fa-magnifying-glass"></i> Cari Data
        </button>
    </form>
</div>

<!-- Users Table -->
<div class="card">
    <div class="desktop-only-table table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Nama Pengguna</th>
                    <th>Email / Kontak</th>
                    <th>Role Otoritas</th>
                    <th>Alamat Rumah</th>
                    <th>Status Rumah</th>
                    <th>Kartu RFID IoT</th>
                    <th>Wilayah</th>
                    @if(in_array(auth()->user()->role, ['superadmin', 'bendahara']))
                    <th>Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                <tr>
                    <td>
                        <strong>{{ $u->name }}</strong>
                    </td>
                    <td>
                        {{ $u->email }}<br>
                        <small style="color: var(--text-muted);"><i class="fa-solid fa-phone"></i> {{ $u->phone ?? '-' }}</small>
                    </td>
                    <td>
                        <span class="user-role-badge role-{{ $u->role }}">{{ strtoupper($u->role) }}</span>
                    </td>
                    <td>{{ $u->address ?? '-' }}</td>
                    <td>
                        <span class="badge {{ $u->status_warga === 'tetap' ? 'badge-success' : 'badge-pending' }}">
                            {{ strtoupper($u->status_warga) }}
                        </span>
                    </td>
                    <td>
                        @if($u->rfid_uid)
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="font-family: monospace; font-size: 11px; color: #38bdf8; background: rgba(6,182,212,0.15); padding: 3px 8px; border-radius: 6px; border: 1px solid rgba(6,182,212,0.3);">
                                    <i class="fa-solid fa-id-card"></i> {{ $u->rfid_uid }}
                                </span>
                                @if(in_array(auth()->user()->role, ['superadmin', 'bendahara']))
                                <button type="button" onclick="openDirectPairModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ $u->rfid_uid }}')" class="btn btn-secondary" style="padding: 3px 7px; font-size: 10px;" title="Ganti / Scan Ulang e-KTP">
                                    <i class="fa-solid fa-arrows-rotate"></i>
                                </button>
                                @endif
                            </div>
                        @else
                            @if(in_array(auth()->user()->role, ['superadmin', 'bendahara']))
                            <button type="button" onclick="openDirectPairModal({{ $u->id }}, '{{ addslashes($u->name) }}', '')" class="btn btn-primary" style="padding: 4px 10px; font-size: 11px; background: linear-gradient(135deg, #06b6d4, #3b82f6); border: none; font-weight: 700;">
                                <i class="fa-solid fa-wifi"></i> Rekam e-KTP
                            </button>
                            @else
                            <span style="color: var(--text-muted); font-size: 12px;">-</span>
                            @endif
                        @endif
                    </td>
                    <td>{{ $u->rt_rw }}</td>
                    @if(in_array(auth()->user()->role, ['superadmin', 'bendahara']))
                    <td>
                        <div style="display: flex; gap: 6px;">
                            @if(auth()->user()->role === 'superadmin' && $u->id !== auth()->id())
                            <form action="{{ route('warga.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Hapus pengguna {{ $u->name }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" style="padding: 4px 8px; font-size: 11px;">
                                    <i class="fa-solid fa-trash"></i> Hapus
                                </button>
                            </form>
                            @else
                            <small style="color: var(--text-muted);">-</small>
                            @endif
                        </div>
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 30px;">Tidak ada pengguna ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile-Only User Directory Cards -->
    <div class="mobile-only-cards" style="margin-top: 10px;">
        @forelse($users as $u)
        <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid var(--border-color); border-radius: 14px; padding: 14px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                <strong style="color: #fff; font-size: 14px;">{{ $u->name }}</strong>
                <span class="user-role-badge role-{{ $u->role }}">{{ strtoupper($u->role) }}</span>
            </div>

            <div style="font-size: 11.5px; color: var(--text-muted); margin-bottom: 8px;">
                <i class="fa-solid fa-location-dot"></i> {{ $u->address ?? '-' }} ({{ $u->rt_rw }})
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(30, 41, 59, 0.5); padding: 8px 10px; border-radius: 8px; margin-bottom: 10px; font-size: 11.5px;">
                <div>
                    <div style="color: #cbd5e1;"><i class="fa-solid fa-envelope"></i> {{ $u->email }}</div>
                    @if($u->phone)
                    <div style="color: #38bdf8; margin-top: 2px;"><i class="fa-solid fa-phone"></i> {{ $u->phone }}</div>
                    @endif
                </div>
                <div style="text-align: right;">
                    <span class="badge {{ $u->status_warga === 'tetap' ? 'badge-success' : 'badge-pending' }}" style="font-size: 9.5px; padding: 2px 7px;">
                        {{ strtoupper($u->status_warga) }}
                    </span>
                    @if($u->rfid_uid)
                    <div style="margin-top: 4px; display: flex; align-items: center; justify-content: flex-end; gap: 4px;">
                        <span style="font-family: monospace; font-size: 10px; color: #38bdf8; background: rgba(6,182,212,0.15); padding: 2px 6px; border-radius: 4px;">
                            <i class="fa-solid fa-id-card"></i> {{ $u->rfid_uid }}
                        </span>
                        @if(in_array(auth()->user()->role, ['superadmin', 'bendahara']))
                        <button type="button" onclick="openDirectPairModal({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ $u->rfid_uid }}')" class="btn btn-secondary" style="padding: 2px 5px; font-size: 9px;" title="Ganti e-KTP">
                            <i class="fa-solid fa-arrows-rotate"></i>
                        </button>
                        @endif
                    </div>
                    @else
                        @if(in_array(auth()->user()->role, ['superadmin', 'bendahara']))
                        <div style="margin-top: 4px;">
                            <button type="button" onclick="openDirectPairModal({{ $u->id }}, '{{ addslashes($u->name) }}', '')" class="btn btn-primary" style="padding: 3px 8px; font-size: 10px; background: linear-gradient(135deg, #06b6d4, #3b82f6); border: none; font-weight: 700;">
                                <i class="fa-solid fa-wifi"></i> Rekam e-KTP
                            </button>
                        </div>
                        @else
                        <div style="margin-top: 4px; color: var(--text-muted); font-size: 10px;">Belum Ada e-KTP</div>
                        @endif
                    @endif
                </div>
            </div>

            @if(auth()->user()->role === 'superadmin' && $u->id !== auth()->id())
            <div style="display: flex; justify-content: flex-end; border-top: 1px solid rgba(255,255,255,0.06); padding-top: 8px;">
                <form action="{{ route('warga.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Hapus pengguna {{ $u->name }}?');" style="margin: 0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" style="padding: 6px 14px; font-size: 11.5px;">
                        <i class="fa-solid fa-trash"></i> Hapus Pengguna
                    </button>
                </form>
            </div>
            @endif
        </div>
        @empty
        <div style="text-align: center; padding: 24px; color: var(--text-muted); font-size: 12px;">
            Tidak ada pengguna ditemukan.
        </div>
        @endforelse
    </div>

    <div style="margin-top: 20px;">
        {{ $users->links() }}
    </div>
</div>

<!-- Modal Tambah Akun / Warga Baru dengan Fitur Rekam e-KTP -->
@if(in_array(auth()->user()->role, ['superadmin', 'bendahara']))
<div id="modal-tambah-user" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(8px); z-index: 99999; align-items:center; justify-content:center; padding: 16px; overflow-y: auto;">
    <div class="card" style="width: 100%; max-width: 580px; margin: auto; max-height: 92vh; overflow-y: auto;">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
            <div class="card-title">
                <i class="fa-solid fa-user-plus" style="color: var(--primary);"></i>
                <span>Tambah Akun / Registrasi Warga Baru</span>
            </div>
            <button type="button" onclick="closeTambahWargaModal()" class="btn btn-secondary" style="padding: 4px 8px; font-size: 16px;">&times;</button>
        </div>

        <form action="{{ route('warga.store') }}" method="POST" style="margin-top: 14px;">
            @csrf
            <div class="form-group">
                <label>Nama Lengkap Warga</label>
                <input type="text" name="name" class="form-control" placeholder="Contoh: Ahmad Subagyo" required>
            </div>

            <div class="grid grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label>Email (Username Login)</label>
                    <input type="email" name="email" class="form-control" placeholder="ahmad@kasrt.local" required>
                </div>
                <div class="form-group">
                    <label>Kata Sandi Akun</label>
                    <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                </div>
            </div>

            <div class="grid grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label>Role Otoritas</label>
                    <select name="role" class="form-control" required>
                        <option value="warga" selected>Warga</option>
                        <option value="rt">Ketua RT</option>
                        <option value="bendahara">Bendahara</option>
                        @if(auth()->user()->role === 'superadmin')
                        <option value="superadmin">Superadmin</option>
                        @endif
                    </select>
                </div>
                <div class="form-group">
                    <label>No. HP / WhatsApp</label>
                    <input type="text" name="phone" class="form-control" placeholder="08123456789">
                </div>
            </div>

            <div class="grid grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label>Alamat Rumah (No. Blok)</label>
                    <input type="text" name="address" class="form-control" placeholder="Blok C3 No. 05" required>
                </div>
                <div class="form-group">
                    <label>Status Warga</label>
                    <select name="status_warga" class="form-control" required>
                        <option value="tetap">Warga Tetap</option>
                        <option value="kontrak">Warga Kontrak</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Nomor RT / RW</label>
                <input type="text" name="rt_rw" class="form-control" value="RT 01 / RW 05" required>
            </div>

            <!-- KOTAK FITUR REKAM e-KTP REALTIME -->
            <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(6, 182, 212, 0.3); border-radius: 14px; padding: 14px; margin-top: 10px; margin-bottom: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <label style="font-size: 13px; font-weight: 700; color: #fff; margin-bottom: 0; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-id-card" style="color: #38bdf8;"></i> Rekam e-KTP / Kartu Akses Portal
                    </label>
                    <span style="font-size: 10px; background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.3); color: #38bdf8; padding: 2px 7px; border-radius: 10px; font-weight: 700;">
                        RFID / NFC 13.56 MHz
                    </span>
                </div>

                <p style="font-size: 11.5px; color: var(--text-muted); line-height: 1.4; margin-bottom: 10px;">
                    Rekam chip RFID e-KTP warga secara otomatis dengan menempelkan KTP ke alat <strong>ESP32 Reader</strong>, scan via <strong>NFC HP Android</strong>, atau masukkan kode UID secara manual.
                </p>

                <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 8px;">
                    <div class="input-icon-wrapper" style="flex: 2 1 180px;">
                        <i class="fa-solid fa-barcode" style="color: #64748b;"></i>
                        <input type="text" id="tambah_rfid_uid" name="rfid_uid" class="form-control" placeholder="Contoh: 93:B4:72:A1" style="font-family: monospace; font-weight: 700; letter-spacing: 0.5px; color: #38bdf8;" oninput="validateUidAvailability(this.value, 'tambah_uid_status')">
                    </div>
                    <button type="button" id="btnRekamEsp32" onclick="startScanEsp32('tambah_rfid_uid', 'tambahScanBox', 'tambah_uid_status', 'btnRekamEsp32')" class="btn btn-primary" style="flex: 1 1 130px; font-size: 12px; padding: 10px 12px; white-space: nowrap; background: linear-gradient(135deg, #06b6d4, #3b82f6); border: none;">
                        <i class="fa-solid fa-satellite-dish"></i> Rekam ESP32
                    </button>
                    <button type="button" onclick="scanWebNfc('tambah_rfid_uid', 'tambah_uid_status')" class="btn btn-secondary" style="flex: 1 1 90px; font-size: 12px; padding: 10px 12px; white-space: nowrap;">
                        <i class="fa-solid fa-mobile-screen-button"></i> NFC HP
                    </button>
                </div>

                <!-- Live Radar Scan Box (Muncul saat mode rekam aktif) -->
                <div id="tambahScanBox" style="display: none; background: rgba(6, 182, 212, 0.08); border: 1px dashed #06b6d4; border-radius: 12px; padding: 12px; margin-top: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="dot-pulse" style="background: #06b6d4;"></div>
                            <div>
                                <strong style="color: #38bdf8; font-size: 12.5px;"><i class="fa-solid fa-satellite-dish fa-spin"></i> Reader ESP32 Siaga...</strong>
                                <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">Tempelkan e-KTP calon warga ke reader ESP32 sekarang</div>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span id="scanTimerBadge" style="font-family: monospace; font-size: 11px; font-weight: 800; color: #fbbf24; background: rgba(0,0,0,0.4); padding: 3px 8px; border-radius: 6px;">60s</span>
                            <button type="button" onclick="cancelScanEsp32()" class="btn btn-secondary" style="padding: 3px 8px; font-size: 11px;">Batal</button>
                        </div>
                    </div>

                    <!-- Simulator Demo Quick Helper -->
                    <div style="margin-top: 8px; padding-top: 8px; border-top: 1px dashed rgba(255,255,255,0.1); display: flex; justify-content: space-between; align-items: center; font-size: 11px;">
                        <span style="color: var(--text-muted);"><i class="fa-solid fa-vial"></i> Uji Coba Tanpa Hardware:</span>
                        <button type="button" onclick="triggerSimulatedScan('tambah_rfid_uid', 'tambah_uid_status')" style="background: rgba(99, 102, 241, 0.2); border: 1px solid var(--primary); color: #a5b4fc; border-radius: 6px; padding: 2px 8px; cursor: pointer; font-size: 10.5px;">
                            ⚡ Simulasi Tap e-KTP
                        </button>
                    </div>
                </div>

                <!-- Dynamic Status / Validation Feedback Box -->
                <div id="tambah_uid_status" style="display: none; margin-top: 10px; font-size: 12px; padding: 8px 12px; border-radius: 8px;"></div>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" onclick="closeTambahWargaModal()" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary" style="font-weight: 700;">
                    <i class="fa-solid fa-check"></i> Simpan Data Warga
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Rekam / Pasangkan e-KTP Langsung untuk Warga Terdaftar -->
<div id="modal-direct-pair" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(8px); z-index: 99999; align-items:center; justify-content:center; padding: 16px;">
    <div class="card" style="width: 100%; max-width: 480px; margin: auto;">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
            <div class="card-title">
                <i class="fa-solid fa-id-card" style="color: #38bdf8;"></i>
                <span>Rekam / Ganti e-KTP Warga</span>
            </div>
            <button onclick="closeDirectPairModal()" class="btn btn-secondary" style="padding: 4px 8px; font-size: 16px;">&times;</button>
        </div>

        <div style="background: rgba(30, 41, 59, 0.6); padding: 12px 14px; border-radius: 12px; margin: 14px 0;">
            <div style="font-size: 11px; color: var(--text-muted);">Nama Warga:</div>
            <strong id="dpUserName" style="font-size: 15px; color: #fff;"></strong>
            <div id="dpCurrentUidNotice" style="font-size: 11.5px; color: #38bdf8; margin-top: 4px; font-family: monospace;"></div>
        </div>

        <form id="formDirectPair" action="" method="POST">
            @csrf
            <div class="form-group" style="margin-bottom: 12px;">
                <label style="font-size: 12px; font-weight: 700; color: #fff;">UID e-KTP Baru</label>
                <div style="display: flex; gap: 8px;">
                    <div class="input-icon-wrapper" style="flex: 1;">
                        <i class="fa-solid fa-barcode" style="color: #64748b;"></i>
                        <input type="text" id="dp_rfid_uid" name="rfid_uid" class="form-control" placeholder="Contoh: 93:B4:72:A1" style="font-family: monospace; font-weight: 700; color: #38bdf8;" required oninput="validateUidAvailability(this.value, 'dp_uid_status', currentDirectPairUserId)">
                    </div>
                    <button type="button" id="btnDpEsp32" onclick="startScanEsp32('dp_rfid_uid', 'dpScanBox', 'dp_uid_status', 'btnDpEsp32', currentDirectPairUserId)" class="btn btn-primary" style="font-size: 12px; white-space: nowrap; background: linear-gradient(135deg, #06b6d4, #3b82f6); border: none;">
                        <i class="fa-solid fa-satellite-dish"></i> Rekam ESP32
                    </button>
                    <button type="button" onclick="scanWebNfc('dp_rfid_uid', 'dp_uid_status')" class="btn btn-secondary" style="font-size: 12px; padding: 8px 10px;">
                        <i class="fa-solid fa-mobile-screen-button"></i> NFC
                    </button>
                </div>
            </div>

            <!-- Live Radar Scan Box Modal Langsung -->
            <div id="dpScanBox" style="display: none; background: rgba(6, 182, 212, 0.08); border: 1px dashed #06b6d4; border-radius: 12px; padding: 12px; margin-bottom: 12px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div class="dot-pulse" style="background: #06b6d4;"></div>
                        <span style="font-size: 12px; color: #38bdf8; font-weight: 600;">Tempelkan e-KTP ke Reader ESP32...</span>
                    </div>
                    <button type="button" onclick="cancelScanEsp32()" class="btn btn-secondary" style="padding: 2px 7px; font-size: 11px;">Batal</button>
                </div>
                <div style="margin-top: 8px; text-align: right;">
                    <button type="button" onclick="triggerSimulatedScan('dp_rfid_uid', 'dp_uid_status', currentDirectPairUserId)" style="background: none; border: none; color: #818cf8; font-size: 11px; cursor: pointer; text-decoration: underline;">
                        ⚡ Simulasi Tap Kartu
                    </button>
                </div>
            </div>

            <div id="dp_uid_status" style="display: none; margin-bottom: 14px; font-size: 12px; padding: 8px 12px; border-radius: 8px;"></div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 18px; border-top: 1px solid rgba(255,255,255,0.06); padding-top: 14px;">
                <div id="dpUnpairBtnContainer"></div>
                <div style="display: flex; gap: 8px;">
                    <button type="button" onclick="closeDirectPairModal()" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-primary" style="font-weight: 700;">
                        <i class="fa-solid fa-save"></i> Pasangkan e-KTP
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

@push('scripts')
<script>
    let scanPollInterval = null;
    let scanSecondsRemaining = 60;
    let currentTargetInputId = null;
    let currentScanBoxId = null;
    let currentStatusBoxId = null;
    let currentBtnId = null;
    let currentDirectPairUserId = null;

    // Normalisasi format UID secara instan (XX:XX:XX:XX)
    function formatHexUid(raw) {
        let clean = raw.replace(/[^A-Fa-f0-9]/g, '').toUpperCase();
        if (clean.length < 4) return clean;
        let parts = clean.match(/.{1,2}/g);
        return parts ? parts.join(':') : clean;
    }

    // Validasi apakah UID sudah dipakai orang lain
    function validateUidAvailability(rawUid, statusBoxId, excludeUserId = null) {
        const box = document.getElementById(statusBoxId);
        if (!box) return;

        let clean = rawUid.trim();
        if (clean.length < 4) {
            box.style.display = 'none';
            return;
        }

        box.style.display = 'block';
        box.style.background = 'rgba(30, 41, 59, 0.6)';
        box.style.color = 'var(--text-muted)';
        box.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memeriksa status UID di database...';

        fetch('{{ route("warga.check.uid") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ rfid_uid: clean, user_id: excludeUserId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.available) {
                box.style.background = 'rgba(16, 185, 129, 0.15)';
                box.style.border = '1px solid #10b981';
                box.style.color = '#34d399';
                box.innerHTML = `<i class="fa-solid fa-circle-check"></i> <strong>e-KTP Tersedia:</strong> [${data.uid}] belum terdaftar milik warga lain. Siap digunakan!`;
            } else {
                box.style.background = 'rgba(239, 68, 68, 0.15)';
                box.style.border = '1px solid #ef4444';
                box.style.color = '#f87171';
                box.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> <strong>e-KTP Sudah Terdaftar:</strong> Kartu [${data.uid}] sudah dimiliki oleh <strong>${data.owner_name}</strong> (${data.owner_address || 'RT 01'}).`;
            }
        })
        .catch(() => {
            box.style.display = 'none';
        });
    }

    // 1. REKAM e-KTP VIA ESP32 HARDWARE READER
    function startScanEsp32(targetInputId, scanBoxId, statusBoxId, btnId, excludeUserId = null) {
        currentTargetInputId = targetInputId;
        currentScanBoxId = scanBoxId;
        currentStatusBoxId = statusBoxId;
        currentBtnId = btnId;

        const btn = document.getElementById(btnId);
        const scanBox = document.getElementById(scanBoxId);
        const timerBadge = document.getElementById('scanTimerBadge');

        if (btn) btn.disabled = true;
        if (scanBox) scanBox.style.display = 'block';

        scanSecondsRemaining = 60;
        if (timerBadge) timerBadge.innerText = scanSecondsRemaining + 's';

        fetch('{{ route("warga.scan.start") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (scanPollInterval) clearInterval(scanPollInterval);

            scanPollInterval = setInterval(() => {
                scanSecondsRemaining--;
                if (timerBadge) timerBadge.innerText = scanSecondsRemaining + 's';

                if (scanSecondsRemaining <= 0) {
                    cancelScanEsp32();
                    alert('Waktu scan via reader ESP32 telah habis (60s). Silakan klik tombol Rekam lagi jika ingin mencoba kembali.');
                    return;
                }

                // Polling cek hasil kartu yang ditempelkan
                fetch('{{ route("warga.scan.check") }}')
                .then(r => r.json())
                .then(result => {
                    if (result.status === 'success' && result.uid) {
                        clearInterval(scanPollInterval);
                        scanPollInterval = null;

                        const input = document.getElementById(currentTargetInputId);
                        if (input) {
                            input.value = result.uid;
                            validateUidAvailability(result.uid, currentStatusBoxId, excludeUserId);
                        }

                        if (scanBox) scanBox.style.display = 'none';
                        if (btn) btn.disabled = false;

                        // Audio chime efek (opsional web audio synth)
                        try {
                            const ctx = new (window.AudioContext || window.webkitAudioContext)();
                            const osc = ctx.createOscillator();
                            osc.type = 'sine';
                            osc.frequency.setValueAtTime(880, ctx.currentTime);
                            osc.connect(ctx.destination);
                            osc.start();
                            osc.stop(ctx.currentTime + 0.15);
                        } catch(e) {}
                    }
                })
                .catch(() => {});
            }, 1000);
        })
        .catch(err => {
            if (btn) btn.disabled = false;
            if (scanBox) scanBox.style.display = 'none';
            alert('Gagal mengaktifkan mode pairing di server.');
        });
    }

    function cancelScanEsp32() {
        if (scanPollInterval) {
            clearInterval(scanPollInterval);
            scanPollInterval = null;
        }

        if (currentScanBoxId) {
            const scanBox = document.getElementById(currentScanBoxId);
            if (scanBox) scanBox.style.display = 'none';
        }

        if (currentBtnId) {
            const btn = document.getElementById(currentBtnId);
            if (btn) btn.disabled = false;
        }

        fetch('{{ route("warga.scan.cancel") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        }).catch(() => {});
    }

    // 2. SCAN VIA SMARTPHONE DENGAN WEB NFC (CHROME ANDROID)
    async function scanWebNfc(targetInputId, statusBoxId) {
        const box = document.getElementById(statusBoxId);
        if (box) box.style.display = 'block';

        if (!('NDEFReader' in window)) {
            if (box) {
                box.style.background = 'rgba(245, 158, 11, 0.15)';
                box.style.border = '1px solid #f59e0b';
                box.style.color = '#fbbf24';
                box.innerHTML = '<i class="fa-solid fa-circle-info"></i> <strong>Web NFC tidak didukung di browser ini.</strong> Gunakan Google Chrome di HP Android ber-NFC, atau gunakan tombol <strong>Rekam ESP32</strong>.';
            }
            return;
        }

        try {
            if (box) {
                box.style.background = 'rgba(6, 182, 212, 0.15)';
                box.style.border = '1px solid #06b6d4';
                box.style.color = '#38bdf8';
                box.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <strong>NFC HP Siaga!</strong> Tempelkan e-KTP ke belakang bodi smartphone Anda sekarang...';
            }

            const ndef = new NDEFReader();
            await ndef.scan();

            ndef.onreading = event => {
                const serial = event.serialNumber;
                if (serial) {
                    const formatted = formatHexUid(serial);
                    const input = document.getElementById(targetInputId);
                    if (input) {
                        input.value = formatted;
                        validateUidAvailability(formatted, statusBoxId);
                    }
                }
            };

            ndef.onreadingerror = () => {
                if (box) {
                    box.style.color = '#f87171';
                    box.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Gagal membaca kartu. Pastikan posisi chip KTP pas di antena NFC HP.';
                }
            };
        } catch (error) {
            if (box) {
                box.style.background = 'rgba(239, 68, 68, 0.15)';
                box.style.border = '1px solid #ef4444';
                box.style.color = '#f87171';
                box.innerHTML = '<i class="fa-solid fa-xmark"></i> Izin NFC belum aktif: ' + error.message;
            }
        }
    }

    // 3. SIMULASI TAP e-KTP UNTUK PENGUJIAN / DEMO
    function triggerSimulatedScan(targetInputId, statusBoxId, excludeUserId = null) {
        fetch('{{ route("warga.simulate.scan") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.uid) {
                const input = document.getElementById(targetInputId);
                if (input) {
                    input.value = data.uid;
                    validateUidAvailability(data.uid, statusBoxId, excludeUserId);
                }
            }
        })
        .catch(() => alert('Gagal memicu simulasi tap.'));
    }

    // 4. MODAL DIRECT PAIRING UNTUK WARGA DI TABEL
    function openDirectPairModal(userId, userName, currentUid) {
        currentDirectPairUserId = userId;
        document.getElementById('dpUserName').textContent = userName;
        const notice = document.getElementById('dpCurrentUidNotice');
        const input = document.getElementById('dp_rfid_uid');
        const statusBox = document.getElementById('dp_uid_status');
        const unpairContainer = document.getElementById('dpUnpairBtnContainer');
        const form = document.getElementById('formDirectPair');

        form.action = `/warga/${userId}/pair-ktp`;
        statusBox.style.display = 'none';

        if (currentUid) {
            notice.innerHTML = `<i class="fa-solid fa-circle-check"></i> KTP Saat Ini: <strong>${currentUid}</strong> (Akan ditimpa)`;
            input.value = currentUid;
            unpairContainer.innerHTML = `
                <form action="/warga/${userId}/unpair-ktp" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mencabut e-KTP ini?');" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn btn-danger" style="padding: 8px 12px; font-size: 11px;">
                        <i class="fa-solid fa-trash-can"></i> Cabut e-KTP
                    </button>
                </form>
            `;
        } else {
            notice.innerHTML = `<span style="color: var(--text-muted);"><i class="fa-solid fa-circle-question"></i> Belum ada e-KTP terdaftar</span>`;
            input.value = '';
            unpairContainer.innerHTML = '';
        }

        document.getElementById('modal-direct-pair').style.display = 'flex';
    }

    function closeDirectPairModal() {
        document.getElementById('modal-direct-pair').style.display = 'none';
        cancelScanEsp32();
        currentDirectPairUserId = null;
    }
    // 5. MODAL TAMBAH WARGA
    function openTambahWargaModal() {
        const modal = document.getElementById('modal-tambah-user');
        if (modal) {
            modal.style.display = 'flex';
            const input = modal.querySelector('input[name="name"]');
            if (input) input.focus();
        }
    }

    function closeTambahWargaModal() {
        const modal = document.getElementById('modal-tambah-user');
        if (modal) {
            modal.style.display = 'none';
            cancelScanEsp32();
        }
    }

    // Tutup modal jika user klik di luar card (backdrop click)
    window.addEventListener('click', function(e) {
        const modalTambah = document.getElementById('modal-tambah-user');
        if (modalTambah && e.target === modalTambah) {
            closeTambahWargaModal();
        }
        const modalPair = document.getElementById('modal-direct-pair');
        if (modalPair && e.target === modalPair) {
            closeDirectPairModal();
        }
    });
</script>
@endpush
@endsection
