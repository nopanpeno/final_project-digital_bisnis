@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-12 mt-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold">Kelola Event Saya</h1>
            <p class="text-slate-500 mt-1">Buat dan atur event milik organizer kamu sendiri.</p>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('organizer.dashboard') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">
                ← Kembali ke Dashboard
            </a>
            <a href="{{ route('organizer.events.create') }}"
               class="px-6 py-3 bg-indigo-600 text-white rounded-2xl font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 active:scale-95 transition">
                + Tambah Event Baru
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-4 rounded-2xl mb-6 font-bold text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200 shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-100">
                    <tr>
                        <th class="px-6 py-3 font-semibold">Poster</th>
                        <th class="px-6 py-3 font-semibold">Event</th>
                        <th class="px-6 py-3 font-semibold">Harga / Stok</th>
                        <th class="px-6 py-3 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($events as $event)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4">
                                <img src="{{ ($event->poster_path && Storage::disk('public')->exists($event->poster_path))
                                    ? asset('storage/' . $event->poster_path)
                                    : 'https://placehold.co/64x80' }}" class="w-12 h-16 rounded-xl object-cover shadow-sm">
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-bold text-slate-800">{{ $event->title }}</p>
                                <p class="text-xs text-slate-400">{{ $event->category->name ?? '-' }} &middot; {{ \Carbon\Carbon::parse($event->date)->format('d M Y, H:i') }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-bold text-indigo-600">Rp {{ number_format($event->price, 0, ',', '.') }}</p>
                                <p class="text-xs text-slate-400">Stok: {{ $event->stock }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('organizer.events.edit', $event->id) }}"
                                       class="p-2.5 bg-indigo-50 text-indigo-600 rounded-xl hover:bg-indigo-600 hover:text-white transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <form action="{{ route('organizer.events.destroy', $event->id) }}" method="POST"
                                          onsubmit="return confirm('Yakin mau hapus event ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2.5 bg-rose-50 text-rose-600 rounded-xl hover:bg-rose-600 hover:text-white transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-slate-400">
                                Kamu belum punya event. Yuk buat event pertamamu!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 bg-slate-50 border-t">
            {{ $events->links() }}
        </div>
    </div>
</div>
@endsection