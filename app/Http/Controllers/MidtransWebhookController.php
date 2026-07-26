<?php

namespace App\Http\Controllers;

use App\Mail\EventTicketMail;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();

        $orderId = $payload['order_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;

        if (!$orderId) {
            return response()->json([
                'message' => 'Invalid payload'
            ], 400);
        }

        $transaction = Transaction::with('event')
            ->where('order_id', $orderId)
            ->first();

        if (!$transaction) {
            return response()->json([
                'message' => 'Transaction not found'
            ], 404);
        }

        // Hindari proses dua kali
        if (in_array($transaction->status, ['success', 'settlement'])) {
            return response()->json([
                'message' => 'Already processed'
            ]);
        }

        switch ($transactionStatus) {

            case 'capture':
                if ($fraudStatus == 'challenge') {
                    $transaction->status = 'challenge';
                } else {
                    $transaction->status = 'success';
                    $this->processSuccess($transaction);
                }
                break;

            case 'settlement':
                $transaction->status = 'settlement';
                $this->processSuccess($transaction);
                break;

            case 'pending':
                $transaction->status = 'pending';
                break;

            case 'cancel':
            case 'deny':
            case 'expire':
                $transaction->status = 'failed';
                break;
        }

        $transaction->save();

        return response()->json([
            'message' => 'OK'
        ]);
    }

    private function processSuccess(Transaction $transaction)
    {
        $event = $transaction->event;

        if ($event && $event->stock > 0) {

            $event->stock -= 1;
            $event->save();

            try {

                Mail::to($transaction->customer_email)
                    ->send(new EventTicketMail($transaction));

            } catch (\Exception $e) {

                Log::error('Gagal mengirim email E-Ticket', [
                    'order_id' => $transaction->order_id,
                    'error' => $e->getMessage(),
                ]);

            }

        } else {

            Log::warning('Stock habis setelah pembayaran berhasil', [
                'order_id' => $transaction->order_id
            ]);

        }
    }
}