<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\RondaSchedule;
use App\Models\RondaMember;
use App\Models\RondaAttendance;
use App\Models\PosyanduActivity;
use App\Models\PosyanduRecord;
use App\Models\WargaBill;
use App\Models\IuranType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

echo "====================================================================\n";
echo "   SIKAS-RT: PENGUJIAN FITUR RONDA, POSYANDU & TAGIHAN WARGA\n";
echo "====================================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertTest($title, $condition, $info = '') {
    global $passCount, $failCount;
    if ($condition) {
        echo " [PASS] $title\n";
        $passCount++;
    } else {
        echo " [FAIL] $title" . ($info ? " -> $info" : "") . "\n";
        $failCount++;
    }
}

$superadmin = User::where('role', 'superadmin')->first();
$bendahara = User::where('role', 'bendahara')->first();
$rt = User::where('role', 'rt')->first();
$warga = User::where('role', 'warga')->first();

// =========================================================================
// 1. PENGUJIAN FITUR JADWAL RONDA
// =========================================================================
echo "--- 1. PENGUJIAN FITUR JADWAL RONDA ---\n";
$rondaCtrl = app(\App\Http\Controllers\RondaController::class);

// 1a. Render View Ronda (Warga & RT)
Auth::login($warga);
$reqViewWarga = Request::create('/ronda', 'GET');
$resViewWarga = $rondaCtrl->index($reqViewWarga);
assertTest("View Ronda Warga Render OK", strlen($resViewWarga->render()) > 1000);

Auth::login($rt);
$reqViewRt = Request::create('/ronda', 'GET');
$resViewRt = $rondaCtrl->index($reqViewRt);
assertTest("View Ronda Pengurus RT Render OK", strlen($resViewRt->render()) > 1000);

// 1b. Tambah Jadwal Regu Baru
$reqStoreSchedule = Request::create('/ronda/jadwal', 'POST', [
    'day_name' => 'Senin',
    'group_name' => 'Regu Test Unit',
    'start_time' => '22:00',
    'end_time' => '04:00',
    'location' => 'Pos Pantau Gerbang Selatan',
    'coordinator_id' => $rt->id,
    'notes' => 'Uji coba otomatis jadwal ronda',
]);
$rondaCtrl->storeSchedule($reqStoreSchedule);
$newSched = RondaSchedule::where('group_name', 'Regu Test Unit')->first();
assertTest("Tambah Jadwal Regu Baru Berhasil", $newSched !== null);

// 1c. Tambah Anggota Warga ke Regu
$reqAddMember = Request::create('/ronda/jadwal/' . $newSched->id . '/anggota', 'POST', [
    'user_id' => $warga->id,
    'role_in_team' => 'Anggota',
]);
$rondaCtrl->addMember($reqAddMember, $newSched->id);
$isMember = RondaMember::where('ronda_schedule_id', $newSched->id)->where('user_id', $warga->id)->exists();
assertTest("Tambah Anggota Warga ke Regu Berhasil", $isMember);

// 1d. Check-in Kehadiran Ronda
Auth::login($warga);
$reqCheckin = Request::create('/ronda/checkin', 'POST', [
    'ronda_schedule_id' => $newSched->id,
    'status' => 'hadir',
    'notes' => 'Uji check-in kehadiran',
]);
$rondaCtrl->checkin($reqCheckin);
$att = RondaAttendance::where('user_id', $warga->id)->whereDate('attendance_date', Carbon::today())->first();
assertTest("Check-in Kehadiran Ronda Berhasil Tercatat", $att && $att->status === 'hadir');

// Cleanup
$newSched->delete();

// =========================================================================
// 2. PENGUJIAN FITUR POSYANDU
// =========================================================================
echo "\n--- 2. PENGUJIAN FITUR LAYANAN POSYANDU ---\n";
$posyanduCtrl = app(\App\Http\Controllers\PosyanduController::class);

// 2a. Render View Posyandu
Auth::login($warga);
$reqPosView = Request::create('/posyandu', 'GET');
$resPosView = $posyanduCtrl->index($reqPosView);
assertTest("View Posyandu Warga Render OK", strlen($resPosView->render()) > 1000);

