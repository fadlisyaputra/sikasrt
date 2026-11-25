<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PosyanduActivity;
use App\Models\PosyanduRecord;
use App\Models\User;
use Carbon\Carbon;

class PosyanduController extends Controller
{
    /**
     * Tampilan Utama Posyandu (Agenda & Buku Kesehatan Warga)
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Agenda Posyandu Mendatang
        $upcomingActivities = PosyanduActivity::where('activity_date', '>=', Carbon::today())
            ->orderBy('activity_date', 'asc')
            ->get();

        // Riwayat Kegiatan Lalu
        $pastActivities = PosyanduActivity::where('activity_date', '<', Carbon::today())
            ->orderBy('activity_date', 'desc')
            ->take(10)
            ->get();

        // Filter kategori pemeriksaan
        $category = $request->get('category', 'all');

        // Query Catatan Pemeriksaan Kesehatan
        $recordsQuery = PosyanduRecord::with(['activity', 'user', 'recorder'])->latest();

        if ($category !== 'all') {
            $recordsQuery->where('participant_category', $category);
        }

        if ($user->isWarga()) {
            // Warga melihat catatan keluarga mereka sendiri
            $myRecords = PosyanduRecord::where('user_id', $user->id)->latest()->get();
            $recentRecords = $recordsQuery->take(20)->get();
        } else {
            $myRecords = collect();
            $recentRecords = $recordsQuery->paginate(15);
        }

        // Statistik Cepat Posyandu
        $totalBalitaChecked = PosyanduRecord::where('participant_category', 'balita')->count();
        $totalLansiaChecked = PosyanduRecord::where('participant_category', 'lansia')->count();
        $totalActivities = PosyanduActivity::count();

        // Daftar kepala keluarga warga untuk linking
        $wargaList = User::where('role', 'warga')->orderBy('name')->get();

        return view('posyandu.index', compact(
            'upcomingActivities',
            'pastActivities',
            'myRecords',
            'recentRecords',
            'totalBalitaChecked',
            'totalLansiaChecked',
            'totalActivities',
            'wargaList',
            'category'
        ));
    }

    /**
     * Buat Agenda Kegiatan Posyandu Baru
     */
    public function storeActivity(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|in:balita,lansia,ibu_hamil,umum',
            'activity_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|string',
            'end_time' => 'required|string',
            'location' => 'required|string|max:255',
            'officer_name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        PosyanduActivity::create([
            'title' => $request->title,
            'category' => $request->category,
            'activity_date' => $request->activity_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'location' => $request->location,
            'officer_name' => $request->officer_name,
            'description' => $request->description,
            'status' => 'upcoming',
            'created_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Jadwal kegiatan Posyandu baru berhasil ditambahkan.');
    }

    /**
     * Hapus Agenda Kegiatan Posyandu
     */
    public function destroyActivity($id)
    {
        $activity = PosyanduActivity::findOrFail($id);
        $activity->delete();

        return redirect()->back()->with('success', 'Agenda Posyandu berhasil dihapus.');
    }

    /**
     * Input Hasil Pemeriksaan Kesehatan Posyandu
     */
    public function storeRecord(Request $request)
    {
        $request->validate([
            'posyandu_activity_id' => 'nullable|exists:posyandu_activities,id',
            'user_id' => 'nullable|exists:users,id',
            'participant_name' => 'required|string|max:255',
            'participant_category' => 'required|in:balita,lansia,ibu_hamil,umum',
            'gender' => 'required|in:L,P',
            'age_text' => 'nullable|string|max:50',
            'weight' => 'nullable|numeric|between:1,250',
            'height' => 'nullable|numeric|between:30,250',
            'blood_pressure' => 'nullable|string|max:20',
            'blood_sugar' => 'nullable|numeric|between:30,600',
            'cholesterol' => 'nullable|numeric|between:50,500',
            'vitamin_immunization' => 'nullable|string|max:255',
            'health_status' => 'required|string|max:100',
            'notes' => 'nullable|string',
        ]);

        PosyanduRecord::create([
            'posyandu_activity_id' => $request->posyandu_activity_id,
            'user_id' => $request->user_id,
            'participant_name' => $request->participant_name,
            'participant_category' => $request->participant_category,
            'gender' => $request->gender,
            'age_text' => $request->age_text,
            'weight' => $request->weight,
            'height' => $request->height,
            'blood_pressure' => $request->blood_pressure,
            'blood_sugar' => $request->blood_sugar,
            'cholesterol' => $request->cholesterol,
            'vitamin_immunization' => $request->vitamin_immunization,
            'health_status' => $request->health_status,
            'notes' => $request->notes,
            'recorded_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Catatan pemeriksaan kesehatan Posyandu berhasil disimpan.');
    }

    /**
     * Hapus Catatan Pemeriksaan
     */
    public function destroyRecord($id)
    {
        $record = PosyanduRecord::findOrFail($id);
        $record->delete();

        return redirect()->back()->with('success', 'Catatan Posyandu berhasil dihapus.');
    }
}
