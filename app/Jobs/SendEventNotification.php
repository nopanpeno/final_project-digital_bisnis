<?php

namespace App\Jobs;

use App\Mail\EventTicketMail;
use App\Models\Transaction;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEventNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;
    public array $backoff = [10, 30, 60];

    public function __construct(
        public Transaction $transaction,
        public string $type
    ) {}

    public function handle(WhatsAppService $wa): void
    {
        try {
            if ($this->type === 'success') {
                Mail::to($this->transaction->customer_email)
                    ->send(new EventTicketMail($this->transaction));

                $this->sendWA($wa, 'success');
            } else {
                $this->sendWA($wa, 'pending');
            }
        } catch (\Throwable $e) {
            Log::error('SendEventNotification failed: ' . $e->getMessage(), [
                'order_id' => $this->transaction->order_id,
                'type' => $this->type,
            ]);

            $this->fail($e);
        }
    }

    private function sendWA(WhatsAppService $wa, string $type): void
    {
        $phone = $this->transaction->customer_phone;
        if (empty($phone)) {
            return;
        }

        $name = $this->transaction->customer_name;
        $email = $this->transaction->customer_email;

        $message = $type === 'success'
            ? "Halo {$name}, pembayaran berhasil. E-ticket sudah dikirim ke {$email}."
            : "Halo {$name}, selesaikan pembayaran di: " . route('checkout.payment', $this->transaction->order_id, true);

        $wa->send($phone, $message);
    }
}