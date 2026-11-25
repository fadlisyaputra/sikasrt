@extends('layouts.app')

@section('title', 'Jadwal Ronda Siskamling')
@section('page_title', 'Jadwal Ronda & Keamanan Siskamling')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Banner Ronda Malam Ini -->
    <div style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(6, 182, 212, 0.2) 100%); border: 1px solid #10b981; border-radius: 16px; padding: 22px 26px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(16, 185, 129, 0.25); border: 1px solid #10b981; display: flex; align-items: center; justify-content: center; font-size: 26px; color: #34d399;">
                    <i class="fa-solid fa-moon"></i>
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span class="badge badge-success" style="font-size: 11px; font-weight: 700;">MALAM INI: {{ strtoupper($todayIndo) }}</span>
                        @if($todaySchedule)
                            <span style="color: #38bdf8; font-weight: 700; font-size: 14px;">{{ $todaySchedule->group_name }}</span>
                        @endif
                    </div>
                    <h3 style="font-size: 18px; font-weight: 800; color: #fff; margin-top: 4px;">
                        @if($todaySchedule)
                            {{ $todaySchedule->location }} (Pukul {{ $todaySchedule->start_time }} - {{ $todaySchedule->end_time }} WIB)
                        @else
                            Belum ada jadwal ronda yang ditentukan untuk malam ini.
                        @endif
                    </h3>
                    <p style="font-size: 13px; color: var(--text-muted); margin-top: 2px;">
                        @if($todaySchedule && $todaySchedule->members->isNotEmpty())
                            Petugas Jaga: 
                            <strong style="color: #e2e8f0;">
                                {{ $todaySchedule->members->map(fn($m) => $m->user->name)->join(', ') }}
                            </strong>
                        @else
                            Silakan cek jadwal regu di bawah.
                        @endif
                    </p>
                </div>
            </div>

            <!-- Tombol Aksi Check-in Kehadiran -->
            <div style="display: flex; align-items: center; gap: 10px;">
                @if(in_array(auth()->user()->role, ['superadmin', 'bendahara', 'rt']))
                <button type="button" onclick="document.getElementById('modalTambahJadwal').style.display='block'" class="btn btn-primary" style="font-size: 13px;">
                    <i class="fa-solid fa-plus"></i> Tambah Jadwal Regu
                </button>
                @endif

                @if(!$hasCheckedInToday)
                <form action="{{ route('ronda.checkin') }}" method="POST" style="margin: 0;">
                    @csrf
                    @if($todaySchedule)
                        <input type="hidden" name="ronda_schedule_id" value="{{ $todaySchedule->id }}">
                    @endif
                    <input type="hidden" name="status" value="hadir">
                    <button type="submit" class="btn btn-success" style="font-size: 13px; font-weight: 700;">
                        <i class="fa-solid fa-user-check"></i> Check-in Hadir Ronda
                    </button>
                </form>
                @else
                <div class="badge badge-success" style="padding: 10px 16px; font-size: 13px;">
                    <i class="fa-solid fa-circle-check"></i> Anda Sudah Absen Hadir Malam Ini
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Jadwal Ronda Pribadi untuk Warga yang Login -->
    @if(auth()->user()->isWarga())
    <div class="card" style="border: 1px solid rgba(99, 102, 241, 0.3); background: rgba(30, 41, 59, 0.7);">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
            <h3 class="card-title" style="color: #818cf8;">
                <i class="fa-solid fa-calendar-check"></i> Jadwal Giliran Ronda Saya
            </h3>
        </div>
        @if($mySchedules->isEmpty())
        <p style="font-size: 13px; color: var(--text-muted); margin-top: 10px;">
            Anda belum dijadwalkan ke dalam regu ronda siskamling. Pengurus RT akan menugaskan jadwal ronda Anda.
        </p>
        @else
        <div class="grid grid-3" style="margin-top: 14px; gap: 14px;">
            @foreach($mySchedules as $mySched)
            <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-color); border-radius: 12px; padding: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span class="badge badge-info" style="font-size: 12px; font-weight: 800;">HARI {{ strtoupper($mySched->day_name) }}</span>
                    <span style="font-size: 12px; color: #38bdf8; font-weight: 600;"><i class="fa-solid fa-clock"></i> {{ $mySched->start_time }} - {{ $mySched->end_time }}</span>
                </div>
                <h4 style="font-size: 15px; font-weight: 700; color: #fff;">{{ $mySched->group_name }}</h4>
                <p style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                    <i class="fa-solid fa-location-dot"></i> {{ $mySched->location }}
                </p>
                <div style="margin-top: 12px; font-size: 12px; border-top: 1px dashed var(--border-color); padding-top: 8px;">
                    <span style="color: var(--text-muted);">Rekan Jaga: </span>
                    <strong style="color: #cbd5e1;">{{ $mySched->members->map(fn($m) => $m->user->name)->join(', ') }}</strong>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @endif

    <!-- Grid 7 Hari Jadwal Ronda Siskamling RT -->
    <div>
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
            <h3 style="font-size: 18px; font-weight: 800; color: #fff;">
                <i class="fa-solid fa-list-check" style="color: var(--secondary);"></i> Seluruh Jadwal Ronda Mingguan (Senin - Minggu)
            </h3>
            <span style="font-size: 12px; color: var(--text-muted);">Terintegrasi tap absensi pos ronda ESP32</span>
        </div>

        <div class="grid grid-3" style="gap: 16px;">
            @forelse($schedules as $sched)
            <div class="card" style="border: 1px solid {{ $sched->day_name === $todayIndo ? '#10b981' : 'var(--border-color)' }}; position: relative; background: {{ $sched->day_name === $todayIndo ? 'rgba(16, 185, 129, 0.05)' : 'var(--bg-card)' }};">
                @if($sched->day_name === $todayIndo)
                <div style="position: absolute; top: 12px; right: 12px;">
                    <span class="badge badge-success" style="font-size: 10px;"><i class="fa-solid fa-circle-dot"></i> HARI INI</span>
                </div>
                @endif

                <div style="margin-bottom: 12px;">
                    <span style="font-size: 11px; font-weight: 800; color: var(--secondary); text-transform: uppercase;">{{ $sched->day_name }}</span>
                    <h4 style="font-size: 16px; font-weight: 800; color: #fff; margin-top: 2px;">{{ $sched->group_name }}</h4>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-clock"></i> {{ $sched->start_time }} - {{ $sched->end_time }} WIB
                    </div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-location-dot"></i> {{ $sched->location }}
                    </div>
                </div>

                <div style="border-top: 1px solid var(--border-color); padding-top: 10px; margin-top: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">
                            Anggota Regu ({{ $sched->members->count() }} Warga)
                        </span>
                        @if(in_array(auth()->user()->role, ['superadmin', 'bendahara', 'rt']))
                        <button type="button" onclick="openAddMemberModal({{ $sched->id }}, '{{ $sched->day_name }} - {{ $sched->group_name }}')" class="btn btn-secondary" style="font-size: 10px; padding: 2px 8px;">
                            <i class="fa-solid fa-user-plus"></i> Tambah
                        </button>
                        @endif
                    </div>

                    @if($sched->members->isEmpty())
                        <p style="font-size: 12px; color: var(--text-muted); font-style: italic;">Belum ada anggota ditugaskan.</p>
                    @else
                        <div style="display: flex; flex-direction: column; gap: 6px;">
                            @foreach($sched->members as $member)
                            <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(15, 23, 42, 0.5); padding: 6px 10px; border-radius: 8px; font-size: 12px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <i class="fa-solid {{ $member->role_in_team === 'Koordinator' ? 'fa-user-tie' : 'fa-user' }}" style="color: {{ $member->role_in_team === 'Koordinator' ? '#fbbf24' : '#94a3b8' }};"></i>
                                    <span style="color: #fff; font-weight: 600;">{{ $member->user->name }}</span>
                                    @if($member->role_in_team === 'Koordinator')
                                        <span class="badge badge-warning" style="font-size: 9px; padding: 2px 6px;">PJ</span>
                                    @endif
                                </div>
                                @if(in_array(auth()->user()->role, ['superadmin', 'bendahara', 'rt']))
                                <form action="{{ route('ronda.member.remove', [$sched->id, $member->user_id]) }}" method="POST" onsubmit="return confirm('Keluarkan anggota ini?')" style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background: none; border: none; color: #f87171; cursor: pointer; font-size: 12px;" title="Hapus dari regu">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if(in_array(auth()->user()->role, ['superadmin', 'bendahara', 'rt']))
                <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 14px; border-top: 1px solid var(--border-color); padding-top: 10px;">
                    <form action="{{ route('ronda.schedule.destroy', $sched->id) }}" method="POST" onsubmit="return confirm('Hapus jadwal ini?')" style="margin: 0;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" style="font-size: 11px; padding: 4px 10px;">
                            <i class="fa-solid fa-trash"></i> Hapus
                        </button>
                    </form>
                </div>
                @endif
            </div>
            @empty
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--text-muted);">
                <i class="fa-solid fa-shield-halved" style="font-size: 40px; margin-bottom: 10px; opacity: 0.3;"></i>
                <p>Belum ada jadwal ronda yang dibuat.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Riwayat Absensi Kehadiran Ronda Siskamling -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fa-solid fa-clipboard-user" style="color: var(--primary);"></i>
                Riwayat & Log Kehadiran Ronda Siskamling
            </h3>
        </div>

        <div style="overflow-x: auto; margin-top: 12px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-color); color: var(--text-muted); text-align: left;">
                        <th style="padding: 10px 8px;">Tanggal</th>
                        <th style="padding: 10px 8px;">Nama Warga</th>
                        <th style="padding: 10px 8px;">Regu / Jadwal</th>
                        <th style="padding: 10px 8px;">Status</th>
                        <th style="padding: 10px 8px;">Metode Absen</th>
                        <th style="padding: 10px 8px;">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @php $listData = auth()->user()->isWarga() ? $myAttendances : $recentAttendances; @endphp
                    @forelse($listData as $att)
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td style="padding: 10px 8px; color: var(--text-muted); font-size: 12px;">
                            {{ \Carbon\Carbon::parse($att->attendance_date)->translatedFormat('d F Y') }}
                        </td>
                        <td style="padding: 10px 8px; font-weight: 700; color: #fff;">
                            {{ $att->user->name }}
                        </td>
                        <td style="padding: 10px 8px; color: #38bdf8;">
                            {{ $att->schedule ? $att->schedule->group_name : 'Pos Ronda RT' }}
                        </td>
                        <td style="padding: 10px 8px;">
                            @if($att->status === 'hadir')
                                <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Hadir</span>
                            @elseif($att->status === 'izin')
                                <span class="badge badge-pending"><i class="fa-solid fa-envelope-open-text"></i> Izin</span>
                            @else
                                <span class="badge badge-danger"><i class="fa-solid fa-circle-xmark"></i> Alpha</span>
                            @endif
                        </td>
                        <td style="padding: 10px 8px;">
                            @if($att->method === 'rfid_ktp')
                                <span style="color: #38bdf8; font-family: monospace; font-size: 11px;"><i class="fa-solid fa-id-card"></i> Tap e-KTP</span>
                            @else
                                <span style="color: var(--text-muted); font-size: 11px;"><i class="fa-solid fa-check"></i> Manual Web</span>
                            @endif
                        </td>
                        <td style="padding: 10px 8px; color: #cbd5e1; font-size: 12px;">
                            {{ $att->notes ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 24px; color: var(--text-muted);">Belum ada catatan kehadiran ronda.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal Tambah Jadwal Regu (Admin/RT) -->
<div id="modalTambahJadwal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); z-index: 999; padding: 20px; overflow-y: auto;">
    <div style="max-width: 500px; margin: 50px auto; background: var(--bg-card); border-radius: 16px; border: 1px solid var(--border-color); padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
            <h3 style="font-size: 18px; font-weight: 800; color: #fff;">Tambah Jadwal Regu Ronda</h3>
            <button type="button" onclick="document.getElementById('modalTambahJadwal').style.display='none'" style="background: none; border: none; color: #fff; font-size: 20px; cursor: pointer;">&times;</button>
        </div>

        <form action="{{ route('ronda.schedule.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Pilih Hari</label>
                <select name="day_name" class="form-control" required>
                    <option value="Senin">Senin</option>
                    <option value="Selasa">Selasa</option>
                    <option value="Rabu">Rabu</option>
                    <option value="Kamis">Kamis</option>
                    <option value="Jumat">Jumat</option>
                    <option value="Sabtu">Sabtu</option>
                    <option value="Minggu">Minggu</option>
                </select>
            </div>
            <div class="form-group">
                <label>Nama Regu</label>
                <input type="text" name="group_name" class="form-control" placeholder="Contoh: Regu 1 (Garuda)" required>
            </div>
            <div class="grid grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label>Jam Mulai</label>
                    <input type="text" name="start_time" class="form-control" value="22:00" required>
                </div>
                <div class="form-group">
                    <label>Jam Selesai</label>
                    <input type="text" name="end_time" class="form-control" value="04:00" required>
                </div>
            </div>
            <div class="form-group">
                <label>Pos / Lokasi Pos Ronda</label>
                <input type="text" name="location" class="form-control" value="Pos Ronda Utama RT 01" required>
            </div>
            <div class="form-group">
                <label>Penanggung Jawab / Koordinator</label>
                <select name="coordinator_id" class="form-control">
                    <option value="">-- Pilih Warga / Koordinator --</option>
                    @foreach($wargaList as $w)
                    <option value="{{ $w->id }}">{{ $w->name }} ({{ $w->address ?? 'Warga' }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Catatan / Instruksi Khusus (Opsional)</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Instruksi rute patroli, pengecekan portal..."></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px;">
                <button type="button" onclick="document.getElementById('modalTambahJadwal').style.display='none'" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan Jadwal</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Anggota ke Jadwal (Admin/RT) -->
<div id="modalTambahAnggota" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); z-index: 999; padding: 20px;">
    <div style="max-width: 440px; margin: 80px auto; background: var(--bg-card); border-radius: 16px; border: 1px solid var(--border-color); padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 16px; font-weight: 800; color: #fff;">Tambah Anggota Warga</h3>
            <button type="button" onclick="document.getElementById('modalTambahAnggota').style.display='none'" style="background: none; border: none; color: #fff; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <p id="labelScheduleTarget" style="font-size: 13px; color: var(--secondary); margin-bottom: 14px; font-weight: 600;"></p>

        <form id="formTambahAnggota" action="" method="POST">
            @csrf
            <div class="form-group">
                <label>Pilih Warga</label>
                <select name="user_id" class="form-control" required>
                    @foreach($wargaList as $w)
                    <option value="{{ $w->id }}">{{ $w->name }} - {{ $w->address ?? 'RT 01' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Peran dalam Regu</label>
                <select name="role_in_team" class="form-control">
                    <option value="Anggota">Anggota Regu</option>
                    <option value="Koordinator">Koordinator / Ketua Regu</option>
                </select>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px;">
                <button type="button" onclick="document.getElementById('modalTambahAnggota').style.display='none'" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-success"><i class="fa-solid fa-plus"></i> Tambahkan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddMemberModal(scheduleId, label) {
        document.getElementById('labelScheduleTarget').innerText = 'Menambahkan ke: ' + label;
        document.getElementById('formTambahAnggota').action = '/ronda/jadwal/' + scheduleId + '/anggota';
        document.getElementById('modalTambahAnggota').style.display = 'block';
    }
</script>
@endsection
