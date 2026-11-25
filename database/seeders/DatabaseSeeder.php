<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\IuranType;
use App\Models\IuranPayment;
use App\Models\CashTransaction;
use App\Models\Announcement;
use App\Models\IotDevice;
use App\Models\IotRfidCard;
use App\Models\IotTelemetryLog;
use App\Models\IotEmergencyAlert;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Demo Users for each of the 4 roles
        $superadmin = User::create([
            'name' => 'Superadmin System',
            'email' => 'admin@kasrt.local',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
            'phone' => '081234567890',
            'address' => 'Blok A1 No. 01',
            'rt_rw' => 'RT 01 / RW 05',
            'status_warga' => 'tetap',
            'rfid_uid' => 'AA:11:BB:22',
        ]);

        $bendahara = User::create([
            'name' => 'Hj. Ratna (Bendahara RT)',
            'email' => 'bendahara@kasrt.local',
            'password' => Hash::make('password'),
            'role' => 'bendahara',
            'phone' => '081298765432',
            'address' => 'Blok A2 No. 05',
            'rt_rw' => 'RT 01 / RW 05',
            'status_warga' => 'tetap',
            'rfid_uid' => 'BB:22:CC:33',
        ]);

        $rt = User::create([
            'name' => 'Bpk. H. Bambang (Ketua RT)',
            'email' => 'rt@kasrt.local',
            'password' => Hash::make('password'),
            'role' => 'rt',
            'phone' => '081311223344',
            'address' => 'Blok B1 No. 10',
            'rt_rw' => 'RT 01 / RW 05',
            'status_warga' => 'tetap',
            'rfid_uid' => 'CC:33:DD:44',
        ]);

        $warga1 = User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@kasrt.local',
            'password' => Hash::make('password'),
            'role' => 'warga',
            'phone' => '081555666777',
            'address' => 'Blok C1 No. 12',
            'rt_rw' => 'RT 01 / RW 05',
            'status_warga' => 'tetap',
            'rfid_uid' => '93:B4:72:A1',
        ]);

        $warga2 = User::create([
            'name' => 'Siti Nurhaliza',
            'email' => 'siti@kasrt.local',
            'password' => Hash::make('password'),
            'role' => 'warga',
            'phone' => '081888999000',
            'address' => 'Blok C2 No. 08',
            'rt_rw' => 'RT 01 / RW 05',
            'status_warga' => 'kontrak',
            'rfid_uid' => '4A:8C:31:E9',
        ]);

        $warga3 = User::create([
            'name' => 'Joko Widodo',
            'email' => 'joko@kasrt.local',
            'password' => Hash::make('password'),
            'role' => 'warga',
            'phone' => '081777888999',
            'address' => 'Blok D1 No. 03',
            'rt_rw' => 'RT 01 / RW 05',
            'status_warga' => 'tetap',
            'rfid_uid' => 'F2:1D:6E:B8',
        ]);

        // 2. Create Master Iuran Types
        $iuranWajib = IuranType::create([
            'name' => 'Iuran Keamanan & Kebersihan',
            'amount' => 50000,
            'period_type' => 'monthly',
            'description' => 'Iuran wajib bulanan untuk petugas ronda malam, pengangkutan sampah & pemeliharaan IoT lingkungan.',
            'is_active' => true,
        ]);

        $iuranSosial = IuranType::create([
            'name' => 'Kas Sosial & Dana Duka',
            'amount' => 20000,
            'period_type' => 'monthly',
            'description' => 'Dana bantuan sosial warga sakit, duka cita, dan musibah lingkungan.',
            'is_active' => true,
        ]);

        $iuranAgustusan = IuranType::create([
            'name' => 'Iuran Perayaan HUT RI Ke-81',
            'amount' => 100000,
            'period_type' => 'incident',
            'description' => 'Sumbangan insidental perlombaan dan panggung gembira kemerdekaan.',
            'is_active' => true,
        ]);

        // 3. Create Sample Payments
        $p1 = IuranPayment::create([
            'user_id' => $warga1->id,
            'iuran_type_id' => $iuranWajib->id,
            'period_month' => 7,
            'period_year' => 2026,
            'amount' => 50000,
            'payment_method' => 'iot_rfid',
            'status' => 'approved',
            'notes' => 'Pembayaran Tap RFID ESP32 Terminal Pos Utama',
            'verified_by' => $bendahara->id,
            'verified_at' => Carbon::now()->subDays(5),
        ]);

        $p2 = IuranPayment::create([
            'user_id' => $warga2->id,
            'iuran_type_id' => $iuranWajib->id,
            'period_month' => 7,
            'period_year' => 2026,
            'amount' => 50000,
            'payment_method' => 'cash',
            'status' => 'approved',
            'notes' => 'Titip di pos security',
            'verified_by' => $bendahara->id,
            'verified_at' => Carbon::now()->subDays(3),
        ]);

        $p3 = IuranPayment::create([
            'user_id' => $warga3->id,
            'iuran_type_id' => $iuranWajib->id,
            'period_month' => 7,
            'period_year' => 2026,
            'amount' => 50000,
            'payment_method' => 'transfer',
            'status' => 'pending',
            'notes' => 'Menunggu verifikasi bukti transfer',
        ]);

        // 4. Create Cash Ledger Records
        CashTransaction::create([
            'type' => 'pemasukan',
            'category' => 'Iuran Warga (IoT RFID)',
            'amount' => 50000,
            'transaction_date' => Carbon::now()->subDays(5)->toDateString(),
            'description' => 'Iuran Keamanan Budi Santoso (Tap RFID Pos)',
            'iuran_payment_id' => $p1->id,
            'created_by' => $bendahara->id,
        ]);

        CashTransaction::create([
            'type' => 'pemasukan',
            'category' => 'Iuran Warga',
            'amount' => 50000,
            'transaction_date' => Carbon::now()->subDays(3)->toDateString(),
            'description' => 'Iuran Keamanan Siti Nurhaliza (Juli 2026)',
            'iuran_payment_id' => $p2->id,
            'created_by' => $bendahara->id,
        ]);

        CashTransaction::create([
            'type' => 'pemasukan',
            'category' => 'Kotak Kas Mandiri / Infaq Pos',
            'amount' => 150000,
            'transaction_date' => Carbon::now()->subDays(2)->toDateString(),
            'description' => 'Setoran Uang Fisik Smart Kotak Kas IoT Musholla',
            'created_by' => $bendahara->id,
        ]);

        CashTransaction::create([
            'type' => 'pemasukan',
            'category' => 'Sumbangan Donatur',
            'amount' => 500000,
            'transaction_date' => Carbon::now()->subDays(10)->toDateString(),
            'description' => 'Donasi H. Ridwan untuk kas fasilitas musholla RT',
            'created_by' => $bendahara->id,
        ]);

        CashTransaction::create([
            'type' => 'pengeluaran',
            'category' => 'Keamanan & Ronda',
            'amount' => 300000,
            'transaction_date' => Carbon::now()->subDays(4)->toDateString(),
            'description' => 'Honor mingguan petugas ronda & konsumsi posko security',
            'created_by' => $bendahara->id,
        ]);

        CashTransaction::create([
            'type' => 'pengeluaran',
            'category' => 'Maintenance & Fasilitas',
            'amount' => 125000,
            'transaction_date' => Carbon::now()->subDays(2)->toDateString(),
            'description' => 'Pemeliharaan sensor gerbang otomatis ESP32 & bohlam pos ronda',
            'created_by' => $bendahara->id,
        ]);

        // 5. Announcements
        Announcement::create([
            'title' => 'Integrasi Sistem Smart RT & Portal Otomatis ESP32 Beroperasi Penuh',
            'content' => 'Kabar gembira bagi seluruh warga RT 01 / RW 05! Portal otomatis gerbang perumahan kini telah terintegrasi dengan kartu RFID dan aplikasi SIKAS-RT. Warga dapat melakukan tap kartu untuk buka gerbang dan cek status iuran secara real-time.',
            'category' => 'Pengumuman RT',
            'author_id' => $rt->id,
            'is_active' => true,
        ]);

        Announcement::create([
            'title' => 'Transparansi Kas RT & Uang Kas Masuk via Smart Box IoT',
            'content' => 'Laporan kas RT diperbarui otomatis melalui sistem SIKAS-RT. Seluruh setoran iuran tunai maupun tap RFID dapat dipantau langsung secara terbuka oleh seluruh warga.',
            'category' => 'Keuangan',
            'author_id' => $bendahara->id,
            'is_active' => true,
        ]);

        // 6. IoT & ESP32 Infrastructure Seed Data
        $devGate = IotDevice::create([
            'device_id' => 'ESP32-GATE-01',
            'name' => 'Portal Otomatis Gerbang Utama RT 01',
            'type' => 'portal_gate',
            'location' => 'Gerbang Utama Blok A',
            'ip_address' => '192.168.1.101',
            'mac_address' => '24:6F:28:B1:A0:12',
            'api_key' => 'esp_gate_rt01_key_889911',
            'status' => 'online',
            'relay_state' => false,
            'rssi_signal' => -64,
            'firmware_version' => '1.2.0-esp32-mfrc522',
            'last_seen_at' => Carbon::now(),
            'registered_by' => $superadmin->id,
        ]);

        $devPos = IotDevice::create([
            'device_id' => 'ESP32-RONDA-POS',
            'name' => 'Terminal Smart Pos Ronda Siskamling',
            'type' => 'pos_ronda',
            'location' => 'Pos Security Blok B',
            'ip_address' => '192.168.1.102',
            'mac_address' => '24:6F:28:B1:A0:25',
            'api_key' => 'esp_ronda_key_774422',
            'status' => 'online',
            'relay_state' => false,
            'rssi_signal' => -58,
            'firmware_version' => '1.2.0-esp32-dht11',
            'last_seen_at' => Carbon::now(),
            'registered_by' => $superadmin->id,
        ]);

        $devKasbox = IotDevice::create([
            'device_id' => 'ESP32-KASBOX-01',
            'name' => 'Smart Kotak Kas & Infaq Lingkungan',
            'type' => 'smart_kasbox',
            'location' => 'Musholla Al-Ikhlas RT 01',
            'ip_address' => '192.168.1.103',
            'mac_address' => '24:6F:28:B1:A0:36',
            'api_key' => 'esp_kasbox_key_332211',
            'status' => 'online',
            'relay_state' => false,
            'rssi_signal' => -68,
            'firmware_version' => '1.1.0-esp32-ir',
            'last_seen_at' => Carbon::now(),
            'registered_by' => $superadmin->id,
        ]);

        $devPanic = IotDevice::create([
            'device_id' => 'ESP32-PANIC-01',
            'name' => 'Tombol Darurat / Panic Alert Pos Ronda',
            'type' => 'panic_button',
            'location' => 'Tiang Siskamling Gang Belakang Blok C',
            'ip_address' => '192.168.1.104',
            'mac_address' => '24:6F:28:B1:A0:49',
            'api_key' => 'esp_panic_key_998877',
            'status' => 'online',
            'relay_state' => false,
            'rssi_signal' => -72,
            'firmware_version' => '1.0.0-esp32-btn',
            'last_seen_at' => Carbon::now(),
            'registered_by' => $superadmin->id,
        ]);

        // 7. RFID Cards for Warga
        IotRfidCard::create([
            'rfid_uid' => '93:B4:72:A1',
            'user_id' => $warga1->id,
            'card_label' => 'Kartu Akses Utama Budi',
            'status' => 'active',
            'last_tapped_at' => Carbon::now()->subMinutes(14),
            'last_location' => 'Portal Otomatis Gerbang Utama RT 01',
        ]);

        IotRfidCard::create([
            'rfid_uid' => '4A:8C:31:E9',
            'user_id' => $warga2->id,
            'card_label' => 'Kartu Akses Siti Nurhaliza',
            'status' => 'active',
            'last_tapped_at' => Carbon::now()->subHours(2),
            'last_location' => 'Terminal Smart Pos Ronda',
        ]);

        IotRfidCard::create([
            'rfid_uid' => 'F2:1D:6E:B8',
            'user_id' => $warga3->id,
            'card_label' => 'Kartu Akses Joko Widodo',
            'status' => 'active',
            'last_tapped_at' => Carbon::now()->subHours(5),
            'last_location' => 'Portal Otomatis Gerbang Utama RT 01',
        ]);

        // 8. Initial Telemetry Logs
        IotTelemetryLog::create([
            'iot_device_id' => $devGate->id,
            'device_id' => $devGate->device_id,
            'event_type' => 'gate_access',
            'rfid_uid' => '93:B4:72:A1',
            'user_id' => $warga1->id,
            'status' => 'success',
            'message' => 'Portal dibuka untuk Budi Santoso (Blok C1 No. 12)',
            'logged_at' => Carbon::now()->subMinutes(14),
        ]);

        IotTelemetryLog::create([
            'iot_device_id' => $devPos->id,
            'device_id' => $devPos->device_id,
            'event_type' => 'pos_ronda',
            'rfid_uid' => '4A:8C:31:E9',
            'user_id' => $warga2->id,
            'status' => 'success',
            'message' => 'Absensi Ronda Siskamling Siti Nurhaliza tercatat',
            'logged_at' => Carbon::now()->subHours(2),
        ]);

        IotTelemetryLog::create([
            'iot_device_id' => $devKasbox->id,
            'device_id' => $devKasbox->device_id,
            'event_type' => 'kas_drop',
            'value' => 20000,
            'unit' => 'IDR',
            'status' => 'success',
            'message' => 'Setoran koin/uang fisik terdeteksi di Smart Kotak Kas',
            'logged_at' => Carbon::now()->subHours(3),
        ]);

        IotTelemetryLog::create([
            'iot_device_id' => $devPos->id,
            'device_id' => $devPos->device_id,
            'event_type' => 'temperature',
            'value' => 29.4,
            'unit' => 'C',
            'status' => 'success',
            'message' => 'Suhu lingkungan Pos Ronda: 29.4 C',
            'logged_at' => Carbon::now()->subMinutes(5),
        ]);

        // 9. Past Resolved Emergency Alert (for audit log)
        IotEmergencyAlert::create([
            'device_id' => 'ESP32-PANIC-01',
            'triggered_by' => $rt->id,
            'alert_type' => 'uji_coba_sistem',
            'location' => 'Pos Keamanan Gerbang Depan',
            'description' => 'Simulasi pengujian sirene dan notifikasi darurat siskamling.',
            'status' => 'resolved',
            'resolved_by' => $rt->id,
            'resolved_at' => Carbon::now()->subDays(1),
            'resolution_notes' => 'Pengujian respons sirene berjalan normal dalam 3 detik.',
        ]);
    }
}
