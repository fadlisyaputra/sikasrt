<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\IotDevice;
use App\Models\IotRfidCard;
use App\Models\IotTelemetryLog;
use App\Models\IotEmergencyAlert;
use App\Models\CashTransaction;
use App\Models\IuranPayment;
use App\Models\IuranType;
use App\Models\Announcement;
use Illuminate\Http\Request;

echo "========================================================\n";
echo "   SIKAS-RT IoT: PENGUJIAN SEMUA FITUR & ROLE SISTEM\n";
echo "========================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertTest($title, $condition) {
    global $passCount, $failCount;
    if ($condition) {
        echo " [PASS] $title\n";
        $passCount++;
    } else {
        echo " [FAIL] $title\n";
        $failCount++;
    }
}

// 1. Test Database & Seeders
echo "--- 1. PENGUJIAN DATABASE & DATA SEEDING ---\n";
$userCount = User::count();
assertTest("Total Pengguna Terdaftar (>= 6 akun)", $userCount >= 6);

$deviceCount = IotDevice::count();
assertTest("Perangkat IoT ESP32 Terdaftar (>= 4 unit)", $deviceCount >= 4);

$rfidCardCount = IotRfidCard::count();
assertTest("Kartu RFID Warga Terdaftar (>= 3 kartu)", $rfidCardCount >= 3);

$kasTransactionsCount = CashTransaction::count();
assertTest("Transaksi Kas RT Terdaftar", $kasTransactionsCount >= 5);

// 2. Test All 4 Roles
echo "\n--- 2. PENGUJIAN OTORITAS ROLE ---\n";
$superadmin = User::where('role', 'superadmin')->first();
assertTest("Superadmin Role Verification", $superadmin && $superadmin->isSuperadmin());

$bendahara = User::where('role', 'bendahara')->first();
assertTest("Bendahara Role Verification", $bendahara && $bendahara->isBendahara());

$rt = User::where('role', 'rt')->first();
assertTest("Ketua RT Role Verification", $rt && $rt->isRt());

$warga = User::where('role', 'warga')->first();
assertTest("Warga Role Verification", $warga && $warga->isWarga());

// 3. Test ESP32 Controller Endpoints
echo "\n--- 3. PENGUJIAN ENDPOINT ESP32 & IOT ---\n";
$esp32Ctrl = app(\App\Http\Controllers\Api\Esp32Controller::class);

// 3a. Heartbeat
$reqHeartbeat = Request::create('/api/esp32/heartbeat', 'POST', [
    'device_id' => 'ESP32-GATE-01',
    'rssi' => -60,
    'ip_address' => '192.168.1.150',
]);
$resHeartbeat = $esp32Ctrl->heartbeat($reqHeartbeat);
$dataHeartbeat = json_decode($resHeartbeat->getContent(), true);
assertTest("ESP32 Heartbeat Response OK", ($dataHeartbeat['status'] ?? '') === 'ok');

// 3b. Valid RFID Tap (Gate Barrier)
$reqGateTap = Request::create('/api/esp32/rfid-tap', 'POST', [
    'device_id' => 'ESP32-GATE-01',
    'rfid_uid' => '93:B4:72:A1', // Budi
]);
$resGateTap = $esp32Ctrl->rfidTap($reqGateTap);
$dataGateTap = json_decode($resGateTap->getContent(), true);
assertTest("RFID Portal Tap (Budi Santoso) -> Buka Gerbang", 
    ($dataGateTap['authorized'] ?? false) === true && ($dataGateTap['action'] ?? '') === 'open_gate');

// 3c. Invalid RFID Tap
$reqInvalidTap = Request::create('/api/esp32/rfid-tap', 'POST', [
    'device_id' => 'ESP32-GATE-01',
    'rfid_uid' => '99:99:99:99',
]);
$resInvalidTap = $esp32Ctrl->rfidTap($reqInvalidTap);
$dataInvalidTap = json_decode($resInvalidTap->getContent(), true);
assertTest("RFID Kartu Tak Dikenal -> Ditolak", ($dataInvalidTap['authorized'] ?? true) === false);

// 3d. Tap-to-Pay Iuran via RFID
$saldoAwal = CashTransaction::where('type', 'pemasukan')->sum('amount') - CashTransaction::where('type', 'pengeluaran')->sum('amount');
$reqPayTap = Request::create('/api/esp32/rfid-tap', 'POST', [
    'device_id' => 'ESP32-RONDA-POS',
    'rfid_uid' => '4A:8C:31:E9', // Siti
    'mode' => 'pay',
    'amount' => 50000,
]);
$resPayTap = $esp32Ctrl->rfidTap($reqPayTap);
$dataPayTap = json_decode($resPayTap->getContent(), true);
$saldoSetelahPay = CashTransaction::where('type', 'pemasukan')->sum('amount') - CashTransaction::where('type', 'pengeluaran')->sum('amount');
assertTest("Tap-to-Pay RFID Iuran Kas RT Rp 50.000", 
    ($dataPayTap['status'] ?? '') === 'paid' && ($saldoSetelahPay - $saldoAwal) == 50000);

// 3e. Smart Kas Drop
$reqKasDrop = Request::create('/api/esp32/kas-drop', 'POST', [
    'device_id' => 'ESP32-KASBOX-01',
    'amount' => 25000,
]);
$resKasDrop = $esp32Ctrl->kasDrop($reqKasDrop);
$dataKasDrop = json_decode($resKasDrop->getContent(), true);
assertTest("Smart Kotak Kas Drop Fisik Rp 25.000", ($dataKasDrop['status'] ?? '') === 'ok');

