<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RondaAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'ronda_schedule_id',
        'user_id',
        'attendance_date',
        'status',
        'method',
        'notes',
        'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
        ];
    }

    public function schedule()
    {
        return $this->belongsTo(RondaSchedule::class, 'ronda_schedule_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
