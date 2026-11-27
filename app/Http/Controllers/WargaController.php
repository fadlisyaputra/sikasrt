<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\IotRfidCard;
use App\Models\IotTelemetryLog;
use App\Models\IotDevice;
use Carbon\Carbon;

class WargaController extends Controller
{
    public function index(Request $request)
    {
        $query = User::latest();

        if ($request->has('role') && in_array($request->role, ['superadmin', 'bendahara', 'rt', 'warga'])) {
            $query->where('role', $request->role);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('rfid_uid', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15);
        $portalDevice = IotDevice::where('type', 'portal_gate')->first();

        return view('warga.index', compact('users', 'portalDevice'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:superadmin,bendahara,rt,warga',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'rt_rw' => 'required|string|max:50',
            'status_warga' => 'required|in:tetap,kontrak',
            'rfid_uid' => 'nullable|string|max:50',
        ]);

        $normalizedUid = $this->normalizeUid($request->rfid_uid);

        // Jika UID diisi, periksa duplikasi di database
        if ($normalizedUid) {
            $existing = User::where('rfid_uid', $normalizedUid)->first();
            if ($existing) {
                return redirect()->back()->withInput()->with('error', 'UID e-KTP (' . $normalizedUid . ') sudah terdaftar atas nama warga: ' . $existing->name . ' (' . $existing->address . '). Gunakan e-KTP lain.');
            }
        }

        $newUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
            'address' => $request->address,
            'rt_rw' => $request->rt_rw,
            'status_warga' => $request->status_warga,
            'rfid_uid' => $normalizedUid,
        ]);

        if ($newUser->rfid_uid) {
            IotRfidCard::updateOrCreate(
                ['user_id' => $newUser->id],
                [
                    'rfid_uid' => $newUser->rfid_uid,
                    'card_label' => 'e-KTP ' . $newUser->name,
                    'status' => 'active',
                    'last_tapped_at' => Carbon::now(),
                    'last_location' => 'Pendaftaran Warga Baru',
                ]
            );

            IotTelemetryLog::create([
                'device_id' => 'WEB-WARGA-REG',
                'event_type' => 'ktp_registration',
                'rfid_uid' => $newUser->rfid_uid,
                'user_id' => $newUser->id,
                'status' => 'success',
                'message' => 'e-KTP berhasil direkam saat registrasi warga baru ' . $newUser->name . ' [UID: ' . $newUser->rfid_uid . ']',
                'logged_at' => Carbon::now(),
            ]);
        }

        // Hapus sesi scan jika ada
        Cache::forget('warga_scan_session');
        Cache::forget('warga_scan_result');

        return redirect()->back()->with('success', 'Data warga/pengguna berhasil ditambahkan' . ($newUser->rfid_uid ? ' beserta rekaman e-KTP!' : '.'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (!$request->has('status_warga') && $request->has('status_rumah')) {
            $request->merge(['status_warga' => $request->status_rumah]);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|in:superadmin,bendahara,rt,warga',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'rt_rw' => 'required|string|max:50',
            'status_warga' => 'required|in:tetap,kontrak',
            'rfid_uid' => 'nullable|string|max:50',
        ]);

        $normalizedUid = $this->normalizeUid($request->rfid_uid);

        if ($normalizedUid) {
            $existing = User::where('rfid_uid', $normalizedUid)->where('id', '!=', $id)->first();
            if ($existing) {
                return redirect()->back()->withInput()->with('error', 'UID e-KTP (' . $normalizedUid . ') sudah terdaftar atas nama: ' . $existing->name . '.');
            }
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'phone' => $request->phone,
            'address' => $request->address,
            'rt_rw' => $request->rt_rw,
            'status_warga' => $request->status_warga,
            'rfid_uid' => $normalizedUid,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        if ($user->rfid_uid) {
            IotRfidCard::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'rfid_uid' => $user->rfid_uid,
                    'card_label' => 'e-KTP ' . $user->name,
                    'status' => 'active',
                ]
            );
        } else {
            IotRfidCard::where('user_id', $user->id)->delete();
        }

