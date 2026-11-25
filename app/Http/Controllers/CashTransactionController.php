<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CashTransaction;
use Carbon\Carbon;

class CashTransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = CashTransaction::with('creator')->latest('transaction_date')->latest('id');

        if ($request->has('type') && in_array($request->type, ['pemasukan', 'pengeluaran'])) {
            $query->where('type', $request->type);
        }

        if ($request->has('month') && is_numeric($request->month)) {
            $query->whereMonth('transaction_date', $request->month);
        }

        if ($request->has('year') && is_numeric($request->year)) {
            $query->whereYear('transaction_date', $request->year);
        }

        $transactions = $query->paginate(20);

        $totalIn = CashTransaction::where('type', 'pemasukan')->sum('amount');
        $totalOut = CashTransaction::where('type', 'pengeluaran')->sum('amount');
        $balance = $totalIn - $totalOut;

        return view('kas.index', compact('transactions', 'totalIn', 'totalOut', 'balance'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:pemasukan,pengeluaran',
            'category' => 'required|string|max:100',
            'amount' => 'required|numeric|min:1',
            'transaction_date' => 'required|date',
            'description' => 'required|string',
            'receipt_file' => 'nullable|file|mimes:jpg,png,pdf|max:4096',
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt_file')) {
            $file = $request->file('receipt_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/receipts'), $fileName);
            $receiptPath = 'uploads/receipts/' . $fileName;
        }

        CashTransaction::create([
            'type' => $request->type,
            'category' => $request->category,
            'amount' => $request->amount,
            'transaction_date' => $request->transaction_date,
            'description' => $request->description,
            'receipt_file' => $receiptPath,
            'created_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Transaksi Kas berhasil dicatat.');
    }

    public function destroy($id)
    {
        $transaction = CashTransaction::findOrFail($id);
        $transaction->delete();

        return redirect()->back()->with('success', 'Transaksi Kas berhasil dihapus.');
    }
}
