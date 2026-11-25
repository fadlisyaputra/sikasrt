<?php

namespace App\Http\Controllers;

use App\Models\BankSampahCategory;
use App\Models\BankSampahTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BankSampahController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isPengurus = in_array($user->role, ['superadmin', 'rt', 'bendahara']);

        // Kategori sampah & daftar harga
        $categories = BankSampahCategory::orderBy('nama_kategori')->get();

        // Transaksi
        $txQuery = BankSampahTransaction::with(['user', 'category', 'petugas'])->latest();
        if (!$isPengurus) {
            $txQuery->where('user_id', $user->id);
        }
        $transactions = $txQuery->paginate(15);

        // Metrics
        $totalKg = BankSampahTransaction::where('tipe', 'setor')->sum('berat_kg');
        $totalSetorRp = BankSampahTransaction::where('tipe', 'setor')->sum('total_rp');
        $myBalance = $user->bank_sampah_balance;
        $myKg = BankSampahTransaction::where('user_id', $user->id)->where('tipe', 'setor')->sum('berat_kg');

        $wargaList = $isPengurus ? User::where('role', 'warga')->orderBy('name')->get() : collect();

        return view('bank_sampah.index', compact(
            'categories',
            'transactions',
            'isPengurus',
            'totalKg',
            'totalSetorRp',
            'myBalance',
            'myKg',
            'wargaList'
        ));
    }

    public function storeTransaction(Request $request)
    {
        $user = Auth::user();
        $isPengurus = in_array($user->role, ['superadmin', 'rt', 'bendahara']);

        if (!$isPengurus) {
            return back()->with('error', 'Hanya pengurus RT atau petugas Bank Sampah yang dapat mencatat transaksi timbangan.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'tipe' => 'required|in:setor,tarik',
            'category_id' => 'required_if:tipe,setor|nullable|exists:bank_sampah_categories,id',
            'berat_kg' => 'required_if:tipe,setor|nullable|numeric|min:0.1',
            'nominal_tarik' => 'required_if:tipe,tarik|nullable|numeric|min:1000',
            'catatan' => 'nullable|string|max:255',
        ]);

        $targetUser = User::findOrFail($request->user_id);

        if ($request->tipe === 'setor') {
            $cat = BankSampahCategory::findOrFail($request->category_id);
            $berat = (float)$request->berat_kg;
            $hargaSatuan = (float)$cat->harga_per_kg;
            $totalRp = $berat * $hargaSatuan;

            BankSampahTransaction::create([
                'user_id' => $targetUser->id,
                'category_id' => $cat->id,
                'petugas_id' => $user->id,
                'tipe' => 'setor',
                'berat_kg' => $berat,
                'harga_satuan' => $hargaSatuan,
                'total_rp' => $totalRp,
                'status' => 'completed',
                'catatan' => $request->catatan ?? "Setoran {$cat->nama_kategori} seberat {$berat} kg",
            ]);

            return redirect()->route('bank_sampah.index')->with('success', "Setoran sampah {$targetUser->name} berhasil dicatat! Saldo bertambah Rp " . number_format($totalRp, 0, ',', '.'));
        } else {
            // Tarik Tunai Tabungan
            $nominalTarik = (float)$request->nominal_tarik;
            if ($nominalTarik > $targetUser->bank_sampah_balance) {
                return back()->with('error', "Gagal menarik saldo: Saldo tabungan {$targetUser->name} hanya Rp " . number_format($targetUser->bank_sampah_balance, 0, ',', '.'));
            }

            BankSampahTransaction::create([
                'user_id' => $targetUser->id,
                'category_id' => null,
                'petugas_id' => $user->id,
                'tipe' => 'tarik',
                'berat_kg' => 0,
                'harga_satuan' => 0,
                'total_rp' => $nominalTarik,
                'status' => 'completed',
                'catatan' => $request->catatan ?? "Penarikan tunai tabungan sampah",
            ]);

            return redirect()->route('bank_sampah.index')->with('success', "Penarikan tunai Rp " . number_format($nominalTarik, 0, ',', '.') . " untuk {$targetUser->name} berhasil diselesaikan!");
        }
    }

    public function storeCategory(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['superadmin', 'rt', 'bendahara'])) {
            return back()->with('error', 'Hanya pengurus yang dapat menambah jenis sampah.');
        }

        $request->validate([
            'nama_kategori' => 'required|string|max:100',
            'kode' => 'required|string|max:30|unique:bank_sampah_categories,kode',
            'harga_per_kg' => 'required|numeric|min:0',
            'satuan' => 'required|string|max:20',
            'deskripsi' => 'nullable|string|max:255',
        ]);

        BankSampahCategory::create($request->all());

        return redirect()->route('bank_sampah.index')->with('success', 'Jenis sampah baru berhasil ditambahkan.');
    }
}
