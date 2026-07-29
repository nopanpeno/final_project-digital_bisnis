<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class MyTicketController extends Controller
{
    /**
     * Menampilkan riwayat transaksi/tiket milik user yang sedang login.
     * Sengaja TIDAK difilter berdasarkan tanggal event, supaya event yang
     * sudah lewat (dan butuh direview) tetap muncul di sini.
     */
    public function index()
    {
        $email = Auth::user()->email;

        $transactions = Transaction::with('event')
            ->where('customer_email', $email)
            ->whereIn('status', ['success', 'settlement', 'capture'])
            ->latest()
            ->get();

        return view('my-tickets', compact('transactions'));
    }
}