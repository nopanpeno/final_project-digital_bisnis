@extends('layouts.app')
@section('title', 'Checkout - ' . $event->title)
@section('content')
    <main class="max-w-3xl mx-auto px-6 py-20">
        <div class="mb-12">
            <a href="{{ route('events.show', $event->id) }}" class="text-indigo-600 font-bold flex items-center gap-2 mb-6">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Kembali ke Event
            </a>
            <h1 class="text-4xl font-extrabold">Checkout</h1>
            <p class="text-slate-500 mt-2">Lengkapi data Anda untuk mendapatkan tiket.</p>
        </div>

        @if (session('error'))
            <div class="mb-6 p-4 bg-red-100 text-red-700 rounded-xl font-bold">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 gap-8">
            <!-- Summary Card -->
            <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-sm">
                <h3 class="text-xl font-bold mb-6 border-b pb-4">Pesanan Anda</h3>
                <div class="flex gap-6 items-start">
                    <img src="{{ $event->poster_path && Storage::disk('public')->exists($event->poster_path)
                        ? asset('storage/' . $event->poster_path)
                        : 'https://placehold.co/200x200' }}"
                        alt="Event" class="w-24 h-24 rounded-2xl object-cover">
                    <div>
                        <h4 class="font-extrabold text-lg">{{ $event->title }}</h4>
                        <p class="text-slate-500">{{ $event->date->format('d M Y') }} • {{ $event->location }}</p>
                        <p class="text-indigo-600 font-bold mt-2">1 x Rp {{ number_format($event->price, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="mt-8 pt-6 border-t space-y-3">
                    <div class="flex justify-between text-slate-500">
                        <span>Harga Tiket</span>
                        <span>{{ $event->price == 0 ? 'Gratis' : 'Rp ' . number_format($event->price, 0, ',', '.') }}</span>
                    </div>
                    @if($event->price > 0)
                        <div class="flex justify-between text-slate-500">
                            <span>Biaya Layanan</span>
                            <span>Rp 5.000</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-2xl font-black mt-4 pt-4 border-t">
                        <span>Total Bayar</span>
                        <span class="text-indigo-600">{{ $event->price == 0 ? 'Gratis' : 'Rp ' . number_format($event->price + 5000, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-sm">
                <h3 class="text-xl font-bold mb-6 italic text-indigo-600 underline underline-offset-8">📦 Data Pemesan</h3>

                @if (!$googleUser)
                    <a href="{{ route('auth.google.checkout', $event) }}"
                        class="w-full flex items-center justify-center gap-3 border border-gray-300 rounded-lg py-2.5 px-4 hover:bg-gray-50 transition mb-4">
                        <svg class="w-5 h-5" viewBox="0 0 24 24">
                            <path fill="#4285F4"
                                d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                            <path fill="#34A853"
                                d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                            <path fill="#FBBC05"
                                d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" />
                            <path fill="#EA4335"
                                d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" />
                        </svg>
                        <span class="text-sm font-medium text-gray-700">Continue with Google untuk isi otomatis</span>
                    </a>

                    <div class="flex items-center gap-3 my-4">
                        <div class="flex-1 h-px bg-gray-300"></div>
                        <span class="text-sm text-gray-500">atau isi manual</span>
                        <div class="flex-1 h-px bg-gray-300"></div>
                    </div>
                @else
                    <div class="flex items-center gap-3 mb-6 bg-indigo-50 border border-indigo-100 rounded-2xl p-4">
                        @if ($googleUser->avatar)
                            <img src="{{ $googleUser->avatar }}" alt="{{ $googleUser->name }}" class="w-10 h-10 rounded-full">
                        @endif
                        <div>
                            <p class="text-sm font-bold text-slate-700">Masuk sebagai {{ $googleUser->name }}</p>
                            <p class="text-xs text-slate-500">{{ $googleUser->email }}</p>
                        </div>
                    </div>
                @endif

                <form action="{{ route('checkout.store', $event->id) }}" method="POST" class="space-y-6">
                    @csrf
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide">Nama
                            Lengkap</label>
                        <input type="text" name="customer_name" placeholder="Masukkan nama sesuai identitas"
                            class="w-full px-5 py-4 bg-white border-2 border-slate-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 outline-none transition font-medium"
                            required value="{{ old('customer_name', $googleUser->name ?? '') }}">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide">Email
                                Aktif</label>
                            <input type="email" name="customer_email" placeholder="contoh@gmail.com"
                                class="w-full px-5 py-4 bg-white border-2 border-slate-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 outline-none transition font-medium"
                                required value="{{ old('customer_email', $googleUser->email ?? '') }}">
                            <p class="text-[10px] text-slate-400 mt-2 font-bold uppercase tracking-tighter">*E-Ticket
                                akan dikirim ke email ini</p>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide">No.
                                WhatsApp</label>
                            <input type="tel" name="customer_phone" placeholder="08xxxxxxx"
                                class="w-full px-5 py-4 bg-white border-2 border-slate-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-600 outline-none transition font-medium"
                                required value="{{ old('customer_phone') }}">
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full py-5 bg-indigo-600 text-white rounded-2xl font-black text-xl shadow-xl shadow-indigo-200 hover:bg-indigo-700 active:scale-95 transition-all">
                        Lanjut Pembayaran
                    </button>
                    <p class="text-center text-xs text-slate-400">Dengan menekan tombol di atas, Anda menyetujui Syarat
                        & Ketentuan kami.</p>
                </form>
            </div>

        </div>
    </main>
@endsection