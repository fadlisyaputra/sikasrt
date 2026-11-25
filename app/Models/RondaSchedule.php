<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RondaSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'day_name',
        'group_name',
        'start_time',
        'end_time',
        'location',
        'coordinator_id',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function coordinator()
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    public function members()
    {
        return $this->hasMany(RondaMember::class);
    }

    public function memberUsers()
    {
        return $this->belongsToMany(User::class, 'ronda_members', 'ronda_schedule_id', 'user_id')
                    ->withPivot('role_in_team')
                    ->withTimestamps();
    }

    public function attendances()
    {
        return $this->hasMany(RondaAttendance::class);
    }
}