        return redirect()->back()->with('success', 'Data warga/pengguna berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'Pengguna berhasil dihapus.');
    }

    /**
     * Memulai sesi rekam e-KTP via ESP32 reader di halaman warga
     */
    public function startScanSession(Request $request)
    {
        $sessionData = [
            'admin_id' => auth()->id(),
            'admin_name' => auth()->user()?->name,
            'started_at' => Carbon::now()->toIso8601String(),
            'expires_at' => Carbon::now()->addSeconds(60)->toIso8601String(),
        ];

        Cache::put('warga_scan_session', $sessionData, 60);
        Cache::forget('warga_scan_result');

        return response()->json([
            'status' => 'waiting',
            'message' => 'Reader ESP32 Siaga... Silakan tempelkan e-KTP calon warga sekarang.',
            'expires_in' => 60,
        ]);
    }

    /**
     * Polling cek hasil rekam e-KTP via ESP32
     */
    public function checkScanSession()
    {
        $result = Cache::get('warga_scan_result');

        if ($result) {
            Cache::forget('warga_scan_result');
            return response()->json([
                'status' => 'success',
                'uid' => $result['uid'] ?? '',
                'is_existing' => $result['is_existing'] ?? false,
                'existing_user_name' => $result['existing_user_name'] ?? null,
                'existing_user_address' => $result['existing_user_address'] ?? null,
                'message' => 'e-KTP Berhasil Terbaca: ' . ($result['uid'] ?? ''),
            ]);
        }

        $session = Cache::get('warga_scan_session');
        if (!$session) {
            return response()->json([
                'status' => 'expired',
                'message' => 'Sesi rekam e-KTP telah berakhir. Klik tombol Rekam untuk memulai lagi.',
            ]);
        }

        return response()->json([
            'status' => 'waiting',
            'message' => 'Menunggu e-KTP ditempelkan ke reader ESP32...',
        ]);
    }

    /**
     * Batalkan sesi rekam e-KTP
     */
    public function cancelScanSession()
    {
        Cache::forget('warga_scan_session');
        Cache::forget('warga_scan_result');
        return response()->json(['status' => 'cancelled']);
    }

    /**
     * Cek apakah UID sudah digunakan oleh warga lain (AJAX validasi instan)
     */
    public function checkUidAvailability(Request $request)
    {
        $uid = $this->normalizeUid($request->input('rfid_uid'));
        if (!$uid) {
            return response()->json(['available' => true, 'uid' => null]);
        }

        $cleanHex = preg_replace('/[^A-F0-9]/', '', strtoupper($uid));
        $existing = User::where(function($q) use ($uid, $cleanHex) {
            $q->where('rfid_uid', $uid)
              ->orWhereRaw("REPLACE(REPLACE(rfid_uid, ':', ''), '-', '') = ?", [$cleanHex]);
        });

        if ($request->has('user_id') && !empty($request->user_id)) {
            $existing->where('id', '!=', $request->user_id);
        }

        $owner = $existing->first();

        return response()->json([
            'available' => !$owner,
            'uid' => $uid,
            'owner_name' => $owner ? $owner->name : null,
            'owner_address' => $owner ? $owner->address : null,
            'message' => $owner ? 'UID sudah digunakan oleh: ' . $owner->name . ' (' . $owner->address . ')' : 'UID tersedia.',
        ]);
    }

