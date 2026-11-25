<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IuranController;
use App\Http\Controllers\CashTransactionController;
use App\Http\Controllers\WargaController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\IotController;
use App\Http\Controllers\KtpController;
use App\Http\Controllers\RondaController;
use App\Http\Controllers\PosyanduController;
use App\Http\Controllers\TagihanController;
use App\Http\Controllers\SuratController;
use App\Http\Controllers\UmkmController;
use App\Http\Controllers\BankSampahController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/quick-login/{email}', [AuthController::class, 'quickLogin'])->name('quick-login');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Iuran Routes
    Route::get('/iuran', [IuranController::class, 'index'])->name('iuran.index');
    Route::post('/iuran/bayar', [IuranController::class, 'storePayment'])->name('iuran.pay');
    
    // Iuran Admin / Bendahara Management
    Route::middleware(['role:superadmin,bendahara'])->group(function () {
        Route::post('/iuran/jenis', [IuranController::class, 'storeType'])->name('iuran.store_type');
        Route::post('/iuran/verifikasi/{id}', [IuranController::class, 'verifyPayment'])->name('iuran.verify');
    });

    // Kas Transaction Routes
    Route::get('/kas', [CashTransactionController::class, 'index'])->name('kas.index');
    Route::middleware(['role:superadmin,bendahara'])->group(function () {
        Route::post('/kas/tambah', [CashTransactionController::class, 'store'])->name('kas.store');
        Route::delete('/kas/{id}', [CashTransactionController::class, 'destroy'])->name('kas.destroy');
    });

    // Warga / User Directory Management Routes
    Route::middleware(['role:superadmin,bendahara,rt'])->group(function () {
        Route::get('/warga', [WargaController::class, 'index'])->name('warga.index');
    });
    Route::middleware(['role:superadmin,bendahara'])->group(function () {
        Route::post('/warga/tambah', [WargaController::class, 'store'])->name('warga.store');
        Route::put('/warga/{id}/update', [WargaController::class, 'update'])->name('warga.update');
    });
    Route::middleware(['role:superadmin'])->group(function () {
        Route::delete('/warga/{id}/delete', [WargaController::class, 'destroy'])->name('warga.destroy');
    });

    // Announcement Routes
    Route::get('/pengumuman', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::middleware(['role:superadmin,bendahara,rt'])->group(function () {
        Route::post('/pengumuman/tambah', [AnnouncementController::class, 'store'])->name('announcements.store');
        Route::delete('/pengumuman/{id}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
    });

    // e-KTP & Akses Portal Perumahan Routes (Untuk Semua User yang Login)
    Route::get('/ktp', [KtpController::class, 'index'])->name('ktp.index');
    Route::post('/ktp/register', [KtpController::class, 'registerUid'])->name('ktp.register');
    Route::post('/ktp/unpair', [KtpController::class, 'unpairUid'])->name('ktp.unpair');
    Route::post('/ktp/pairing/start', [KtpController::class, 'startPairing'])->name('ktp.pairing.start');
    Route::get('/ktp/pairing/status', [KtpController::class, 'checkPairingStatus'])->name('ktp.pairing.status');
    Route::post('/ktp/pairing/cancel', [KtpController::class, 'cancelPairing'])->name('ktp.pairing.cancel');
    Route::post('/ktp/simulate-portal', [KtpController::class, 'simulateTap'])->name('ktp.simulate.portal');

    // 1. Jadwal Ronda & Siskamling Routes
    Route::get('/ronda', [RondaController::class, 'index'])->name('ronda.index');
    Route::post('/ronda/checkin', [RondaController::class, 'checkin'])->name('ronda.checkin');
    Route::middleware(['role:superadmin,bendahara,rt'])->group(function () {
        Route::post('/ronda/jadwal', [RondaController::class, 'storeSchedule'])->name('ronda.schedule.store');
        Route::put('/ronda/jadwal/{id}', [RondaController::class, 'updateSchedule'])->name('ronda.schedule.update');
        Route::delete('/ronda/jadwal/{id}', [RondaController::class, 'destroySchedule'])->name('ronda.schedule.destroy');
        Route::post('/ronda/jadwal/{id}/anggota', [RondaController::class, 'addMember'])->name('ronda.member.add');
        Route::delete('/ronda/jadwal/{scheduleId}/anggota/{userId}', [RondaController::class, 'removeMember'])->name('ronda.member.remove');
    });

    // 2. Posyandu Balita & Lansia Routes
    Route::get('/posyandu', [PosyanduController::class, 'index'])->name('posyandu.index');
    Route::middleware(['role:superadmin,bendahara,rt'])->group(function () {
        Route::post('/posyandu/kegiatan', [PosyanduController::class, 'storeActivity'])->name('posyandu.activity.store');
        Route::delete('/posyandu/kegiatan/{id}', [PosyanduController::class, 'destroyActivity'])->name('posyandu.activity.destroy');
        Route::post('/posyandu/pemeriksaan', [PosyanduController::class, 'storeRecord'])->name('posyandu.record.store');
        Route::delete('/posyandu/pemeriksaan/{id}', [PosyanduController::class, 'destroyRecord'])->name('posyandu.record.destroy');
    });

    // 3. Tagihan Warga (Billing & Invoicing) Routes
    Route::get('/tagihan', [TagihanController::class, 'index'])->name('tagihan.index');
    Route::get('/tagihan/invoice/{id}', [TagihanController::class, 'invoice'])->name('tagihan.invoice');
    Route::post('/tagihan/bayar/{id}', [TagihanController::class, 'payBill'])->name('tagihan.pay');
    Route::middleware(['role:superadmin,bendahara,rt'])->group(function () {
        Route::post('/tagihan/generate', [TagihanController::class, 'generate'])->name('tagihan.generate');
        Route::post('/tagihan/tambah', [TagihanController::class, 'store'])->name('tagihan.store');
        Route::post('/tagihan/lunas/{id}', [TagihanController::class, 'markPaid'])->name('tagihan.mark_paid');
        Route::delete('/tagihan/{id}', [TagihanController::class, 'destroy'])->name('tagihan.destroy');
    });

    // 4. Layanan Surat Menyurat Routes
    Route::get('/surat', [SuratController::class, 'index'])->name('surat.index');
    Route::post('/surat/ajukan', [SuratController::class, 'store'])->name('surat.store');
    Route::get('/surat/cetak/{id}', [SuratController::class, 'print'])->name('surat.print');
    Route::middleware(['role:superadmin,rt'])->group(function () {
        Route::post('/surat/setujui/{id}', [SuratController::class, 'approve'])->name('surat.approve');
        Route::post('/surat/tolak/{id}', [SuratController::class, 'reject'])->name('surat.reject');
    });

    // 5. Etalase UMKM Warga Routes
    Route::get('/umkm', [UmkmController::class, 'index'])->name('umkm.index');
    Route::post('/umkm/tambah', [UmkmController::class, 'store'])->name('umkm.store');
    Route::put('/umkm/update/{id}', [UmkmController::class, 'update'])->name('umkm.update');
    Route::delete('/umkm/hapus/{id}', [UmkmController::class, 'destroy'])->name('umkm.destroy');

    // 6. Bank Sampah RT Routes
    Route::get('/bank-sampah', [BankSampahController::class, 'index'])->name('bank_sampah.index');
    Route::middleware(['role:superadmin,bendahara,rt'])->group(function () {
        Route::post('/bank-sampah/transaksi', [BankSampahController::class, 'storeTransaction'])->name('bank_sampah.transaction.store');
        Route::post('/bank-sampah/kategori', [BankSampahController::class, 'storeCategory'])->name('bank_sampah.category.store');
    });

    // IoT & ESP32 Smart RT Management Routes
    Route::get('/iot', [IotController::class, 'index'])->name('iot.index');
    Route::post('/iot/panic', [IotController::class, 'triggerPanic'])->name('iot.panic.trigger');

    Route::middleware(['role:superadmin,bendahara,rt'])->group(function () {
        Route::post('/iot/command', [IotController::class, 'sendCommand'])->name('iot.command.send');
        Route::post('/iot/emergency/{id}/resolve', [IotController::class, 'resolveEmergency'])->name('iot.emergency.resolve');
    });

    Route::middleware(['role:superadmin,bendahara'])->group(function () {
        Route::post('/iot/perangkat/tambah', [IotController::class, 'storeDevice'])->name('iot.device.store');
        Route::put('/iot/perangkat/{id}/update', [IotController::class, 'updateDevice'])->name('iot.device.update');
        Route::delete('/iot/perangkat/{id}/delete', [IotController::class, 'destroyDevice'])->name('iot.device.destroy');
        Route::post('/iot/rfid/tambah', [IotController::class, 'storeRfidCard'])->name('iot.rfid.store');
        Route::delete('/iot/rfid/{id}/delete', [IotController::class, 'destroyRfidCard'])->name('iot.rfid.destroy');
    });
});
