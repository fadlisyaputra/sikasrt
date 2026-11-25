<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IotTelemetryLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'iot_device_id',
        'device_id',
        'event_type',
        'rfid_uid',
        'user_id',
        'value',
        'unit',
        'status',
        'message',
        'raw_data',
        'logged_at',
    ];

    protected $casts = [
        'value' => 'float',
        'raw_data' => 'array',
        'logged_at' => 'datetime',
    ];

    public function device()
    {
        return $this->belongsTo(IotDevice::class, 'iot_device_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