    /**
     * Rekam / pasangkan e-KTP langsung ke akun warga yang sudah ada di tabel
     */
    public function pairKtpDirect(Request $request, $id)
    {
        $request->validate([
            'rfid_uid' => 'required|string|min:4|max:50',
        ]);

        $user = User::findOrFail($id);
        $uid = $this->normalizeUid($request->rfid_uid);

        if (!$uid) {
            return redirect()->back()->with('error', 'Format UID e-KTP tidak valid.');
        }

        // Cek apakah UID sudah digunakan orang lain
        $existing = User::where('rfid_uid', $uid)->where('id', '!=', $id)->first();
        if ($existing) {
            return redirect()->back()->with('error', 'UID e-KTP (' . $uid . ') sudah terdaftar milik warga lain: ' . $existing->name . '.');
        }

        $user->update(['rfid_uid' => $uid]);

        IotRfidCard::updateOrCreate(
            ['user_id' => $user->id],
            [
                'rfid_uid' => $uid,
                'card_label' => 'e-KTP ' . $user->name,
                'status' => 'active',
                'last_tapped_at' => Carbon::now(),
                'last_location' => 'Web Manajemen Warga',
            ]
        );

        IotTelemetryLog::create([
            'device_id' => 'WEB-WARGA-MANAJEMEN',
            'event_type' => 'ktp_registration',
            'rfid_uid' => $uid,
            'user_id' => $user->id,
            'status' => 'success',
            'message' => 'e-KTP berhasil direkam dan dipasangkan langsung untuk ' . $user->name . ' [UID: ' . $uid . ']',
            'logged_at' => Carbon::now(),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'uid' => $uid,
                'message' => 'e-KTP berhasil direkam untuk ' . $user->name . '!',
            ]);
        }

        return redirect()->back()->with('success', 'e-KTP berhasil direkam dan dipasangkan untuk ' . $user->name . ' [UID: ' . $uid . '].');
    }

    /**
     * Cabut / hapus e-KTP dari user tertentu di tabel
     */
    public function unpairKtpDirect($id)
    {
        $user = User::findOrFail($id);
        $oldUid = $user->rfid_uid;

        $user->update(['rfid_uid' => null]);
        IotRfidCard::where('user_id', $user->id)->delete();

        if ($oldUid) {
            IotTelemetryLog::create([
                'device_id' => 'WEB-WARGA-MANAJEMEN',
                'event_type' => 'ktp_unpaired',
                'rfid_uid' => $oldUid,
                'user_id' => $user->id,
                'status' => 'warning',
                'message' => 'e-KTP [UID: ' . $oldUid . '] telah dicabut dari akun ' . $user->name,
                'logged_at' => Carbon::now(),
            ]);
        }

        return redirect()->back()->with('success', 'e-KTP berhasil dicabut dari ' . $user->name . '.');
    }

    /**
     * Simulasi Tap e-KTP untuk pengujian live di web
     */
    public function simulateScan(Request $request)
    {
        $uid = $request->input('rfid_uid');
        if (!$uid) {
            // Generate UID acak jika kosong
            $bytes = array_map(function() { return sprintf('%02X', mt_rand(0, 255)); }, range(1, 4));
            $uid = implode(':', $bytes);
        } else {
            $uid = $this->normalizeUid($uid);
        }

        $esp32Ctrl = app(\App\Http\Controllers\Api\Esp32Controller::class);
        $req = Request::create('/api/esp32/rfid-tap', 'POST', [
            'device_id' => 'ESP32-GATE-01',
            'rfid_uid' => $uid,
        ]);

        $res = $esp32Ctrl->rfidTap($req);
        return response()->json(json_decode($res->getContent(), true));
    }

    /**
     * Normalisasi format UID heksadesimal (misal 93b472a1 -> 93:B4:72:A1)
     */
    private function normalizeUid($rawUid)
    {
        if (empty($rawUid)) {
            return null;
        }

        $clean = strtoupper(preg_replace('/[^a-fA-F0-9]/', '', (string)$rawUid));
        if (strlen($clean) < 4 || strlen($clean) > 20) {
            return strtoupper(trim((string)$rawUid));
        }

        $parts = str_split($clean, 2);
        return implode(':', $parts);
    }
}

