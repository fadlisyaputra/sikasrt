<?php

namespace App\Http\Controllers;

use App\Models\SuratRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class SuratController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isPengurus = in_array($user->role, ['superadmin', 'rt', 'bendahara']);

        $query = SuratRequest::with(['user', 'approver'])->latest();

        if (!$isPengurus) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis_surat', 'like', '%' . $request->jenis . '%');
        }

        $suratRequests = $query->paginate(15);

        // Metrics
        $totalQuery = $isPengurus ? SuratRequest::query() : SuratRequest::where('user_id', $user->id);
        $totalPengajuan = (clone $totalQuery)->count();
        $totalApproved = (clone $totalQuery)->where('status', 'approved')->count();
        $totalPending = (clone $totalQuery)->where('status', 'pending')->count();
        $totalRejected = (clone $totalQuery)->where('status', 'rejected')->count();

        $wargaList = $isPengurus ? User::where('role', 'warga')->orderBy('name')->get() : collect();

        return view('surat.index', compact(
            'suratRequests',
            'isPengurus',
            'totalPengajuan',
            'totalApproved',
            'totalPending',
            'totalRejected',
            'wargaList'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis_surat' => 'required|string|max:255',
            'keperluan' => 'required|string|max:1000',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        $userId = Auth::id();
        if (in_array(Auth::user()->role, ['superadmin', 'rt', 'bendahara']) && $request->filled('user_id')) {
            $userId = $request->user_id;
        }

        SuratRequest::create([
            'user_id' => $userId,
            'jenis_surat' => $request->jenis_surat,
            'keperluan' => $request->keperluan,
            'keterangan' => $request->keterangan,
            'status' => 'pending',
        ]);

        return redirect()->route('surat.index')->with('success', 'Pengajuan surat berhasil dikirim! Menunggu persetujuan pengurus RT.');
    }

    public function approve(Request $request, $id)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['superadmin', 'rt'])) {
            return back()->with('error', 'Hanya Ketua RT atau Superadmin yang dapat menyetujui surat pengantar.');
        }

        $surat = SuratRequest::findOrFail($id);

        $request->validate([
            'nomor_surat' => 'nullable|string|max:100',
            'catatan_pengurus' => 'nullable|string|max:500',
        ]);

        // Auto format nomor surat jika kosong
        $nomorSurat = $request->nomor_surat;
        if (empty($nomorSurat)) {
            $countThisMonth = SuratRequest::whereNotNull('nomor_surat')
                ->whereYear('approved_at', Carbon::now()->year)
                ->whereMonth('approved_at', Carbon::now()->month)
                ->count() + 1;
            
            $romanMonths = [
                1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
                7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
            ];
            $roman = $romanMonths[Carbon::now()->month] ?? 'XI';

            $nomorSurat = sprintf('%03d/SK-RT05/%s/%d', $countThisMonth, $roman, Carbon::now()->year);
        }

        $surat->update([
            'nomor_surat' => $nomorSurat,
            'status' => 'approved',
            'catatan_pengurus' => $request->catatan_pengurus ?? 'Surat pengantar telah disetujui.',
            'approved_by' => $user->id,
            'approved_at' => Carbon::now(),
        ]);

        return redirect()->route('surat.index')->with('success', "Surat berhasil disetujui dengan Nomor: {$nomorSurat}");
    }

    public function reject(Request $request, $id)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['superadmin', 'rt'])) {
            return back()->with('error', 'Hanya Ketua RT atau Superadmin yang dapat memproses pengajuan surat.');
        }

        $surat = SuratRequest::findOrFail($id);

        $request->validate([
            'catatan_pengurus' => 'required|string|max:500',
        ]);

        $surat->update([
            'status' => 'rejected',
            'catatan_pengurus' => $request->catatan_pengurus,
            'approved_by' => $user->id,
            'approved_at' => Carbon::now(),
        ]);

        return redirect()->route('surat.index')->with('success', 'Pengajuan surat telah ditolak dengan catatan.');
    }

    public function print($id)
    {
        $surat = SuratRequest::with(['user', 'approver'])->findOrFail($id);
        $user = Auth::user();

        // Security check
        if (!in_array($user->role, ['superadmin', 'rt', 'bendahara']) && $surat->user_id !== $user->id) {
            abort(403, 'Akses tidak diizinkan');
        }

        $ketuaRt = User::where('role', 'rt')->first() ?? $surat->approver ?? $user;

        return view('surat.print', compact('surat', 'ketuaRt'));
    }
}
