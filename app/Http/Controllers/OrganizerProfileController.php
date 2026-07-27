<?php

namespace App\Http\Controllers;

use App\Models\Organizer;

class OrganizerProfileController extends Controller
{
    public function show(string $slug)
    {
        $organizer = Organizer::where('slug', $slug)
            ->where('status', 'approved')
            ->firstOrFail();

        $events = $organizer->events()->latest()->get();

        $reviews = $organizer->reviews()
            ->with(['user', 'event'])
            ->latest()
            ->get();

        $averageRating = $organizer->averageRating();
        $reviewCount = $organizer->reviewCount();

        return view('organizer.profile', compact(
            'organizer', 'events', 'reviews', 'averageRating', 'reviewCount'
        ));
    }
}