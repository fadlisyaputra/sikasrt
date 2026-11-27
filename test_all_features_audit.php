<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\IuranType;
use App\Models\WargaBill;
use App\Models\IuranPayment;
use App\Models\CashTransaction;
use App\Models\IotDevice;
use App\Models\IotRfidCard;
use App\Models\IotTelemetryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

echo "====================================================================\n";
echo "   SIKAS-RT: AUDIT KOMPREHENSIF SELURUH FITUR & MODUL APLIKASI\n";
echo "====================================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertAudit($title, $condition, $info = '') {
    global $passCount, $failCount;
    if ($condition) {
        echo " [PASS] $title\n";
        $passCount++;
    } else {
        echo " [FAIL] $title" . ($info ? " -> $info" : "") . "\n";
        $failCount++;
    }
}

$admin = User::where('role', 'superadmin')->first();
$bendahara = User::where('role', 'bendahara')->first();
$ketuaRt = User::where('role', 'rt')->first();
$wargaBudi = User::where('email', 'budi@kasrt.local')->first();
$session = app('session')->driver();

// -------------------------------------------------------------
// 1. MODUL AUTENTIKASI & KODE AKSES
// -------------------------------------------------------------
echo "--- 1. AUDIT AUTENTIKASI & KODE AKSES ---\n";
$authCtrl = app(\App\Http\Controllers\AuthController::class);

// Superadmin quick login dengan kode
$reqQuickAdmin = Request::create('/quick-login/' . $admin->email . '?code=RT2026', 'GET', ['code' => 'RT2026']);
$reqQuickAdmin->setLaravelSession($session);
$resQuickAdmin = $authCtrl->quickLogin($reqQuickAdmin, $admin->email);
assertAudit("Quick Login Superadmin dengan kode RT2026", Auth::check() && Auth::id() === $admin->id);

// Warga quick login tanpa kode
$reqQuickWarga = Request::create('/quick-login/' . $wargaBudi->email, 'GET');
$reqQuickWarga->setLaravelSession($session);
$resQuickWarga = $authCtrl->quickLogin($reqQuickWarga, $wargaBudi->email);
assertAudit("Quick Login Warga langsung tanpa kode", Auth::check() && Auth::id() === $wargaBudi->id);

// -------------------------------------------------------------
// 2. MODUL DASHBOARD (UNTUK SEMUA ROLE)
// -------------------------------------------------------------
echo "\n--- 2. AUDIT DASHBOARD MULTI-ROLE ---\n";
$dashCtrl = app(\App\Http\Controllers\DashboardController::class);

foreach ([$admin, $bendahara, $ketuaRt, $wargaBudi] as $u) {
    Auth::login($u);
    $viewDash = $dashCtrl->index();
    assertAudit("Render Dashboard untuk Role [{$u->role}] ({$viewDash->name()})", $viewDash && str_starts_with($viewDash->name(), 'dashboard.'));
}

// -------------------------------------------------------------
// 3. MODUL TAGIHAN IURAN WARGA (GENERATE, STORE, PAY, LUNAS)
// -------------------------------------------------------------
echo "\n--- 3. AUDIT TAGIHAN IURAN WARGA ---\n";
Auth::login($admin);
$tagihanCtrl = app(\App\Http\Controllers\TagihanController::class);

// 3.1 Generate Massal Type 1, 2, dan 3 (Pastikan No Conflict)
foreach (IuranType::all() as $t) {
    $reqGen = Request::create('/tagihan/generate', 'POST', [
        'iuran_type_id' => $t->id,
        'period_month' => 12,
        'period_year' => 2026,
        'due_date' => '2026-12-31',
    ]);
    $reqGen->setLaravelSession($session);
    $resGen = $tagihanCtrl->generate($reqGen);
    assertAudit("Generate Massal Jenis Iuran [{$t->name}]", $resGen->getStatusCode() === 302);
}

// 3.2 Tambah Tagihan Manual untuk 1 Warga
$reqStoreBill = Request::create('/tagihan/tambah', 'POST', [
    'user_id' => $wargaBudi->id,
    'iuran_type_id' => 1,
    'period_month' => 1,
    'period_year' => 2027,
    'amount' => 50000,
    'due_date' => '2027-01-31',
    'notes' => 'Tagihan Manual Test Audit',
]);
$reqStoreBill->setLaravelSession($session);
$resStoreBill = $tagihanCtrl->store($reqStoreBill);
assertAudit("Tambah Tagihan Manual 1 Warga via store()", $resStoreBill->getStatusCode() === 302);

$testBill = WargaBill::where('user_id', $wargaBudi->id)->where('period_year', 2027)->first();
assertAudit("Tagihan tersimpan di database", $testBill !== null);

// 3.3 Cetak / Tampilkan Invoice Tagihan
$resInvoice = $tagihanCtrl->invoice($testBill->id);
assertAudit("Render Kuitansi/Invoice Tagihan", $resInvoice && $resInvoice->name() === 'tagihan.invoice');

