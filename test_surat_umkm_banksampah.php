<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\SuratRequest;
use App\Models\UmkmProduct;
use App\Models\BankSampahCategory;
use App\Models\BankSampahTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use App\Http\Controllers\SuratController;
use App\Http\Controllers\UmkmController;
use App\Http\Controllers\BankSampahController;

$totalTests = 0;
$passedTests = 0;

function assertTest($condition, $name) {
    global $totalTests, $passedTests;
    $totalTests++;
    if ($condition) {
        $passedTests++;
        echo " [PASS] " . $name . "\n";
    } else {
        echo " [FAIL] " . $name . "\n";
    }
}

echo "====================================================================\n";
echo "   SIKAS-RT: PENGUJIAN FITUR SURAT, UMKM & BANK SAMPAH\n";
echo "====================================================================\n\n";

$admin = User::where('role', 'superadmin')->first();
$rt = User::where('role', 'rt')->first() ?? $admin;
$budi = User::where('email', 'budi@kasrt.local')->first() ?? User::where('role', 'warga')->first();
$siti = User::where('email', 'siti@kasrt.local')->first() ?? User::where('role', 'warga')->skip(1)->first();

// --- 1. PENGUJIAN SURAT MENYURAT ---
echo "--- 1. PENGUJIAN FITUR SURAT MENYURAT ---\n";

Auth::login($budi);
$suratController = new SuratController();

// 1.1 View Surat Warga
$viewSurat = $suratController->index(new Request());
assertTest($viewSurat->getName() === 'surat.index', 'View Surat Warga Render OK');

// 1.2 Ajukan Surat Baru
$reqSurat = new Request([
    'jenis_surat' => 'Surat Pengantar SKCK Kepolisian',
    'keperluan' => 'Melamar pekerjaan di instansi BUMN',
    'keterangan' => 'Fotokopi KTP dan ijazah sudah siap',
]);
$suratController->store($reqSurat);

$newSurat = SuratRequest::where('user_id', $budi->id)
    ->where('jenis_surat', 'Surat Pengantar SKCK Kepolisian')
    ->latest()
    ->first();
assertTest($newSurat !== null && $newSurat->status === 'pending', 'Pengajuan Surat Warga Baru Berhasil Disimpan');

// 1.3 Persetujuan Surat oleh Ketua RT
Auth::login($rt);
$reqApprove = new Request([
    'nomor_surat' => '099/SK-RT05/XI/2026',
    'catatan_pengurus' => 'Disetujui untuk persyaratan lamaran kerja BUMN',
]);
$suratController->approve($reqApprove, $newSurat->id);
$newSurat->refresh();
assertTest($newSurat->status === 'approved' && $newSurat->nomor_surat === '099/SK-RT05/XI/2026', 'Persetujuan Surat oleh Ketua RT Berhasil (Nomor Surat Terbit)');

// 1.4 Render Cetak Surat Resmi
$printView = $suratController->print($newSurat->id);
$renderedPrint = $printView->render();
assertTest(
    str_contains($renderedPrint, 'SURAT PENGANTAR') && str_contains($renderedPrint, '099/SK-RT05/XI/2026'),
    'Dokumen Cetak Surat Pengantar RT Resmi Render OK'
);


// --- 2. PENGUJIAN UMKM WARGA ---
echo "\n--- 2. PENGUJIAN FITUR UMKM WARGA ---\n";

Auth::login($siti);
$umkmController = new UmkmController();

// 2.1 View Katalog UMKM
$viewUmkm = $umkmController->index(new Request());
assertTest($viewUmkm->getName() === 'umkm.index', 'View Katalog UMKM Render OK');

// 2.2 Tambah Produk UMKM Baru
UmkmProduct::where('nama_produk', 'like', 'Tumpeng Mini%')->delete();
$reqUmkm = new Request([
    'nama_usaha' => 'Catering Selera Rasa Siti',
    'nama_produk' => 'Tumpeng Mini Kuning Lengkap',
    'kategori' => 'Makanan & Minuman',
    'deskripsi' => 'Tumpeng mini lauk ayam goreng, telur suwir, urap sayur, dan perkedel kentang.',
    'harga' => 35000,
    'satuan' => 'porsi',
    'no_whatsapp' => '081298765432',
    'alamat_usaha' => 'Blok B No. 04',
]);
$umkmController->store($reqUmkm);

