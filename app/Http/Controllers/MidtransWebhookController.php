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

        // Proses berdasarkan status
        switch ($request->transaction_status) {
            case 'settlement':
            case 'capture':
                $this->processSuccess($transaction);
                break;
            case 'pending':
                $transaction->update(['status' => 'pending']);
                break;
            case 'expire':
            case 'cancel':
                $transaction->update(['status' => 'expired']);
                break;
        }

        // RESPON CEPAT KE MIDTRANS (200 OK) agar tidak RTO
        return response()->json(['status' => 'ok'], 200);
    }

    private function processSuccess(Transaction $transaction)
    {
        DB::transaction(function () use ($transaction) {
            $event = $transaction->event()->lockForUpdate()->first();

            if ($event && $event->stock > 0) {
                $event->decrement('stock');
            }

            $transaction->update(['status' => 'success']);

            // 🔥 PANGGIL LANGSUNG (SINKRONUS), BUKAN DISPATCH
            // Ini menjamin notif masuk detik itu juga tanpa antrian
            $job = new \App\Jobs\SendEventNotification($transaction, 'success');
            $job->handle(app(\App\Services\WhatsAppService::class));
        });
    }
}