// 3.4 Warga Bayar Tagihan (Submit Payment)
Auth::login($wargaBudi);
$reqPay = Request::create('/tagihan/bayar/' . $testBill->id, 'POST', [
    'payment_method' => 'transfer',
    'notes' => 'Sudah ditransfer via BCA',
]);
$reqPay->setLaravelSession($session);
$resPay = $tagihanCtrl->payBill($reqPay, $testBill->id);
$testBill->refresh();
assertAudit("Warga mengajukan pembayaran tagihan", $testBill->status === 'pending');

// 3.5 Bendahara Tandai Lunas Tagihan
Auth::login($bendahara);
$reqLunas = Request::create('/tagihan/lunas/' . $testBill->id, 'POST', [
    'payment_method' => 'transfer',
    'notes' => 'Dikonfirmasi mutasi rekening',
]);
$reqLunas->setLaravelSession($session);
$resLunas = $tagihanCtrl->markPaid($reqLunas, $testBill->id);
$testBill->refresh();
assertAudit("Bendahara konfirmasi pelunasan tagihan", $testBill->status === 'paid' && $testBill->paid_at !== null);

// 3.6 Hapus Tagihan Uji Coba
$resDestroyBill = $tagihanCtrl->destroy($testBill->id);
assertAudit("Hapus Tagihan berhasil", WargaBill::find($testBill->id) === null);

// -------------------------------------------------------------
// 4. MODUL KAS RT (PEMASUKAN & PENGELUARAN)
// -------------------------------------------------------------
echo "\n--- 4. AUDIT KAS RT & TRANSAKSI ---\n";
Auth::login($bendahara);
$kasCtrl = app(\App\Http\Controllers\CashTransactionController::class);

$reqKasIn = Request::create('/kas/tambah', 'POST', [
    'type' => 'pemasukan',
    'category' => 'Donasi Warga',
    'amount' => 250000,
    'transaction_date' => '2026-11-27',
    'description' => 'Donasi pembangunan pos ronda uji coba',
]);
$reqKasIn->setLaravelSession($session);
$resKasIn = $kasCtrl->store($reqKasIn);
assertAudit("Catat Transaksi Pemasukan Kas RT", $resKasIn->getStatusCode() === 302);

$createdKas = CashTransaction::where('description', 'Donasi pembangunan pos ronda uji coba')->first();
assertAudit("Data transaksi kas tersimpan di DB", $createdKas && (int)$createdKas->amount === 250000);

if ($createdKas) {
    $resKasDel = $kasCtrl->destroy($createdKas->id);
    assertAudit("Hapus Transaksi Kas RT", CashTransaction::find($createdKas->id) === null);
}

// -------------------------------------------------------------
// 5. MODUL DATA WARGA & REKAM e-KTP
// -------------------------------------------------------------
echo "\n--- 5. AUDIT DATA WARGA & REKAM e-KTP ---\n";
Auth::login($admin);
$wargaCtrl = app(\App\Http\Controllers\WargaController::class);

// Pengecekan Ketersediaan UID
$reqCheckUid = Request::create('/warga/check-uid', 'POST', ['rfid_uid' => '12:34:56:78']);
$resCheckUid = $wargaCtrl->checkUidAvailability($reqCheckUid);
$dataCheckUid = json_decode($resCheckUid->getContent(), true);
assertAudit("API Validasi Duplikasi UID Warga", ($dataCheckUid['available'] ?? false) === true);