$newProduct = UmkmProduct::where('user_id', $siti->id)
    ->where('nama_produk', 'Tumpeng Mini Kuning Lengkap')
    ->first();
assertTest($newProduct !== null && (float)$newProduct->harga === 35000.0, 'Pendaftaran Produk UMKM Baru Berhasil');
assertTest(str_contains($newProduct->whatsapp_link, '6281298765432'), 'Generasi Deep Link WhatsApp UMKM Valid');

// 2.3 Update Produk UMKM
$reqUpdate = new Request([
    'nama_usaha' => 'Catering Selera Rasa Siti',
    'nama_produk' => 'Tumpeng Mini Kuning Lengkap Special',
    'kategori' => 'Makanan & Minuman',
    'deskripsi' => 'Porsi lebih besar dengan tambahan sambal goreng ati.',
    'harga' => 38000,
    'satuan' => 'porsi',
    'no_whatsapp' => '081298765432',
    'alamat_usaha' => 'Blok B No. 04',
    'status' => 'active',
]);
$umkmController->update($reqUpdate, $newProduct->id);
$newProduct->refresh();
assertTest((float)$newProduct->harga === 38000.0, 'Perubahan Data Produk UMKM Berhasil Disimpan');


// --- 3. PENGUJIAN BANK SAMPAH RT ---
echo "\n--- 3. PENGUJIAN FITUR BANK SAMPAH RT ---\n";

Auth::login($budi);
$bankController = new BankSampahController();

// 3.1 View Bank Sampah
$viewBank = $bankController->index(new Request());
assertTest($viewBank->getName() === 'bank_sampah.index', 'View Bank Sampah Warga Render OK');

// 3.2 Tambah Kategori Baru (oleh Pengurus)
Auth::login($rt);
BankSampahCategory::where('kode', 'like', 'TEMBAGA_%')->delete();
$testKode = 'TEMBAGA_' . time();
$reqCat = new Request([
    'nama_kategori' => 'Tembaga Kabel Murni',
    'kode' => $testKode,
    'harga_per_kg' => 85000,
    'satuan' => 'kg',
    'deskripsi' => 'Kabel kupas tembaga murni',
]);
$bankController->storeCategory($reqCat);
$tembagaCat = BankSampahCategory::where('kode', $testKode)->first();
assertTest($tembagaCat !== null && (float)$tembagaCat->harga_per_kg === 85000.0, 'Tambah Master Kategori Sampah Berhasil');

// 3.3 Catat Timbangan Setoran Sampah Warga
$saldoAwal = $budi->bank_sampah_balance;
$reqSetor = new Request([
    'user_id' => $budi->id,
    'tipe' => 'setor',
    'category_id' => $tembagaCat->id,
    'berat_kg' => 2.0,
    'catatan' => 'Setoran kabel tembaga renovasi rumah',
]);
$bankController->storeTransaction($reqSetor);
$budi->refresh();
$saldoSetelahSetor = $budi->bank_sampah_balance;
$expectedTambah = 2.0 * 85000;
assertTest($saldoSetelahSetor == ($saldoAwal + $expectedTambah), 'Pencatatan Setoran Sampah Sukses & Saldo Warga Bertambah (Rp ' . number_format($expectedTambah, 0, ',', '.') . ')');

// 3.4 Pencairan / Tarik Saldo Tunai
$reqTarik = new Request([
    'user_id' => $budi->id,
    'tipe' => 'tarik',
    'nominal_tarik' => 50000,
    'catatan' => 'Pencairan tunai di pos RT',
]);
$bankController->storeTransaction($reqTarik);
$budi->refresh();
$saldoSetelahTarik = $budi->bank_sampah_balance;
assertTest($saldoSetelahTarik == ($saldoSetelahSetor - 50000), 'Pencairan Saldo Tabungan Tunai Berhasil Mengurangi Saldo');

echo "\n====================================================================\n";
echo "HASIL PENGUJIAN: {$passedTests} BERHASIL, " . ($totalTests - $passedTests) . " GAGAL\n";
echo "====================================================================\n";

if ($totalTests === $passedTests) {
    exit(0);
} else {
    exit(1);
}
