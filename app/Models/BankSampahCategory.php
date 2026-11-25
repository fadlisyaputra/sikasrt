<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankSampahCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_kategori',
        'kode',
        'harga_per_kg',
        'satuan',
        'deskripsi',
    ];

    protected $casts = [
        'harga_per_kg' => 'decimal:2',
    ];

    public function transactions()
    {
        return $this->hasMany(BankSampahTransaction::class, 'category_id');
    }

    public function getFormattedHargaAttribute()
    {
        return 'Rp ' . number_format($this->harga_per_kg, 0, ',', '.') . '/' . $this->satuan;
    }
}
