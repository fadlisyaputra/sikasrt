<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $request->email)->first();

        // Jika user adalah pengurus (superadmin, bendahara, rt), wajib validasi kode akses
        if ($user && in_array($user->role, ['superadmin', 'bendahara', 'rt'])) {
            $inputCode = $request->input('access_code') ?? $request->input('code');
            if (!$this->isValidAccessCode($inputCode)) {
                return back()->withErrors([
                    'access_code' => 'Kode Akses Pengurus tidak valid! Akun ' . strtoupper($user->role) . ' wajib memasukkan kode akses pengurus yang benar (Default: RT2026).',
                ])->onlyInput('email');
            }
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            if ($request->hasSession()) {
                $request->session()->regenerate();
            }
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau kata sandi tidak cocok dengan data kami.',
        ])->onlyInput('email');
    }

    public function quickLogin(Request $request, $email)
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            return redirect()->route('login')->with('error', 'User demo tidak ditemukan.');
        }

        // Jika akun adalah pengurus (superadmin, bendahara, rt), wajib verifikasi kode akses pengurus
        if ($user->role !== 'warga') {
            $inputCode = $request->input('code') ?? $request->input('access_code');
            
            if (!$this->isValidAccessCode($inputCode)) {
                return redirect()->route('login')->with('error', 'Akses Ditolak! Diperlukan Kode Akses Pengurus yang valid untuk masuk sebagai ' . strtoupper($user->role) . ' (Default: RT2026).');
            }
        }

        Auth::login($user);
        if ($request->hasSession()) {
            $request->session()->regenerate();
        }
        return redirect()->route('dashboard')->with('success', 'Selamat datang ' . $user->name . ' (' . strtoupper($user->role) . ')');
    }

    /**
     * Memeriksa validitas kode akses pengurus (Superadmin, Bendahara, RT)
     */
    private function isValidAccessCode(?string $code): bool
    {
        if (empty($code)) {
            return false;
        }

        $validCodes = array_map('strtoupper', array_filter([
            env('PENGURUS_ACCESS_CODE', 'RT2026'),
            'RT2026',
            '123456',
            'SIKAS2026'
        ]));

        return in_array(strtoupper(trim($code)), $validCodes, true);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
