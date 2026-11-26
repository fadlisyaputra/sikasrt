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
    <button onclick="document.getElementById('modal-tambah-user').style.display='flex';" class="btn btn-primary">
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
                            <span style="font-family: monospace; font-size: 11px; color: #38bdf8; background: rgba(6,182,212,0.15); padding: 3px 8px; border-radius: 6px; border: 1px solid rgba(6,182,212,0.3);">
                                <i class="fa-solid fa-id-card"></i> {{ $u->rfid_uid }}
                            </span>
                        @else
                            <span style="color: var(--text-muted); font-size: 12px;">-</span>
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
                    <div style="margin-top: 4px; font-family: monospace; font-size: 10px; color: #38bdf8;">
                        <i class="fa-solid fa-id-card"></i> {{ $u->rfid_uid }}
                    </div>
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

<!-- Modal Create User -->
@if(in_array(auth()->user()->role, ['superadmin', 'bendahara']))
<div id="modal-tambah-user" style="display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.7); backdrop-filter: blur(5px); z-index: 1000; align-items:center; justify-content:center;">
    <div class="card" style="width: 100%; max-width: 550px;">
        <div class="card-header">
            <div class="card-title">Tambah Akun / Warga Baru</div>
            <button onclick="document.getElementById('modal-tambah-user').style.display='none';" class="btn btn-secondary" style="padding: 4px 8px;">&times;</button>
        </div>

        <form action="{{ route('warga.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="name" class="form-control" placeholder="Contoh: Ahmad Subagyo" required>
            </div>

            <div class="grid grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label>Email (Username)</label>
                    <input type="email" name="email" class="form-control" placeholder="ahmad@kasrt.local" required>
                </div>
                <div class="form-group">
                    <label>Kata Sandi</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
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

            <div class="grid grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label>Nomor RT / RW</label>
                    <input type="text" name="rt_rw" class="form-control" value="RT 01 / RW 05" required>
                </div>
                <div class="form-group">
                    <label>UID Kartu RFID IoT (Opsional)</label>
                    <input type="text" name="rfid_uid" class="form-control" placeholder="Contoh: 93:B4:72:A1" style="font-family: monospace;">
                </div>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" onclick="document.getElementById('modal-tambah-user').style.display='none';" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan User</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
