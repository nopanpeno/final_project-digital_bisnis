@extends('layouts.app')

@section('content')
<main class="max-w-4xl mx-auto px-6 py-12 space-y-8">
    <div>
        <h1 class="text-3xl md:text-4xl font-black">Tiket Saya</h1>
        <p class="text-slate-500 mt-2">Semua riwayat pembelian tiket kamu, termasuk event yang sudah selesai.</p>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-4 rounded-2xl">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 text-red-700 p-4 rounded-2xl">{{ session('error') }}</div>
    @endif

    <div class="space-y-4">
        @forelse($transactions as $trx)
            @php
                $event = $trx->event;
                $alreadyReviewed = $event
                    ? $event->reviews()->where('user_id', auth()->id())->exists()
                    : false;
                $isPast = $event ? $event->isReviewPeriodOpen() : false;
            @endphp

            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 flex flex-col md:flex-row md:items-center gap-6">
                <div class="w-full md:w-32 shrink-0">
                    <img src="{{ ($event && $event->poster_path && Storage::disk('public')->exists($event->poster_path))
                        ? asset('storage/' . $event->poster_path)
                        : 'https://placehold.co/200x200' }}"
                        alt="{{ $event->title ?? 'Event' }}"
                        class="w-full h-32 md:h-24 object-cover rounded-2xl">
                </div>

                <div class="flex-1 space-y-1">
                    <h3 class="font-bold text-lg text-slate-800">{{ $event->title ?? 'Event tidak ditemukan' }}</h3>
                    @if($event)
                        <p class="text-sm text-slate-500">
                            {{ \Carbon\Carbon::parse($event->date)->format('d M Y, H:i') }} &middot; {{ $event->location }}
                        </p>
                    @endif
                    <p class="text-xs text-slate-400 font-mono">Order ID: {{ $trx->order_id }}</p>
                </div>

                <div class="shrink-0">
                    @if(!$event)
                        <span class="text-slate-400 text-sm">-</span>
                    @elseif($alreadyReviewed)
                        <span class="inline-block px-4 py-2 rounded-xl bg-green-100 text-green-700 font-bold text-sm">
                            ✓ Sudah Direview
                        </span>
                    @elseif($isPast)
                        <a href="{{ route('events.show', $event->id) }}#ulasan"
                           class="inline-block px-4 py-2 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700 transition">
                            Beri Ulasan
                        </a>
                    @else
                        <span class="inline-block px-4 py-2 rounded-xl bg-slate-100 text-slate-500 font-bold text-sm text-center">
                            Review dibuka H+1<br>setelah acara
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-12 text-center text-slate-500">
                Kamu belum pernah membeli tiket event apapun.
                <div class="mt-4">
                    <a href="{{ route('home') }}" class="text-indigo-600 font-bold underline">Jelajahi event</a>
                </div>
            </div>
        @endforelse
    </div>
</main>
@endsection