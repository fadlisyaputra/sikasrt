<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\IuranType;
use App\Models\IuranPayment;
use App\Models\CashTransaction;
use Carbon\Carbon;

class IuranController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isWarga()) {
            $payments = IuranPayment::with('iuranType', 'verifier')
                ->where('user_id', $user->id)
                ->latest()
                ->paginate(15);
        } else {
            $payments = IuranPayment::with('user', 'iuranType', 'verifier')
                ->latest()
                ->paginate(20);
        }

        $iuranTypes = IuranType::where('is_active', true)->get();

        return view('iuran.index', compact('payments', 'iuranTypes'));
    }

    public function storeType(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'period_type' => 'required|in:monthly,incident',
            'description' => 'nullable|string',
        ]);

        IuranType::create([
            'name' => $request->name,
            'amount' => $request->amount,
            'period_type' => $request->period_type,
            'description' => $request->description,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Jenis iuran baru berhasil ditambahkan.');
    }

    public function storePayment(Request $request)
    {
        $request->validate([
            'iuran_type_id' => 'required|exists:iuran_types,id',
            'period_month' => 'required|integer|between:1,12',
            'period_year' => 'required|integer|min:2020',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:transfer,cash',
            'proof_image' => 'nullable|image|max:4096',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();
        $proofPath = null;

        if ($request->hasFile('proof_image')) {
            $file = $request->file('proof_image');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/proofs'), $fileName);
            $proofPath = 'uploads/proofs/' . $fileName;
        }

        IuranPayment::create([
            'user_id' => $user->id,
            'iuran_type_id' => $request->iuran_type_id,
            'period_month' => $request->period_month,
            'period_year' => $request->period_year,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'proof_image' => $proofPath,
            'status' => 'pending',
            'notes' => $request->notes,
        ]);

        return redirect()->back()->with('success', 'Bukti bayar iuran berhasil diunggah! Menunggu verifikasi Bendahara.');
    }

    public function verifyPayment(Request $request, $id)
    {
        $payment = IuranPayment::findOrFail($id);

        $request->validate([
            'action' => 'required|in:approve,reject',
            'notes' => 'nullable|string',
        ]);

        if ($request->action === 'approve') {
            $payment->update([
                'status' => 'approved',
                'verified_by' => Auth::id(),
                'verified_at' => Carbon::now(),
                'notes' => $request->notes ?? $payment->notes,
            ]);

            // Automatically record in Cash Ledger (Pemasukan)
            CashTransaction::create([
                'type' => 'pemasukan',
                'category' => 'Iuran Warga',
                'amount' => $payment->amount,
                'transaction_date' => Carbon::now()->toDateString(),
                'description' => 'Iuran ' . $payment->iuranType->name . ' - ' . $payment->user->name . ' (' . $payment->month_name . ' ' . $payment->period_year . ')',
                'iuran_payment_id' => $payment->id,
                'created_by' => Auth::id(),
            ]);

            $message = 'Pembayaran iuran disetujui dan otomatis masuk ke Kas RT.';
        } else {
            $payment->update([
                'status' => 'rejected',
                'verified_by' => Auth::id(),
                'verified_at' => Carbon::now(),
                'notes' => $request->notes ?? 'Ditolak oleh bendahara/admin.',
            ]);

            $message = 'Pembayaran iuran ditolak.';
        }

        return redirect()->back()->with('success', $message);
    }
}
