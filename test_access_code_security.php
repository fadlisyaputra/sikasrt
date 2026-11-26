<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

echo "====================================================================\n";
echo "   SIKAS-RT: PENGUJIAN KODE AKSES PENGURUS (SUPERADMIN/BENDAHARA/RT)\n";
echo "====================================================================\n\n";

$passCount = 0;
$failCount = 0;

function assertTest($condition, $message) {
    global $passCount, $failCount;
    if ($condition) {
        echo " [PASS] $message\n";
        $passCount++;
    } else {
        echo " [FAIL] $message\n";
        $failCount++;
    }
}

$authController = new \App\Http\Controllers\AuthController();

// Test 1: Quick Login Warga tanpa kode akses harus Berhasil
echo "--- 1. PENGUJIAN QUICK LOGIN WARGA (BEBAS KODE AKSES) ---\n";
Auth::logout();
$request = Request::create('/quick-login/budi@kasrt.local', 'GET');
$response = $authController->quickLogin($request, 'budi@kasrt.local');
assertTest(Auth::check() && Auth::user()->role === 'warga', 'Warga berhasil login langsung tanpa kode akses');
assertTest(str_contains($response->headers->get('Location') ?? '', 'dashboard'), 'Warga langsung diarahkan ke /dashboard');

// Test 2: Quick Login Superadmin TANPA kode akses harus DITOLAK
echo "\n--- 2. PENGUJIAN PROTEKSI: QUICK LOGIN SUPERADMIN TANPA KODE AKSES ---\n";
Auth::logout();
$request = Request::create('/quick-login/admin@kasrt.local', 'GET');
$response = $authController->quickLogin($request, 'admin@kasrt.local');
assertTest(!Auth::check(), 'Superadmin DITOLAK login jika tidak menyertakan kode akses');
assertTest(str_contains($response->headers->get('Location') ?? '', 'login'), 'Diarahkan kembali ke /login');
assertTest(session()->has('error'), 'Muncul pesan error penolakan di session');

// Test 3: Quick Login Superadmin dengan KODE SALAH harus DITOLAK
echo "\n--- 3. PENGUJIAN PROTEKSI: QUICK LOGIN SUPERADMIN DENGAN KODE SALAH ---\n";
Auth::logout();
$request = Request::create('/quick-login/admin@kasrt.local?code=SALAH123', 'GET', ['code' => 'SALAH123']);
$response = $authController->quickLogin($request, 'admin@kasrt.local');
assertTest(!Auth::check(), 'Superadmin DITOLAK login jika kode akses salah');
assertTest(str_contains($response->headers->get('Location') ?? '', 'login'), 'Diarahkan kembali ke /login');

// Test 4: Quick Login Superadmin dengan KODE BENAR (RT2026) harus BERHASIL
echo "\n--- 4. PENGUJIAN SUKSES: QUICK LOGIN SUPERADMIN DENGAN KODE RT2026 ---\n";
Auth::logout();
$request = Request::create('/quick-login/admin@kasrt.local?code=RT2026', 'GET', ['code' => 'RT2026']);
$response = $authController->quickLogin($request, 'admin@kasrt.local');
assertTest(Auth::check() && Auth::user()->role === 'superadmin', 'Superadmin BERHASIL login dengan kode RT2026');
assertTest(str_contains($response->headers->get('Location') ?? '', 'dashboard'), 'Superadmin diarahkan ke /dashboard');

// Test 5: Quick Login Bendahara dengan KODE BENAR (123456) harus BERHASIL
echo "\n--- 5. PENGUJIAN SUKSES: QUICK LOGIN BENDAHARA DENGAN KODE 123456 ---\n";
Auth::logout();
$request = Request::create('/quick-login/bendahara@kasrt.local?code=123456', 'GET', ['code' => '123456']);
$response = $authController->quickLogin($request, 'bendahara@kasrt.local');
assertTest(Auth::check() && Auth::user()->role === 'bendahara', 'Bendahara BERHASIL login dengan kode PIN 123456');

// Test 6: Quick Login Ketua RT dengan KODE BENAR lowercase (rt2026) harus BERHASIL
echo "\n--- 6. PENGUJIAN SUKSES: QUICK LOGIN KETUA RT CASE-INSENSITIVE (rt2026) ---\n";
Auth::logout();
$request = Request::create('/quick-login/rt@kasrt.local?code=rt2026', 'GET', ['code' => 'rt2026']);
$response = $authController->quickLogin($request, 'rt@kasrt.local');
assertTest(Auth::check() && Auth::user()->role === 'rt', 'Ketua RT BERHASIL login dengan kode lowercase rt2026');

// Test 7: Regular Form Login Superadmin TANPA kode akses harus DITOLAK
echo "\n--- 7. PENGUJIAN REGULAR FORM: SUPERADMIN TANPA KODE AKSES ---\n";
Auth::logout();
$request = Request::create('/login', 'POST', [
    'email' => 'admin@kasrt.local',
    'password' => 'password',
    'access_code' => '',
]);
$response = $authController->login($request);
assertTest(!Auth::check(), 'Form Login Superadmin DITOLAK jika tanpa kode akses');

// Test 8: Regular Form Login Superadmin DENGAN kode akses RT2026 harus BERHASIL
echo "\n--- 8. PENGUJIAN REGULAR FORM: SUPERADMIN DENGAN KODE RT2026 ---\n";
Auth::logout();
$request = Request::create('/login', 'POST', [
    'email' => 'admin@kasrt.local',
    'password' => 'password',
    'access_code' => 'RT2026',
]);
$response = $authController->login($request);
assertTest(Auth::check() && Auth::user()->role === 'superadmin', 'Form Login Superadmin BERHASIL dengan password + kode akses RT2026');

// Test 9: Regular Form Login Warga TANPA kode akses harus BERHASIL
echo "\n--- 9. PENGUJIAN REGULAR FORM: WARGA TANPA KODE AKSES ---\n";
Auth::logout();
$request = Request::create('/login', 'POST', [
    'email' => 'budi@kasrt.local',
    'password' => 'password',
    'access_code' => '',
]);
$response = $authController->login($request);
assertTest(Auth::check() && Auth::user()->role === 'warga', 'Form Login Warga BERHASIL tanpa kode akses');

echo "\n====================================================================\n";
echo "HASIL PENGUJIAN KODE AKSES: $passCount BERHASIL, $failCount GAGAL\n";
echo "====================================================================\n";

if ($failCount > 0) {
    exit(1);
}
exit(0);
