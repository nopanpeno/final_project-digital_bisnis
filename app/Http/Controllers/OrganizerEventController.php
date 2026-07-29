<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrganizerEventController extends Controller
{
    /**
     * Daftar event milik organizer yang sedang login.
     * Event::query() otomatis ke-filter oleh OrganizerScope (global scope di model Event),
     * jadi organizer cuma bisa lihat event miliknya sendiri, gak bisa liat punya organizer lain.
     */
    public function index()
    {
        $events = Event::with('category')->latest()->paginate(10);

        return view('organizer.events.index', compact('events'));
    }

    public function create()
    {
        $categories = Category::all();

        return view('organizer.events.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id'  => 'required|exists:categories,id',
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'date'         => 'required|date',
            'location'     => 'required|string|max:255',
            'price'        => 'required|numeric|min:0',
            'stock'        => 'required|numeric|min:1',
            'poster'       => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('poster')) {
            $data['poster_path'] = $request->file('poster')->store('posters', 'public');
        }

        // organizer_id otomatis ke-set lewat Event::booted() -> static::creating(),
        // karena user yang login role-nya 'organizer'. Gak perlu di-set manual di sini.
        Event::create($data);

        return redirect()
            ->route('organizer.events.index')
            ->with('success', 'Event berhasil ditambahkan.');
    }

    /**
     * Route model binding otomatis 404 kalau event ini bukan milik organizer yang login,
     * karena OrganizerScope sudah nyaring query-nya dari level model.
     */
    public function edit(Event $event)
    {
        $categories = Category::all();

        return view('organizer.events.edit', compact('event', 'categories'));
    }

    public function update(Request $request, Event $event)
    {
        $data = $request->validate([
            'category_id'  => 'required|exists:categories,id',
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string',
            'date'         => 'required|date',
            'location'     => 'required|string|max:255',
            'price'        => 'required|numeric|min:0',
            'stock'        => 'required|numeric|min:1',
            'poster'       => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('poster')) {
            if ($event->poster_path) {
                Storage::disk('public')->delete($event->poster_path);
            }
            $data['poster_path'] = $request->file('poster')->store('posters', 'public');
        }

        $event->update($data);

        return redirect()
            ->route('organizer.events.index')
            ->with('success', 'Event berhasil diperbarui.');
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return redirect()
            ->route('organizer.events.index')
            ->with('success', 'Event berhasil dihapus.');
    }
}