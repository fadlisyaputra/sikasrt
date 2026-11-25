@extends('layouts.app')

@section('title', 'Tagihan Iuran Warga')
@section('page_title', 'Manajemen Tagihan Iuran Warga RT')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Metric Overview Cards -->
    <div class="grid grid-4">
        <div class="card metric-card">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <span class="metric-sub">Total Nilai Tagihan</span>
                    <h3 class="metric-value">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</h3>
                    <span class="badge badge-info"><i class="fa-solid fa-calendar"></i> Periode Terpilih</span>
                </div>
                <div class="metric-icon" style="background: var(--gradient-accent);">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </div>
            </div>
        </div>

        <div class="card metric-card">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <span class="metric-sub">Tagihan Terbayar (Lunas)</span>
                    <h3 class="metric-value" style="color: #34d399;">Rp {{ number_format($totalLunas, 0, ',', '.') }}</h3>
                    <span class="badge badge-success"><i class="fa-solid fa-check"></i> Sudah Masuk Kas</span>
                </div>
                <div class="metric-icon" style="background: var(--gradient-emerald);">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
        </div>

        <div class="card metric-card">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <span class="metric-sub">Tunggakan (Belum Bayar)</span>
                    <h3 class="metric-value" style="color: #f87171;">Rp {{ number_format($totalTunggakan, 0, ',', '.') }}</h3>
                    <span class="badge badge-danger"><i class="fa-solid fa-clock"></i> Belum Lunas</span>
                </div>
                <div class="metric-icon" style="background: var(--gradient-danger);">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
        </div>

        <div class="card metric-card">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <span class="metric-sub">Persentase Kepatuhan</span>
                    <h3 class="metric-value" style="color: #38bdf8;">{{ $persenLunas }}%</h3>
                    <span class="badge badge-info"><i class="fa-solid fa-chart-pie"></i> Warga Tertib</span>
                </div>
                <div class="metric-icon" style="background: var(--gradient-sky);">
                    <i class="fa-solid fa-percent"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Aksi Tambah Tagihan -->
    <div class="card" style="padding: 18px 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <form action="{{ route('tagihan.index') }}" method="GET" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin: 0;">
                <div style="display: flex; align-items: center; gap: 6px;">
                    <label style="font-size: 12px; color: var(--text-muted); font-weight: 600;">Bulan:</label>
                    <select name="month" class="form-control" style="width: auto; padding: 6px 12px; font-size: 13px;">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>

                <div style="display: flex; align-items: center; gap: 6px;">
                    <label style="font-size: 12px; color: var(--text-muted); font-weight: 600;">Tahun:</label>
                    <select name="year" class="form-control" style="width: auto; padding: 6px 12px; font-size: 13px;">
                        @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div style="display: flex; align-items: center; gap: 6px;">
                    <label style="font-size: 12px; color: var(--text-muted); font-weight: 600;">Status:</label>
                    <select name="status" class="form-control" style="width: auto; padding: 6px 12px; font-size: 13px;">
                        <option value="all" {{ $selectedStatus === 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="unpaid" {{ $selectedStatus === 'unpaid' ? 'selected' : '' }}>Belum Lunas</option>
                        <option value="pending" {{ $selectedStatus === 'pending' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                        <option value="paid" {{ $selectedStatus === 'paid' ? 'selected' : '' }}>Lunas</option>
                    </select>
                </div>

                @if(!auth()->user()->isWarga())
                <input type="text" name="search" class="form-control" placeholder="Cari nama warga..." value="{{ request('search') }}" style="width: 180px; padding: 6px 12px; font-size: 13px;">
                @endif

                <button type="submit" class="btn btn-secondary" style="padding: 6px 14px; font-size: 12px;">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
            </form>

            @if(in_array(auth()->user()->role, ['superadmin', 'bendahara', 'rt']))
            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="document.getElementById('modalGenerate').style.display='block'" class="btn btn-primary" style="font-size: 13px; font-weight: 700;">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Generate Tagihan Bulanan
                </button>
                <button type="button" onclick="document.getElementById('modalTambahTagihan').style.display='block'" class="btn btn-secondary" style="font-size: 13px;">
                    <i class="fa-solid fa-plus"></i> Tambah Tagihan Manual
                </button>
            </div>
            @endif
        </div>
    </div>

    <!-- Tabel Daftar Tagihan Warga -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding-bottom: 14px;">
            <h3 class="card-title">
                <i class="fa-solid fa-receipt" style="color: var(--secondary);"></i>
                {{ auth()->user()->isWarga() ? 'Daftar Tagihan Iuran Saya' : 'Daftar Tagihan Iuran Seluruh Warga RT' }}
            </h3>
            <span style="font-size: 12px; color: var(--text-muted);">
                Total: {{ $bills->total() }} Data Tagihan
            </span>
        </div>

        <div style="overflow-x: auto; margin-top: 14px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="border-bottom: 1px solid var(--border-color); color: var(--text-muted); text-align: left;">
                        <th style="padding: 10px 8px;">No. Invoice</th>
                        <th style="padding: 10px 8px;">Nama Warga</th>
                        <th style="padding: 10px 8px;">Jenis Iuran & Periode</th>
                        <th style="padding: 10px 8px;">Jatuh Tempo</th>
                        <th style="padding: 10px 8px;">Nominal (Rp)</th>
                        <th style="padding: 10px 8px;">Status Tagihan</th>
                        <th style="padding: 10px 8px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bills as $bill)
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td style="padding: 10px 8px; font-family: monospace; font-weight: 700; color: #38bdf8;">
                            {{ $bill->bill_code }}
                        </td>
                        <td style="padding: 10px 8px;">
                            <strong style="color: #fff;">{{ $bill->user->name }}</strong>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ $bill->user->address ?? 'RT 01' }}</div>
                        </td>
                        <td style="padding: 10px 8px;">
                            <span style="color: #cbd5e1; font-weight: 600;">{{ $bill->iuranType->name }}</span>
                            <div style="font-size: 11px; color: var(--secondary);">
                                {{ \Carbon\Carbon::create()->month($bill->period_month)->translatedFormat('F') }} {{ $bill->period_year }}
                            </div>
                        </td>
                        <td style="padding: 10px 8px; font-size: 12px; color: {{ $bill->isOverdue() ? '#f87171' : 'var(--text-muted)' }};">
                            {{ \Carbon\Carbon::parse($bill->due_date)->translatedFormat('d M Y') }}
                            @if($bill->isOverdue())
                                <span class="badge badge-danger" style="font-size: 9px;">Terlambat</span>
                            @endif
                        </td>
                        <td style="padding: 10px 8px; font-weight: 800; color: #fff; font-size: 14px;">
                            Rp {{ number_format($bill->amount, 0, ',', '.') }}
                        </td>
                        <td style="padding: 10px 8px;">
                            @if($bill->status === 'paid')
                                <span class="badge badge-success"><i class="fa-solid fa-circle-check"></i> Lunas</span>
                                @if($bill->payment_method)
                                    <div style="font-size: 10px; color: var(--text-muted); text-transform: uppercase;">Via {{ $bill->payment_method }}</div>
                                @endif
                            @elseif($bill->status === 'pending')
                                <span class="badge badge-pending"><i class="fa-solid fa-clock"></i> Verifikasi</span>
                            @else
                                <span class="badge badge-danger"><i class="fa-solid fa-circle-xmark"></i> Belum Bayar</span>
                            @endif
                        </td>
                        <td style="padding: 10px 8px; text-align: center;">
                            <div style="display: flex; justify-content: center; gap: 6px;">
                                <!-- Tombol Cetak / Lihat Kuitansi Invoice -->
                                <a href="{{ route('tagihan.invoice', $bill->id) }}" target="_blank" class="btn btn-secondary" style="padding: 4px 10px; font-size: 11px;" title="Cetak Kuitansi / Invoice">
                                    <i class="fa-solid fa-print"></i> Kuitansi
                                </a>

                                <!-- Tombol Bayar untuk Warga -->
                                @if(auth()->user()->isWarga() && $bill->status === 'unpaid')
                                <button type="button" onclick="openBayarModal({{ $bill->id }}, '{{ $bill->bill_code }}', '{{ $bill->iuranType->name }}', {{ $bill->amount }})" class="btn btn-success" style="padding: 4px 10px; font-size: 11px;">
                                    <i class="fa-solid fa-wallet"></i> Bayar
                                </button>
                                @endif

                                <!-- Tombol Tandai Lunas untuk Bendahara / RT -->
                                @if(in_array(auth()->user()->role, ['superadmin', 'bendahara', 'rt']) && $bill->status !== 'paid')
                                <button type="button" onclick="openMarkPaidModal({{ $bill->id }}, '{{ $bill->bill_code }}', '{{ $bill->user->name }}', {{ $bill->amount }})" class="btn btn-primary" style="padding: 4px 10px; font-size: 11px;" title="Konfirmasi Pembayaran">
                                    <i class="fa-solid fa-check-double"></i> Tandai Lunas
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 30px; color: var(--text-muted);">
                            <i class="fa-solid fa-file-invoice" style="font-size: 36px; margin-bottom: 8px; opacity: 0.3;"></i>
                            <p>Tidak ada data tagihan ditemukan untuk kriteria filter ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 20px;">
            {{ $bills->links() }}
        </div>
    </div>

</div>

<!-- Modal Generate Tagihan Massal Bulanan (Bendahara / RT) -->
<div id="modalGenerate" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); z-index: 999; padding: 20px; overflow-y: auto;">
    <div style="max-width: 480px; margin: 60px auto; background: var(--bg-card); border-radius: 16px; border: 1px solid var(--border-color); padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 18px; font-weight: 800; color: #fff;">Generate Tagihan Bulanan</h3>
            <button type="button" onclick="document.getElementById('modalGenerate').style.display='none'" style="background: none; border: none; color: #fff; font-size: 20px; cursor: pointer;">&times;</button>
        </div>
        <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 16px;">
            Sistem akan secara otomatis menerbitkan tagihan iuran resmi kepada seluruh warga terdaftar yang belum memiliki tagihan pada periode ini.
        </p>

        <form action="{{ route('tagihan.generate') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Pilih Jenis Iuran</label>
                <select name="iuran_type_id" class="form-control" required>
                    @foreach($iuranTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->name }} - Rp {{ number_format($type->amount, 0, ',', '.') }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-2" style="gap: 12px;">
                <div class="form-group">
                    <label>Bulan Periode</label>
                    <select name="period_month" class="form-control" required>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>
                                {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="form-group">
                    <label>Tahun</label>
                    <input type="number" name="period_year" class="form-control" value="{{ date('Y') }}" required>
                </div>
            </div>
            <div class="form-group">
                <label>Batas Akhir / Tanggal Jatuh Tempo</label>
                <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-t') }}" required>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px;">
                <button type="button" onclick="document.getElementById('modalGenerate').style.display='none'" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-wand-magic-sparkles"></i> Terbitkan Sekarang</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tandai Lunas (Bendahara / RT) -->
<div id="modalMarkPaid" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); z-index: 999; padding: 20px;">
    <div style="max-width: 440px; margin: 80px auto; background: var(--bg-card); border-radius: 16px; border: 1px solid var(--border-color); padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 16px; font-weight: 800; color: #fff;">Konfirmasi Pelunasan Tagihan</h3>
            <button type="button" onclick="document.getElementById('modalMarkPaid').style.display='none'" style="background: none; border: none; color: #fff; font-size: 20px; cursor: pointer;">&times;</button>
        </div>

        <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; border-radius: 10px; padding: 12px; margin-bottom: 16px;">
            <div id="mpInvoiceCode" style="font-family: monospace; font-size: 13px; font-weight: 700; color: #38bdf8;"></div>
            <div id="mpWargaName" style="font-size: 14px; font-weight: 800; color: #fff; margin-top: 2px;"></div>
            <div id="mpAmount" style="font-size: 16px; font-weight: 800; color: #34d399; margin-top: 4px;"></div>
        </div>

        <form id="formMarkPaid" action="" method="POST">
            @csrf
            <div class="form-group">
                <label>Metode Pembayaran</label>
                <select name="payment_method" class="form-control" required>
                    <option value="cash">Tunai / Diterima Langsung</option>
                    <option value="transfer">Transfer Bank / QRIS</option>
                    <option value="iot_rfid">Tap e-KTP / Kartu RFID</option>
                </select>
            </div>
            <div class="form-group">
                <label>Catatan Bukti (Opsional)</label>
                <input type="text" name="notes" class="form-control" placeholder="Contoh: Diterima langsung oleh Bendahara">
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px;">
                <button type="button" onclick="document.getElementById('modalMarkPaid').style.display='none'" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-success"><i class="fa-solid fa-check"></i> Simpan & Tandai Lunas</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Bayar Tagihan (Warga) -->
<div id="modalBayarTagihan" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); z-index: 999; padding: 20px; overflow-y: auto;">
    <div style="max-width: 480px; margin: 60px auto; background: var(--bg-card); border-radius: 16px; border: 1px solid var(--border-color); padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 18px; font-weight: 800; color: #fff;">Pembayaran Tagihan Iuran</h3>
            <button type="button" onclick="document.getElementById('modalBayarTagihan').style.display='none'" style="background: none; border: none; color: #fff; font-size: 20px; cursor: pointer;">&times;</button>
        </div>

        <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-color); border-radius: 10px; padding: 12px; margin-bottom: 16px;">
            <div id="byInvoiceCode" style="font-family: monospace; font-size: 13px; font-weight: 700; color: #38bdf8;"></div>
            <div id="byIuranName" style="font-size: 14px; font-weight: 800; color: #fff; margin-top: 2px;"></div>
            <div id="byAmount" style="font-size: 18px; font-weight: 800; color: #34d399; margin-top: 4px;"></div>
        </div>

        <form id="formBayarTagihan" action="" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label>Pilih Cara Pembayaran</label>
                <select name="payment_method" class="form-control" required>
                    <option value="transfer">Transfer Bank / QRIS RT</option>
                    <option value="cash">Titip Tunai ke Pengurus</option>
                </select>
            </div>
            <div class="form-group">
                <label>Unggah Foto / Scan Bukti Transfer (Opsional)</label>
                <input type="file" name="proof_image" class="form-control" accept="image/*">
            </div>
            <div class="form-group">
                <label>Catatan Tambahan (Opsional)</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Nama bank pengirim, tanggal transfer..."></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px;">
                <button type="button" onclick="document.getElementById('modalBayarTagihan').style.display='none'" class="btn btn-secondary">Batal</button>
                <button type="submit" class="btn btn-success"><i class="fa-solid fa-cloud-arrow-up"></i> Kirim Pembayaran</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openMarkPaidModal(billId, code, wargaName, amount) {
        document.getElementById('mpInvoiceCode').innerText = 'No: ' + code;
        document.getElementById('mpWargaName').innerText = wargaName;
        document.getElementById('mpAmount').innerText = 'Rp ' + Number(amount).toLocaleString('id-ID');
        document.getElementById('formMarkPaid').action = '/tagihan/lunas/' + billId;
        document.getElementById('modalMarkPaid').style.display = 'block';
    }

    function openBayarModal(billId, code, iuranName, amount) {
        document.getElementById('byInvoiceCode').innerText = 'No Tagihan: ' + code;
        document.getElementById('byIuranName').innerText = iuranName;
        document.getElementById('byAmount').innerText = 'Total: Rp ' + Number(amount).toLocaleString('id-ID');
        document.getElementById('formBayarTagihan').action = '/tagihan/bayar/' + billId;
        document.getElementById('modalBayarTagihan').style.display = 'block';
    }
</script>
@endsection
