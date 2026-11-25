<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IuranType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'amount',
        'period_type',
        'description',
        'is_active',
    ];

    public function payments()
    {
        return $this->hasMany(IuranPayment::class);
    }
}
