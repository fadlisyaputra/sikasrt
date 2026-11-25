@extends('layouts.app')

@section('page_title', 'Layanan Surat Pengantar RT')

@section('content')
<div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
    <div>
        <h2 style="font-size: 20px; font-weight: 700; color: #fff;">Layanan Administrasi Surat Menyurat RT</h2>
        <p style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">Pengajuan surat pengantar resmi RT secara mandiri, tracking status verifikasi, dan cetak dokumen resmi ber-kop surat.</p>
    </div>
    <div>
        <button type="button" class="btn btn-primary" onclick="openModal('modalAjukanSurat')" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9); display: inline-flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-plus"></i> Ajukan Surat Baru
        </button>
    </div>
</div>

<!-- Metrics Cards -->
<div class="grid grid-4" style="margin-bottom: 28px;">
    <div class="card metric-card">
        <div class="metric-icon" style="background: rgba(139, 92, 246, 0.2); color: #a78bfa;">
            <i class="fa-solid fa-envelope-open-text"></i>
        </div>
        <div class="metric-value">{{ $totalPengajuan }}</div>
        <div class="metric-sub">Total Pengajuan Surat</div>
    </div>
    <div class="card metric-card">
        <div class="metric-icon" style="background: rgba(16, 185, 129, 0.2); color: #34d399;">
            <i class="fa-solid fa-file-circle-check"></i>
        </div>
        <div class="metric-value" style="color: #34d399;">{{ $totalApproved }}</div>
        <div class="metric-sub">Surat Disetujui / Selesai</div>
    </div>
    <div class="card metric-card">
        <div class="metric-icon" style="background: rgba(245, 158, 11, 0.2); color: #fbbf24;">
            <i class="fa-solid fa-hourglass-half"></i>
        </div>
        <div class="metric-value" style="color: #fbbf24;">{{ $totalPending }}</div>
        <div class="metric-sub">Menunggu Verifikasi Pengurus</div>
    </div>
    <div class="card metric-card">
        <div class="metric-icon" style="background: rgba(239, 68, 68, 0.2); color: #f87171;">
            <i class="fa-solid fa-file-circle-xmark"></i>
        </div>
        <div class="metric-value" style="color: #f87171;">{{ $totalRejected }}</div>
        <div class="metric-sub">Permohonan Ditolak</div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card" style="padding: 16px; margin-bottom: 24px;">
    <form action="{{ route('surat.index') }}" method="GET" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: center;">
        <div style="flex: 1; min-width: 220px;">
            <select name="status" class="form-control" onchange="this.form.submit()">
                <option value="">Semua Status Pengajuan</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>⏳ Menunggu Verifikasi</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>✅ Disetujui</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>❌ Ditolak</option>
            </select>
        </div>
        <div style="flex: 1; min-width: 240px;">
            <input type="text" name="jenis" class="form-control" placeholder="Cari jenis surat..." value="{{ request('jenis') }}">
        </div>
        <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-filter"></i> Filter</button>
        @if(request()->hasAny(['status', 'jenis']))
            <a href="{{ route('surat.index') }}" class="btn btn-secondary" style="color: var(--text-muted);"><i class="fa-solid fa-rotate-left"></i> Reset</a>
        @endif
    </form>
</div>

<!-- Table Daftar Surat -->
<div class="card" style="padding: 0; overflow: hidden;">
    <div style="padding: 18px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 15px; font-weight: 700; color: #fff;">
            <i class="fa-solid fa-list-check" style="color: #a78bfa; margin-right: 8px;"></i> Daftar Berkas Pengajuan Surat
        </h3>
        <span style="font-size: 12px; color: var(--text-muted);">Menampilkan {{ $suratRequests->count() }} data</span>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
            <thead>
                <tr style="background: rgba(15, 23, 42, 0.6); color: var(--text-muted); border-bottom: 1px solid var(--border-color);">
                    <th style="padding: 12px 18px;">Tanggal & Pemohon</th>
                    <th style="padding: 12px 18px;">Jenis Surat</th>
                    <th style="padding: 12px 18px;">Keperluan</th>
                    <th style="padding: 12px 18px;">Nomor Surat</th>
                    <th style="padding: 12px 18px;">Status</th>
                    <th style="padding: 12px 18px; text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suratRequests as $surat)
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td style="padding: 14px 18px;">
                        <div style="font-weight: 600; color: #fff;">{{ $surat->user->name }}</div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                            {{ $surat->user->address }} | {{ $surat->created_at->format('d M Y, H:i') }}
                        </div>
                    </td>
                    <td style="padding: 14px 18px;">
                        <span style="font-weight: 600; color: #e2e8f0;">{{ $surat->jenis_surat }}</span>
                    </td>
                    <td style="padding: 14px 18px; max-width: 280px;">
                        <div style="color: #cbd5e1;">{{ Str::limit($surat->keperluan, 90) }}</div>
                        @if($surat->catatan_pengurus)
                            <div style="font-size: 11px; color: #94a3b8; margin-top: 4px; background: rgba(15, 23, 42, 0.5); padding: 4px 8px; border-radius: 6px;">
                                <i class="fa-solid fa-comment-dots" style="color: #38bdf8;"></i> {{ $surat->catatan_pengurus }}
                            </div>
                        @endif
                    </td>
                    <td style="padding: 14px 18px;">
                        @if($surat->nomor_surat)
                            <code style="background: rgba(139, 92, 246, 0.15); color: #c4b5fd; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 600;">
                                {{ $surat->nomor_surat }}
                            </code>
                        @else
                            <span style="color: var(--text-muted); font-size: 12px;">-</span>
                        @endif
                    </td>
                    <td style="padding: 14px 18px;">
                        @if($surat->status === 'approved')
                            <span style="background: rgba(16, 185, 129, 0.2); color: #34d399; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700;">
                                <i class="fa-solid fa-check"></i> Disetujui
                            </span>
                        @elseif($surat->status === 'rejected')
                            <span style="background: rgba(239, 68, 68, 0.2); color: #f87171; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700;">
                                <i class="fa-solid fa-xmark"></i> Ditolak
                            </span>
                        @else
                            <span style="background: rgba(245, 158, 11, 0.2); color: #fbbf24; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700;">
                                <i class="fa-solid fa-clock"></i> Menunggu
                            </span>
                        @endif
                    </td>
                    <td style="padding: 14px 18px; text-align: center;">
                        <div style="display: flex; gap: 8px; justify-content: center; align-items: center;">
                            @if($surat->status === 'approved')
                                <a href="{{ route('surat.print', $surat->id) }}" target="_blank" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px; background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3);">
                                    <i class="fa-solid fa-print"></i> Cetak
                                </a>
                            @endif

                            @if($isPengurus && $surat->status === 'pending')
                                <button type="button" class="btn btn-success" style="padding: 6px 12px; font-size: 12px;" onclick="openApproveModal({{ $surat->id }}, '{{ addslashes($surat->user->name) }}', '{{ addslashes($surat->jenis_surat) }}')">
                                    <i class="fa-solid fa-check"></i> Setujui
                                </button>
                                <button type="button" class="btn btn-danger" style="padding: 6px 12px; font-size: 12px;" onclick="openRejectModal({{ $surat->id }})">
                                    <i class="fa-solid fa-ban"></i> Tolak
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding: 30px; text-align: center; color: var(--text-muted);">
                        <i class="fa-regular fa-folder-open" style="font-size: 32px; margin-bottom: 10px; display: block;"></i>
                        Belum ada permohonan surat pengantar yang diajukan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($suratRequests->hasPages())
    <div style="padding: 16px 24px; border-top: 1px solid var(--border-color);">
        {{ $suratRequests->links() }}
    </div>
    @endif
</div>

