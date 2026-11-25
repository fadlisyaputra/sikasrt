<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'category',
        'amount',
        'transaction_date',
        'description',
        'receipt_file',
        'iuran_payment_id',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function iuranPayment()
    {
        return $this->belongsTo(IuranPayment::class);
    }
}
