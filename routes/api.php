<?php

use App\Http\Controllers\Api\Esp32Controller;
use Illuminate\Support\Facades\Route;

Route::prefix('esp32')->middleware(['api.key'])->group(function () {
    // 1. Heartbeat & Command Polling
    Route::post('/heartbeat', [Esp32Controller::class, 'heartbeat']);
    Route::get('/heartbeat', [Esp32Controller::class, 'heartbeat']);

    // 2. RFID/NFC Card Tap (Portal Gate Barrier / e-KTP Registration / Tap-to-Pay / Ronda)
    Route::post('/rfid-tap', [Esp32Controller::class, 'rfidTap']);
    Route::get('/rfid-tap', [Esp32Controller::class, 'rfidTap']); // for test/microcontroller GET fallback
    Route::post('/portal/scan', [Esp32Controller::class, 'rfidTap']);
    Route::post('/ktp/scan', [Esp32Controller::class, 'rfidTap']);

    // 3. Smart Cash Box / Drop Infaq Kas Detection
    Route::post('/kas-drop', [Esp32Controller::class, 'kasDrop']);

    // 4. Emergency / Panic Button Alert
    Route::post('/panic-alert', [Esp32Controller::class, 'panicAlert']);
    Route::get('/panic-alert', [Esp32Controller::class, 'panicAlert']);

    // 5. Environmental & Sensor Telemetry
    Route::post('/sensor-data', [Esp32Controller::class, 'storeSensorData']);

    // 6. Device Status Monitoring
    Route::get('/status', [Esp32Controller::class, 'allDeviceStatus']);
});
