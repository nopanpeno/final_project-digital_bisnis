{{-- resources/views/organizer/analytics.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-12 mt-8">
    <h1 class="text-3xl font-bold mb-8">Analitik — {{ $organizer->name }}</h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-10">
        <div class="glass rounded-2xl p-6 border border-white/20 shadow-lg">
            <h5 class="text-slate-500 font-medium mb-4">Tiket Terjual per Bulan</h5>
            <canvas id="chartTickets" height="220"></canvas>
        </div>
        <div class="glass rounded-2xl p-6 border border-white/20 shadow-lg">
            <h5 class="text-slate-500 font-medium mb-4">Revenue per Bulan</h5>
            <canvas id="chartRevenue" height="220"></canvas>
        </div>
    </div>

    <h2 class="text-xl font-bold mb-4">Breakdown per Event</h2>
    <div class="overflow-x-auto rounded-2xl border border-slate-200 shadow">
        <table class="w-full text-left">
            <thead class="bg-slate-100">
                <tr>
                    <th class="px-6 py-3 font-semibold">Judul Event</th>
                    <th class="px-6 py-3 font-semibold text-right">Tiket Terjual</th>
                    <th class="px-6 py-3 font-semibold text-right">Revenue</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($eventBreakdown as $row)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-4">{{ $row->title }}</td>
                        <td class="px-6 py-4 text-right">{{ $row->tickets_sold ?? 0 }}</td>
                        <td class="px-6 py-4 text-right">
                            Rp {{ number_format($row->revenue ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-6 py-4 text-slate-400">Belum ada data penjualan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
    const chartLabels  = @json($chartLabels);
    const chartTickets = @json($chartTickets);
    const chartRevenue = @json($chartRevenue);

    new Chart(document.getElementById('chartTickets'), {
        type: 'bar',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Tiket Terjual',
                data: chartTickets,
                backgroundColor: 'rgba(99, 102, 241, 0.6)',
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });

    new Chart(document.getElementById('chartRevenue'), {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'Revenue',
                data: chartRevenue,
                borderColor: 'rgba(245, 158, 11, 1)',
                backgroundColor: 'rgba(245, 158, 11, 0.15)',
                fill: true,
                tension: 0.35,
                pointRadius: 3,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (value) => 'Rp ' + value.toLocaleString('id-ID')
                    }
                }
            }
        }
    });
</script>
@endsection