<!-- Modal Ajukan Surat Baru -->
<div id="modalAjukanSurat" style="display: none; position: fixed; inset: 0; z-index: 999; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="width: 100%; max-width: 520px; background: #1e293b; border: 1px solid var(--border-color); padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 17px; font-weight: 700; color: #fff;">
                <i class="fa-solid fa-file-signature" style="color: #a78bfa; margin-right: 8px;"></i> Formulir Permohonan Surat Pengantar RT
            </h3>
            <button type="button" onclick="closeModal('modalAjukanSurat')" style="background: none; border: none; color: var(--text-muted); font-size: 18px; cursor: pointer;">&times;</button>
        </div>

        <form action="{{ route('surat.store') }}" method="POST">
            @csrf

            @if($isPengurus && $wargaList->count() > 0)
            <div class="form-group">
                <label>Pilih Warga Pemohon (Pengurus Mode)</label>
                <select name="user_id" class="form-control" required>
                    <option value="{{ auth()->id() }}">Diri Sendiri ({{ auth()->user()->name }})</option>
                    @foreach($wargaList as $w)
                        <option value="{{ $w->id }}">{{ $w->name }} - {{ $w->address }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="form-group">
                <label>Jenis Surat Pengantar</label>
                <select name="jenis_surat" class="form-control" required>
                    <option value="Surat Pengantar Pembuatan KTP / KK">Surat Pengantar Pembuatan KTP / KK</option>
                    <option value="Surat Keterangan Domisili">Surat Keterangan Domisili Tinggal</option>
                    <option value="Surat Keterangan Usaha (SKU)">Surat Keterangan Usaha (SKU)</option>
                    <option value="Surat Pengantar SKCK Kepolisian">Surat Pengantar SKCK Kepolisian</option>
                    <option value="Surat Keterangan Belum Menikah">Surat Keterangan Belum Menikah</option>
                    <option value="Surat Keterangan Kematian">Surat Keterangan Kematian</option>
                    <option value="Surat Keterangan Tidak Mampu (SKTM)">Surat Keterangan Tidak Mampu (SKTM)</option>
                    <option value="Surat Pengantar Izin Keramaian / Acara">Surat Pengantar Izin Keramaian / Acara</option>
                    <option value="Surat Pengantar Lainnya">Surat Pengantar Lainnya</option>
                </select>
            </div>

            <div class="form-group">
                <label>Keperluan / Tujuan Surat</label>
                <textarea name="keperluan" rows="3" class="form-control" placeholder="Contoh: Pengurusan perpanjangan KTP elektronik dan pembaharuan kartu keluarga di Kantor Kelurahan" required></textarea>
            </div>

            <div class="form-group">
                <label>Keterangan Tambahan / Berkas Persyaratan (Opsional)</label>
                <textarea name="keterangan" rows="2" class="form-control" placeholder="Contoh: Dokumen fotokopi KK dan surat nikah sudah siap difotokopi"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalAjukanSurat')">Batal</button>
                <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9);">Kirim Permohonan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Approval Surat (Pengurus Only) -->
<div id="modalApproveSurat" style="display: none; position: fixed; inset: 0; z-index: 999; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="width: 100%; max-width: 480px; background: #1e293b; border: 1px solid var(--border-color); padding: 24px;">
        <h3 style="font-size: 17px; font-weight: 700; color: #fff; margin-bottom: 8px;">
            <i class="fa-solid fa-stamp" style="color: #34d399; margin-right: 8px;"></i> Persetujuan Surat Pengantar
        </h3>
        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 18px;" id="approveDetailText">Setujui permohonan surat pengantar ini.</p>

        <form id="formApproveSurat" method="POST">
            @csrf
            <div class="form-group">
                <label>Nomor Surat Resmi (Kosongkan untuk nomor otomatis)</label>
                <input type="text" name="nomor_surat" class="form-control" placeholder="Otomatis: 001/SK-RT05/XI/2026">
            </div>

            <div class="form-group">
                <label>Catatan Pengurus / Pesan untuk Pemohon</label>
                <textarea name="catatan_pengurus" rows="2" class="form-control" placeholder="Surat pengantar telah disetujui. Silakan cetak dokumen resmi ini."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalApproveSurat')">Batal</button>
                <button type="submit" class="btn btn-success"><i class="fa-solid fa-check"></i> Konfirmasi Setuju</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Reject Surat -->
<div id="modalRejectSurat" style="display: none; position: fixed; inset: 0; z-index: 999; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="width: 100%; max-width: 460px; background: #1e293b; border: 1px solid var(--border-color); padding: 24px;">
        <h3 style="font-size: 17px; font-weight: 700; color: #fff; margin-bottom: 8px;">
            <i class="fa-solid fa-ban" style="color: #f87171; margin-right: 8px;"></i> Tolak Permohonan Surat
        </h3>
        <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 18px;">Berikan alasan penolakan agar warga dapat melengkapi berkas yang dibutuhkan.</p>

        <form id="formRejectSurat" method="POST">
            @csrf
            <div class="form-group">
                <label>Alasan Penolakan</label>
                <textarea name="catatan_pengurus" rows="3" class="form-control" placeholder="Contoh: Mohon lengkapi fotokopi kartu keluarga terbaru terlebih dahulu sebelum pengajuan." required></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalRejectSurat')">Batal</button>
                <button type="submit" class="btn btn-danger"><i class="fa-solid fa-xmark"></i> Tolak Permohonan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).style.display = 'flex';
    }
    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    function openApproveModal(id, nama, jenis) {
        document.getElementById('formApproveSurat').action = '/surat/setujui/' + id;
        document.getElementById('approveDetailText').innerText = 'Pemohon: ' + nama + ' (' + jenis + ')';
        openModal('modalApproveSurat');
    }

    function openRejectModal(id) {
        document.getElementById('formRejectSurat').action = '/surat/tolak/' + id;
        openModal('modalRejectSurat');
    }
</script>
@endsection
