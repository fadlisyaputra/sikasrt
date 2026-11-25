<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UmkmProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nama_usaha',
        'nama_produk',
        'kategori',
        'deskripsi',
        'harga',
        'satuan',
        'no_whatsapp',
        'alamat_usaha',
        'status',
        'is_verified',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'is_verified' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedHargaAttribute()
    {
        return 'Rp ' . number_format($this->harga, 0, ',', '.');
    }

    public function getWhatsappLinkAttribute()
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $this->no_whatsapp);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        }
        $text = urlencode("Halo {$this->nama_usaha}, saya warga RT 05 tertarik dengan produk *{$this->nama_produk}* yang ada di Aplikasi SIKAS-RT.");
        return "https://wa.me/{$cleanPhone}?text={$text}";
    }
}
