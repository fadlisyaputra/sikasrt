<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Perangkat IoT / ESP32 Devices
        Schema::create('iot_devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->unique(); // e.g., ESP32-GATE-01, ESP32-POS-RONDA, ESP32-KASBOX-01
            $table->string('name');
            $table->string('type'); // portal_gate, pos_ronda, smart_kasbox, panic_button, environment_sensor
            $table->string('location');
            $table->string('ip_address')->nullable();
            $table->string('mac_address')->nullable();
            $table->string('api_key', 64)->unique();
            $table->enum('status', ['online', 'offline', 'alert'])->default('offline');
            $table->boolean('relay_state')->default(false); // e.g. Barrier gate open or relay active
            $table->integer('rssi_signal')->nullable(); // WiFi Signal in dBm
            $table->string('firmware_version')->default('1.0.0-esp32');
            $table->timestamp('last_seen_at')->nullable();
            $table->foreignId('registered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // 2. Kartu RFID Terdaftar untuk Warga (Tap-to-Pay & Akses Portal)
        Schema::create('iot_rfid_cards', function (Blueprint $table) {
            $table->id();
            $table->string('rfid_uid')->unique(); // e.g., 93:B4:72:A1
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('card_label')->default('Kartu Warga');
            $table->enum('status', ['active', 'blocked', 'lost'])->default('active');
            $table->timestamp('last_tapped_at')->nullable();
            $table->string('last_location')->nullable();
            $table->timestamps();
        });

        // 3. Log Telemetri & Event Sensor IoT
        Schema::create('iot_telemetry_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('iot_device_id')->nullable()->constrained('iot_devices')->onDelete('cascade');
            $table->string('device_id')->index();
            $table->string('event_type'); // rfid_tap, gate_access, pos_ronda, kas_drop, panic_alert, temperature, humidity
            $table->string('rfid_uid')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('value', 12, 2)->nullable(); // sensor value or cash amount
            $table->string('unit', 20)->nullable(); // IDR, C, %, etc.
            $table->enum('status', ['success', 'denied', 'warning', 'emergency'])->default('success');
            $table->text('message')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamp('logged_at');
            $table->timestamps();
        });

        // 4. Antrian Perintah Kontrol untuk ESP32 (Remote Gate, Siren, Buzzer, Reset)
        Schema::create('iot_commands', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->index();
            $table->string('command'); // open_gate, buzzer_on, buzzer_off, trigger_siren, reset_alarm, reboot
            $table->json('payload')->nullable();
            $table->enum('status', ['pending', 'executed', 'expired'])->default('pending');
            $table->foreignId('issued_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();
        });

        // 5. Peringatan Keadaan Darurat / Panic Alert Lingkungan
        Schema::create('iot_emergency_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->nullable()->index();
            $table->foreignId('triggered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('alert_type'); // panic_button, fire_alarm, medical, security
            $table->string('location');
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'resolved'])->default('active');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iot_emergency_alerts');
        Schema::dropIfExists('iot_commands');
        Schema::dropIfExists('iot_telemetry_logs');
        Schema::dropIfExists('iot_rfid_cards');
        Schema::dropIfExists('iot_devices');
    }
};