// 2b. Tambah Agenda Posyandu Baru
Auth::login($rt);
$reqStoreActivity = Request::create('/posyandu/kegiatan', 'POST', [
    'title' => 'Posyandu Balita Tes Unit',
    'category' => 'balita',
    'activity_date' => Carbon::now()->addDays(7)->toDateString(),
    'start_time' => '08:30',
    'end_time' => '11:00',
    'location' => 'Balai Warga RT 01',
    'officer_name' => 'Bidan Siti & Kader Posyandu',
    'description' => 'Penimbangan dan pembagian vitamin.',
]);
$posyanduCtrl->storeActivity($reqStoreActivity);
$newAct = PosyanduActivity::where('title', 'Posyandu Balita Tes Unit')->first();
assertTest("Tambah Agenda Kegiatan Posyandu Berhasil", $newAct !== null);

// 2c. Input Catatan Pemeriksaan Balita
$reqStoreRecord = Request::create('/posyandu/pemeriksaan', 'POST', [
    'posyandu_activity_id' => $newAct->id,
    'user_id' => $warga->id,
    'participant_name' => 'Rayyan (Anak Warga)',
    'participant_category' => 'balita',
    'gender' => 'L',
    'age_text' => '18 Bulan',
    'weight' => 11.2,
    'height' => 84.0,
    'vitamin_immunization' => 'Vitamin A & DPT-3',
    'health_status' => 'Gizi Baik & Sehat',
    'notes' => 'Kondisi fisik prima',
]);
$posyanduCtrl->storeRecord($reqStoreRecord);
$newRec = PosyanduRecord::where('participant_name', 'Rayyan (Anak Warga)')->first();
assertTest("Input Data Hasil Pemeriksaan Posyandu Balita Berhasil", $newRec && $newRec->weight == 11.2);

// Cleanup
$newAct->delete();

// =========================================================================
// 3. PENGUJIAN FITUR TAGIHAN WARGA
// =========================================================================
echo "\n--- 3. PENGUJIAN FITUR TAGIHAN WARGA (BILLING) ---\n";
$tagihanCtrl = app(\App\Http\Controllers\TagihanController::class);

// 3a. Render View Tagihan (Warga & Bendahara)
Auth::login($warga);
$reqTagihanView = Request::create('/tagihan', 'GET');
$resTagihanView = $tagihanCtrl->index($reqTagihanView);
assertTest("View Tagihan Warga Render OK", strlen($resTagihanView->render()) > 1000);

Auth::login($bendahara);
$reqTagihanViewAdmin = Request::create('/tagihan', 'GET');
$resTagihanViewAdmin = $tagihanCtrl->index($reqTagihanViewAdmin);
assertTest("View Tagihan Bendahara Render OK", strlen($resTagihanViewAdmin->render()) > 1000);

// 3b. Buat Tagihan Baru untuk Warga
$iuranType = IuranType::first();
$reqCreateBill = Request::create('/tagihan/tambah', 'POST', [
    'user_id' => $warga->id,
    'iuran_type_id' => $iuranType->id,
    'period_month' => 12,
    'period_year' => 2026,
    'amount' => 50000,
    'due_date' => '2026-12-31',
    'notes' => 'Tagihan Uji Coba Unit Test',
]);
$tagihanCtrl->store($reqCreateBill);
$testBill = WargaBill::where('user_id', $warga->id)->where('period_month', 12)->where('period_year', 2026)->first();
assertTest("Penerbitan Tagihan Warga Berhasil", $testBill && $testBill->amount == 50000);

// 3c. Tandai Lunas oleh Bendahara
$reqMarkPaid = Request::create('/tagihan/lunas/' . $testBill->id, 'POST', [
    'payment_method' => 'cash',
    'notes' => 'Pelunasan diserahkan langsung ke Bendahara',
]);
$tagihanCtrl->markPaid($reqMarkPaid, $testBill->id);
$testBillFresh = WargaBill::find($testBill->id);
assertTest("Tandai Tagihan LUNAS Berhasil (status = paid)", $testBillFresh->status === 'paid' && $testBillFresh->paid_at !== null);

// 3d. Cetak / Tampilkan Kuitansi Faktur Invoice
$resInvoice = $tagihanCtrl->invoice($testBill->id);
assertTest("Render Kuitansi Faktur Invoice Berhasil", str_contains($resInvoice->render(), $testBill->bill_code));

// Cleanup
$testBill->delete();

echo "\n====================================================================\n";
echo "HASIL PENGUJIAN: $passCount BERHASIL, $failCount GAGAL\n";
echo "====================================================================\n";

if ($failCount > 0) {
    exit(1);
}
exit(0);
