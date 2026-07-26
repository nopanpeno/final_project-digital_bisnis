<?php

namespace App\Http\Controllers;

use App\Models\Organizer;
use Illuminate\Http\Request;

class SuperadminOrganizerController extends Controller
{
    public function index(Request $request)
    {
        $organizers = Organizer::withCount('events')
            ->with('user')
            ->latest()
            ->get();

        return view('superadmin.organizers.index', compact('organizers'));
    }

    public function approve(Organizer $organizer)
    {
        $organizer->update(['status' => 'approved']);

        return back()->with('success', "Organizer \"{$organizer->name}\" berhasil di-approve.");
    }

    public function suspend(Organizer $organizer)
    {
        $organizer->update(['status' => 'suspended']);

        return back()->with('success', "Organizer \"{$organizer->name}\" berhasil di-suspend.");
    }
}