// Direct Pairing e-KTP ke Warga Budi
$newUidBudi = 'AA:BB:CC:DD';
$reqPairBudi = Request::create("/warga/{$wargaBudi->id}/pair-ktp", 'POST', ['rfid_uid' => 'aabbccdd'], [], [], ['HTTP_ACCEPT' => 'application/json', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);
$reqPairBudi->setLaravelSession($session);
$resPairBudi = $wargaCtrl->pairKtpDirect($reqPairBudi, $wargaBudi->id);
$wargaBudi->refresh();
assertAudit("Direct Pair e-KTP ke Budi", $wargaBudi->rfid_uid === $newUidBudi);

// Direct Unpair
$resUnpairBudi = $wargaCtrl->unpairKtpDirect($wargaBudi->id);
$wargaBudi->refresh();
assertAudit("Direct Unpair e-KTP Budi", $wargaBudi->rfid_uid === null);

// Kembalikan UID asli Budi
$wargaBudi->update(['rfid_uid' => '93:B4:72:A1']);
IotRfidCard::updateOrCreate(['user_id' => $wargaBudi->id], ['rfid_uid' => '93:B4:72:A1', 'card_label' => 'e-KTP Budi Santoso', 'status' => 'active']);

// -------------------------------------------------------------
// 6. MODUL IOT ESP32 PORTAL GATE & TELEMETRI
// -------------------------------------------------------------
echo "\n--- 6. AUDIT IOT ESP32 & PORTAL GATE ---\n";
$esp32Ctrl = app(\App\Http\Controllers\Api\Esp32Controller::class);

// Tap KTP Budi (Terdaftar) -> Open Gate
$reqTapGate = Request::create('/api/esp32/rfid-tap', 'POST', [
    'device_id' => 'ESP32-GATE-01',
    'rfid_uid' => '93:B4:72:A1',
]);
$resTapGate = $esp32Ctrl->rfidTap($reqTapGate);
$dataTapGate = json_decode($resTapGate->getContent(), true);
assertAudit("Tap KTP Budi Membuka Portal (authorized = true)", ($dataTapGate['authorized'] ?? false) === true && ($dataTapGate['action'] ?? '') === 'open_gate');

// Tap KTP Belum Terdaftar -> Akses Ditolak
$reqTapUnknown = Request::create('/api/esp32/rfid-tap', 'POST', [
    'device_id' => 'ESP32-GATE-01',
    'rfid_uid' => 'EE:FF:11:22',
]);
$resTapUnknown = $esp32Ctrl->rfidTap($reqTapUnknown);
$dataTapUnknown = json_decode($resTapUnknown->getContent(), true);
assertAudit("Tap KTP Tidak Dikenal Ditolak (authorized = false)", ($dataTapUnknown['authorized'] ?? true) === false && ($dataTapUnknown['status'] ?? '') === 'denied');

// -------------------------------------------------------------
// 7. MODUL SURAT PENGANTAR RT
// -------------------------------------------------------------
echo "\n--- 7. AUDIT LAYANAN SURAT PENGANTAR RT ---\n";
Auth::login($wargaBudi);
$suratCtrl = app(\App\Http\Controllers\SuratController::class);

$reqSurat = Request::create('/surat/ajukan', 'POST', [
    'jenis_surat' => 'Surat Pengantar Pembuatan KTP',
    'keperluan' => 'Pengurusan e-KTP Baru di Kelurahan',
    'keterangan' => 'Membawa KK asli',
]);
$reqSurat->setLaravelSession($session);
$resSurat = $suratCtrl->store($reqSurat);
assertAudit("Warga mengajukan surat pengantar RT", $resSurat->getStatusCode() === 302);

$createdSurat = \App\Models\SuratRequest::where('user_id', $wargaBudi->id)->latest()->first();
assertAudit("Surat pengantar tercatat di DB", $createdSurat !== null);

// Ketua RT Menyetujui Surat
Auth::login($ketuaRt);
$reqApprove = Request::create('/surat/setujui/' . $createdSurat->id, 'POST', []);
$reqApprove->setLaravelSession($session);
$resApprove = $suratCtrl->approve($reqApprove, $createdSurat->id);
$createdSurat->refresh();
assertAudit("Ketua RT menyetujui surat pengantar", $createdSurat->status === 'approved' && $createdSurat->nomor_surat !== null);

// Cetak Surat
$resPrint = $suratCtrl->print($createdSurat->id);
assertAudit("Render Cetak Surat Pengantar Resmi RT", $resPrint && $resPrint->name() === 'surat.print');

// -------------------------------------------------------------
// 8. AUDIT RENDERING SELURUH HALAMAN WEB UTAMA
// -------------------------------------------------------------
echo "\n--- 8. AUDIT RENDERING VIEW SELURUH HALAMAN UTAMA ---\n";
Auth::login($admin);

$viewsToTest = [
    '/dashboard' => \App\Http\Controllers\DashboardController::class,
    '/tagihan' => \App\Http\Controllers\TagihanController::class,
    '/iuran' => \App\Http\Controllers\IuranController::class,
    '/kas' => \App\Http\Controllers\CashTransactionController::class,
    '/warga' => \App\Http\Controllers\WargaController::class,
    '/ktp' => \App\Http\Controllers\KtpController::class,
    '/iot' => \App\Http\Controllers\IotController::class,
    '/ronda' => \App\Http\Controllers\RondaController::class,
    '/posyandu' => \App\Http\Controllers\PosyanduController::class,
    '/surat' => \App\Http\Controllers\SuratController::class,
    '/umkm' => \App\Http\Controllers\UmkmController::class,
    '/bank-sampah' => \App\Http\Controllers\BankSampahController::class,
];

foreach ($viewsToTest as $uri => $ctrlClass) {
    try {
        $ctrl = app($ctrlClass);
        $req = Request::create($uri, 'GET');
        $req->setLaravelSession($session);
        $view = $ctrl->index($req);
        $rendered = $view->render();
        assertAudit("Halaman [$uri] Render Sukses (Panjang HTML: " . strlen($rendered) . " bytes)", strlen($rendered) > 500);
    } catch (\Throwable $e) {
        assertAudit("Halaman [$uri] Render Gagal", false, $e->getMessage());
    }
}

echo "\n====================================================================\n";
echo "   TOTAL AUDIT: $passCount LULUS, $failCount GAGAL\n";
echo "====================================================================\n";

if ($failCount > 0) {
    exit(1);
}
exit(0);
