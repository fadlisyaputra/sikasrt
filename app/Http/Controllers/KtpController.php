<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\IotRfidCard;
use App\Models\IotTelemetryLog;
use App\Models\IotDevice;
use Carbon\Carbon;

class KtpController extends Controller
{
    /**
     * Halaman e-KTP & Manajemen Akses Portal Warga
     */
    public function index()
    {
        $user = Auth::user();

        // Ambil data kartu KTP terdaftar milik user
        $ktpCard = IotRfidCard::where('user_id', $user->id)->first();
        $registeredUid = $user->rfid_uid ?? ($ktpCard ? $ktpCard->rfid_uid : null);

        // Ambil riwayat akses portal perumahan untuk user ini
        $accessLogs = IotTelemetryLog::where(function($q) use ($user, $registeredUid) {
                $q->where('user_id', $user->id);
                if ($registeredUid) {
                    $q->orWhere('rfid_uid', $registeredUid);
                }
            })
            ->where('event_type', 'gate_access')
            ->latest('logged_at')
            ->take(15)
            ->get();

        // Perangkat portal aktif
        $portalDevice = IotDevice::where('type', 'portal_gate')->first();

        // Cek apakah ada sesi pairing aktif untuk user ini
        $pairingSession = Cache::get('ktp_pairing_session');
        $isPairingActive = $pairingSession && ($pairingSession['user_id'] ?? null) === $user->id;

        return view('ktp.index', compact(
            'user',
            'ktpCard',
            'registeredUid',
            'accessLogs',
            'portalDevice',
            'isPairingActive'
        ));
    }

