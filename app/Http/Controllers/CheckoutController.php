<?php

namespace App\Http\Controllers;

use App\Jobs\SendEventNotification;
use App\Models\Category;
use App\Models\Event;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function create(Event $event)
    {
        $categories = Category::all();
        $googleUser = auth()->check() ? auth()->user() : null;

        return view('checkout.create', compact('event', 'categories', 'googleUser'));
    }

    public function store(Request $request, Event $event)
    {
        $request->merge([
            'customer_name'  => trim($request->input('customer_name', '')),
            'customer_email' => trim($request->input('customer_email', '')),
            'customer_phone' => trim($request->input('customer_phone', '')),
        ]);

        $request->validate([
            'customer_name'  => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:20',
        ]);

        $customerEmail = strtolower($request->customer_email);

        if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            return back()->with('error', 'Alamat email tidak valid.');
        }

        if ($event->stock <= 0) {
            return back()->with(
                'error',
                'Mohon maaf, tiket untuk acara ini sudah habis.'
            );
        }

        $orderId = 'TRX-' . time() . '-' . strtoupper(Str::random(5));

        $isFreeEvent = (int) $event->price === 0;
        $totalPrice = $isFreeEvent ? 0 : $event->price + 5000;

        $transaction = Transaction::create([
            'event_id'       => $event->id,
            'order_id'       => $orderId,
            'customer_name'  => $request->customer_name,
            'customer_email' => $customerEmail,
            'customer_phone' => $request->customer_phone,
            'total_price'    => $totalPrice,
            'status'         => 'pending',
        ]);

        if ($isFreeEvent) {
            $transaction->update(['status' => 'success']);

            if ($event->stock > 0) {
                $event->decrement('stock');
            }

            try {
                Mail::to($transaction->customer_email)
                    ->queue(new \App\Mail\EventTicketMail($transaction));

                dispatch(new SendEventNotification($transaction, 'success'));
            } catch (\Exception $e) {
                Log::error('Gagal mengantri notifikasi e-ticket gratis: ' . $e->getMessage());
            }

            return redirect()->route('checkout.success', ['order_id' => $orderId]);
        }

        $successUrl = route('checkout.success', [
            'order_id' => $orderId
        ], true);

        $params = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => $totalPrice,
            ],
            'customer_details' => [
                'first_name' => $request->customer_name,
                'email'      => $customerEmail,
                'phone'      => $request->customer_phone,
            ],
            'finish_redirect_url' => $successUrl,
            'unfinish_redirect_url' => $successUrl,
            'error_redirect_url' => $successUrl,
            'notification_url' => env(
                'MIDTRANS_NOTIFICATION_URL',
                route('midtrans.callback', [], true)
            ),
        ];

        try {

            // Inisialisasi konfigurasi Midtrans SEBELUM generate Snap token.
            // Tanpa ini, \Midtrans\Config::$serverKey akan tetap null
            // meskipun env var MIDTRANS_SERVER_KEY sudah di-set di server.
            \Midtrans\Config::$serverKey = config('midtrans.server_key');
            \Midtrans\Config::$isProduction = config('midtrans.is_production');
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;

            $snapToken = \Midtrans\Snap::getSnapToken($params);
            
            $transaction->update([
                'snap_token' => $snapToken,
            ]);
// Di method store, setelah generate Snap token:
dispatch(new SendEventNotification($transaction, 'pending'));
            // GANTI dengan ini (panggil langsung):
$job = new \App\Jobs\SendEventNotification($transaction, 'pending');
$job->handle(app(\App\Services\WhatsAppService::class));

            return redirect()->route(
                'checkout.payment',
                $transaction->order_id
            );

        } catch (\Exception $e) {

            $transaction->delete();

            return back()->with(
                'error',
                'Gagal memproses pembayaran: ' . $e->getMessage()
            );
        }
    }

    public function payment($order_id)
    {
        $categories = Category::all();

        $transaction = Transaction::with('event')
            ->where('order_id', $order_id)
            ->firstOrFail();

        return view(
            'checkout.payment',
            compact('transaction', 'categories')
        );
    }

    public function success($order_id)
    {
        $categories = Category::all();

        $transaction = Transaction::with('event')
            ->where('order_id', $order_id)
            ->firstOrFail();

        return view(
            'checkout.success',
            compact('transaction', 'categories')
        );
    }
}