<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\RondaSchedule;
use App\Models\RondaMember;
use App\Models\RondaAttendance;
use App\Models\PosyanduActivity;
use App\Models\PosyanduRecord;
use App\Models\WargaBill;
use App\Models\IuranType;
use Carbon\Carbon;

class RondaPosyanduTagihanSeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = User::where('role', 'superadmin')->first();
        $rt = User::where('role', 'rt')->first();
        $bendahara = User::where('role', 'bendahara')->first();
        $wargaList = User::where('role', 'warga')->get();

        if ($wargaList->isEmpty()) {
            return;
        }

        // ==========================================
        // 1. SEED DATA JADWAL RONDA
        // ==========================================
        if (RondaSchedule::count() == 0) {
            $days = [
                ['day' => 'Senin', 'group' => 'Regu 1 (Garuda)', 'loc' => 'Pos Ronda Blok A (Depan Gerbang)'],
                ['day' => 'Selasa', 'group' => 'Regu 2 (Rajawali)', 'loc' => 'Pos Ronda Blok B (Taman RT)'],
                ['day' => 'Rabu', 'group' => 'Regu 3 (Elang)', 'loc' => 'Pos Ronda Utama RT 01'],
                ['day' => 'Kamis', 'group' => 'Regu 4 (Cendrawasih)', 'loc' => 'Pos Ronda Blok C'],
                ['day' => 'Jumat', 'group' => 'Regu 5 (Merpati)', 'loc' => 'Pos Ronda Blok D'],
                ['day' => 'Sabtu', 'group' => 'Regu 6 (Komando Weekend)', 'loc' => 'Pos Ronda Terpadu RT 01 / RW 05'],
                ['day' => 'Minggu', 'group' => 'Regu 7 (Bhayangkara)', 'loc' => 'Pos Ronda Utama RT 01'],
            ];

            foreach ($days as $idx => $d) {
                $sched = RondaSchedule::create([
                    'day_name' => $d['day'],
                    'group_name' => $d['group'],
                    'start_time' => '22:00',
                    'end_time' => '04:00',
                    'location' => $d['loc'],
                    'coordinator_id' => $rt?->id ?? $wargaList->first()->id,
                    'notes' => 'Patroli keliling setiap 1 jam sekali, cek portal gerbang dan pastikan lampu jalan menyala.',
                    'is_active' => true,
                ]);

                // Assign 2-3 warga to each schedule
                foreach ($wargaList as $wIdx => $w) {
                    if (($wIdx + $idx) % 2 == 0) {
                        RondaMember::create([
                            'ronda_schedule_id' => $sched->id,
                            'user_id' => $w->id,
                            'role_in_team' => $wIdx == 0 ? 'Koordinator' : 'Anggota',
                        ]);
                    }
                }

                // Sample attendance record
                RondaAttendance::create([
                    'ronda_schedule_id' => $sched->id,
                    'user_id' => $wargaList->first()->id,
                    'attendance_date' => Carbon::now()->subDays($idx + 1)->toDateString(),
                    'status' => 'hadir',
                    'method' => 'rfid_ktp',
                    'notes' => 'Tap RFID e-KTP di Pos Ronda Siskamling',
                    'verified_by' => $rt?->id,
                ]);
            }
        }

        // ==========================================
        // 2. SEED DATA POSYANDU
        // ==========================================
        if (PosyanduActivity::count() == 0) {
            $act1 = PosyanduActivity::create([
                'title' => 'Posyandu Balita Melati: Penimbangan & Vitamin A',
                'category' => 'balita',
                'activity_date' => Carbon::now()->addDays(5)->toDateString(),
                'start_time' => '08:30',
                'end_time' => '11:30',
                'location' => 'Balai Pertemuan RT 01 / RW 05',
                'officer_name' => 'Bidan Yuli, S.Tr.Keb & Kader Melati',
                'description' => 'Pemeriksaan rutin tumbuh kembang balita, pengukuran tinggi & berat badan, serta pembagian kapsul Vitamin A.',
                'status' => 'upcoming',
                'created_by' => $rt?->id,
            ]);

            $act2 = PosyanduActivity::create([
                'title' => 'Posyandu Lansia Sehat & Bugar RT 01',
                'category' => 'lansia',
                'activity_date' => Carbon::now()->addDays(12)->toDateString(),
                'start_time' => '07:30',
                'end_time' => '10:30',
                'location' => 'Taman Blok B RT 01',
                'officer_name' => 'dr. Hendra (Puskesmas) & Kader Lansia',
                'description' => 'Senam sehat lansia, skrining tekanan darah (tensi), cek kadar gula darah, kolesterol, dan konsultasi kesehatan geriatri gratis.',
                'status' => 'upcoming',
                'created_by' => $rt?->id,
            ]);

            // Sample records
            PosyanduRecord::create([
                'posyandu_activity_id' => $act1->id,
                'user_id' => $wargaList->first()->id,
                'participant_name' => 'Kenzo Santoso (Anak Bpk. Budi)',
                'participant_category' => 'balita',
                'gender' => 'L',
                'age_text' => '24 Bulan',
                'weight' => 12.5,
                'height' => 88.0,
                'vitamin_immunization' => 'Vitamin A Merah, DPT-HB-Hib Lanjutan',
                'health_status' => 'Gizi Baik & Normal',
                'notes' => 'Tumbuh kembang sangat baik, motorik aktif.',
                'recorded_by' => $rt?->id,
            ]);

            PosyanduRecord::create([
                'posyandu_activity_id' => $act2->id,
                'user_id' => $wargaList->last()->id,
                'participant_name' => 'Bpk. Soedarmono (Orang Tua)',
                'participant_category' => 'lansia',
                'gender' => 'L',
                'age_text' => '68 Tahun',
                'blood_pressure' => '125/80',
                'blood_sugar' => 110.0,
                'cholesterol' => 185.0,
                'health_status' => 'Sehat & Terkontrol',
                'notes' => 'Tekanan darah normal, disarankan rutin jalan santai pagi.',
                'recorded_by' => $rt?->id,
            ]);
        }

        // ==========================================
        // 3. SEED DATA TAGIHAN WARGA
        // ==========================================
        if (WargaBill::count() == 0) {
            $iuranWajib = IuranType::first() ?? IuranType::create([
                'name' => 'Iuran Keamanan & Kebersihan',
                'amount' => 50000,
                'period_type' => 'monthly',
                'description' => 'Iuran rutin bulanan warga RT',
                'is_active' => true,
            ]);

            $currentMonth = (int)Carbon::now()->format('n');
            $currentYear = (int)Carbon::now()->format('Y');

            foreach ($wargaList as $i => $w) {
                $isPaid = ($i == 0); // Budi already paid
                WargaBill::create([
                    'bill_code' => 'INV-' . $currentYear . sprintf('%02d', $currentMonth) . '-' . sprintf('%03d', $w->id),
                    'user_id' => $w->id,
                    'iuran_type_id' => $iuranWajib->id,
                    'period_month' => $currentMonth,
                    'period_year' => $currentYear,
                    'amount' => $iuranWajib->amount,
                    'due_date' => Carbon::now()->endOfMonth()->toDateString(),
                    'status' => $isPaid ? 'paid' : 'unpaid',
                    'paid_at' => $isPaid ? Carbon::now()->subDays(2) : null,
                    'payment_method' => $isPaid ? 'transfer' : null,
                    'notes' => 'Tagihan Iuran Warga Bulan ' . Carbon::now()->translatedFormat('F Y'),
                    'created_by' => $bendahara?->id,
                ]);

                // Previous month bill (Lunas)
                $prevMonth = $currentMonth > 1 ? $currentMonth - 1 : 12;
                $prevYear = $currentMonth > 1 ? $currentYear : $currentYear - 1;
                WargaBill::create([
                    'bill_code' => 'INV-' . $prevYear . sprintf('%02d', $prevMonth) . '-' . sprintf('%03d', $w->id),
                    'user_id' => $w->id,
                    'iuran_type_id' => $iuranWajib->id,
                    'period_month' => $prevMonth,
                    'period_year' => $prevYear,
                    'amount' => $iuranWajib->amount,
                    'due_date' => Carbon::create($prevYear, $prevMonth, 1)->endOfMonth()->toDateString(),
                    'status' => 'paid',
                    'paid_at' => Carbon::create($prevYear, $prevMonth, 10),
                    'payment_method' => 'iot_rfid',
                    'notes' => 'Tagihan Iuran Warga Bulan Lalu (Lunas)',
                    'created_by' => $bendahara?->id,
                ]);
            }
        }
    }
}
