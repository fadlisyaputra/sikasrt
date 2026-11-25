<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nomor_surat',
        'jenis_surat',
        'keperluan',
        'keterangan',
        'status',
        'catatan_pengurus',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'approved' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300"><i class="fas fa-check-circle mr-1"></i> Disetujui</span>',
            'rejected' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300"><i class="fas fa-times-circle mr-1"></i> Ditolak</span>',
            default => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300"><i class="fas fa-clock mr-1"></i> Menunggu Verifikasi</span>',
        };
    }
}
