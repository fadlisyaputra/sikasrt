@extends('layouts.app')

@section('title', 'Posyandu Balita & Lansia')
@section('page_title', 'Layanan Posyandu Balita & Lansia RT')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Metric Overview Cards -->
    <div class="grid grid-3">
        <div class="card metric-card">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <span class="metric-sub">Total Agenda Posyandu</span>
                    <h3 class="metric-value">{{ $totalActivities }} <span style="font-size: 14px; font-weight: 500; color: #94a3b8;">Kegiatan</span></h3>
                    <span class="badge badge-info"><i class="fa-solid fa-calendar-check"></i> Balita & Lansia</span>
                </div>
                <div class="metric-icon" style="background: var(--gradient-accent);">
                    <i class="fa-solid fa-heart-pulse"></i>
                </div>
            </div>
        </div>

        <div class="card metric-card">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <span class="metric-sub">Balita Terperiksa</span>
                    <h3 class="metric-value" style="color: #38bdf8;">{{ $totalBalitaChecked }} <span style="font-size: 14px; font-weight: 500; color: #94a3b8;">Anak</span></h3>
                    <span class="badge badge-success"><i class="fa-solid fa-baby"></i> Tumbuh Kembang & Imunisasi</span>
                </div>
                <div class="metric-icon" style="background: var(--gradient-sky);">
                    <i class="fa-solid fa-baby-carriage"></i>
                </div>
            </div>
        </div>

        <div class="card metric-card">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <span class="metric-sub">Lansia Terperiksa</span>
                    <h3 class="metric-value" style="color: #f472b6;">{{ $totalLansiaChecked }} <span style="font-size: 14px; font-weight: 500; color: #94a3b8;">Warga</span></h3>
                    <span class="badge badge-pending"><i class="fa-solid fa-notes-medical"></i> Tensi & Gula Darah</span>
                </div>
                <div class="metric-icon" style="background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);">
                    <i class="fa-solid fa-person-cane"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Agenda Posyandu Mendatang -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 14px;">
            <div>
                <h3 class="card-title" style="color: #f472b6;">
                    <i class="fa-solid fa-calendar-days"></i> Agenda & Jadwal Kegiatan Posyandu Mendatang
                </h3>
                <p style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                    Jadwal pemeriksaan rutin kesehatan balita, imunisasi, dan skrining kesehatan lansia di lingkungan RT 01 / RW 05.
                </p>
            </div>
            @if(in_array(auth()->user()->role, ['superadmin', 'bendahara', 'rt']))
            <button type="button" onclick="document.getElementById('modalTambahKegiatan').style.display='block'" class="btn btn-primary" style="font-size: 12px;">
                <i class="fa-solid fa-plus"></i> Tambah Agenda Posyandu
            </button>
            @endif
        </div>

        <div class="grid grid-2" style="margin-top: 16px; gap: 16px;">
            @forelse($upcomingActivities as $act)
            <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-color); border-radius: 14px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span class="badge {{ $act->category === 'balita' ? 'badge-info' : 'badge-warning' }}" style="font-size: 11px; text-transform: uppercase;">
                            <i class="fa-solid {{ $act->category === 'balita' ? 'fa-baby' : 'fa-person-cane' }}"></i> Posyandu {{ $act->category }}
                        </span>
                        <span style="font-size: 12px; color: #34d399; font-weight: 700;">
                            <i class="fa-solid fa-calendar-day"></i> {{ \Carbon\Carbon::parse($act->activity_date)->translatedFormat('l, d F Y') }}
                        </span>
                    </div>

                    <h4 style="font-size: 16px; font-weight: 800; color: #fff; margin-bottom: 6px;">{{ $act->title }}</h4>
                    <p style="font-size: 12px; color: var(--text-muted); line-height: 1.5; margin-bottom: 12px;">
                        {{ $act->description }}
                    </p>

                    <div style="display: flex; flex-direction: column; gap: 4px; font-size: 12px; border-top: 1px dashed var(--border-color); padding-top: 10px;">
                        <div style="color: #cbd5e1;"><i class="fa-solid fa-clock" style="color: var(--secondary); width: 16px;"></i> Pukul {{ $act->start_time }} - {{ $act->end_time }} WIB</div>
                        <div style="color: #cbd5e1;"><i class="fa-solid fa-location-dot" style="color: #f87171; width: 16px;"></i> {{ $act->location }}</div>
                        <div style="color: #cbd5e1;"><i class="fa-solid fa-user-doctor" style="color: #34d399; width: 16px;"></i> Petugas: {{ $act->officer_name }}</div>
                    </div>
                </div>

                @if(in_array(auth()->user()->role, ['superadmin', 'bendahara', 'rt']))
                <div style="display: flex; justify-content: flex-end; gap: 8px; margin-top: 14px; border-top: 1px solid var(--border-color); padding-top: 10px;">
                    <form action="{{ route('posyandu.activity.destroy', $act->id) }}" method="POST" onsubmit="return confirm('Hapus agenda ini?')" style="margin: 0;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" style="font-size: 11px; padding: 4px 10px;">
                            <i class="fa-solid fa-trash"></i> Hapus Agenda
                        </button>
                    </form>
                </div>
                @endif
            </div>
            @empty
            <div style="grid-column: 1 / -1; text-align: center; padding: 30px; color: var(--text-muted);">
                <i class="fa-solid fa-notes-medical" style="font-size: 36px; margin-bottom: 8px; opacity: 0.3;"></i>
                <p>Belum ada jadwal kegiatan Posyandu mendatang.</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Catatan Pemeriksaan Posyandu Warga -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 14px; flex-wrap: wrap; gap: 12px;">
            <div>
                <h3 class="card-title">
                    <i class="fa-solid fa-book-medical" style="color: var(--primary);"></i>
                    {{ auth()->user()->isWarga() ? 'Buku Catatan Posyandu Keluarga Saya' : 'Daftar Hasil Pemeriksaan Posyandu Warga' }}
                </h3>
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
                @if(in_array(auth()->user()->role, ['superadmin', 'bendahara', 'rt']))
                <button type="button" onclick="document.getElementById('modalInputHasil').style.display='block'" class="btn btn-success" style="font-size: 12px;">
                    <i class="fa-solid fa-file-medical"></i> Input Hasil Pemeriksaan
                </button>
                @endif
            </div>
        </div>

        <div style="overflow-x: auto; margin-top: 14px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-color); color: var(--text-muted); text-align: left;">
                        <th style="padding: 10px 8px;">Nama Peserta</th>
                        <th style="padding: 10px 8px;">Kategori</th>
                        <th style="padding: 10px 8px;">Usia / JK</th>
                        <th style="padding: 10px 8px;">BB / TB</th>
                        <th style="padding: 10px 8px;">Tensi / Gula Darah</th>
                        <th style="padding: 10px 8px;">Imunisasi / Vitamin</th>
                        <th style="padding: 10px 8px;">Status Kesehatan</th>
                        <th style="padding: 10px 8px;">Catatan</th>
                        @if(in_array(auth()->user()->role, ['superadmin', 'bendahara', 'rt']))
                        <th style="padding: 10px 8px;">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @php $listRecords = auth()->user()->isWarga() ? $myRecords : $recentRecords; @endphp
                    @forelse($listRecords as $rec)
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td style="padding: 10px 8px;">
                            <strong style="color: #fff;">{{ $rec->participant_name }}</strong>
                            @if($rec->user)
                                <div style="font-size: 11px; color: var(--text-muted);">KK: {{ $rec->user->name }}</div>
                            @endif
                        </td>
                        <td style="padding: 10px 8px;">
                            <span class="badge {{ $rec->participant_category === 'balita' ? 'badge-info' : 'badge-warning' }}" style="font-size: 10px;">
                                {{ strtoupper($rec->participant_category) }}
                            </span>
                        </td>
                        <td style="padding: 10px 8px; color: #cbd5e1;">
                            {{ $rec->age_text ?? '-' }} ({{ $rec->gender }})
                        </td>
                        <td style="padding: 10px 8px;">
                            @if($rec->weight || $rec->height)
                                <span style="color: #38bdf8; font-weight: 700;">{{ $rec->weight ? $rec->weight . ' kg' : '' }}</span> / 
                                <span style="color: #cbd5e1;">{{ $rec->height ? $rec->height . ' cm' : '' }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td style="padding: 10px 8px;">
                            @if($rec->blood_pressure || $rec->blood_sugar)
                                <span style="color: #f472b6; font-weight: 600;">{{ $rec->blood_pressure ? 'Tensi: ' . $rec->blood_pressure : '' }}</span>
                                @if($rec->blood_sugar)
                                    <div style="font-size: 11px; color: var(--text-muted);">Gula: {{ $rec->blood_sugar }} mg/dL</div>
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        <td style="padding: 10px 8px; color: #34d399; font-size: 12px;">
                            {{ $rec->vitamin_immunization ?? '-' }}
                        </td>
                        <td style="padding: 10px 8px;">
                            <span class="badge badge-success" style="font-size: 11px;">
                                <i class="fa-solid fa-check"></i> {{ $rec->health_status }}
                            </span>
                        </td>
                        <td style="padding: 10px 8px; font-size: 12px; color: var(--text-muted); max-width: 200px;">
                            {{ $rec->notes ?? '-' }}
                        </td>
                        @if(in_array(auth()->user()->role, ['superadmin', 'bendahara', 'rt']))
                        <td style="padding: 10px 8px;">
                            <form action="{{ route('posyandu.record.destroy', $rec->id) }}" method="POST" onsubmit="return confirm('Hapus data ini?')" style="margin: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="background: none; border: none; color: #f87171; cursor: pointer;" title="Hapus Data">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ in_array(auth()->user()->role, ['superadmin', 'bendahara', 'rt']) ? 9 : 8 }}" style="text-align: center; padding: 24px; color: var(--text-muted);">
                            Belum ada catatan hasil pemeriksaan Posyandu.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal Tambah Agenda Kegiatan Posyandu (Admin/RT) -->
<div id="modalTambahKegiatan" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); z-index: 999; padding: 20px; overflow-y: auto;">
    <div style="max-width: 520px; margin: 50px auto; background: var(--bg-card); border-radius: 16px; border: 1px solid var(--border-color); padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 18px; font-weight: 800; color: #fff;">Tambah Agenda Posyandu Baru</h3>
            <button type="button" onclick="document.getElementById('modalTambahKegiatan').style.display='none'" style="background: none; border: none; color: #fff; font-size: 20px; cursor: pointer;">&times;</button>
        </div>

        <form action="{{ route('posyandu.activity.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Judul Kegiatan</label>
                <input type="text" name="title" class="form-control" placeholder="Contoh: Posyandu Balita Melati: Imunisasi & Gizi" required>
            </div>
            <div class="grid grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label>Kategori Layanan</label>
                    <select name="category" class="form-control" required>
                        <option value="balita">Posyandu Balita & Anak</option>
                        <option value="lansia">Posyandu Lansia</option>
                        <option value="ibu_hamil">Ibu Hamil & Menyusui</option>
                        <option value="umum">Pemeriksaan Umum RT</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tanggal Pelaksanaan</label>
                    <input type="date" name="activity_date" class="form-control" min="{{ date('Y-m-d') }}" required>
                </div>
            </div>
            <div class="grid grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label>Jam Mulai</label>
                    <input type="text" name="start_time" class="form-control" value="08:30" required>
                </div>
                <div class="form-group">
                    <label>Jam Selesai</label>
                    <input type="text" name="end_time" class="form-control" value="11:30" required>
                </div>
            </div>
            <div class="form-group">
                <label>Lokasi Pemeriksaan</label>
                <input type="text" name="location" class="form-control" value="Balai Pertemuan Warga RT 01 / RW 05" required>
            </div>
            <div class="form-group">
                <label>Petugas Medis / Bidan / Kader Posyandu</label>
                <input type="text" name="officer_name" class="form-control" placeholder="Bidan Yuli, S.Tr.Keb & Kader Melati" required>
            </div>
            <div class="form-group">
                <label>Deskripsi & Layanan yang Disediakan</label>
                <textarea name="description" class="form-control" rows="2" placeholder="Rincian penimbangan, jenis vitamin yang dibagikan..."></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px;">
                <button type="button" onclick="document.getElementById('modalTambahKegiatan').style.display='none'" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Simpan Agenda</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Input Hasil Pemeriksaan Kesehatan (Admin/RT) -->
