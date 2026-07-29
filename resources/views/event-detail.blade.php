@extends('layouts.app')

@section('content')
<main class="max-w-7xl mx-auto px-6 py-12 grid grid-cols-1 lg:grid-cols-3 gap-12">
    <!-- Left: Poster -->
    <div class="lg:col-span-1">
        <div class="sticky top-32">
            <img src="{{ ($event->poster_path && Storage::disk('public')->exists($event->poster_path))
                ? asset('storage/' . $event->poster_path)
                : 'https://placehold.co/200x600' }}" alt="{{ $event->title }}" class="w-full rounded-[2.5rem] shadow-2xl border-8 border-white object-cover aspect-[3/4]">

            @if($event->organizer)
            <a href="{{ route('organizer.profile', $event->organizer->slug) }}" class="block mt-8 p-6 bg-white rounded-3xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                <h4 class="font-bold mb-4">Penyelenggara</h4>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold">
                        {{ strtoupper(substr($event->organizer->name, 0, 2)) }}
                    </div>
                    <div>
                        <p class="font-bold text-slate-800">{{ $event->organizer->name }}</p>
                        <p class="text-xs text-slate-500 flex items-center gap-1">
                            @if($event->organizer->averageRating())
                                <span class="text-yellow-500">★ {{ number_format($event->organizer->averageRating(), 1) }}</span>
                            @endif
                            Lihat Profil
                        </p>
                    </div>
                </div>
            </a>
            @endif
        </div>
    </div>

    <!-- Right: Details -->
    <div class="lg:col-span-2 space-y-12">
        <div class="space-y-4">
            <span class="px-4 py-1.5 bg-indigo-100 text-indigo-700 rounded-full text-sm font-bold uppercase tracking-wider">{{ $event->category->name }}</span>
            <h1 class="text-4xl md:text-5xl font-black leading-tight">{{ $event->title }}</h1>
            <div class="flex flex-wrap gap-6 text-slate-500 font-medium">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                    <span>{{ \Carbon\Carbon::parse($event->date)->format('d M Y, H:i') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                        </path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span>{{ $event->location }}</span>
                </div>
            </div>
        </div>

        <div class="prose prose-slate max-w-none">
            <h3 class="text-2xl font-bold mb-4">Deskripsi Event</h3>
            <p class="text-lg text-slate-600 leading-relaxed">
                {{ $event->description }}
            </p>
        </div>

        <div class="bg-indigo-600 rounded-[2.5rem] p-8 md:p-12 text-white shadow-2xl shadow-indigo-200 relative overflow-hidden">
            <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-8">
                <div>
                    <p class="text-indigo-200 font-bold uppercase tracking-widest text-sm mb-2">Harga Tiket</p>
                    <h2 class="text-5xl font-black">Rp {{ number_format($event->price, 0, ',', '.') }}<span class="text-lg font-medium text-indigo-200">/
                            orang</span></h2>
                    <p class="mt-4 text-indigo-100 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Sisa stok: <span class="font-bold underline">{{ $event->stock }} Tiket lagi!</span>
                    </p>
                </div>
                <div>
                    <a href="{{ route('checkout.create', $event->id) }}" class="inline-block px-10 py-5 bg-white text-indigo-600 rounded-2xl font-black text-xl hover:scale-105 transition-transform shadow-xl">
                        Pesan Sekarang
                    </a>
                </div>
            </div>
            <!-- Decoration -->
            <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-white opacity-10 rounded-full"></div>
            <div class="absolute -left-10 -top-10 w-32 h-32 bg-indigo-400 opacity-20 rounded-full"></div>
        </div>

        <div class="space-y-4">
            <h3 class="text-xl font-bold">Kebijakan Tiket</h3>
            <ul class="space-y-3 text-slate-500">
                <li class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-green-500 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                        </path>
                    </svg>
                    E-Ticket akan dikirimkan otomatis setelah pembayaran berhasil.
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-green-500 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                        </path>
                    </svg>
                    Tiket dapat discan di pintu masuk (Check-in).
                </li>
                <li class="flex items-start gap-2 text-rose-500">
                    <svg class="w-5 h-5 text-rose-500 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    Tiket yang sudah dibeli tidak dapat direfund.
                </li>
            </ul>
        </div>

        <!-- Rating & Review -->
        <div class="space-y-6" id="ulasan">
            <h3 class="text-2xl font-bold">Ulasan & Testimoni</h3>

            @auth
                @if(session('success'))
                    <div class="bg-green-100 text-green-700 p-4 rounded-2xl">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="bg-red-100 text-red-700 p-4 rounded-2xl">{{ session('error') }}</div>
                @endif

                @if($event->hasSuccessfulTransactionFor(auth()->user()->email) && $event->isReviewPeriodOpen())
                    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
                        <h4 class="font-bold mb-3">Beri Ulasan untuk Event Ini</h4>
                        <form action="{{ route('reviews.store', $event->id) }}" method="POST" class="space-y-3">
                            @csrf
                            <select name="rating" class="border rounded-xl p-3 w-full" required>
                                <option value="">Pilih rating</option>
                                @for($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}">{{ $i }} ★</option>
                                @endfor
                            </select>
                            <textarea name="comment" class="border rounded-xl p-3 w-full" rows="3" placeholder="Ceritakan pengalaman Anda... (opsional)"></textarea>
                            <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700">
                                Kirim Ulasan
                            </button>
                        </form>
                    </div>
                @elseif(!$event->hasSuccessfulTransactionFor(auth()->user()->email))
                    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 text-center text-slate-500">
                        Anda harus membeli tiket event ini sebelum bisa memberi ulasan.
                    </div>
                @else
                    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 text-center text-slate-500">
                        Ulasan baru bisa diberikan setelah acara selesai (minimal H+1).
                    </div>
                @endif
            @else
                <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6 text-center text-slate-500">
                    <a href="{{ route('auth.google') }}" class="text-indigo-600 font-bold underline">Login</a> untuk memberi ulasan
                </div>
            @endauth

            <div class="space-y-4">
                @forelse($event->reviews()->with('user')->latest()->get() as $review)
                    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
                        <div class="flex justify-between items-start">
                            <p class="font-bold text-slate-800">{{ $review->user->name }}</p>
                            <span class="text-yellow-500">
                                {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
                            </span>
                        </div>
                        @if($review->comment)
                            <p class="mt-2 text-slate-600">{{ $review->comment }}</p>
                        @endif
                    </div>
                @empty
                    <p class="text-slate-500">Belum ada ulasan untuk event ini.</p>
                @endforelse
            </div>
        </div>
    </div>
</main>
@endsection