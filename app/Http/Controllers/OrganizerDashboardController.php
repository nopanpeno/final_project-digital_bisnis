<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class OrganizerDashboardController extends Controller
{
    /**
     * Dashboard utama organizer.
     * Event::query() otomatis ke-filter oleh OrganizerScope (cuma event milik organizer login).
     */
    public function index(Request $request)
    {
        $organizer = $request->user()->organizer;

        $events = Event::withCount([
                'transactions as tickets_sold_count' => function ($q) {
                    $q->where('status', 'success');
                },
                'transactions as revenue_sum' => function ($q) {
                    $q->where('status', 'success')->select(\DB::raw('sum(total_price)'));
                },
            ])
            ->latest()
            ->take(5)
            ->get();

        $totalRevenue = $organizer->totalRevenue();
        $totalTicketsSold = $organizer->totalTicketsSold();
        $totalEvents = Event::count(); // sudah ke-scope otomatis

        return view('organizer.dashboard', [
            'organizer' => $organizer,
            'events' => $events,
            'totalRevenue' => $totalRevenue,
            'totalTicketsSold' => $totalTicketsSold,
            'totalEvents' => $totalEvents,
        ]);
    }

    /**
     * Analitik: revenue & tiket terjual per bulan (6 bulan terakhir) untuk grafik,
     * plus breakdown per event.
     */
    public function analytics(Request $request)
    {
        $organizer = $request->user()->organizer;

        // Breakdown per event (cuma transaksi sukses)
        $eventBreakdown = Event::withCount([
                'transactions as tickets_sold' => fn ($q) => $q->where('status', 'success'),
            ])
            ->withSum([
                'transactions as revenue' => fn ($q) => $q->where('status', 'success'),
            ], 'total_price')
            ->latest()
            ->get();

        // Data per bulan (6 bulan terakhir) untuk Chart.js
        $eventIds = Event::pluck('id'); // ke-scope otomatis, cuma event milik organizer ini

        $monthly = \App\Models\Transaction::whereIn('event_id', $eventIds)
            ->where('status', 'success')
            ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as tickets, SUM(total_price) as revenue")
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Isi bulan yang kosong (biar grafik gak bolong)
        $labels = [];
        $ticketsData = [];
        $revenueData = [];
        for ($i = 5; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $labels[] = now()->subMonths($i)->translatedFormat('M Y');
            $row = $monthly->firstWhere('month', $key);
            $ticketsData[] = $row->tickets ?? 0;
            $revenueData[] = $row->revenue ?? 0;
        }

        return view('organizer.analytics', [
            'organizer' => $organizer,
            'eventBreakdown' => $eventBreakdown,
            'chartLabels' => $labels,
            'chartTickets' => $ticketsData,
            'chartRevenue' => $revenueData,
        ]);
    }
}