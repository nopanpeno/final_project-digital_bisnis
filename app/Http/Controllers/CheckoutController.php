<?php
namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Event;
use App\Models\Transaction;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    protected WhatsAppService $whatsAppService;

    public function __construct()
    {
        \Midtrans\Config::$serverKey    = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction = false; // Ubah ke true jika sudah production
        \Midtrans\Config::$isSanitized  = true;
        \Midtrans\Config::$is3ds        = true;
        $this->whatsAppService          = new WhatsAppService();
    }

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

        if ($event->stock <= 0) {
            return back()->with('error', 'Mohon maaf, tiket untuk acara ini sudah habis.');
        }

        $orderId     = 'TRX-' . time() . '-' . strtoupper(Str::random(5));
        $isFreeEvent = (int) $event->price === 0;
        $totalPrice  = $isFreeEvent ? 0 : $event->price + 5000;

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
                Mail::to($transaction->customer_email)->send(new \App\Mail\EventTicketMail($transaction));
                $this->sendSuccessWhatsApp($transaction);
            } catch (\Exception $e) {
                Log::error('Gagal mengirim e-ticket gratis: ' . $e->getMessage());
            }

            return redirect()->route('checkout.success', ['order_id' => $orderId]);
        }

        $successUrl = route('checkout.success', ['order_id' => $orderId], true);

        $params = [
            'transaction_details'   => [
                'order_id'     => $orderId,
                'gross_amount' => $totalPrice,
            ],
            'customer_details'      => [
                'first_name' => $request->customer_name,
                'email'      => $customerEmail,
                'phone'      => $request->customer_phone,
            ],
            'finish_redirect_url'   => $successUrl,
            'unfinish_redirect_url' => $successUrl,
            'error_redirect_url'    => $successUrl,
            'notification_url'      => env('MIDTRANS_NOTIFICATION_URL', route('midtrans.callback', [], true)),
        ];

        try {
            $snapToken = \Midtrans\Snap::getSnapToken($params);
            $transaction->update(['snap_token' => $snapToken]);

            // 🔥 KIRIM NOTIF PENDING SECARA LANGSUNG (SINKRONUS)
            $this->sendPendingWhatsApp($transaction, $request->customer_phone);

            return redirect()->route('checkout.payment', $transaction->order_id);

        } catch (\Exception $e) {
            $transaction->delete();
            Log::error('Midtrans error: ' . $e->getMessage());
            return back()->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }

    protected function sendPendingWhatsApp(Transaction $transaction, string $phone): void
    {
        if (empty($phone)) {
            return;
        }

        $paymentUrl = route('checkout.payment', $transaction->order_id, true);
        $message    = "Halo {$transaction->customer_name}, transaksi Anda sedang menunggu pembayaran. Silakan selesaikan pembayaran di: {$paymentUrl}";

        $this->whatsAppService->send($phone, $message);
    }

    protected function sendSuccessWhatsApp(Transaction $transaction): void
    {
        $phone = $transaction->customer_phone;
        if (empty($phone)) {
            return;
        }

        $message = "Halo {$transaction->customer_name}, pembayaran berhasil. E-ticket Anda sudah dikirim ke email {$transaction->customer_email}. Terima kasih telah berpartisipasi.";

        $this->whatsAppService->send($phone, $message);
    }

    public function payment($order_id)
    {
        $categories  = Category::all();
        $transaction = Transaction::with('event')->where('order_id', $order_id)->firstOrFail();
        return view('checkout.payment', compact('transaction', 'categories'));
    }

    public function success($order_id)
    {
        $categories  = Category::all();
        $transaction = Transaction::with('event')->where('order_id', $order_id)->firstOrFail();

        try {
            $status     = \Midtrans\Transaction::status($order_id);
            $trx_status = is_array($status) ? ($status['transaction_status'] ?? '') : ($status->transaction_status ?? '');

            if (in_array($trx_status, ['settlement', 'capture']) && $transaction->status === 'pending') {
                $transaction->update(['status' => 'success']);

                if ($transaction->event && $transaction->event->stock > 0) {
                    $transaction->event->decrement('stock');
                }

                try {
                    Mail::to($transaction->customer_email)->send(new \App\Mail\EventTicketMail($transaction));
                    $this->sendSuccessWhatsApp($transaction);
                } catch (\Exception $e) {
                    Log::error('Gagal mengirim email E-Ticket di success page: ' . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            Log::error('Midtrans Error di success page: ' . $e->getMessage());
        }

        return view('checkout.success', compact('transaction', 'categories'));
    }
}
