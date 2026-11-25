<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\WargaBill;
use App\Models\IuranType;
use App\Models\IuranPayment;
use App\Models\CashTransaction;
use App\Models\User;
use Carbon\Carbon;

class TagihanController extends Controller
{
    /**
     * Tampilan Utama Tagihan Iuran Warga
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $selectedMonth = (int)$request->input('month', Carbon::now()->month);
        $selectedYear = (int)$request->input('year', Carbon::now()->year);
        $selectedStatus = $request->input('status', 'all');

        $query = WargaBill::with(['user', 'iuranType', 'creator']);

        if ($selectedMonth) {
            $query->where('period_month', $selectedMonth);
        }
        if ($selectedYear) {
            $query->where('period_year', $selectedYear);
        }
        if ($selectedStatus !== 'all') {
            $query->where('status', $selectedStatus);
        }

        if ($user->isWarga()) {
            // Warga hanya melihat tagihan miliknya
            $query->where('user_id', $user->id);
            $bills = $query->latest('due_date')->paginate(15);

            $myUnpaidCount = WargaBill::where('user_id', $user->id)->where('status', 'unpaid')->count();
            $myUnpaidTotal = WargaBill::where('user_id', $user->id)->where('status', 'unpaid')->sum('amount');
            $myPaidTotal = WargaBill::where('user_id', $user->id)->where('status', 'paid')->sum('amount');

            $totalTagihan = $myUnpaidTotal + $myPaidTotal;
            $totalLunas = $myPaidTotal;
            $totalTunggakan = $myUnpaidTotal;
            $persenLunas = $totalTagihan > 0 ? round(($totalLunas / $totalTagihan) * 100) : 0;
        } else {
            // Admin / Bendahara / RT melihat seluruh tagihan warga
            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            $bills = $query->latest()->paginate(20);

            // Ringkasan Finansial Tagihan
            $calcQuery = WargaBill::where('period_month', $selectedMonth)->where('period_year', $selectedYear);
            $totalTagihan = (clone $calcQuery)->sum('amount');
            $totalLunas = (clone $calcQuery)->where('status', 'paid')->sum('amount');
            $totalTunggakan = (clone $calcQuery)->where('status', 'unpaid')->sum('amount');
            $persenLunas = $totalTagihan > 0 ? round(($totalLunas / $totalTagihan) * 100) : 0;
        }

        $iuranTypes = IuranType::where('is_active', true)->get();
        $wargaList = User::where('role', 'warga')->orderBy('name')->get();

        return view('tagihan.index', compact(
            'bills',
            'selectedMonth',
            'selectedYear',
            'selectedStatus',
            'totalTagihan',
            'totalLunas',
            'totalTunggakan',
            'persenLunas',
            'iuranTypes',
            'wargaList'
        ));
    }

    /**
     * Generate Tagihan Massal untuk Seluruh Warga (Bendahara / RT)
     */
    public function generate(Request $request)
    {
        $request->validate([
            'iuran_type_id' => 'required|exists:iuran_types,id',
            'period_month' => 'required|integer|between:1,12',
            'period_year' => 'required|integer|min:2020',
            'due_date' => 'required|date',
        ]);

        $iuranType = IuranType::findOrFail($request->iuran_type_id);
        $wargas = User::where('role', 'warga')->get();
        $generatedCount = 0;

        foreach ($wargas as $warga) {
            // Cek apakah tagihan untuk warga, jenis iuran, bulan dan tahun ini sudah ada
            $existing = WargaBill::where('user_id', $warga->id)
                ->where('iuran_type_id', $iuranType->id)
                ->where('period_month', $request->period_month)
                ->where('period_year', $request->period_year)
                ->first();

            if (!$existing) {
                $billCode = 'INV-' . $request->period_year . sprintf('%02d', $request->period_month) . '-' . sprintf('%04d', $warga->id);

                WargaBill::create([
                    'bill_code' => $billCode,
                    'user_id' => $warga->id,
                    'iuran_type_id' => $iuranType->id,
                    'period_month' => $request->period_month,
                    'period_year' => $request->period_year,
                    'amount' => $iuranType->amount,
                    'due_date' => $request->due_date,
                    'status' => 'unpaid',
                    'notes' => 'Tagihan ' . $iuranType->name . ' - Periode ' . Carbon::create()->month($request->period_month)->translatedFormat('F') . ' ' . $request->period_year,
                    'created_by' => Auth::id(),
                ]);
                $generatedCount++;
            }
        }

        return redirect()->back()->with('success', "Berhasil men-generate $generatedCount tagihan warga baru untuk bulan " . Carbon::create()->month($request->period_month)->translatedFormat('F') . " $request->period_year.");
    }

