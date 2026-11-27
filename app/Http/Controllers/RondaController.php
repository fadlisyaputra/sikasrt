<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\RondaSchedule;
use App\Models\RondaMember;
use App\Models\RondaAttendance;
use App\Models\User;
use Carbon\Carbon;

class RondaController extends Controller
{
    /**
     * Tampilan Utama Jadwal Ronda Siskamling
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Ambil semua jadwal ronda aktif dengan anggotanya
        $schedules = RondaSchedule::with(['members.user', 'coordinator'])
            ->orderByRaw("CASE day_name 
                WHEN 'Senin' THEN 1 
                WHEN 'Selasa' THEN 2 
                WHEN 'Rabu' THEN 3 
                WHEN 'Kamis' THEN 4 
                WHEN 'Jumat' THEN 5 
                WHEN 'Sabtu' THEN 6 
                WHEN 'Minggu' THEN 7 
                ELSE 8 END")
            ->get();

        // Hari ini dalam bahasa Indonesia
        $dayMap = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
        ];
        $todayEnglish = Carbon::now()->format('l');
        $todayIndo = $dayMap[$todayEnglish] ?? 'Senin';

        // Jadwal ronda malam ini
        $todaySchedule = $schedules->firstWhere('day_name', $todayIndo);

        // Jadwal ronda milik user yang sedang login
        $myScheduleIds = RondaMember::where('user_id', $user->id)->pluck('ronda_schedule_id')->toArray();
        $mySchedules = $schedules->whereIn('id', $myScheduleIds);

        // Riwayat absensi ronda terkini
        $attendancesQuery = RondaAttendance::with(['user', 'schedule', 'verifier'])->latest('attendance_date');
        if ($user->isWarga()) {
            $myAttendances = (clone $attendancesQuery)->where('user_id', $user->id)->take(15)->get();
            $recentAttendances = $attendancesQuery->take(15)->get();
        } else {
            $myAttendances = collect();
            $recentAttendances = $attendancesQuery->paginate(20);
        }

        // Daftar semua warga untuk dropdown tambah anggota
        $wargaList = User::where('role', 'warga')->orderBy('name')->get();

        // Cek apakah user sudah absen hari ini
        $hasCheckedInToday = RondaAttendance::where('user_id', $user->id)
            ->whereDate('attendance_date', Carbon::today())
            ->exists();

        return view('ronda.index', compact(
            'schedules',
            'todayIndo',
            'todaySchedule',
            'mySchedules',
            'myAttendances',
            'recentAttendances',
            'wargaList',
            'hasCheckedInToday'
        ));
    }

    /**
     * Tambah Jadwal Ronda Baru (Admin / RT)
     */
    public function storeSchedule(Request $request)
    {
        $request->validate([
            'day_name' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'group_name' => 'required|string|max:100',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'location' => 'required|string|max:255',
            'coordinator_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $schedule = RondaSchedule::create([
            'day_name' => $request->day_name,
            'group_name' => $request->group_name,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'location' => $request->location,
            'coordinator_id' => $request->coordinator_id,
            'notes' => $request->notes,
            'is_active' => true,
        ]);

        if ($request->filled('coordinator_id')) {
            RondaMember::firstOrCreate([
                'ronda_schedule_id' => $schedule->id,
                'user_id' => $request->coordinator_id,
            ], [
                'role_in_team' => 'Koordinator',
            ]);
        }

        return redirect()->back()->with('success', 'Jadwal Ronda baru (' . $request->day_name . ' - ' . $request->group_name . ') berhasil dibuat.');
    }

    /**
     * Update Jadwal Ronda (Admin / RT)
     */
    public function updateSchedule(Request $request, $id)
    {
        $schedule = RondaSchedule::findOrFail($id);

        $request->validate([
            'day_name' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'group_name' => 'required|string|max:100',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'location' => 'required|string|max:255',
            'coordinator_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $schedule->update($request->only('day_name', 'group_name', 'start_time', 'end_time', 'location', 'coordinator_id', 'notes'));

        if ($request->filled('coordinator_id')) {
            RondaMember::firstOrCreate([
                'ronda_schedule_id' => $schedule->id,
                'user_id' => $request->coordinator_id,
            ], [
                'role_in_team' => 'Koordinator',
            ]);
        }

        return redirect()->back()->with('success', 'Jadwal Ronda (' . $schedule->day_name . ' - ' . $schedule->group_name . ') berhasil diperbarui.');
    }

    /**
     * Hapus Jadwal Ronda (Admin / RT)
     */
    public function destroySchedule($id)
    {
        $schedule = RondaSchedule::findOrFail($id);
        $schedule->delete();

        return redirect()->back()->with('success', 'Jadwal Ronda berhasil dihapus.');
    }

    /**
     * Tambah Anggota Warga ke Jadwal Ronda
     */
    public function addMember(Request $request, $scheduleId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_in_team' => 'required|in:Koordinator,Anggota',
        ]);

        $schedule = RondaSchedule::findOrFail($scheduleId);

        RondaMember::firstOrCreate([
            'ronda_schedule_id' => $schedule->id,
            'user_id' => $request->user_id,
        ], [
            'role_in_team' => $request->role_in_team,
        ]);

        return redirect()->back()->with('success', 'Warga berhasil ditambahkan ke jadwal ronda ' . $schedule->day_name);
    }

    /**
     * Hapus Anggota dari Jadwal Ronda
     */
    public function removeMember($scheduleId, $userId)
    {
        RondaMember::where('ronda_schedule_id', $scheduleId)->where('user_id', $userId)->delete();
        return redirect()->back()->with('success', 'Anggota berhasil dikeluarkan dari jadwal ronda.');
    }

    /**
     * Check-in Kehadiran Ronda Malam
     */
    public function checkin(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'ronda_schedule_id' => 'nullable|exists:ronda_schedules,id',
            'status' => 'required|in:hadir,izin,alpha',
            'notes' => 'nullable|string',
        ]);

        $targetUserId = $request->input('user_id', $user->id);

        RondaAttendance::updateOrCreate(
            [
                'user_id' => $targetUserId,
                'attendance_date' => Carbon::today()->toDateString(),
            ],
            [
                'ronda_schedule_id' => $request->ronda_schedule_id,
                'status' => $request->status,
                'method' => 'manual_admin',
                'notes' => $request->notes ?? ($request->status === 'hadir' ? 'Check-in mandiri via portal SIKAS-RT' : 'Izin tidak ronda'),
                'verified_by' => Auth::id(),
            ]
        );

        return redirect()->back()->with('success', 'Kehadiran ronda berhasil dicatat!');
    }
}
