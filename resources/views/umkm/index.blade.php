@extends('layouts.app')

@section('page_title', 'Etalase UMKM Warga RT')

@section('content')
<div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
    <div>
        <h2 style="font-size: 20px; font-weight: 700; color: #fff;">Etalase Bisnis & UMKM Warga Lingkungan RT</h2>
        <p style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">Pemberdayaan ekonomi tetangga: promosikan usaha rumahan Anda, belanja kebutuhan harian, dan dukung usaha warga sekitar.</p>
    </div>
    <div>
        <button type="button" class="btn btn-primary" onclick="openModal('modalTambahProduk')" style="background: linear-gradient(135deg, #f59e0b, #d97706); display: inline-flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-plus"></i> Pasang Produk / Usaha Baru
        </button>
    </div>
</div>

<!-- Metrics Cards -->
<div class="grid grid-3" style="margin-bottom: 28px;">
    <div class="card metric-card">
        <div class="metric-icon" style="background: rgba(245, 158, 11, 0.2); color: #fbbf24;">
            <i class="fa-solid fa-store"></i>
        </div>
        <div class="metric-value" style="color: #fbbf24;">{{ $totalProduk }}</div>
        <div class="metric-sub">Total Produk & Layanan Aktif</div>
    </div>
    <div class="card metric-card">
        <div class="metric-icon" style="background: rgba(16, 185, 129, 0.2); color: #34d399;">
            <i class="fa-solid fa-shop"></i>
        </div>
        <div class="metric-value" style="color: #34d399;">{{ $totalUsaha }}</div>
        <div class="metric-sub">Unit Usaha Warga Terdaftar</div>
    </div>
    <div class="card metric-card">
        <div class="metric-icon" style="background: rgba(56, 189, 248, 0.2); color: #38bdf8;">
            <i class="fa-solid fa-boxes-stacked"></i>
        </div>
        <div class="metric-value" style="color: #38bdf8;">{{ $myProdukCount }}</div>
        <div class="metric-sub">Produk Milik Toko Saya</div>
    </div>
</div>

