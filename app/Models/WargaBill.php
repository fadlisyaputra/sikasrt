<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class WargaBill extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_code',
        'user_id',
        'iuran_type_id',
        'period_month',
        'period_year',
        'amount',
        'due_date',
        'status',
        'paid_at',
        'iuran_payment_id',
        'payment_method',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'due_date' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function iuranType()
    {
        return $this->belongsTo(IuranType::class);
    }

    public function iuranPayment()
    {
        return $this->belongsTo(IuranPayment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getMonthNameAttribute(): string
    {
        return Carbon::create()->month($this->period_month)->translatedFormat('F');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOverdue(): bool
    {
        return !$this->isPaid() && Carbon::parse($this->due_date)->isPast();
    }
}
