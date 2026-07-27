<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Transaction;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Menjumlahkan semua nominal total_price dari kolom Transaksi Lunas
        $totalRevenue = Transaction::whereIn('status', ['settlement', 'success'])->sum('total_price');
        
        // 2. Menghitung Berapa orang tamu yang tiketnya sudah Lunas
        $ticketsSold = Transaction::whereIn('status', ['settlement', 'success'])->count();
        
        // 3. Menghitung Jumlah Acara Mendatang yang aktif diselenggarakan
        $activeEvents = Event::where('date', '>=', now())->count();
        
        // 4. Menghitung Transaksi Ngadat (Status belum dibayar pelanggan / Expired)
        $pendingOrders = Transaction::where('status', 'pending')->count();
        
        // 5. Menyertakan 5 daftar riwayat pesanan (History) paling mutakhir di panel
        $recentTransactions = Transaction::with('event')->latest()->take(5)->get();

        // 6. Data ringkas untuk dashboard admin: pertumbuhan pengguna & event
        $totalUsers = User::count();
        $newUsersThisMonth = User::where('created_at', '>=', now()->subMonth())->count();
        $usersCreatedLastMonth = User::whereBetween('created_at', [now()->subMonths(2), now()->subMonth()])->count();

        $eventsCreatedThisMonth = Event::where('created_at', '>=', now()->subMonth())->count();
        $eventsCreatedLastMonth = Event::whereBetween('created_at', [now()->subMonths(2), now()->subMonth()])->count();

        $userGrowth = $usersCreatedLastMonth > 0
            ? round((($newUsersThisMonth - $usersCreatedLastMonth) / max($usersCreatedLastMonth, 1)) * 100, 1)
            : 0;

        $eventGrowth = $eventsCreatedLastMonth > 0
            ? round((($eventsCreatedThisMonth - $eventsCreatedLastMonth) / max($eventsCreatedLastMonth, 1)) * 100, 1)
            : 0;

        $userGrowthLabel = $userGrowth >= 0 ? '+' . $userGrowth . '%' : $userGrowth . '%';
        $eventGrowthLabel = $eventGrowth >= 0 ? '+' . $eventGrowth . '%' : $eventGrowth . '%';

        $userGrowthSeries = [];
        $eventGrowthSeries = [];
        $monthLabels = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $monthLabels[] = now()->subMonths($i)->translatedFormat('M');

            $userGrowthSeries[] = User::whereBetween('created_at', [
                now()->subMonths($i)->startOfMonth(),
                now()->subMonths($i)->endOfMonth(),
            ])->count();

            $eventGrowthSeries[] = Event::whereBetween('created_at', [
                now()->subMonths($i)->startOfMonth(),
                now()->subMonths($i)->endOfMonth(),
            ])->count();
        }

        return view('admin.dashboard', compact(
            'totalRevenue',
            'ticketsSold',
            'activeEvents',
            'pendingOrders',
            'recentTransactions',
            'totalUsers',
            'newUsersThisMonth',
            'eventsCreatedThisMonth',
            'userGrowthLabel',
            'eventGrowthLabel',
            'userGrowthSeries',
            'eventGrowthSeries',
            'monthLabels'
        ));
    }


    function indexEvent(){
        return view('admin.events');
    }

    function indexTransaction(){
        return view('admin.transactions');
    }
}
