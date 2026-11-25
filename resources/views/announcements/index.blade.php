@extends('layouts.app')

@section('title', 'Pengumuman RT')
@section('page_title', 'Papan Pengumuman & Informasi Lingkungan')

@section('content')
<!-- Header Row -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <h2 style="font-size: 18px; font-weight: 700; color: #fff;">Pengumuman & Info RT 01</h2>
        <p style="font-size: 13px; color: var(--text-muted);">Pemberitahuan resmi kegiatan, jadwal ronda, dan update lingkungan.</p>
    </div>

    @if(in_array(auth()->user()->role, ['superadmin', 'bendahara', 'rt']))
    <button onclick="document.getElementById('modal-tambah-pengumuman').style.display='flex';" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Buat Pengumuman Baru
    </button>
    @endif
</div>

<!-- Announcements Feed -->
<div class="grid grid-2">
    @forelse($announcements as $ann)
    <div class="card" style="border-top: 4px solid var(--primary);">
        <div class="card-header" style="margin-bottom: 12px;">
            <span class="badge badge-pending">{{ $ann->category }}</span>
            <small style="color: var(--text-muted);">{{ $ann->created_at->format('d M Y H:i') }}</small>
        </div>

        <h3 style="font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 10px;">{{ $ann->title }}</h3>
        <p style="font-size: 14px; color: #cbd5e1; line-height: 1.6; white-space: pre-line;">{{ $ann->content }}</p>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 14px; border-top: 1px solid var(--border-color);">
            <div style="font-size: 12px; color: var(--text-muted);">
                <i class="fa-solid fa-user-pen"></i> Penulis: <strong>{{ $ann->author->name }}</strong>
            </div>

            @if(in_array(auth()->user()->role, ['superadmin', 'bendahara', 'rt']))
            <form action="{{ route('announcements.destroy', $ann->id) }}" method="POST" onsubmit="return confirm('Hapus pengumuman ini?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" style="padding: 4px 8px; font-size: 11px;">
                    <i class="fa-solid fa-trash"></i> Hapus
                </button>
            </form>
            @endif
        </div>
    </div>
    @empty
    <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--text-muted);">
        <i class="fa-solid fa-bullhorn" style="font-size: 36px; color: var(--border-color); margin-bottom: 12px;"></i>
        <p>Belum ada pengumuman yang dipublikasikan.</p>
    </div>
    @endforelse
</div>

<div style="margin-top: 20px;">
    {{ $announcements->links() }}
</div>

<!-- Modal Publish Announcement -->
@if(in_array(auth()->user()->role, ['superadmin', 'bendahara', 'rt']))
<div id="modal-tambah-pengumuman" style="display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.7); backdrop-filter: blur(5px); z-index: 1000; align-items:center; justify-content:center;">
    <div class="card" style="width: 100%; max-width: 550px;">
        <div class="card-header">
            <div class="card-title">Buat Pengumuman Baru</div>
            <button onclick="document.getElementById('modal-tambah-pengumuman').style.display='none';" class="btn btn-secondary" style="padding: 4px 8px;">&times;</button>
        </div>

        <form action="{{ route('announcements.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Judul Pengumuman</label>
                <input type="text" name="title" class="form-control" placeholder="Judul singkat pengumuman..." required>
            </div>

            <div class="form-group">
                <label>Kategori</label>
                <select name="category" class="form-control" required>
                    <option value="Kegiatan RT">Kegiatan RT</option>
                    <option value="Keuangan & Kas">Keuangan & Kas</option>
                    <option value="Keamanan & Ronda">Keamanan & Ronda</option>
                    <option value="Kerja Bakti">Kerja Bakti</option>
                    <option value="Informasi Umum">Informasi Umum</option>
                </select>
            </div>

            <div class="form-group">
                <label>Isi Pengumuman</label>
                <textarea name="content" class="form-control" rows="5" placeholder="Tulis rincian informasi..." required></textarea>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" onclick="document.getElementById('modal-tambah-pengumuman').style.display='none';" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary">Publikasikan</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