    /**
     * Buat Tagihan Individual untuk 1 Warga
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'iuran_type_id' => 'required|exists:iuran_types,id',
            'period_month' => 'required|integer|between:1,12',
            'period_year' => 'required|integer|min:2020',
            'amount' => 'required|numeric|min:1',
            'due_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $billCode = 'INV-' . $request->period_year . sprintf('%02d', $request->period_month) . '-' . sprintf('%04d', $request->user_id) . '-' . rand(10, 99);

        WargaBill::create([
            'bill_code' => $billCode,
            'user_id' => $request->user_id,
            'iuran_type_id' => $request->iuran_type_id,
            'period_month' => $request->period_month,
            'period_year' => $request->period_year,
            'amount' => $request->amount,
            'due_date' => $request->due_date,
            'status' => 'unpaid',
            'notes' => $request->notes,
            'created_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Tagihan warga berhasil diterbitkan.');
    }

    /**
     * Tandai Tagihan Lunas Langsung oleh Bendahara / Admin
     */
    public function markPaid(Request $request, $id)
    {
        $bill = WargaBill::findOrFail($id);

        $request->validate([
            'payment_method' => 'required|in:transfer,cash,iot_rfid',
            'notes' => 'nullable|string',
        ]);

        $now = Carbon::now();

        // 1. Buat record di iuran_payments
        $payment = IuranPayment::create([
            'user_id' => $bill->user_id,
            'iuran_type_id' => $bill->iuran_type_id,
            'period_month' => $bill->period_month,
            'period_year' => $bill->period_year,
            'amount' => $bill->amount,
            'payment_method' => $request->payment_method,
            'status' => 'approved',
            'notes' => 'Pelunasan Tagihan [' . $bill->bill_code . ']',
            'verified_by' => Auth::id(),
            'verified_at' => $now,
        ]);

        // 2. Update status tagihan menjadi paid
        $bill->update([
            'status' => 'paid',
            'paid_at' => $now,
            'iuran_payment_id' => $payment->id,
            'payment_method' => $request->payment_method,
            'notes' => $request->notes ?? $bill->notes,
        ]);

        // 3. Catat pemasukan di Buku Kas RT
        CashTransaction::create([
            'type' => 'pemasukan',
            'category' => 'Iuran Warga',
            'amount' => $bill->amount,
            'transaction_date' => $now->toDateString(),
            'description' => 'Pelunasan Tagihan ' . $bill->bill_code . ' - ' . $bill->user->name . ' (' . $bill->iuranType->name . ')',
            'iuran_payment_id' => $payment->id,
            'created_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Tagihan ' . $bill->bill_code . ' berhasil ditandai LUNAS dan dicatat ke Kas RT.');
    }

    /**
     * Warga Membayar Tagihannya Sendiri (Upload Bukti Transfer atau Titip Tunai)
     */
    public function payBill(Request $request, $id)
    {
        $bill = WargaBill::findOrFail($id);

        if (Auth::user()->isWarga() && $bill->user_id !== Auth::id()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $request->validate([
            'payment_method' => 'required|in:transfer,cash',
            'proof_image' => 'nullable|image|max:4096',
            'notes' => 'nullable|string',
        ]);

        $proofPath = null;
        if ($request->hasFile('proof_image')) {
            $file = $request->file('proof_image');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/proofs'), $fileName);
            $proofPath = 'uploads/proofs/' . $fileName;
        }

        $payment = IuranPayment::create([
            'user_id' => $bill->user_id,
            'iuran_type_id' => $bill->iuran_type_id,
            'period_month' => $bill->period_month,
            'period_year' => $bill->period_year,
            'amount' => $bill->amount,
            'payment_method' => $request->payment_method,
            'proof_image' => $proofPath,
            'status' => 'pending',
            'notes' => $request->notes ?? ('Bayar tagihan ' . $bill->bill_code),
        ]);

        $bill->update([
            'status' => 'pending',
            'iuran_payment_id' => $payment->id,
            'payment_method' => $request->payment_method,
        ]);

        return redirect()->back()->with('success', 'Pembayaran tagihan berhasil diajukan! Menunggu konfirmasi Bendahara.');
    }

    /**
     * Tampilan Kuitansi / Faktur Tagihan (Printable Invoice)
     */
    public function invoice($id)
    {
        $bill = WargaBill::with(['user', 'iuranType', 'iuranPayment', 'creator'])->findOrFail($id);

        if (Auth::user()->isWarga() && $bill->user_id !== Auth::id()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        return view('tagihan.invoice', compact('bill'));
    }

    /**
     * Hapus Tagihan
     */
    public function destroy($id)
    {
        $bill = WargaBill::findOrFail($id);
        $bill->delete();

        return redirect()->back()->with('success', 'Tagihan berhasil dihapus.');
    }
}
