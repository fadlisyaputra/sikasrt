<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\IotDevice;
use App\Models\IotRfidCard;
use App\Models\IotTelemetryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

echo "====================================================================\n";
echo "   SIKAS-RT: PENGUJIAN FITUR e-KTP IOT ESP32 & PROTEKSI PORTAL\n";
echo "====================================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertCheck($title, $condition, $info = '') {
    global $passCount, $failCount;
    if ($condition) {
        echo " [PASS] $title\n";
        $passCount++;
    } else {
        echo " [FAIL] $title" . ($info ? " -> $info" : "") . "\n";
        $failCount++;
    }
}

// -------------------------------------------------------------
// 1. Uji Registrasi UID e-KTP oleh User yang Login
// -------------------------------------------------------------
echo "--- 1. PENGUJIAN REGISTRASI e-KTP OLEH USER LOGIN ---\n";

$warga = User::where('email', 'budi@kasrt.local')->first();
Auth::login($warga);

$ktpCtrl = app(\App\Http\Controllers\KtpController::class);

// Daftarkan UID KTP baru untuk Budi: 93:B4:72:A1 (atau normalisasi dari 93b472a1)
$reqReg = Request::create('/ktp/register', 'POST', [
    'rfid_uid' => '93B472A1',
    'card_label' => 'e-KTP Budi Santoso',
]);
$resReg = $ktpCtrl->registerUid($reqReg);

$wargaFresh = User::find($warga->id);
assertCheck("User rfid_uid terisi setelah registrasi", $wargaFresh->rfid_uid === '93:B4:72:A1');

$card = IotRfidCard::where('user_id', $warga->id)->first();
assertCheck("Data iot_rfid_cards tersinkronisasi otomatis", $card && $card->rfid_uid === '93:B4:72:A1' && $card->status === 'active');

// Cek Log Pendaftaran
$regLog = IotTelemetryLog::where('user_id', $warga->id)->where('event_type', 'ktp_registration')->latest()->first();
assertCheck("Log telemetri pendaftaran e-KTP tercatat", $regLog && $regLog->status === 'success');

// -------------------------------------------------------------
// 2. Uji Proteksi Duplikasi UID KTP (Tidak Boleh Dipakai User Lain)
// -------------------------------------------------------------
echo "\n--- 2. PENGUJIAN PROTEKSI DUPLIKASI UID KTP ---\n";
$userSiti = User::where('email', 'siti@kasrt.local')->first();
Auth::login($userSiti);

// Siti mencoba mendaftarkan UID milik Budi (93:B4:72:A1)
$reqDuplicate = Request::create('/ktp/register', 'POST', [
    'rfid_uid' => '93:B4:72:A1',
]);
$resDuplicate = $ktpCtrl->registerUid($reqDuplicate);

$userSitiFresh = User::find($userSiti->id);
assertCheck("Proteksi: UID milik Budi tidak bisa diklaim oleh Siti", $userSitiFresh->rfid_uid !== '93:B4:72:A1');

// -------------------------------------------------------------
// 3. Uji Mode Live Pairing e-KTP via ESP32 Hardware Reader
// -------------------------------------------------------------
echo "\n--- 3. PENGUJIAN MODE LIVE PAIRING e-KTP VIA ESP32 ---\n";
// Login kembali sebagai Siti untuk pair KTP baru
Auth::login($userSiti);

// Siti memulai sesi pairing di web
$reqPairStart = Request::create('/ktp/pairing/start', 'POST');
$resPairStart = $ktpCtrl->startPairing($reqPairStart);
$dataPairStart = json_decode($resPairStart->getContent(), true);
assertCheck("Sesi Pairing ESP32 aktif di Cache", ($dataPairStart['status'] ?? '') === 'waiting');

// ESP32 menembak API /api/esp32/rfid-tap saat KTP Siti (misal 55:66:77:88) ditempelkan
$esp32Ctrl = app(\App\Http\Controllers\Api\Esp32Controller::class);
$reqEspPairTap = Request::create('/api/esp32/rfid-tap', 'POST', [
    'device_id' => 'ESP32-GATE-01',
    'rfid_uid' => '55:66:77:88',
]);
$resEspPairTap = $esp32Ctrl->rfidTap($reqEspPairTap);
$dataEspPairTap = json_decode($resEspPairTap->getContent(), true);

assertCheck("ESP32 mendeteksi mode pairing dan mendaftarkan e-KTP Siti", 
    ($dataEspPairTap['authorized'] ?? false) === true && ($dataEspPairTap['status'] ?? '') === 'registered');

// Browser Siti mengecek status pairing via AJAX polling
$resPairStatus = $ktpCtrl->checkPairingStatus();
$dataPairStatus = json_decode($resPairStatus->getContent(), true);
assertCheck("Polling AJAX Web mendeteksi sukses pairing e-KTP", ($dataPairStatus['status'] ?? '') === 'success');

$userSitiUpdated = User::find($userSiti->id);
assertCheck("UID e-KTP Siti (55:66:77:88) berhasil terdaftar", $userSitiUpdated->rfid_uid === '55:66:77:88');

// -------------------------------------------------------------
// 4. Uji Buka Portal Perumahan dengan e-KTP Terdaftar
// -------------------------------------------------------------
echo "\n--- 4. PENGUJIAN AKSES BUKA PORTAL DENGAN e-KTP TERDAFTAR ---\n";

$reqGateOpen = Request::create('/api/esp32/rfid-tap', 'POST', [
    'device_id' => 'ESP32-GATE-01',
    'rfid_uid' => '93:B4:72:A1', // KTP Budi
]);
$resGateOpen = $esp32Ctrl->rfidTap($reqGateOpen);
$dataGateOpen = json_decode($resGateOpen->getContent(), true);

