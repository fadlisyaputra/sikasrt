<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'address',
        'rt_rw',
        'status_warga',
        'avatar',
        'rfid_uid',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isSuperadmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function isBendahara(): bool
    {
        return $this->role === 'bendahara';
    }

    public function isRt(): bool
    {
        return $this->role === 'rt';
    }

    public function isWarga(): bool
    {
        return $this->role === 'warga';
    }

    public function iuranPayments()
    {
        return $this->hasMany(IuranPayment::class);
    }

    public function cashTransactions()
    {
        return $this->hasMany(CashTransaction::class, 'created_by');
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class, 'author_id');
    }

    public function rfidCards()
    {
        return $this->hasMany(IotRfidCard::class);
    }

    public function telemetryLogs()
    {
        return $this->hasMany(IotTelemetryLog::class);
    }

    public function emergencyAlerts()
    {
        return $this->hasMany(IotEmergencyAlert::class, 'triggered_by');
    }

    public function rondaSchedules()
    {
        return $this->belongsToMany(RondaSchedule::class, 'ronda_members', 'user_id', 'ronda_schedule_id')
                    ->withPivot('role_in_team')
                    ->withTimestamps();
    }

    public function rondaAttendances()
    {
        return $this->hasMany(RondaAttendance::class);
    }

    public function posyanduRecords()
    {
        return $this->hasMany(PosyanduRecord::class);
    }

    public function bills()
    {
        return $this->hasMany(WargaBill::class);
    }

    public function suratRequests()
    {
        return $this->hasMany(SuratRequest::class);
    }

    public function umkmProducts()
    {
        return $this->hasMany(UmkmProduct::class);
    }

    public function bankSampahTransactions()
    {
        return $this->hasMany(BankSampahTransaction::class);
    }

    public function getBankSampahBalanceAttribute()
    {
        $setor = $this->bankSampahTransactions()->where('tipe', 'setor')->where('status', 'completed')->sum('total_rp');
        $tarik = $this->bankSampahTransactions()->where('tipe', 'tarik')->where('status', 'completed')->sum('total_rp');
        return max(0, (float)$setor - (float)$tarik);
    }
}
