<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RondaMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'ronda_schedule_id',
        'user_id',
        'role_in_team',
    ];

    public function schedule()
    {
        return $this->belongsTo(RondaSchedule::class, 'ronda_schedule_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