assertCheck("Portal Terbuka untuk KTP Budi (authorized = true)", ($dataGateOpen['authorized'] ?? false) === true);
assertCheck("Action = 'open_gate' & Relay = 'on'", 
    ($dataGateOpen['action'] ?? '') === 'open_gate' && ($dataGateOpen['relay'] ?? '') === 'on');
assertCheck("Data Nama Warga & Display Muncul", 
    ($dataGateOpen['user_name'] ?? '') === 'Budi Santoso' && ($dataGateOpen['display']['line1'] ?? '') === 'PORTAL TERBUKA');

// Cek Log Telemetri Portal
$gateLog = IotTelemetryLog::where('user_id', $warga->id)->where('event_type', 'gate_access')->latest()->first();
assertCheck("Log telemetri akses portal tersimpan dengan status 'success'", $gateLog && $gateLog->status === 'success');

// -------------------------------------------------------------
// 5. Uji Proteksi: e-KTP Belum Terdaftar -> AKSES DITOLAK!
// -------------------------------------------------------------
echo "\n--- 5. PENGUJIAN PROTEKSI: KTP BELUM TERDAFTAR -> AKSES DITOLAK ---\n";

$unregisteredUid = 'DE:AD:BE:EF';
$reqGateDenied = Request::create('/api/esp32/rfid-tap', 'POST', [
    'device_id' => 'ESP32-GATE-01',
    'rfid_uid' => $unregisteredUid,
]);
$resGateDenied = $esp32Ctrl->rfidTap($reqGateDenied);
$dataGateDenied = json_decode($resGateDenied->getContent(), true);

assertCheck("Proteksi: authorized = false untuk KTP belum terdaftar", ($dataGateDenied['authorized'] ?? true) === false);
assertCheck("Proteksi: status = 'denied' & access = 'rejected'", 
    ($dataGateDenied['status'] ?? '') === 'denied' && ($dataGateDenied['access'] ?? '') === 'rejected');
assertCheck("Proteksi: Relay palang portal tetap 'off' (tidak terbuka)", ($dataGateDenied['relay'] ?? '') === 'off');
assertCheck("Proteksi: Action memicu buzzer_error", ($dataGateDenied['action'] ?? '') === 'buzzer_error');
assertCheck("Proteksi: Pesan penolakan 'AKSES DITOLAK! KTP BELUM TERDAFTAR'", 
    str_contains($dataGateDenied['message'] ?? '', 'AKSES DITOLAK'));

// Cek Log Telemetri Penolakan
$deniedLog = IotTelemetryLog::where('rfid_uid', $unregisteredUid)->where('event_type', 'gate_access')->latest()->first();
assertCheck("Log penolakan keamanan tercatat di database dengan status 'denied'", 
    $deniedLog && $deniedLog->status === 'denied');

// -------------------------------------------------------------
// 6. Uji Proteksi: KTP Berstatus Diblokir -> AKSES DITOLAK!
// -------------------------------------------------------------
echo "\n--- 6. PENGUJIAN PROTEKSI: KARTU DIBLOKIR -> AKSES DITOLAK ---\n";

// Blokir kartu Siti untuk pengujian proteksi
$cardSiti = IotRfidCard::where('user_id', $userSiti->id)->first();
$cardSiti->update(['status' => 'blocked']);

$reqBlockedTap = Request::create('/api/esp32/rfid-tap', 'POST', [
    'device_id' => 'ESP32-GATE-01',
    'rfid_uid' => '55:66:77:88',
]);
$resBlockedTap = $esp32Ctrl->rfidTap($reqBlockedTap);
$dataBlockedTap = json_decode($resBlockedTap->getContent(), true);

assertCheck("Proteksi: authorized = false untuk KTP yang diblokir", ($dataBlockedTap['authorized'] ?? true) === false);
assertCheck("Proteksi: status = 'blocked' & relay = 'off'", 
    ($dataBlockedTap['status'] ?? '') === 'blocked' && ($dataBlockedTap['relay'] ?? '') === 'off');

// Kembalikan status kartu Siti ke active dan pulihkan UID aslinya
$userSiti->update(['rfid_uid' => '4A:8C:31:E9']);
$cardSiti->update(['rfid_uid' => '4A:8C:31:E9', 'status' => 'active']);

// -------------------------------------------------------------
// 7. Uji Rendering Halaman View /ktp
// -------------------------------------------------------------
echo "\n--- 7. PENGUJIAN RENDERING VIEW /ktp ---\n";
Auth::login($warga);
$view = view('ktp.index', [
    'user' => $warga,
    'ktpCard' => $card,
    'registeredUid' => $warga->rfid_uid,
    'accessLogs' => collect([$gateLog]),
    'portalDevice' => IotDevice::where('type', 'portal_gate')->first(),
    'isPairingActive' => false,
])->render();

assertCheck("View /ktp berhasil di-render tanpa exception", strlen($view) > 1000);
assertCheck("View memuat teks 'e-KTP Anda Sudah Terdaftar'", str_contains($view, 'e-KTP Anda Sudah Terdaftar'));
assertCheck("View memuat simulator palang portal", str_contains($view, 'Simulator Palang Pintu Portal'));

echo "\n====================================================================\n";
echo "HASIL PENGUJIAN: $passCount BERHASIL, $failCount GAGAL\n";
echo "====================================================================\n";

if ($failCount > 0) {
    exit(1);
}
exit(0);