// 3f. Emergency Panic Alert
$reqPanic = Request::create('/api/esp32/panic-alert', 'POST', [
    'device_id' => 'ESP32-PANIC-01',
    'alert_type' => 'panic_button',
    'location' => 'Pos Siskamling Depan',
]);
$resPanic = $esp32Ctrl->panicAlert($reqPanic);
$dataPanic = json_decode($resPanic->getContent(), true);
assertTest("ESP32 Panic Button Alarm Trigger", ($dataPanic['status'] ?? '') === 'emergency_active');

// 4. Test View Rendering for All Dashboards
echo "\n--- 4. PENGUJIAN RENDERING TAMPILAN SEMUA ROLE ---\n";
try {
    // Login as Superadmin
    auth()->login($superadmin);
    $viewSuperadmin = view('dashboard.superadmin', [
        'user' => $superadmin,
        'totalKas' => 1000000,
        'totalPemasukan' => 2000000,
        'totalPengeluaran' => 1000000,
        'pemasukanBulanIni' => 500000,
        'pengeluaranBulanIni' => 200000,
        'totalUsers' => 6,
        'userCountsByRole' => ['superadmin' => 1, 'bendahara' => 1, 'rt' => 1, 'warga' => 3],
        'iuranTypes' => IuranType::all(),
        'pendingPaymentsCount' => 1,
        'recentAnnouncements' => Announcement::all(),
        'recentTransactions' => CashTransaction::all(),
        'iotDeviceCount' => 4,
        'iotOnlineCount' => 4,
        'iotAlertCount' => 1,
        'recentIotLogs' => IotTelemetryLog::all(),
        'devices' => IotDevice::all(),
    ])->render();
    assertTest("View Dashboard Superadmin Rendered Successfully", strlen($viewSuperadmin) > 1000);

    // Login as Bendahara
    auth()->login($bendahara);
    $viewBendahara = view('dashboard.bendahara', [
        'user' => $bendahara,
        'totalKas' => 1000000,
        'totalPemasukan' => 2000000,
        'totalPengeluaran' => 1000000,
        'pemasukanBulanIni' => 500000,
        'pengeluaranBulanIni' => 200000,
        'pendingPayments' => IuranPayment::where('status', 'pending')->get(),
        'activeIuranTypes' => IuranType::where('is_active', true)->get(),
        'recentTransactions' => CashTransaction::all(),
        'recentAnnouncements' => Announcement::all(),
        'iotDeviceCount' => 4,
        'iotOnlineCount' => 4,
        'iotAlertCount' => 1,
        'recentIotLogs' => IotTelemetryLog::all(),
        'iotKasMasuk' => 150000,
    ])->render();
    assertTest("View Dashboard Bendahara Rendered Successfully", strlen($viewBendahara) > 1000);

    // Login as RT
    auth()->login($rt);
    $viewRt = view('dashboard.rt', [
        'user' => $rt,
        'totalKas' => 1000000,
        'totalPemasukan' => 2000000,
        'totalPengeluaran' => 1000000,
        'pemasukanBulanIni' => 500000,
        'pengeluaranBulanIni' => 200000,
        'totalWarga' => 3,
        'wargaStatusList' => User::where('role', 'warga')->get(),
        'recentTransactions' => CashTransaction::all(),
        'recentAnnouncements' => Announcement::all(),
        'pendingPaymentsCount' => 1,
        'iotDeviceCount' => 4,
        'iotOnlineCount' => 4,
        'iotAlertCount' => 1,
        'recentIotLogs' => IotTelemetryLog::all(),
    ])->render();
    assertTest("View Dashboard Ketua RT Rendered Successfully", strlen($viewRt) > 1000);

    // Login as Warga
    auth()->login($warga);
    $viewWarga = view('dashboard.warga', [
        'user' => $warga,
        'totalKas' => 1000000,
        'pemasukanBulanIni' => 500000,
        'pengeluaranBulanIni' => 200000,
        'myPayments' => IuranPayment::where('user_id', $warga->id)->get(),
        'activeIuranTypes' => IuranType::where('is_active', true)->get(),
        'hasPaidThisMonth' => true,
        'recentAnnouncements' => Announcement::all(),
        'recentTransactions' => CashTransaction::all(),
        'myRfidCard' => IotRfidCard::where('user_id', $warga->id)->first(),
        'myRecentLogs' => IotTelemetryLog::where('user_id', $warga->id)->get(),
    ])->render();
    assertTest("View Dashboard Warga Rendered Successfully", strlen($viewWarga) > 1000);

    // IoT Hub View
    auth()->login($superadmin);
    $viewIot = view('iot.index', [
        'devices' => IotDevice::all(),
        'rfidCards' => IotRfidCard::all(),
        'telemetryLogs' => IotTelemetryLog::all(),
        'activeAlerts' => IotEmergencyAlert::where('status', 'active')->get(),
        'resolvedAlerts' => IotEmergencyAlert::where('status', 'resolved')->get(),
        'wargaList' => User::where('role', 'warga')->get(),
        'totalDevices' => 4,
        'onlineCount' => 4,
        'alertCount' => 1,
        'offlineCount' => 0,
    ])->render();
    assertTest("View Smart IoT & ESP32 Hub Rendered Successfully", strlen($viewIot) > 1000);

} catch (\Throwable $e) {
    echo "ERROR Rendering: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
    $failCount++;
}

echo "\n========================================================\n";
echo "HASIL PENGUJIAN: $passCount BERHASIL, $failCount GAGAL\n";
echo "========================================================\n";