<!-- Search & Category Filters -->
<div class="card" style="padding: 18px; margin-bottom: 26px;">
    <form action="{{ route('umkm.index') }}" method="GET" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: center;">
        <div style="flex: 2; min-width: 250px;">
            <input type="text" name="search" class="form-control" placeholder="Cari nama makanan, jasa, atau nama toko warga..." value="{{ request('search') }}">
        </div>
        <div style="flex: 1; min-width: 200px;">
            <select name="kategori" class="form-control" onchange="this.form.submit()">
                @foreach($kategoriList as $kat)
                    <option value="{{ $kat }}" {{ (request('kategori') === $kat || (!request('kategori') && $kat === 'Semua')) ? 'selected' : '' }}>
                        {{ $kat }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-magnifying-glass"></i> Cari</button>
        @if(request()->hasAny(['search', 'kategori']))
            <a href="{{ route('umkm.index') }}" class="btn btn-secondary" style="color: var(--text-muted);"><i class="fa-solid fa-rotate-left"></i> Reset</a>
        @endif
    </form>
</div>

<!-- Tabs: Semua Produk vs Produk Saya -->
<div style="display: flex; gap: 12px; margin-bottom: 22px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
    <button type="button" id="tabBtnSemua" class="btn" style="background: rgba(245, 158, 11, 0.2); color: #fbbf24; font-weight: 700;" onclick="switchTab('semua')">
        <i class="fa-solid fa-grid-2"></i> Semua Produk Warga ({{ $products->total() }})
    </button>
    <button type="button" id="tabBtnSaya" class="btn btn-secondary" style="color: var(--text-muted);" onclick="switchTab('saya')">
        <i class="fa-solid fa-user-tag"></i> Produk & Usaha Saya ({{ $myProducts->count() }})
    </button>
</div>

<!-- Grid Produk Semua Warga -->
<div id="tabContentSemua">
    <div class="grid grid-3" style="gap: 20px;">
        @forelse($products as $prod)
        <div class="card" style="padding: 20px; display: flex; flex-direction: column; justify-content: space-between; border: 1px solid rgba(255,255,255,0.08); transition: transform 0.2s;">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                    <span style="font-size: 11px; font-weight: 700; background: rgba(245, 158, 11, 0.15); color: #fbbf24; padding: 3px 8px; border-radius: 6px;">
                        <i class="fa-solid fa-tag mr-1"></i> {{ $prod->kategori }}
                    </span>
                    <span style="font-size: 11px; color: var(--text-muted);">
                        <i class="fa-solid fa-location-dot"></i> {{ $prod->alamat_usaha ?? $prod->user->address }}
                    </span>
                </div>

                <h3 style="font-size: 16px; font-weight: 700; color: #fff; margin-bottom: 4px;">{{ $prod->nama_produk }}</h3>
                <div style="font-size: 12px; font-weight: 600; color: #38bdf8; margin-bottom: 10px;">
                    <i class="fa-solid fa-store"></i> {{ $prod->nama_usaha }} ({{ $prod->user->name }})
                </div>

                <p style="font-size: 13px; color: #94a3b8; line-height: 1.5; margin-bottom: 16px;">
                    {{ Str::limit($prod->deskripsi, 120) }}
                </p>
            </div>

            <div>
                <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 16px; padding-top: 12px; border-top: 1px solid var(--border-color);">
                    <div style="font-size: 11px; color: var(--text-muted);">Harga / Tarif</div>
                    <div style="font-size: 18px; font-weight: 800; color: #34d399;">
                        {{ $prod->formatted_harga }} <span style="font-size: 11px; font-weight: 500; color: var(--text-muted);">/ {{ $prod->satuan }}</span>
                    </div>
                </div>

                <div style="display: flex; gap: 8px;">
                    <a href="{{ $prod->whatsapp_link }}" target="_blank" class="btn" style="flex: 1; text-align: center; background: #16a34a; color: #fff; font-size: 13px; font-weight: 600; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 6px;">
                        <i class="fa-brands fa-whatsapp" style="font-size: 16px;"></i> Hubungi Penjual
                    </a>

                    @if($prod->user_id === auth()->id() || in_array(auth()->user()->role, ['superadmin', 'rt']))
                        <button type="button" class="btn btn-secondary" style="padding: 8px 12px;" onclick='openEditModal(@json($prod))' title="Edit">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="card" style="grid-column: 1 / -1; padding: 40px; text-align: center; color: var(--text-muted);">
            <i class="fa-solid fa-shop-slash" style="font-size: 40px; margin-bottom: 12px; display: block; color: #64748b;"></i>
            Belum ada produk atau jasa warga yang terdaftar pada kategori ini.
        </div>
        @endforelse
    </div>

    @if($products->hasPages())
    <div style="margin-top: 24px;">
        {{ $products->links() }}
    </div>
    @endif
</div>

<!-- Tab Konten: Produk Saya -->
<div id="tabContentSaya" style="display: none;">
    <div class="card" style="padding: 0; overflow: hidden;">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 15px; font-weight: 700; color: #fff;">Daftar Produk & Layanan yang Anda Pasang</h3>
            <button type="button" class="btn btn-primary" onclick="openModal('modalTambahProduk')" style="font-size: 12px; padding: 6px 14px;">
                <i class="fa-solid fa-plus"></i> Tambah Produk Baru
            </button>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
                <thead>
                    <tr style="background: rgba(15, 23, 42, 0.6); color: var(--text-muted); border-bottom: 1px solid var(--border-color);">
                        <th style="padding: 12px 16px;">Nama Usaha & Produk</th>
                        <th style="padding: 12px 16px;">Kategori</th>
                        <th style="padding: 12px 16px;">Harga</th>
                        <th style="padding: 12px 16px;">WhatsApp</th>
                        <th style="padding: 12px 16px;">Status</th>
                        <th style="padding: 12px 16px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($myProducts as $my)
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 12px 16px;">
                            <div style="font-weight: 700; color: #fff;">{{ $my->nama_produk }}</div>
                            <div style="font-size: 11px; color: #38bdf8;">{{ $my->nama_usaha }}</div>
                        </td>
                        <td style="padding: 12px 16px;">
                            <span style="font-size: 11px; background: rgba(245, 158, 11, 0.15); color: #fbbf24; padding: 3px 8px; border-radius: 4px;">{{ $my->kategori }}</span>
                        </td>
                        <td style="padding: 12px 16px; font-weight: 700; color: #34d399;">
                            {{ $my->formatted_harga }} / {{ $my->satuan }}
                        </td>
                        <td style="padding: 12px 16px; color: #cbd5e1;">{{ $my->no_whatsapp }}</td>
                        <td style="padding: 12px 16px;">
                            <span style="padding: 2px 8px; border-radius: 12px; font-size: 11px; font-weight: 700; {{ $my->status === 'active' ? 'background:rgba(16,185,129,0.2); color:#34d399;' : 'background:rgba(239,68,68,0.2); color:#f87171;' }}">
                                {{ $my->status === 'active' ? 'Aktif' : 'Non-Aktif' }}
                            </span>
                        </td>
                        <td style="padding: 12px 16px; text-align: center;">
                            <div style="display: flex; gap: 8px; justify-content: center;">
                                <button type="button" class="btn btn-secondary" style="padding: 4px 10px; font-size: 12px;" onclick='openEditModal(@json($my))'>
                                    <i class="fa-solid fa-pen"></i> Edit
                                </button>
                                <form action="{{ route('umkm.destroy', $my->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" style="padding: 4px 10px; font-size: 12px;">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="padding: 24px; text-align: center; color: var(--text-muted);">
                            Anda belum mendaftarkan produk atau usaha rumahan. Yuk daftarkan sekarang!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Produk UMKM -->
<div id="modalTambahProduk" style="display: none; position: fixed; inset: 0; z-index: 999; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="width: 100%; max-width: 550px; background: #1e293b; border: 1px solid var(--border-color); padding: 24px; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 17px; font-weight: 700; color: #fff;">
                <i class="fa-solid fa-shop" style="color: #fbbf24; margin-right: 8px;"></i> Pasang Produk / Usaha Warga Baru
            </h3>
            <button type="button" onclick="closeModal('modalTambahProduk')" style="background: none; border: none; color: var(--text-muted); font-size: 18px; cursor: pointer;">&times;</button>
        </div>

        <form action="{{ route('umkm.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Nama Usaha / Toko Rumahan</label>
                <input type="text" name="nama_usaha" class="form-control" placeholder="Contoh: Dapur Berkah Bu Siti" required>
            </div>

            <div class="form-group">
                <label>Nama Produk / Layanan Jasa</label>
                <input type="text" name="nama_produk" class="form-control" placeholder="Contoh: Nasi Uduk Betawi Komplit" required>
            </div>

            <div class="grid grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="kategori" class="form-control" required>
                        <option value="Makanan & Minuman">Makanan & Minuman</option>
                        <option value="Jasa & Servis">Jasa & Servis</option>
                        <option value="Sembako">Sembako</option>
                        <option value="Fashion & Busana">Fashion & Busana</option>
                        <option value="Kerajinan Tangan">Kerajinan Tangan</option>
                        <option value="Elektronik & Gadget">Elektronik & Gadget</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Satuan Produk</label>
                    <input type="text" name="satuan" class="form-control" placeholder="porsi / pcs / paket / kg / unit" required>
                </div>
            </div>

            <div class="grid grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label>Harga (Rp)</label>
                    <input type="number" name="harga" class="form-control" placeholder="15000" min="0" required>
                </div>
                <div class="form-group">
                    <label>Nomor WhatsApp Pembelian</label>
                    <input type="text" name="no_whatsapp" class="form-control" placeholder="081234567890" value="{{ auth()->user()->phone ?? '08' }}" required>
                </div>
            </div>

            <div class="form-group">
                <label>Alamat / Lokasi Rumah Pengambilan</label>
                <input type="text" name="alamat_usaha" class="form-control" placeholder="Contoh: Blok B No. 04" value="{{ auth()->user()->address }}">
            </div>

            <div class="form-group">
                <label>Deskripsi Produk / Rincian Menu</label>
                <textarea name="deskripsi" rows="3" class="form-control" placeholder="Jelaskan keunggulan produk, varian rasa, atau jam buka pemesanan..." required></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahProduk')">Batal</button>
                <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #f59e0b, #d97706);">Publikasikan Produk</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Produk UMKM -->
<div id="modalEditProduk" style="display: none; position: fixed; inset: 0; z-index: 999; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="width: 100%; max-width: 550px; background: #1e293b; border: 1px solid var(--border-color); padding: 24px; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 17px; font-weight: 700; color: #fff;">
                <i class="fa-solid fa-pen-to-square" style="color: #38bdf8; margin-right: 8px;"></i> Edit Produk UMKM
            </h3>
            <button type="button" onclick="closeModal('modalEditProduk')" style="background: none; border: none; color: var(--text-muted); font-size: 18px; cursor: pointer;">&times;</button>
        </div>

        <form id="formEditProduk" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label>Nama Usaha / Toko Rumahan</label>
                <input type="text" id="edit_nama_usaha" name="nama_usaha" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Nama Produk</label>
                <input type="text" id="edit_nama_produk" name="nama_produk" class="form-control" required>
            </div>

            <div class="grid grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label>Kategori</label>
                    <select id="edit_kategori" name="kategori" class="form-control" required>
                        <option value="Makanan & Minuman">Makanan & Minuman</option>
                        <option value="Jasa & Servis">Jasa & Servis</option>
                        <option value="Sembako">Sembako</option>
                        <option value="Fashion & Busana">Fashion & Busana</option>
                        <option value="Kerajinan Tangan">Kerajinan Tangan</option>
                        <option value="Elektronik & Gadget">Elektronik & Gadget</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Satuan</label>
                    <input type="text" id="edit_satuan" name="satuan" class="form-control" required>
                </div>
            </div>

            <div class="grid grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label>Harga (Rp)</label>
                    <input type="number" id="edit_harga" name="harga" class="form-control" min="0" required>
                </div>
                <div class="form-group">
                    <label>WhatsApp</label>
                    <input type="text" id="edit_whatsapp" name="no_whatsapp" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label>Status Penjualan</label>
                <select id="edit_status" name="status" class="form-control" required>
                    <option value="active">Aktif (Tampil di Etalase)</option>
                    <option value="inactive">Non-Aktif (Disembunyikan)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Deskripsi</label>
                <textarea id="edit_deskripsi" name="deskripsi" rows="3" class="form-control" required></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditProduk')">Batal</button>
                <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #38bdf8, #0284c7);">Simpan Perubahan</button>
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

    function switchTab(tab) {
        if (tab === 'semua') {
            document.getElementById('tabContentSemua').style.display = 'block';
            document.getElementById('tabContentSaya').style.display = 'none';
            document.getElementById('tabBtnSemua').style.background = 'rgba(245, 158, 11, 0.2)';
            document.getElementById('tabBtnSemua').style.color = '#fbbf24';
            document.getElementById('tabBtnSaya').style.background = 'transparent';
            document.getElementById('tabBtnSaya').style.color = 'var(--text-muted)';
        } else {
            document.getElementById('tabContentSemua').style.display = 'none';
            document.getElementById('tabContentSaya').style.display = 'block';
            document.getElementById('tabBtnSaya').style.background = 'rgba(245, 158, 11, 0.2)';
            document.getElementById('tabBtnSaya').style.color = '#fbbf24';
            document.getElementById('tabBtnSemua').style.background = 'transparent';
            document.getElementById('tabBtnSemua').style.color = 'var(--text-muted)';
        }
    }

    function openEditModal(prod) {
        document.getElementById('formEditProduk').action = '/umkm/update/' + prod.id;
        document.getElementById('edit_nama_usaha').value = prod.nama_usaha;
        document.getElementById('edit_nama_produk').value = prod.nama_produk;
        document.getElementById('edit_kategori').value = prod.kategori;
        document.getElementById('edit_satuan').value = prod.satuan;
        document.getElementById('edit_harga').value = parseInt(prod.harga);
        document.getElementById('edit_whatsapp').value = prod.no_whatsapp;
        document.getElementById('edit_status').value = prod.status;
        document.getElementById('edit_deskripsi').value = prod.deskripsi;
        openModal('modalEditProduk');
    }
</script>
@endsection
