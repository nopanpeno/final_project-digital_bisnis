<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('Midtrans Webhook DITERIMA', [
            'order_id' => $request->order_id,
            'status' => $request->transaction_status
        ]);

        $transaction = Transaction::where('order_id', $request->order_id)->first();
        
        if (!$transaction) {
            Log::error('Transaction tidak ditemukan', ['order_id' => $request->order_id]);
            return response()->json(['error' => 'Transaction not found'], 404);
        }

        // Simpan data untuk diproses nanti
        $orderId = $request->order_id;
        $status = $request->transaction_status;

        // RESPON CEPAT KE MIDTRANS (dalam <1 detik)
        // Ini mencegah timeout
        return response()->json(['status' => 'ok'], 200)
            ->header('Content-Type', 'application/json');
    }

    public function __destruct()
    {
        // Fungsi ini akan dijalankan SETELAH response dikirim ke Midtrans
        // Jadi tidak mempengaruhi response time
        if (isset($this->orderId) && isset($this->status)) {
            $this->processNotification($this->orderId, $this->status);
        }
    }

    private function processNotification(string $orderId, string $status)
    {
        try {
            $transaction = Transaction::where('order_id', $orderId)->first();
            if (!$transaction) return;

            DB::transaction(function () use ($transaction, $status) {
                switch ($status) {
                    case 'settlement':
                    case 'capture':
                        $event = $transaction->event()->lockForUpdate()->first();
                        if ($event && $event->stock > 0) {
                            $event->decrement('stock');
                        }
                        $transaction->update(['status' => 'success']);

                        // Kirim notifikasi
                        $this->sendSuccessNotification($transaction);
                        break;

                    case 'pending':
                        $transaction->update(['status' => 'pending']);
                        break;

                    case 'expire':
                    case 'cancel':
                        $transaction->update(['status' => 'expired']);
                        break;
                }
            });

            Log::info('Webhook processed successfully', ['order_id' => $orderId]);
        } catch (\Exception $e) {
            Log::error('Error processing webhook after response: ' . $e->getMessage(), [
                'order_id' => $orderId
            ]);
        }
    }

    private function sendSuccessNotification(Transaction $transaction)
    {
        try {
            // Kirim Email
            \Illuminate\Support\Facades\Mail::to($transaction->customer_email)
                ->send(new \App\Mail\EventTicketMail($transaction));

            // Kirim WhatsApp
            if (!empty($transaction->customer_phone)) {
                $wa = new \App\Services\WhatsAppService();
                $message = "Halo {$transaction->customer_name}, pembayaran berhasil. E-ticket Anda sudah dikirim ke email {$transaction->customer_email}. Terima kasih telah berpartisipasi.";
                $wa->send($transaction->customer_phone, $message);
            }

            Log::info('Success notification sent', ['order_id' => $transaction->order_id]);
        } catch (\Exception $e) {
            Log::error('Failed to send success notification: ' . $e->getMessage());
        }
    }
}