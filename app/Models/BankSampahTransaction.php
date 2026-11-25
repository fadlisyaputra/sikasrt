<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankSampahTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'petugas_id',
        'tipe',
        'berat_kg',
        'harga_satuan',
        'total_rp',
        'status',
        'catatan',
    ];

    protected $casts = [
        'berat_kg' => 'decimal:2',
        'harga_satuan' => 'decimal:2',
        'total_rp' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(BankSampahCategory::class, 'category_id');
    }

    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function getFormattedTotalAttribute()
    {
        $prefix = $this->tipe === 'setor' ? '+' : '-';
        return $prefix . ' Rp ' . number_format($this->total_rp, 0, ',', '.');
    }
}
