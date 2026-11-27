<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IotDevice;
use App\Models\IotRfidCard;
use App\Models\IotTelemetryLog;
use App\Models\IotCommand;
use App\Models\IotEmergencyAlert;
use App\Models\User;
use App\Models\IuranType;
use App\Models\IuranPayment;
use App\Models\CashTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class Esp32Controller extends Controller
{
    /**
     * Authenticate device by API Key or device_id
     */
    protected function authenticateDevice(Request $request)
    {
        $apiKey = $request->header('X-ESP32-KEY') ?? $request->input('api_key');
        $deviceId = $request->input('device_id');

        if (!$deviceId) {
            return null;
        }

        $query = IotDevice::where('device_id', $deviceId);
        if ($apiKey) {
            $query->where('api_key', $apiKey);
        }

        return $query->first();
    }

    /**
     * Heartbeat & Command Polling
     * POST /api/esp32/heartbeat
     */
    public function heartbeat(Request $request)
    {
        $request->validate(['device_id' => 'required|string']);

        $device = IotDevice::where('device_id', $request->device_id)->first();
        if (!$device) {
            return response()->json([
                'status' => 'error',
                'message' => 'Perangkat belum terdaftar di SIKAS-RT'
            ], 404);
        }

        $device->update([
            'status' => 'online',
            'last_seen_at' => Carbon::now(),
            'ip_address' => $request->ip_address ?? $request->ip(),
            'rssi_signal' => $request->rssi ?? $device->rssi_signal,
            'firmware_version' => $request->firmware_version ?? $device->firmware_version,
        ]);

        // Check for pending remote commands (e.g. open gate, siren)
        $pendingCommands = IotCommand::where('device_id', $device->device_id)
            ->where('status', 'pending')
            ->orderBy('id', 'asc')
            ->get();

        $commandsToExecute = [];
        foreach ($pendingCommands as $cmd) {
            $commandsToExecute[] = [
                'id' => $cmd->id,
                'command' => $cmd->command,
                'payload' => $cmd->payload,
            ];
            $cmd->update([
                'status' => 'executed',
                'executed_at' => Carbon::now(),
            ]);
        }

        // Active emergency count
        $activeEmergency = IotEmergencyAlert::where('status', 'active')->count() > 0;

        return response()->json([
            'status' => 'ok',
            'device_id' => $device->device_id,
            'server_time' => Carbon::now()->toIso8601String(),
            'emergency_active' => $activeEmergency,
            'relay_state' => (bool)$device->relay_state,
            'commands' => $commandsToExecute,
        ]);
    }

    /**
     * RFID/NFC Card Tap Processing (Gate Barrier / e-KTP Registration / Tap-to-Pay / Pos Ronda)
     * POST /api/esp32/rfid-tap
     */
    public function rfidTap(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string',
            'rfid_uid' => 'required|string',
        ]);

        $device = IotDevice::where('device_id', $request->device_id)->first();
        $rawUid = strtoupper(trim($request->rfid_uid));
        
        // Normalisasi format UID: bersihkan tanda pemisah untuk pencocokan fleksibel
        $cleanHex = preg_replace('/[^A-F0-9]/', '', $rawUid);
        $formattedUid = implode(':', str_split($cleanHex, 2));
        $uid = !empty($formattedUid) ? $formattedUid : $rawUid;

        $now = Carbon::now();

        // =========================================================================
        // 0. CEK MODE PAIRING / REGISTRASI e-KTP VIA ESP32 READER
        // =========================================================================
        // A. Cek Sesi Rekam e-KTP dari Halaman Manajemen Warga (Registrasi / Tambah Warga Baru)
        $wargaScanSession = Cache::get('warga_scan_session');
        if ($wargaScanSession) {
            $existingUser = User::where(function($q) use ($uid, $cleanHex) {
                $q->where('rfid_uid', $uid)
                  ->orWhereRaw("REPLACE(REPLACE(rfid_uid, ':', ''), '-', '') = ?", [$cleanHex]);
            })->first();

            Cache::put('warga_scan_result', [
                'status' => 'success',
                'uid' => $uid,
                'is_existing' => (bool)$existingUser,
                'existing_user_name' => $existingUser ? $existingUser->name : null,
                'existing_user_address' => $existingUser ? $existingUser->address : null,
                'scanned_at' => $now->toIso8601String(),
            ], 60);

            Cache::forget('warga_scan_session');

            $msg = $existingUser 
                ? 'e-KTP TERBACA (SUDAH TERDAFTAR MILIK ' . strtoupper($existingUser->name) . ')'
                : 'e-KTP BARU BERHASIL DIREKAM: ' . $uid;

            return response()->json([
                'authorized' => true,
                'status' => 'scanned',
                'uid' => $uid,
                'is_existing' => (bool)$existingUser,
                'existing_user' => $existingUser ? $existingUser->name : null,
                'message' => $msg,
                'action' => 'buzzer_success',
                'display' => [
                    'line1' => 'REKAM e-KTP OK',
                    'line2' => substr($uid, 0, 16),
                    'line3' => $existingUser ? 'SUDAH TERDAFTAR' : 'SIAP DAFTARKAN',
                ],
            ], 200);
        }

        // B. Cek Sesi Pairing Akun Warga Mandiri (ktp_pairing_session)
        $pairingSession = Cache::get('ktp_pairing_session');
        if ($pairingSession && isset($pairingSession['user_id'])) {
            $targetUser = User::find($pairingSession['user_id']);
            if ($targetUser) {
                // Periksa apakah UID KTP ini sudah terdaftar milik warga lain
                $existingOtherUser = User::where(function($q) use ($uid, $cleanHex) {
                    $q->where('rfid_uid', $uid)
                      ->orWhereRaw("REPLACE(REPLACE(rfid_uid, ':', ''), '-', '') = ?", [$cleanHex]);
                })->where('id', '!=', $targetUser->id)->first();

                $existingOtherCard = IotRfidCard::where(function($q) use ($uid, $cleanHex) {
                    $q->where('rfid_uid', $uid)
                      ->orWhereRaw("REPLACE(REPLACE(rfid_uid, ':', ''), '-', '') = ?", [$cleanHex]);
                })->where('user_id', '!=', $targetUser->id)->first();

                if ($existingOtherUser || $existingOtherCard) {
                    $ownerName = $existingOtherUser ? $existingOtherUser->name : $existingOtherCard->user->name;
                    return response()->json([
                        'authorized' => false,
                        'status' => 'denied',
                        'message' => 'REGISTRASI DITOLAK: e-KTP INI SUDAH TERDAFTAR MILIK ' . strtoupper($ownerName),
                        'action' => 'buzzer_error',
                        'display' => [
                            'line1' => 'REGISTRASI GAGAL',
                            'line2' => 'KTP MILIK WARGA',
                            'line3' => substr($ownerName, 0, 16),
                        ],
                    ], 200);
                }

                // Daftarkan UID ke user target
                $targetUser->update(['rfid_uid' => $uid]);
                IotRfidCard::updateOrCreate(
                    ['user_id' => $targetUser->id],
                    [
                        'rfid_uid' => $uid,
                        'card_label' => 'e-KTP ' . $targetUser->name,
                        'status' => 'active',
                        'last_tapped_at' => $now,
                        'last_location' => $device ? $device->name : 'Reader Pendaftaran',
                    ]
                );

                // Catat log telemetri
                IotTelemetryLog::create([
                    'iot_device_id' => $device?->id,
                    'device_id' => $request->device_id,
                    'event_type' => 'ktp_registration',
                    'rfid_uid' => $uid,
                    'user_id' => $targetUser->id,
                    'status' => 'success',
                    'message' => 'e-KTP berhasil dipasangkan untuk ' . $targetUser->name . ' [UID: ' . $uid . '] via ' . ($device ? $device->name : 'ESP32'),
                    'logged_at' => $now,
                ]);

                // Simpan hasil pairing agar browser user mendeteksi sukses secara real-time
                Cache::put('ktp_pairing_result_' . $targetUser->id, [
                    'status' => 'success',
                    'uid' => $uid,
                    'user_name' => $targetUser->name,
                    'registered_at' => $now->toIso8601String(),
                ], 60);

                // Hapus sesi pairing aktif
                Cache::forget('ktp_pairing_session');

                return response()->json([
                    'authorized' => true,
                    'status' => 'registered',
                    'user_name' => $targetUser->name,
                    'uid' => $uid,
                    'message' => 'e-KTP BERHASIL DIDAFTARKAN UNTUK ' . strtoupper($targetUser->name),
                    'action' => 'buzzer_success',
                    'display' => [
                        'line1' => 'REGISTRASI SUKSES',
                        'line2' => substr($targetUser->name, 0, 16),
                        'line3' => 'KTP TERDAFTAR',
                    ],
                ], 200);
            }
        }

        // =========================================================================
        // 1. CARI USER BERDASARKAN UID KTP / RFID
        // =========================================================================
        $rfidCard = IotRfidCard::with('user')->where(function($q) use ($uid, $cleanHex) {
            $q->where('rfid_uid', $uid)
              ->orWhereRaw("REPLACE(REPLACE(rfid_uid, ':', ''), '-', '') = ?", [$cleanHex]);
        })->first();

        $user = $rfidCard ? $rfidCard->user : User::where(function($q) use ($uid, $cleanHex) {
            $q->where('rfid_uid', $uid)
              ->orWhereRaw("REPLACE(REPLACE(rfid_uid, ':', ''), '-', '') = ?", [$cleanHex]);
        })->first();

        // =========================================================================
        // 2. PROTEKSI: JIKA KTP BELUM TERDAFTAR DI APK WEB INI -> AKSES DITOLAK!
        // =========================================================================
        if (!$user) {
            IotTelemetryLog::create([
                'iot_device_id' => $device?->id,
                'device_id' => $request->device_id,
                'event_type' => 'gate_access',
                'rfid_uid' => $uid,
                'status' => 'denied',
                'message' => 'Akses Portal DITOLAK: KTP belum terdaftar di SIKAS-RT [UID: ' . $uid . '] di ' . ($device ? $device->name : 'Portal Utama'),
                'logged_at' => $now,
            ]);

            return response()->json([
                'authorized' => false,
                'status' => 'denied',
                'access' => 'rejected',
                'relay' => 'off',
                'message' => 'AKSES DITOLAK! KTP BELUM TERDAFTAR DI SIKAS-RT',
                'display' => [
                    'line1' => 'PORTAL PERUMAHAN',
                    'line2' => 'AKSES DITOLAK!',
                    'line3' => 'KTP BLUM DAFTAR',
                ],
                'action' => 'buzzer_error',
            ], 200);
        }

        // =========================================================================
        // 3. PROTEKSI: JIKA STATUS KARTU DIBLOKIR ATAU DINONAKTIFKAN
        // =========================================================================
        if ($rfidCard && in_array($rfidCard->status, ['blocked', 'lost'])) {
            IotTelemetryLog::create([
                'iot_device_id' => $device?->id,
                'device_id' => $request->device_id,
                'event_type' => 'gate_access',
                'rfid_uid' => $uid,
                'user_id' => $user->id,
                'status' => 'denied',
                'message' => 'Akses Portal DITOLAK: Kartu e-KTP milik ' . $user->name . ' berstatus ' . strtoupper($rfidCard->status),
                'logged_at' => $now,
            ]);

            return response()->json([
                'authorized' => false,
                'status' => 'blocked',
                'access' => 'rejected',
                'relay' => 'off',
                'message' => 'AKSES DITOLAK! KARTU DINONAKTIFKAN / DIBLOKIR',
                'display' => [
                    'line1' => 'PORTAL PERUMAHAN',
                    'line2' => 'AKSES DITOLAK!',
                    'line3' => 'KARTU DIBLOKIR',
                ],
                'action' => 'buzzer_error',
            ], 200);
        }

        // Perbarui data tap terakhir
        if ($rfidCard) {
            $rfidCard->update([
                'last_tapped_at' => $now,
                'last_location' => $device ? $device->name : 'Portal Utama',
            ]);
        }

        // =========================================================================
        // 4. PEMPROSESAN AKSI BERDASARKAN MODE / TIPE PERANGKAT
        // =========================================================================

        // Case A: Tap-to-Pay Iuran Kas RT (e.g. Di Pos Ronda / Smart Kasir RT)
        if ($request->input('mode') === 'pay' || ($device && $device->type === 'smart_pos')) {
            $iuranWajib = IuranType::where('is_active', true)->where('period_type', 'monthly')->first();
            $amount = $request->input('amount', $iuranWajib ? $iuranWajib->amount : 50000);
            $month = $request->input('month', (int)$now->format('n'));
            $year = $request->input('year', (int)$now->format('Y'));

            $payment = IuranPayment::create([
                'user_id' => $user->id,
                'iuran_type_id' => $iuranWajib ? $iuranWajib->id : 1,
                'period_month' => $month,
                'period_year' => $year,
                'amount' => $amount,
                'payment_method' => 'iot_rfid',
                'status' => 'approved',
                'notes' => 'Pembayaran Tap RFID ESP32 [' . ($device ? $device->name : 'Pos RT') . ']',
                'verified_by' => $user->id,
                'verified_at' => $now,
            ]);

            CashTransaction::create([
                'type' => 'pemasukan',
                'category' => 'Iuran Warga (IoT RFID)',
                'amount' => $amount,
                'transaction_date' => $now->toDateString(),
                'description' => 'Iuran ' . ($iuranWajib ? $iuranWajib->name : 'Kas') . ' - ' . $user->name . ' (Tap RFID)',
                'iuran_payment_id' => $payment->id,
                'created_by' => $user->id,
            ]);

            IotTelemetryLog::create([
                'iot_device_id' => $device?->id,
                'device_id' => $request->device_id,
                'event_type' => 'kas_drop',
                'rfid_uid' => $uid,
                'user_id' => $user->id,
                'value' => $amount,
                'unit' => 'IDR',
                'status' => 'success',
                'message' => 'Iuran Rp ' . number_format($amount, 0, ',', '.') . ' berhasil dibayar oleh ' . $user->name,
                'logged_at' => $now,
            ]);

            return response()->json([
                'authorized' => true,
                'status' => 'paid',
                'user_name' => $user->name,
                'amount' => $amount,
                'message' => 'Iuran Kas Lunas!',
                'action' => 'buzzer_success',
                'display' => [
                    'line1' => 'IURAN LUNAS!',
                    'line2' => substr($user->name, 0, 16),
                    'line3' => 'Rp ' . number_format($amount, 0, ',', '.'),
                ],
            ]);
        }

        // Case B: Absensi Pos Ronda Malam
        if ($request->input('mode') === 'ronda' || ($device && $device->type === 'pos_ronda')) {
            IotTelemetryLog::create([
                'iot_device_id' => $device?->id,
                'device_id' => $request->device_id,
                'event_type' => 'pos_ronda',
                'rfid_uid' => $uid,
                'user_id' => $user->id,
                'status' => 'success',
                'message' => 'Absensi Ronda Malam: ' . $user->name . ' tercatat pada ' . $now->format('H:i:s'),
                'logged_at' => $now,
            ]);

            return response()->json([
                'authorized' => true,
                'status' => 'recorded',
                'user_name' => $user->name,
                'message' => 'Absensi Tercatat!',
                'action' => 'buzzer_beep',
                'display' => [
                    'line1' => 'ABSENSI RONDA',
                    'line2' => substr($user->name, 0, 16),
                    'line3' => 'PUKUL: ' . $now->format('H:i:s'),
                ],
            ]);
        }

        // Case C: Portal Gate Barrier (Default / Gerbang Utama Perumahan)
        if ($device) {
            $device->update([
                'relay_state' => true,
                'status' => 'online',
                'last_seen_at' => $now,
            ]);
        }

        IotTelemetryLog::create([
            'iot_device_id' => $device?->id,
            'device_id' => $request->device_id,
            'event_type' => 'gate_access',
            'rfid_uid' => $uid,
            'user_id' => $user->id,
            'status' => 'success',
            'message' => 'Portal dibuka untuk ' . $user->name . ' (' . ($user->address ?? 'Warga RT') . ')',
            'logged_at' => $now,
        ]);

        return response()->json([
            'authorized' => true,
            'status' => 'granted',
            'access' => 'approved',
            'user_name' => $user->name,
            'address' => $user->address ?? 'Warga RT',
            'role' => $user->role,
            'message' => 'Selamat Datang, ' . $user->name . '! Portal Dibuka.',
            'action' => 'open_gate',
            'relay' => 'on',
            'gate_duration_ms' => 5000,
            'display' => [
                'line1' => 'PORTAL TERBUKA',
                'line2' => substr($user->name, 0, 16),
                'line3' => 'SILAKAN MASUK',
            ],
        ], 200);
    }

    /**
     * Smart Cash Box / Physical Cash Drop Detection
     * POST /api/esp32/kas-drop
     */
    public function kasDrop(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string',
            'amount' => 'nullable|numeric|min:1000',
        ]);

        $device = IotDevice::where('device_id', $request->device_id)->first();
        $amount = $request->amount ?? 10000;
        $now = Carbon::now();

        $kas = CashTransaction::create([
            'type' => 'pemasukan',
            'category' => 'Kotak Kas Mandiri / Infaq Pos',
            'amount' => $amount,
            'transaction_date' => $now->toDateString(),
            'description' => 'Drop Fisik Uang Kas RT via Smart Box [' . ($device ? $device->name : 'ESP32') . ']',
            'created_by' => 1, // Superadmin/System
        ]);

        IotTelemetryLog::create([
            'iot_device_id' => $device?->id,
            'device_id' => $request->device_id,
            'event_type' => 'kas_drop',
            'value' => $amount,
            'unit' => 'IDR',
            'status' => 'success',
            'message' => 'Pemasukan uang fisik terdeteksi: Rp ' . number_format($amount, 0, ',', '.'),
            'logged_at' => $now,
        ]);

        // Calculate latest total balance
        $totalMasuk = CashTransaction::where('type', 'pemasukan')->sum('amount');
        $totalKeluar = CashTransaction::where('type', 'pengeluaran')->sum('amount');
        $saldo = $totalMasuk - $totalKeluar;

        return response()->json([
            'status' => 'ok',
            'message' => 'Kas Mandiri Berhasil Dicatat',
            'recorded_amount' => $amount,
            'current_balance' => $saldo,
            'display' => [
                'line1' => 'KAS DITERIMA',
                'line2' => 'Rp ' . number_format($amount, 0, ',', '.'),
                'line3' => 'TERIMA KASIH',
            ],
        ]);
    }

    /**
     * Emergency Panic Button Trigger
     * POST /api/esp32/panic-alert
     */
    public function panicAlert(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string',
            'alert_type' => 'nullable|string',
            'location' => 'nullable|string',
        ]);

        $device = IotDevice::where('device_id', $request->device_id)->first();
        $now = Carbon::now();

        if ($device) {
            $device->update(['status' => 'alert', 'last_seen_at' => $now]);
        }

        $alert = IotEmergencyAlert::create([
            'device_id' => $request->device_id,
            'alert_type' => $request->alert_type ?? 'panic_button',
            'location' => $request->location ?? ($device ? $device->location : 'Pos Keamanan RT'),
            'description' => $request->description ?? 'Tombol Darurat Pos Keamanan / Warga Ditekan (ESP32 Signal)',
            'status' => 'active',
        ]);

        IotTelemetryLog::create([
            'iot_device_id' => $device?->id,
            'device_id' => $request->device_id,
            'event_type' => 'panic_alert',
            'status' => 'emergency',
            'message' => 'ALARM DARURAT DIAKTIFKAN DI ' . $alert->location,
            'logged_at' => $now,
        ]);

        return response()->json([
            'status' => 'emergency_active',
            'alert_id' => $alert->id,
            'message' => 'Peringatan Darurat Telah Disebarkan ke Seluruh Dashboard RT',
            'siren_action' => 'sound_siren_continuous',
        ]);
    }

    /**
     * Environmental / Sensor Telemetry Upload
     * POST /api/esp32/sensor-data
     */
    public function storeSensorData(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string',
            'sensor_type' => 'required|string',
            'value' => 'required|numeric',
            'unit' => 'required|string',
        ]);

        $device = IotDevice::where('device_id', $request->device_id)->first();
        $now = Carbon::now();

        if ($device) {
            $device->update(['status' => 'online', 'last_seen_at' => $now]);
        }

        SensorLog::create([
            'iot_device_id' => $device?->id,
            'sensor_type' => $request->sensor_type,
            'value' => $request->value,
            'unit' => $request->unit,
            'raw_data' => $request->raw_data,
            'logged_at' => $now,
        ]);

        IotTelemetryLog::create([
            'iot_device_id' => $device?->id,
            'device_id' => $request->device_id,
            'event_type' => $request->sensor_type,
            'value' => $request->value,
            'unit' => $request->unit,
            'status' => 'success',
            'message' => 'Sensor ' . $request->sensor_type . ': ' . $request->value . ' ' . $request->unit,
            'logged_at' => $now,
        ]);

        return response()->json([
            'status' => 'ok',
            'message' => 'Data sensor tersimpan'
        ]);
    }

    /**
     * Get Device Status and Control Commands
     * GET /api/esp32/status
     */
    public function allDeviceStatus()
    {
        $devices = IotDevice::select('device_id', 'name', 'type', 'location', 'status', 'rssi_signal', 'last_seen_at')->get();
        $activeAlerts = IotEmergencyAlert::where('status', 'active')->get();

        return response()->json([
            'status' => 'ok',
            'devices' => $devices,
            'active_alerts' => $activeAlerts,
            'online_count' => IotDevice::where('status', 'online')->count(),
            'total_count' => IotDevice::count(),
        ]);
    }
}
