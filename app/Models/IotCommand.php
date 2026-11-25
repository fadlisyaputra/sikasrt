<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IotCommand extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'command',
        'payload',
        'status',
        'issued_by',
        'executed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'executed_at' => 'datetime',
    ];

    public function issuedBy()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function device()
    {
        return $this->belongsTo(IotDevice::class, 'device_id', 'device_id');
    }
}
