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
echo "   SIKAS-RT: TEST FITUR REKAM e-KTP MANAJEMEN WARGA & ESP32\n";
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

// Pastikan cache bersih sebelum mulai
Cache::forget('warga_scan_session');
Cache::forget('warga_scan_result');
Cache::forget('esp32_pairing_session');
Cache::forget('esp32_pairing_result');

try {
    // 0. Autentikasi sebagai Superadmin
    $admin = User::where('role', 'superadmin')->first();
    Auth::login($admin);

    $wargaCtrl = app(\App\Http\Controllers\WargaController::class);
    $esp32Ctrl = app(\App\Http\Controllers\Api\Esp32Controller::class);

    // -------------------------------------------------------------
    // 1. Sesi Rekam e-KTP (Start & Check Waiting)
    // -------------------------------------------------------------
    echo "--- 1. MULAI SESI REKAM e-KTP ---\n";
    $reqStart = Request::create('/warga/scan-session/start', 'POST');
    $resStart = $wargaCtrl->startScanSession($reqStart);
    $dataStart = json_decode($resStart->getContent(), true);

    assertCheck("Sesi rekam berhasil dimulai", ($dataStart['status'] ?? '') === 'waiting');
    assertCheck("Cache warga_scan_session aktif", Cache::has('warga_scan_session'));

    $reqCheck1 = Request::create('/warga/scan-session/check', 'GET');
    $resCheck1 = $wargaCtrl->checkScanSession($reqCheck1);
    $dataCheck1 = json_decode($resCheck1->getContent(), true);
    assertCheck("Polling awal berstatus waiting", ($dataCheck1['status'] ?? '') === 'waiting');

    // -------------------------------------------------------------
    // 2. Intersepsi Tap ESP32 Reader dalam Mode Rekam
    // -------------------------------------------------------------
    echo "\n--- 2. INTERSEPSI TAP ESP32 READER ---\n";
    $testUidRaw = '5a6b7c8d';
    $expectedUid = '5A:6B:7C:8D';

    // Pastikan ada device terdaftar
    IotDevice::firstOrCreate(
        ['device_id' => 'ESP32-GATE-01'],
        [
            'device_name' => 'ESP32 Gerbang Utama RT',
            'api_key' => 'esp_gate_rt01_key_889911',
            'status' => 'online',
            'ip_address' => '192.168.1.100',
            'is_active' => true,
        ]
    );

    $reqTap = Request::create('/api/esp32/rfid-tap', 'POST', [
        'device_id' => 'ESP32-GATE-01',
        'rfid_uid' => $testUidRaw,
    ]);
    $resTap = $esp32Ctrl->rfidTap($reqTap);
    $dataTap = json_decode($resTap->getContent(), true);

    assertCheck("ESP32 tap diintersepsi untuk enrollment", ($dataTap['authorized'] ?? false) === true && ($dataTap['status'] ?? '') === 'scanned');
    assertCheck("Action mengembalikan buzzer_success", ($dataTap['action'] ?? '') === 'buzzer_success');
    assertCheck("OLED menampilkan 'REKAM e-KTP OK'", ($dataTap['display']['line1'] ?? '') === 'REKAM e-KTP OK');

    // Polling web setelah kartu ditap
    $resCheck2 = $wargaCtrl->checkScanSession($reqCheck1);
    $dataCheck2 = json_decode($resCheck2->getContent(), true);
    assertCheck("Polling web mendeteksi kartu tertempel (status = success)", ($dataCheck2['status'] ?? '') === 'success');
    assertCheck("UID ternormalisasi dengan benar (5A:6B:7C:8D)", ($dataCheck2['uid'] ?? '') === $expectedUid);

    // -------------------------------------------------------------
    // 3. Pengecekan Ketersediaan UID (Duplicate Checking)
    // -------------------------------------------------------------
    echo "\n--- 3. PENGECEKAN DUPLIKASI UID e-KTP ---\n";
    // Bersihkan test user sebelumnya jika ada
    $cleanOld = User::where('email', 'wargabaru_test@kasrt.local')->first();
    if ($cleanOld) {
        IotRfidCard::where('user_id', $cleanOld->id)->delete();
        $cleanOld->delete();
    }

    $reqAvail1 = Request::create('/warga/check-uid', 'GET', ['rfid_uid' => $expectedUid]);
    $resAvail1 = $wargaCtrl->checkUidAvailability($reqAvail1);
    $dataAvail1 = json_decode($resAvail1->getContent(), true);
    assertCheck("UID belum terdaftar berstatus available = true", ($dataAvail1['available'] ?? false) === true);

    // -------------------------------------------------------------
    // 4. Tambah Warga Baru dengan e-KTP Hasil Rekam
    // -------------------------------------------------------------
    echo "\n--- 4. TAMBAH WARGA BARU DENGAN e-KTP ---\n";
    $session = app('session')->driver();
    $reqStore = Request::create('/warga', 'POST', [
        'name' => 'Warga Baru Uji Rekam',
        'email' => 'wargabaru_test@kasrt.local',
        'password' => 'password123',
        'phone' => '081299887766',
        'address' => 'Blok E5 No. 12',
        'rt_rw' => 'RT 01 / RW 05',
        'status_warga' => 'tetap',
        'role' => 'warga',
        'is_active' => '1',
        'rfid_uid' => $expectedUid,
    ]);
    $reqStore->setLaravelSession($session);

    $resStore = $wargaCtrl->store($reqStore);

    $createdUser = User::where('email', 'wargabaru_test@kasrt.local')->first();
    assertCheck("User baru berhasil dibuat di database", $createdUser !== null);
    assertCheck("Field rfid_uid user baru tersimpan", $createdUser && $createdUser->rfid_uid === $expectedUid);

    $cardRecord = IotRfidCard::where('user_id', $createdUser ? $createdUser->id : 0)->first();
    assertCheck("Data IoT RFID Card otomatis tersinkronisasi", $cardRecord && $cardRecord->rfid_uid === $expectedUid);

    $telemetry = IotTelemetryLog::where('user_id', $createdUser ? $createdUser->id : 0)->where('event_type', 'ktp_registration')->first();
    assertCheck("Log telemetri pendaftaran warga tercatat", $telemetry && $telemetry->status === 'success');

    // Cek ulang duplikasi: sekarang UID 5A:6B:7C:8D harus available = false
    $resAvail2 = $wargaCtrl->checkUidAvailability($reqAvail1);
    $dataAvail2 = json_decode($resAvail2->getContent(), true);
    assertCheck("UID yang sudah didaftarkan terdeteksi available = false", ($dataAvail2['available'] ?? true) === false);
    assertCheck("Nama pemilik UID terdeteksi dengan tepat", ($dataAvail2['owner_name'] ?? '') === 'Warga Baru Uji Rekam');

    // -------------------------------------------------------------
    // 5. Direct Pair & Unpair e-KTP pada Warga di Tabel
    // -------------------------------------------------------------
    echo "\n--- 5. DIRECT PAIR & UNPAIR e-KTP ---\n";
    $newDirectUid = '9F:8E:7D:6C';
    $reqPair = Request::create(
        "/warga/{$createdUser->id}/pair-ktp", 
        'POST', 
        ['rfid_uid' => '9f8e7d6c'],
        [],
        [],
        ['HTTP_ACCEPT' => 'application/json', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']
    );
    $reqPair->setLaravelSession($session);

    $resPair = $wargaCtrl->pairKtpDirect($reqPair, $createdUser->id);
    $dataPair = json_decode($resPair->getContent(), true);
    assertCheck("Direct pair e-KTP berhasil", ($dataPair['success'] ?? false) === true && ($dataPair['uid'] ?? '') === $newDirectUid);

    $createdUser->refresh();
    assertCheck("User rfid_uid terupdate ke UID baru", $createdUser->rfid_uid === $newDirectUid);

    // Unpair e-KTP
    $resUnpair = $wargaCtrl->unpairKtpDirect($createdUser->id);
    $createdUser->refresh();
    assertCheck("Unpair e-KTP mengosongkan rfid_uid user", $createdUser->rfid_uid === null);

    $cardAfterUnpair = IotRfidCard::where('user_id', $createdUser->id)->first();
    assertCheck("Kartu iot_rfid_cards terhapus setelah unpair", $cardAfterUnpair === null);

    // -------------------------------------------------------------
    // 6. Simulator Tap untuk Pengujian Tanpa Hardware
    // -------------------------------------------------------------
    echo "\n--- 6. SIMULATOR TAP e-KTP ---\n";
    // Mulai sesi scan terlebih dahulu agar simulator diintersepsi
    $wargaCtrl->startScanSession($reqStart);

    $simUid = '11:22:33:44';
    $reqSim = Request::create('/warga/scan-session/simulate', 'POST', [
        'rfid_uid' => $simUid,
    ]);
    $resSim = $wargaCtrl->simulateScan($reqSim);
    $dataSim = json_decode($resSim->getContent(), true);
    assertCheck("Simulator tap berhasil dijalankan", ($dataSim['authorized'] ?? false) === true && ($dataSim['status'] ?? '') === 'scanned');

    $resCheckSim = $wargaCtrl->checkScanSession($reqCheck1);
    $dataCheckSim = json_decode($resCheckSim->getContent(), true);
    assertCheck("Polling mendeteksi kartu simulator", ($dataCheckSim['status'] ?? '') === 'success' && ($dataCheckSim['uid'] ?? '') === $simUid);

    // Cleanup test user
    IotRfidCard::where('user_id', $createdUser->id)->delete();
    $createdUser->delete();

} finally {
    // Pastikan session rekam selalu dibersihkan
    Cache::forget('warga_scan_session');
    Cache::forget('warga_scan_result');
}

echo "\n====================================================================\n";
echo "   HASIL PENGUJIAN: $passCount LULUS, $failCount GAGAL\n";
echo "====================================================================\n";

if ($failCount > 0) {
    exit(1);
}
exit(0);
