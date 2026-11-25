@extends('layouts.app')

@section('page_title', 'Bank Sampah RT')

@section('content')
<div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
    <div>
        <h2 style="font-size: 20px; font-weight: 700; color: #fff;">Bank Sampah Berkah RT 01</h2>
        <p style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">Ubah sampah anorganik rumah tangga menjadi tabungan rupiah: peduli lingkungan, kurangi limbah plastik, dan raih manfaat ekonomi.</p>
    </div>
    @if($isPengurus)
    <div style="display: flex; gap: 10px;">
        <button type="button" class="btn btn-primary" onclick="openModal('modalTimbangSampah')" style="background: linear-gradient(135deg, #10b981, #059669); display: inline-flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-scale-balanced"></i> Catat Timbangan Setoran
        </button>
        <button type="button" class="btn btn-secondary" onclick="openModal('modalTarikSaldo')" style="display: inline-flex; align-items: center; gap: 8px; border: 1px solid rgba(255,255,255,0.15);">
            <i class="fa-solid fa-hand-holding-dollar"></i> Tarik Tunai Tabungan
        </button>
    </div>
    @endif
</div>

<!-- Metrics Cards -->
<div class="grid grid-4" style="margin-bottom: 28px;">
    <div class="card metric-card" style="border-left: 4px solid #10b981;">
        <div class="metric-icon" style="background: rgba(16, 185, 129, 0.2); color: #34d399;">
            <i class="fa-solid fa-piggy-bank"></i>
        </div>
        <div class="metric-value" style="color: #34d399;">Rp {{ number_format($myBalance, 0, ',', '.') }}</div>
        <div class="metric-sub">Saldo Tabungan Sampah Saya</div>
    </div>

    <div class="card metric-card" style="border-left: 4px solid #06b6d4;">
        <div class="metric-icon" style="background: rgba(6, 182, 212, 0.2); color: #22d3ee;">
            <i class="fa-solid fa-trash-can-arrow-up"></i>
        </div>
        <div class="metric-value" style="color: #22d3ee;">{{ number_format($myKg, 1, ',', '.') }} kg</div>
        <div class="metric-sub">Sampah yang Pernah Saya Setor</div>
    </div>

    <div class="card metric-card" style="border-left: 4px solid #3b82f6;">
        <div class="metric-icon" style="background: rgba(59, 130, 246, 0.2); color: #60a5fa;">
            <i class="fa-solid fa-recycle"></i>
        </div>
        <div class="metric-value" style="color: #60a5fa;">{{ number_format($totalKg, 1, ',', '.') }} kg</div>
        <div class="metric-sub">Total Sampah RT Terdaur Ulang</div>
    </div>

    <div class="card metric-card" style="border-left: 4px solid #8b5cf6;">
        <div class="metric-icon" style="background: rgba(139, 92, 246, 0.2); color: #a78bfa;">
            <i class="fa-solid fa-coins"></i>
        </div>
        <div class="metric-value" style="color: #a78bfa;">Rp {{ number_format($totalSetorRp, 0, ',', '.') }}</div>
        <div class="metric-sub">Perputaran Tabungan Warga</div>
    </div>
</div>

<!-- Daftar Harga Master Sampah -->
<div class="card" style="margin-bottom: 28px; padding: 22px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h3 style="font-size: 15px; font-weight: 700; color: #fff;">
            <i class="fa-solid fa-tags" style="color: #34d399; margin-right: 8px;"></i> Daftar Harga Pembelian Sampah Anorganik Hari Ini
        </h3>
        @if($isPengurus)
            <button type="button" class="btn btn-secondary" onclick="openModal('modalTambahKategori')" style="font-size: 12px; padding: 5px 12px;">
                <i class="fa-solid fa-plus"></i> Tambah Jenis Sampah
            </button>
        @endif
    </div>

    <div class="grid grid-3" style="gap: 14px;">
        @foreach($categories as $cat)
        <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-color); border-radius: 12px; padding: 14px 16px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div style="font-weight: 700; color: #e2e8f0; font-size: 13px;">{{ $cat->nama_kategori }}</div>
                <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">{{ Str::limit($cat->deskripsi, 40) }}</div>
            </div>
            <div style="text-align: right;">
                <div style="font-weight: 800; color: #34d399; font-size: 15px;">{{ $cat->formatted_harga }}</div>
                <code style="font-size: 10px; color: #94a3b8; background: rgba(255,255,255,0.05); padding: 1px 4px; border-radius: 4px;">{{ $cat->kode }}</code>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Riwayat Transaksi Timbangan & Tabungan -->
