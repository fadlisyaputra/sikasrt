<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosyanduActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category',
        'activity_date',
        'start_time',
        'end_time',
        'location',
        'officer_name',
        'description',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'date',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function records()
    {
        return $this->hasMany(PosyanduRecord::class);
    }
}
