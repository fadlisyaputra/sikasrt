<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IotRfidCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'rfid_uid',
        'user_id',
        'card_label',
        'status',
        'last_tapped_at',
        'last_location',
    ];

    protected $casts = [
        'last_tapped_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
