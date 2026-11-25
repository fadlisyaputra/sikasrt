<?php

namespace App\Http\Controllers;

use App\Models\UmkmProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UmkmController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isPengurus = in_array($user->role, ['superadmin', 'rt', 'bendahara']);

        $query = UmkmProduct::with('user')->where('status', 'active')->latest();

        if ($request->filled('kategori') && $request->kategori !== 'Semua') {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('nama_produk', 'like', "%{$s}%")
                  ->orWhere('nama_usaha', 'like', "%{$s}%")
                  ->orWhere('deskripsi', 'like', "%{$s}%");
            });
        }

        $products = $query->paginate(12);

        // Produk milik user yang sedang login
        $myProducts = UmkmProduct::where('user_id', $user->id)->latest()->get();

        // Kategori Master
        $kategoriList = [
            'Semua',
            'Makanan & Minuman',
            'Jasa & Servis',
            'Sembako',
            'Fashion & Busana',
            'Kerajinan Tangan',
            'Elektronik & Gadget',
            'Lainnya',
        ];

        // Metrics
        $totalProduk = UmkmProduct::where('status', 'active')->count();
        $totalUsaha = UmkmProduct::where('status', 'active')->distinct('nama_usaha')->count('nama_usaha');
        $myProdukCount = $myProducts->count();

        return view('umkm.index', compact(
            'products',
            'myProducts',
            'kategoriList',
            'totalProduk',
            'totalUsaha',
            'myProdukCount',
            'isPengurus'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_usaha' => 'required|string|max:150',
            'nama_produk' => 'required|string|max:150',
            'kategori' => 'required|string|max:100',
            'deskripsi' => 'required|string|max:1000',
            'harga' => 'required|numeric|min:0',
            'satuan' => 'required|string|max:50',
            'no_whatsapp' => 'required|string|max:30',
            'alamat_usaha' => 'nullable|string|max:255',
        ]);

        UmkmProduct::create([
            'user_id' => Auth::id(),
            'nama_usaha' => $request->nama_usaha,
            'nama_produk' => $request->nama_produk,
            'kategori' => $request->kategori,
            'deskripsi' => $request->deskripsi,
            'harga' => $request->harga,
            'satuan' => $request->satuan,
            'no_whatsapp' => $request->no_whatsapp,
            'alamat_usaha' => $request->alamat_usaha ?? Auth::user()->address,
            'status' => 'active',
            'is_verified' => true,
        ]);

        return redirect()->route('umkm.index')->with('success', 'Produk UMKM Anda berhasil ditambahkan ke etalase warga RT!');
    }

    public function update(Request $request, $id)
    {
        $product = UmkmProduct::findOrFail($id);
        $user = Auth::user();

        if ($product->user_id !== $user->id && !in_array($user->role, ['superadmin', 'rt'])) {
            abort(403, 'Anda tidak memiliki hak untuk mengubah produk ini.');
        }

        $request->validate([
            'nama_usaha' => 'required|string|max:150',
            'nama_produk' => 'required|string|max:150',
            'kategori' => 'required|string|max:100',
            'deskripsi' => 'required|string|max:1000',
            'harga' => 'required|numeric|min:0',
            'satuan' => 'required|string|max:50',
            'no_whatsapp' => 'required|string|max:30',
            'alamat_usaha' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $product->update($request->only([
            'nama_usaha', 'nama_produk', 'kategori', 'deskripsi',
            'harga', 'satuan', 'no_whatsapp', 'alamat_usaha', 'status'
        ]));

        return redirect()->route('umkm.index')->with('success', 'Data produk UMKM berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $product = UmkmProduct::findOrFail($id);
        $user = Auth::user();

        if ($product->user_id !== $user->id && !in_array($user->role, ['superadmin', 'rt'])) {
            abort(403, 'Anda tidak memiliki hak untuk menghapus produk ini.');
        }

        $product->delete();

        return redirect()->route('umkm.index')->with('success', 'Produk UMKM berhasil dihapus.');
    }
}
