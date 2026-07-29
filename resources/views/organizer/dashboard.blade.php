{{-- resources/views/organizer/dashboard.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-12 mt-8">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-bold">Dashboard Organizer: {{ $organizer->name }}</h1>
        <div class="flex items-center gap-6">
            <a href="{{ route('organizer.events.index') }}"
               class="text-sm font-medium text-indigo-600 hover:text-indigo-700">
                Kelola Event Saya →
            </a>
            <a href="{{ route('organizer.analytics') }}"
               class="text-sm font-medium text-indigo-600 hover:text-indigo-700">
                Lihat Analitik Lengkap →
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="glass rounded-2xl p-6 border border-white/20 shadow-lg">
            <h5 class="text-slate-500 font-medium mb-2">Total Event</h5>
            <p class="text-2xl font-bold text-indigo-600">{{ $totalEvents }}</p>
        </div>
        <div class="glass rounded-2xl p-6 border border-white/20 shadow-lg">
            <h5 class="text-slate-500 font-medium mb-2">Total Tiket Terjual</h5>
            <p class="text-2xl font-bold text-indigo-600">{{ $totalTicketsSold }}</p>
        </div>
        <div class="glass rounded-2xl p-6 border border-white/20 shadow-lg">
            <h5 class="text-slate-500 font-medium mb-2">Total Pendapatan</h5>
            <p class="text-2xl font-bold text-indigo-600">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
        </div>
    </div>

    <h2 class="text-xl font-bold mb-4">5 Event Terbaru</h2>
    <div class="overflow-x-auto rounded-2xl border border-slate-200 shadow">
        <table class="w-full text-left">
            <thead class="bg-slate-100">
                <tr>
                    <th class="px-6 py-3 font-semibold">Judul</th>
                    <th class="px-6 py-3 font-semibold">Tanggal</th>
                    <th class="px-6 py-3 font-semibold">Stok</th>
                    <th class="px-6 py-3 font-semibold text-right">Tiket Terjual</th>
                    <th class="px-6 py-3 font-semibold text-right">Revenue</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($events as $event)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-4">{{ $event->title }}</td>
                        <td class="px-6 py-4">{{ \Carbon\Carbon::parse($event->date)->format('d M Y') }}</td>
                        <td class="px-6 py-4">{{ $event->stock }}</td>
                        <td class="px-6 py-4 text-right">{{ $event->tickets_sold_count ?? 0 }}</td>
                        <td class="px-6 py-4 text-right">
                            Rp {{ number_format($event->revenue_sum ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-4 text-slate-400">Belum ada event.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection