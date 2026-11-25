<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\IotDevice;
use App\Models\IotRfidCard;
use App\Models\IotTelemetryLog;
use App\Models\IotCommand;
use App\Models\IotEmergencyAlert;
use App\Models\User;
use Carbon\Carbon;

class IotController extends Controller
{
    public function index()
    {
        $devices = IotDevice::with('registeredBy')->latest()->get();
        $rfidCards = IotRfidCard::with('user')->latest()->get();
        $telemetryLogs = IotTelemetryLog::with(['device', 'user'])->latest('logged_at')->take(50)->get();
        $activeAlerts = IotEmergencyAlert::with(['triggeredBy', 'resolvedBy'])->where('status', 'active')->latest()->get();
        $resolvedAlerts = IotEmergencyAlert::with(['triggeredBy', 'resolvedBy'])->where('status', 'resolved')->latest()->take(10)->get();

        $wargaList = User::where('role', 'warga')->orderBy('name')->get();

        $totalDevices = $devices->count();
        $onlineCount = $devices->where('status', 'online')->count();
        $alertCount = $activeAlerts->count();
        $offlineCount = $devices->where('status', 'offline')->count();

        return view('iot.index', compact(
            'devices',
            'rfidCards',
            'telemetryLogs',
            'activeAlerts',
            'resolvedAlerts',
            'wargaList',
            'totalDevices',
            'onlineCount',
            'alertCount',
            'offlineCount'
        ));
    }

    public function storeDevice(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string|unique:iot_devices,device_id|max:50',
            'name' => 'required|string|max:255',
            'type' => 'required|in:portal_gate,pos_ronda,smart_kasbox,panic_button,environment_sensor',
            'location' => 'required|string|max:255',
        ]);

        IotDevice::create([
            'device_id' => strtoupper(trim($request->device_id)),
            'name' => $request->name,
            'type' => $request->type,
            'location' => $request->location,
            'api_key' => 'esp_' . Str::random(32),
            'status' => 'online',
            'firmware_version' => '1.0.0-esp32',
            'last_seen_at' => Carbon::now(),
            'registered_by' => Auth::id(),
        ]);

        return redirect()->route('iot.index')->with('success', 'Perangkat ESP32 berhasil didaftarkan ke sistem.');
    }

    public function updateDevice(Request $request, $id)
    {
        $device = IotDevice::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'location' => 'required|string|max:255',
            'status' => 'required|in:online,offline,alert',
        ]);

        $device->update($request->only('name', 'type', 'location', 'status'));

        return redirect()->route('iot.index')->with('success', 'Informasi perangkat ESP32 berhasil diperbarui.');
    }

    public function destroyDevice($id)
    {
        $device = IotDevice::findOrFail($id);
        $device->delete();

        return redirect()->route('iot.index')->with('success', 'Perangkat ESP32 berhasil dihapus.');
    }

    public function sendCommand(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string',
            'command' => 'required|string|in:open_gate,close_gate,buzzer_on,trigger_siren,reset_alarm,reboot',
        ]);

        $device = IotDevice::where('device_id', $request->device_id)->firstOrFail();

        // Update relay state directly if gate command
        if ($request->command === 'open_gate') {
            $device->update(['relay_state' => true, 'last_seen_at' => now(), 'status' => 'online']);
            
            IotTelemetryLog::create([
                'iot_device_id' => $device->id,
                'device_id' => $device->device_id,
                'event_type' => 'gate_access',
                'user_id' => Auth::id(),
                'status' => 'success',
                'message' => 'Portal Gerbang dibuka manual via Remote Dashboard oleh ' . Auth::user()->name,
                'logged_at' => now(),
            ]);
        } elseif ($request->command === 'close_gate') {
            $device->update(['relay_state' => false, 'last_seen_at' => now()]);
        } elseif ($request->command === 'reset_alarm') {
            $device->update(['status' => 'online', 'last_seen_at' => now()]);
            // Also resolve any active alerts on this device
            IotEmergencyAlert::where('device_id', $device->device_id)
                ->where('status', 'active')
                ->update([
                    'status' => 'resolved',
                    'resolved_by' => Auth::id(),
                    'resolved_at' => now(),
                    'resolution_notes' => 'Alarm dinetralkan via Remote Control Dashboard',
                ]);
        }

        // Add to queue for physical ESP32
        IotCommand::create([
            'device_id' => $device->device_id,
            'command' => $request->command,
            'payload' => ['triggered_by' => Auth::user()->name],
            'status' => 'pending',
            'issued_by' => Auth::id(),
        ]);

        return redirect()->route('iot.index')->with('success', 'Perintah remote "' . $request->command . '" berhasil dikirim ke perangkat ' . $device->name);
    }

    public function storeRfidCard(Request $request)
    {
        $request->validate([
            'rfid_uid' => 'required|string|unique:iot_rfid_cards,rfid_uid|max:30',
            'user_id' => 'required|exists:users,id',
            'card_label' => 'required|string|max:100',
        ]);

        $uid = strtoupper(trim($request->rfid_uid));

        IotRfidCard::create([
            'rfid_uid' => $uid,
            'user_id' => $request->user_id,
            'card_label' => $request->card_label,
            'status' => 'active',
        ]);

        // Also update rfid_uid on user record for fast lookup
        User::where('id', $request->user_id)->update(['rfid_uid' => $uid]);

        return redirect()->route('iot.index')->with('success', 'Kartu RFID berhasil didaftarkan untuk warga.');
    }

    public function destroyRfidCard($id)
    {
        $card = IotRfidCard::findOrFail($id);
        User::where('id', $card->user_id)->where('rfid_uid', $card->rfid_uid)->update(['rfid_uid' => null]);
        $card->delete();

        return redirect()->route('iot.index')->with('success', 'Kartu RFID berhasil dihapus.');
    }

    public function resolveEmergency(Request $request, $id)
    {
        $alert = IotEmergencyAlert::findOrFail($id);
        $alert->update([
            'status' => 'resolved',
            'resolved_by' => Auth::id(),
            'resolved_at' => Carbon::now(),
            'resolution_notes' => $request->notes ?? 'Penanganan selesai dan kondisi pos keamanan telah kondusif.',
        ]);

        if ($alert->device_id) {
            IotDevice::where('device_id', $alert->device_id)->update(['status' => 'online']);
        }

        return redirect()->back()->with('success', 'Peringatan Darurat ditandai SELESAI / AMAN.');
    }

    public function triggerPanic(Request $request)
    {
        $alert = IotEmergencyAlert::create([
            'device_id' => 'WEB-PANIC-BTN',
            'triggered_by' => Auth::id(),
            'alert_type' => $request->alert_type ?? 'panic_button',
            'location' => Auth::user()->address ?? 'Lingkungan RT 01 / RW 05',
            'description' => $request->description ?? 'Peringatan Bahaya / Darurat dipicu oleh ' . Auth::user()->name,
            'status' => 'active',
        ]);

        IotTelemetryLog::create([
            'device_id' => 'WEB-PANIC-BTN',
            'event_type' => 'panic_alert',
            'user_id' => Auth::id(),
            'status' => 'emergency',
            'message' => 'TOMBOL PANIK AKTIF: ' . Auth::user()->name . ' di ' . ($alert->location),
            'logged_at' => now(),
        ]);

        return redirect()->back()->with('emergency_triggered', 'ALARM DARURAT BERHASIL DIPICU! Pengurus RT & Keamanan telah diberitahu.');
    }
}