<div id="modalInputHasil" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); z-index: 999; padding: 20px; overflow-y: auto;">
    <div style="max-width: 550px; margin: 40px auto; background: var(--bg-card); border-radius: 16px; border: 1px solid var(--border-color); padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 18px; font-weight: 800; color: #fff;">Input Hasil Pemeriksaan Posyandu</h3>
            <button type="button" onclick="document.getElementById('modalInputHasil').style.display='none'" style="background: none; border: none; color: #fff; font-size: 20px; cursor: pointer;">&times;</button>
        </div>

        <form action="{{ route('posyandu.record.store') }}" method="POST">
            @csrf
            <div class="grid grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label>Agenda Posyandu Terkait</label>
                    <select name="posyandu_activity_id" class="form-control">
                        <option value="">-- Tanpa Agenda Tertentu --</option>
                        @foreach($upcomingActivities as $a)
                        <option value="{{ $a->id }}">{{ $a->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Keluarga / Kepala Keluarga Warga</label>
                    <select name="user_id" class="form-control">
                        <option value="">-- Pilih KK Warga (Opsional) --</option>
                        @foreach($wargaList as $w)
                        <option value="{{ $w->id }}">{{ $w->name }} ({{ $w->address ?? 'RT 01' }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label>Nama Peserta / Anak / Lansia</label>
                    <input type="text" name="participant_name" class="form-control" placeholder="Nama lengkap peserta" required>
                </div>
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="participant_category" class="form-control" required>
                        <option value="balita">Balita / Anak</option>
                        <option value="lansia">Lansia</option>
                        <option value="ibu_hamil">Ibu Hamil</option>
                        <option value="umum">Umum</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label>Jenis Kelamin</label>
                    <select name="gender" class="form-control" required>
                        <option value="L">Laki-laki (L)</option>
                        <option value="P">Perempuan (P)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Usia (Bulan / Tahun)</label>
                    <input type="text" name="age_text" class="form-control" placeholder="Contoh: 18 Bulan atau 64 Tahun">
                </div>
            </div>

            <div class="grid grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label>Berat Badan (kg)</label>
                    <input type="number" step="0.1" name="weight" class="form-control" placeholder="Contoh: 11.5">
                </div>
                <div class="form-group">
                    <label>Tinggi Badan (cm)</label>
                    <input type="number" step="0.1" name="height" class="form-control" placeholder="Contoh: 82.5">
                </div>
            </div>

            <div class="grid grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label>Tekanan Darah (Tensi)</label>
                    <input type="text" name="blood_pressure" class="form-control" placeholder="Contoh: 120/80">
                </div>
                <div class="form-group">
                    <label>Kadar Gula Darah (mg/dL)</label>
                    <input type="number" step="0.1" name="blood_sugar" class="form-control" placeholder="Contoh: 110">
                </div>
            </div>

            <div class="form-group">
                <label>Vaksinasi / Imunisasi / Vitamin Diberikan</label>
                <input type="text" name="vitamin_immunization" class="form-control" placeholder="Contoh: Polio 3, Campak, Vitamin A">
            </div>

            <div class="form-group">
                <label>Status Kesehatan / Gizi</label>
                <select name="health_status" class="form-control" required>
                    <option value="Gizi Baik & Sehat">Gizi Baik & Sehat</option>
                    <option value="Perlu Perhatian / Pantauan">Perlu Perhatian / Pantauan</option>
                    <option value="Gizi Kurang">Gizi Kurang</option>
                    <option value="Tensi / Gula Terkontrol">Tensi / Gula Terkontrol</option>
                    <option value="Hipertensi Ringan">Hipertensi Ringan</option>
                </select>
            </div>

            <div class="form-group">
                <label>Catatan Petugas / Rekomendasi Medis</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Saran asupan makanan bergizi, kontrol ulang..."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px;">
                <button type="button" onclick="document.getElementById('modalInputHasil').style.display='none'" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-success"><i class="fa-solid fa-save"></i> Simpan Hasil Pemeriksaan</button>
            </div>
        </form>
    </div>
</div>
@endsection