    /**
     * Mendaftarkan UID KTP ke user yang sedang login (via Web NFC atau Form Input)
     */
    public function registerUid(Request $request)
    {
        $request->validate([
            'rfid_uid' => 'required|string|min:4|max:30',
            'card_label' => 'nullable|string|max:100',
        ]);

        $user = Auth::user();
        $uid = $this->normalizeUid($request->rfid_uid);

        if (!$uid) {
            return redirect()->back()->with('error', 'Format UID KTP tidak valid. Gunakan format heksadesimal (misal: 93:B4:72:A1 atau 93B472A1).');
        }

        // Cek apakah UID sudah didaftarkan oleh user lain
        $existingOtherUser = User::where('rfid_uid', $uid)->where('id', '!=', $user->id)->first();
        $existingOtherCard = IotRfidCard::where('rfid_uid', $uid)->where('user_id', '!=', $user->id)->first();

        if ($existingOtherUser || $existingOtherCard) {
            $owner = $existingOtherUser ? $existingOtherUser->name : $existingOtherCard->user->name;
            return redirect()->back()->with('error', 'UID KTP (' . $uid . ') sudah terdaftar atas nama warga lain: ' . $owner . '. Harap hubungi Pengurus RT jika ini adalah KTP Anda.');
        }

        // Simpan UID pada data User
        $user->update(['rfid_uid' => $uid]);

        // Simpan / update pada tabel iot_rfid_cards
        IotRfidCard::updateOrCreate(
            ['user_id' => $user->id],
            [
                'rfid_uid' => $uid,
                'card_label' => $request->card_label ?? ('e-KTP ' . $user->name),
                'status' => 'active',
                'last_tapped_at' => Carbon::now(),
                'last_location' => 'Web App SIKAS-RT',
            ]
        );

        // Catat di log telemetri
        IotTelemetryLog::create([
            'device_id' => 'WEB-PORTAL-KTP',
            'event_type' => 'ktp_registration',
            'rfid_uid' => $uid,
            'user_id' => $user->id,
            'status' => 'success',
            'message' => 'e-KTP berhasil didaftarkan untuk ' . $user->name . ' [UID: ' . $uid . ']',
            'logged_at' => Carbon::now(),
        ]);

        // Bersihkan sesi pairing jika ada
        Cache::forget('ktp_pairing_session');

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'uid' => $uid,
                'message' => 'e-KTP berhasil didaftarkan untuk akun ' . $user->name . '!',
            ]);
        }

        return redirect()->back()->with('success', 'e-KTP berhasil didaftarkan dengan UID ' . $uid . '. Anda sekarang dapat membuka portal perumahan dengan men-scan KTP ini!');
    }

    /**
     * Memulai Mode Pairing Scan KTP via ESP32 Reader
     */
    public function startPairing(Request $request)
    {
        $user = Auth::user();

        // Simpan sesi pairing di Cache selama 90 detik
        $sessionData = [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'started_at' => Carbon::now()->toIso8601String(),
            'expires_at' => Carbon::now()->addSeconds(90)->toIso8601String(),
        ];

        Cache::put('ktp_pairing_session', $sessionData, 90);
        Cache::forget('ktp_pairing_result_' . $user->id);

        return response()->json([
            'status' => 'waiting',
            'message' => 'Mode Scan ESP32 Aktif. Tempelkan e-KTP Anda ke alat ESP32 sekarang.',
            'expires_in' => 90,
            'user_name' => $user->name,
        ]);
    }

    /**
     * Cek status pairing scan via ESP32 (Polling AJAX dari browser)
     */
    public function checkPairingStatus()
    {
        $user = Auth::user();
        $result = Cache::get('ktp_pairing_result_' . $user->id);

        if ($result) {
            // Bersihkan hasil pairing setelah diambil
            Cache::forget('ktp_pairing_result_' . $user->id);
            return response()->json([
                'status' => 'success',
                'uid' => $result['uid'] ?? '',
                'user_name' => $result['user_name'] ?? $user->name,
                'message' => 'e-KTP berhasil terdeteksi dan terdaftar untuk ' . $user->name . '!',
            ]);
        }

        $session = Cache::get('ktp_pairing_session');
        if (!$session || ($session['user_id'] ?? null) !== $user->id) {
            return response()->json([
                'status' => 'expired',
                'message' => 'Waktu scan via ESP32 telah habis atau dibatalkan.',
            ]);
        }

        return response()->json([
            'status' => 'waiting',
            'message' => 'Menunggu e-KTP ditempelkan ke reader ESP32...',
        ]);
    }

    /**
     * Batalkan Sesi Pairing
     */
    public function cancelPairing()
    {
        Cache::forget('ktp_pairing_session');
        return response()->json(['status' => 'cancelled']);
    }

    /**
     * Hapus / Unpair UID KTP dari akun user
     */
    public function unpairUid()
    {
        $user = Auth::user();
        $oldUid = $user->rfid_uid;

        $user->update(['rfid_uid' => null]);
        IotRfidCard::where('user_id', $user->id)->delete();

        if ($oldUid) {
            IotTelemetryLog::create([
                'device_id' => 'WEB-PORTAL-KTP',
                'event_type' => 'ktp_unpaired',
                'rfid_uid' => $oldUid,
                'user_id' => $user->id,
                'status' => 'warning',
                'message' => 'e-KTP [UID: ' . $oldUid . '] telah dicabut/dihapus dari akun ' . $user->name,
                'logged_at' => Carbon::now(),
            ]);
        }

        return redirect()->back()->with('success', 'e-KTP berhasil dicabut dari akun Anda.');
    }

    /**
     * Simulasi Tap KTP ke Portal Perumahan dari Web App
     */
    public function simulateTap(Request $request)
    {
        $user = Auth::user();
        $uid = $request->input('rfid_uid');

        // Jika tidak mengisi UID, gunakan UID user yang login
        if (!$uid) {
            $uid = $user->rfid_uid;
        } else {
            $uid = $this->normalizeUid($uid);
        }

        $device = IotDevice::where('type', 'portal_gate')->first();
        $deviceId = $device ? $device->device_id : 'ESP32-GATE-01';

        // Panggil logika verifikasi melalui controller internal
        $esp32Ctrl = app(\App\Http\Controllers\Api\Esp32Controller::class);
        $req = Request::create('/api/esp32/rfid-tap', 'POST', [
            'device_id' => $deviceId,
            'rfid_uid' => $uid,
            'mode' => 'gate',
        ]);

        $res = $esp32Ctrl->rfidTap($req);
        $data = json_decode($res->getContent(), true);

        return response()->json($data);
    }

    /**
     * Normalisasi format UID (mengubah 93b472a1 atau 93-B4-72-A1 menjadi 93:B4:72:A1)
     */
    private function normalizeUid($rawUid)
    {
        $clean = strtoupper(preg_replace('/[^a-fA-F0-9]/', '', $rawUid));
        if (strlen($clean) < 4 || strlen($clean) > 20) {
            return null;
        }

        // Pecah per 2 digit heksadesimal dengan titik dua
        $parts = str_split($clean, 2);
        return implode(':', $parts);
    }
}
