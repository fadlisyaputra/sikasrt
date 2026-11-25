<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Announcement;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with('author')->latest()->paginate(15);
        return view('announcements.index', compact('announcements'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'content' => 'required|string',
        ]);

        Announcement::create([
            'title' => $request->title,
            'category' => $request->category,
            'content' => $request->content,
            'author_id' => Auth::id(),
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Pengumuman berhasil dipublikasikan.');
    }

    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->delete();

        return redirect()->back()->with('success', 'Pengumuman berhasil dihapus.');
    }
}
