<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\IotRfidCard;

class WargaController extends Controller
{
    public function index(Request $request)
    {
        $query = User::latest();

        if ($request->has('role') && in_array($request->role, ['superadmin', 'bendahara', 'rt', 'warga'])) {
            $query->where('role', $request->role);
        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('rfid_uid', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15);

        return view('warga.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:superadmin,bendahara,rt,warga',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'rt_rw' => 'required|string|max:50',
            'status_warga' => 'required|in:tetap,kontrak',
            'rfid_uid' => 'nullable|string|max:50|unique:users,rfid_uid',
        ]);

        $newUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'phone' => $request->phone,
            'address' => $request->address,
            'rt_rw' => $request->rt_rw,
            'status_warga' => $request->status_warga,
            'rfid_uid' => $request->filled('rfid_uid') ? strtoupper(trim($request->rfid_uid)) : null,
        ]);

        if ($newUser->rfid_uid) {
            IotRfidCard::updateOrCreate(
                ['user_id' => $newUser->id],
                [
                    'rfid_uid' => $newUser->rfid_uid,
                    'card_label' => 'e-KTP ' . $newUser->name,
                    'status' => 'active',
                ]
            );
        }

        return redirect()->back()->with('success', 'Data warga/pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|in:superadmin,bendahara,rt,warga',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'rt_rw' => 'required|string|max:50',
            'status_warga' => 'required|in:tetap,kontrak',
            'rfid_uid' => 'nullable|string|max:50|unique:users,rfid_uid,' . $id,
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'phone' => $request->phone,
            'address' => $request->address,
            'rt_rw' => $request->rt_rw,
            'status_warga' => $request->status_warga,
            'rfid_uid' => $request->filled('rfid_uid') ? strtoupper(trim($request->rfid_uid)) : null,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        if ($user->rfid_uid) {
            IotRfidCard::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'rfid_uid' => $user->rfid_uid,
                    'card_label' => 'e-KTP ' . $user->name,
                    'status' => 'active',
                ]
            );
        } else {
            IotRfidCard::where('user_id', $user->id)->delete();
        }

        return redirect()->back()->with('success', 'Data warga/pengguna berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'Pengguna berhasil dihapus.');
    }
}