<div class="card" style="padding: 0; overflow: hidden;">
    <div style="padding: 18px 24px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 15px; font-weight: 700; color: #fff;">
            <i class="fa-solid fa-clock-rotate-left" style="color: #38bdf8; margin-right: 8px;"></i> Riwayat Transaksi Timbangan & Tabungan Sampah
        </h3>
        <span style="font-size: 12px; color: var(--text-muted);">Total {{ $transactions->total() }} catatan</span>
    </div>

    <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
            <thead>
                <tr style="background: rgba(15, 23, 42, 0.6); color: var(--text-muted); border-bottom: 1px solid var(--border-color);">
                    <th style="padding: 12px 18px;">Waktu & Warga</th>
                    <th style="padding: 12px 18px;">Tipe & Kategori</th>
                    <th style="padding: 12px 18px;">Bobot Timbangan</th>
                    <th style="padding: 12px 18px;">Nilai Transaksi (Rp)</th>
                    <th style="padding: 12px 18px;">Petugas Timbang</th>
                    <th style="padding: 12px 18px;">Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $tx)
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td style="padding: 14px 18px;">
                        <div style="font-weight: 600; color: #fff;">{{ $tx->user->name }}</div>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                            {{ $tx->created_at->format('d M Y, H:i') }} ({{ $tx->user->address }})
                        </div>
                    </td>
                    <td style="padding: 14px 18px;">
                        @if($tx->tipe === 'setor')
                            <span style="background: rgba(16, 185, 129, 0.2); color: #34d399; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: 700;">
                                <i class="fa-solid fa-arrow-down mr-1"></i> Setor Sampah
                            </span>
                            <div style="font-size: 12px; color: #e2e8f0; margin-top: 4px; font-weight: 600;">
                                {{ $tx->category->nama_kategori ?? '-' }}
                            </div>
                        @else
                            <span style="background: rgba(239, 68, 68, 0.2); color: #f87171; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: 700;">
                                <i class="fa-solid fa-arrow-up mr-1"></i> Tarik Tabungan
                            </span>
                        @endif
                    </td>
                    <td style="padding: 14px 18px;">
                        @if($tx->tipe === 'setor')
                            <span style="font-weight: 700; color: #fff; font-size: 14px;">{{ number_format($tx->berat_kg, 1, ',', '.') }}</span>
                            <span style="font-size: 11px; color: var(--text-muted);">{{ $tx->category->satuan ?? 'kg' }}</span>
                            <div style="font-size: 11px; color: #94a3b8;">@ Rp {{ number_format($tx->harga_satuan, 0, ',', '.') }}</div>
                        @else
                            <span style="color: var(--text-muted);">-</span>
                        @endif
                    </td>
                    <td style="padding: 14px 18px;">
                        <span style="font-size: 15px; font-weight: 800; {{ $tx->tipe === 'setor' ? 'color:#34d399;' : 'color:#f87171;' }}">
                            {{ $tx->formatted_total }}
                        </span>
                    </td>
                    <td style="padding: 14px 18px; color: #cbd5e1;">
                        <i class="fa-solid fa-user-shield" style="color: #38bdf8; margin-right: 4px;"></i>
                        {{ $tx->petugas->name ?? 'Pengurus RT' }}
                    </td>
                    <td style="padding: 14px 18px; color: #94a3b8; font-size: 12px;">
                        {{ $tx->catatan ?? '-' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="padding: 30px; text-align: center; color: var(--text-muted);">
                        <i class="fa-solid fa-recycle" style="font-size: 32px; margin-bottom: 10px; display: block; color: #64748b;"></i>
                        Belum ada riwayat transaksi bank sampah yang tercatat.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($transactions->hasPages())
    <div style="padding: 16px 24px; border-top: 1px solid var(--border-color);">
        {{ $transactions->links() }}
    </div>
    @endif
</div>

@if($isPengurus)
<!-- Modal Catat Timbangan Setoran Sampah -->
<div id="modalTimbangSampah" style="display: none; position: fixed; inset: 0; z-index: 999; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="width: 100%; max-width: 500px; background: #1e293b; border: 1px solid var(--border-color); padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 17px; font-weight: 700; color: #fff;">
                <i class="fa-solid fa-scale-balanced" style="color: #34d399; margin-right: 8px;"></i> Catat Timbangan Setoran Warga
            </h3>
            <button type="button" onclick="closeModal('modalTimbangSampah')" style="background: none; border: none; color: var(--text-muted); font-size: 18px; cursor: pointer;">&times;</button>
        </div>

        <form action="{{ route('bank_sampah.transaction.store') }}" method="POST">
            @csrf
            <input type="hidden" name="tipe" value="setor">

            <div class="form-group">
                <label>Pilih Warga Penyetor</label>
                <select name="user_id" class="form-control" required>
                    <option value="">-- Pilih Warga --</option>
                    @foreach($wargaList as $w)
                        <option value="{{ $w->id }}">{{ $w->name }} - {{ $w->address }} (Saldo: Rp {{ number_format($w->bank_sampah_balance, 0, ',', '.') }})</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Jenis Sampah Anorganik</label>
                <select name="category_id" id="timbang_category" class="form-control" onchange="hitungPerkiraanTotal()" required>
                    <option value="">-- Pilih Kategori Sampah --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" data-harga="{{ $cat->harga_per_kg }}" data-satuan="{{ $cat->satuan }}">
                            {{ $cat->nama_kategori }} ({{ $cat->formatted_harga }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label>Bobot / Berat (Kg / Liter)</label>
                    <input type="number" step="0.1" name="berat_kg" id="timbang_berat" class="form-control" placeholder="Contoh: 5.5" oninput="hitungPerkiraanTotal()" required>
                </div>
                <div class="form-group">
                    <label>Perkiraan Nilai Tabungan</label>
                    <input type="text" id="timbang_total_view" class="form-control" value="Rp 0" readonly style="background: rgba(16, 185, 129, 0.1); color: #34d399; font-weight: 700;">
                </div>
            </div>

            <div class="form-group">
                <label>Catatan Timbangan (Opsional)</label>
                <input type="text" name="catatan" class="form-control" placeholder="Contoh: 1 karung botol bersih">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTimbangSampah')">Batal</button>
                <button type="submit" class="btn btn-success"><i class="fa-solid fa-check"></i> Simpan Setoran</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tarik Saldo Tunai -->
<div id="modalTarikSaldo" style="display: none; position: fixed; inset: 0; z-index: 999; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="width: 100%; max-width: 480px; background: #1e293b; border: 1px solid var(--border-color); padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 17px; font-weight: 700; color: #fff;">
                <i class="fa-solid fa-hand-holding-dollar" style="color: #fbbf24; margin-right: 8px;"></i> Pencairan Saldo Tabungan Tunai
            </h3>
            <button type="button" onclick="closeModal('modalTarikSaldo')" style="background: none; border: none; color: var(--text-muted); font-size: 18px; cursor: pointer;">&times;</button>
        </div>

        <form action="{{ route('bank_sampah.transaction.store') }}" method="POST">
            @csrf
            <input type="hidden" name="tipe" value="tarik">

            <div class="form-group">
                <label>Pilih Warga Penarik Saldo</label>
                <select name="user_id" class="form-control" required>
                    <option value="">-- Pilih Warga --</option>
                    @foreach($wargaList as $w)
                        <option value="{{ $w->id }}">{{ $w->name }} (Tersedia: Rp {{ number_format($w->bank_sampah_balance, 0, ',', '.') }})</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Nominal Pencairan Tunai (Rp)</label>
                <input type="number" name="nominal_tarik" class="form-control" placeholder="Contoh: 50000" min="1000" required>
            </div>

            <div class="form-group">
                <label>Catatan Pembayaran Tunai</label>
                <input type="text" name="catatan" class="form-control" placeholder="Pencairan tunai diserahkan di pos RT">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTarikSaldo')">Batal</button>
                <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #f59e0b, #d97706);"><i class="fa-solid fa-money-bill-wave"></i> Bayar Tunai</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Kategori Sampah Baru -->
<div id="modalTambahKategori" style="display: none; position: fixed; inset: 0; z-index: 999; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 20px;">
    <div class="card" style="width: 100%; max-width: 480px; background: #1e293b; border: 1px solid var(--border-color); padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 17px; font-weight: 700; color: #fff;">
                <i class="fa-solid fa-plus-circle" style="color: #38bdf8; margin-right: 8px;"></i> Tambah Master Kategori Sampah
            </h3>
            <button type="button" onclick="closeModal('modalTambahKategori')" style="background: none; border: none; color: var(--text-muted); font-size: 18px; cursor: pointer;">&times;</button>
        </div>

        <form action="{{ route('bank_sampah.category.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Nama Kategori Sampah</label>
                <input type="text" name="nama_kategori" class="form-control" placeholder="Contoh: Kaca Botol Sirup" required>
            </div>

            <div class="grid grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label>Kode Unik</label>
                    <input type="text" name="kode" class="form-control" placeholder="KACA" required>
                </div>
                <div class="form-group">
                    <label>Satuan</label>
                    <input type="text" name="satuan" class="form-control" value="kg" required>
                </div>
            </div>

            <div class="form-group">
                <label>Harga Beli per Satuan (Rp)</label>
                <input type="number" name="harga_per_kg" class="form-control" placeholder="1500" min="0" required>
            </div>

            <div class="form-group">
                <label>Deskripsi / Kriteria</label>
                <input type="text" name="deskripsi" class="form-control" placeholder="Botol utuh tidak pecah">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalTambahKategori')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Kategori</button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
    function openModal(id) {
        document.getElementById(id).style.display = 'flex';
    }
    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    function hitungPerkiraanTotal() {
        const catSelect = document.getElementById('timbang_category');
        const beratInput = document.getElementById('timbang_berat');
        const totalView = document.getElementById('timbang_total_view');

        const selectedOption = catSelect.options[catSelect.selectedIndex];
        if (!selectedOption || !selectedOption.dataset.harga) {
            totalView.value = 'Rp 0';
            return;
        }

        const harga = parseFloat(selectedOption.dataset.harga) || 0;
        const berat = parseFloat(beratInput.value) || 0;
        const total = harga * berat;

        totalView.value = 'Rp ' + total.toLocaleString('id-ID');
    }
</script>
@endsection
