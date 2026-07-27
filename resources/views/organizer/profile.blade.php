@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10">

    <div class="glass rounded-2xl p-6 mb-8">
        <div class="flex items-center gap-4">
            <img src="{{ $organizer->logo ? asset('storage/'.$organizer->logo) : 'https://ui-avatars.com/api/?name='.urlencode($organizer->name) }}"
                 class="w-20 h-20 rounded-full object-cover" alt="{{ $organizer->name }}">
            <div>
                <h1 class="text-2xl font-bold">{{ $organizer->name }}</h1>
                <p class="text-gray-500">{{ $organizer->description }}</p>
                <div class="flex items-center gap-2 mt-2">
                    <span class="text-yellow-500 text-lg">
                        {{ str_repeat('★', round($averageRating ?? 0)) }}{{ str_repeat('☆', 5 - round($averageRating ?? 0)) }}
                    </span>
                    <span class="text-sm text-gray-500">
                        {{ number_format($averageRating ?? 0, 1) }} ({{ $reviewCount }} ulasan)
                    </span>
                </div>
            </div>
        </div>
    </div>

    <h2 class="text-xl font-semibold mb-4">Event dari {{ $organizer->name }}</h2>
    <div class="grid md:grid-cols-2 gap-4 mb-10">
        @foreach($events as $event)
            <div class="glass rounded-xl p-4">
                <h3 class="font-bold">{{ $event->title }}</h3>
                <p class="text-sm text-gray-500">{{ $event->date }} · {{ $event->location }}</p>
            </div>
        @endforeach
    </div>

    <h2 class="text-xl font-semibold mb-4">Ulasan & Testimoni</h2>
    <div class="space-y-4">
        @forelse($reviews as $review)
            <div class="glass rounded-xl p-4">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="font-semibold">{{ $review->user->name }}</p>
                        <p class="text-xs text-gray-500">{{ $review->event->title }}</p>
                    </div>
                    <span class="text-yellow-500">
                        {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
                    </span>
                </div>
                @if($review->comment)
                    <p class="mt-2 text-gray-700">{{ $review->comment }}</p>
                @endif
            </div>
        @empty
            <p class="text-gray-500">Belum ada ulasan untuk penyelenggara ini.</p>
        @endforelse
    </div>

</div>
@endsection