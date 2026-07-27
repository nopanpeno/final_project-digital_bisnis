<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Event $event)
{
    $request->validate([
        'rating' => 'required|integer|min:1|max:5',
        'comment' => 'nullable|string|max:1000',
    ]);

    $email = auth()->user()->email;

    if (!$event->hasSuccessfulTransactionFor($email)) {
        return back()->with('error', 'Anda harus membeli tiket event ini sebelum bisa memberi ulasan.');
    }

    if (!$event->isReviewPeriodOpen()) {
        return back()->with('error', 'Ulasan baru bisa diberikan setelah acara selesai (minimal H+1).');
    }

    Review::updateOrCreate(
        [
            'user_id' => auth()->id(),
            'event_id' => $event->id,
        ],
        [
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]
    );

    return back()->with('success', 'Terima kasih atas ulasan Anda!');
}
}