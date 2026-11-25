<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosyanduRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'posyandu_activity_id',
        'user_id',
        'participant_name',
        'participant_category',
        'gender',
        'age_text',
        'weight',
        'height',
        'blood_pressure',
        'blood_sugar',
        'cholesterol',
        'vitamin_immunization',
        'health_status',
        'notes',
        'recorded_by',
    ];

    public function activity()
    {
        return $this->belongsTo(PosyanduActivity::class, 'posyandu_activity_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
