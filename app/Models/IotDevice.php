<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IotDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'name',
        'type',
        'location',
        'ip_address',
        'mac_address',
        'api_key',
        'status',
        'relay_state',
        'rssi_signal',
        'firmware_version',
        'last_seen_at',
        'registered_by',
    ];

    protected $casts = [
        'relay_state' => 'boolean',
        'last_seen_at' => 'datetime',
        'rssi_signal' => 'integer',
    ];

    public function registeredBy()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function telemetryLogs()
    {
        return $this->hasMany(IotTelemetryLog::class);
    }

    public function commands()
    {
        return $this->hasMany(IotCommand::class, 'device_id', 'device_id');
    }

    public function isOnline(): bool
    {
        if ($this->status !== 'online') {
            return false;
        }
        return $this->last_seen_at && $this->last_seen_at->diffInMinutes(now()) <= 5;
    }
}
