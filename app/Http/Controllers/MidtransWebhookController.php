<?php
namespace App\Http\Controllers;

use App\Jobs\SendEventNotification;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();

        $orderId           = $payload['order_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus       = $payload['fraud_status'] ?? null;

        $signatureKey = $payload['signature_key'] ?? null;

        if (! $orderId || ! $signatureKey || ! $this->isValidMidtransSignature($payload)) {
            Log::warning('Invalid Midtrans webhook signature', ['payload' => $payload]);

            return response()->json([
                'message' => 'Invalid signature',
            ], 403);
        }

        $transaction = Transaction::with('event')
            ->where('order_id', $orderId)
            ->first();

        if (! $transaction) {
            return response()->json([
                'message' => 'Transaction not found',
            ], 404);
        }

        // Hindari proses dua kali
        if (in_array($transaction->status, ['success', 'settlement'])) {
            return response()->json([
                'message' => 'Already processed',
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
                $transaction->status = 'success';
                $this->processSuccess($transaction);
                break;

            case 'pending':
                $transaction->status = 'pending';
                $transaction->save();
                dispatch(new SendEventNotification($transaction, 'pending'));
                break;

            case 'cancel':
            case 'deny':
            case 'expire':
                $transaction->status = 'failed';
                break;
        }

        $transaction->save();

        return response()->json([
            'message' => 'OK',
        ]);
    }

    private function processSuccess(Transaction $transaction)
    {
        DB::transaction(function () use ($transaction) {
            $event = $transaction->event()->lockForUpdate()->first();

            if ($event && $event->stock > 0) {
                $event->decrement('stock');
                dispatch(new SendEventNotification($transaction, 'success'));
            } else {
                Log::warning('Stock habis setelah pembayaran berhasil', [
                    'order_id' => $transaction->order_id,
                ]);
            }
        });
    }

    private function isValidMidtransSignature(array $payload): bool
    {
        $orderId      = $payload['order_id'] ?? '';
        $statusCode   = $payload['status_code'] ?? '';
        $grossAmount  = $payload['gross_amount'] ?? '';
        $signatureKey = $payload['signature_key'] ?? '';
        $serverKey    = env('MIDTRANS_SERVER_KEY', '');

        if (empty($orderId) || empty($statusCode) || empty($grossAmount) || empty($signatureKey) || empty($serverKey)) {
            return false;
        }

        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        return hash_equals($expectedSignature, $signatureKey);
    }
}
