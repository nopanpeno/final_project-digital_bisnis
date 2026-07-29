<?php

namespace App\Jobs;

use App\Mail\EventTicketMail;
use App\Models\Transaction;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEventNotification
{
    // Tidak ada implements ShouldQueue di sini!

    public function __construct(
        public Transaction $transaction,
        public string $type
    ) {}

    public function handle(WhatsAppService $wa): void
    {
        try {
            Log::info('Processing notification synchronously', [
                'order_id' => $this->transaction->order_id,
                'type' => $this->type,
            ]);

            if ($this->type === 'success') {
                // 1. Kirim Email E-ticket langsung
                Mail::to($this->transaction->customer_email)
                    ->send(new EventTicketMail($this->transaction));

                // 2. Kirim WA Success langsung
                $this->sendWA($wa, 'success');
            } else {
                // Kirim WA Pending langsung
                $this->sendWA($wa, 'pending');
            }

            Log::info('Notification sent successfully', [
                'order_id' => $this->transaction->order_id,
            ]);
        } catch (\Throwable $e) {
            Log::error('SendEventNotification failed: ' . $e->getMessage(), [
                'order_id' => $this->transaction->order_id,
                'type' => $this->type,
                'error' => $e->getTraceAsString(),
            ]);
        }
    }

    private function sendWA(WhatsAppService $wa, string $type): void
    {
        $phone = $this->transaction->customer_phone;
        if (empty($phone)) {
            Log::warning('Phone number empty, skipping WA', [
                'order_id' => $this->transaction->order_id
            ]);
            return;
        }

        $name = $this->transaction->customer_name;
        $email = $this->transaction->customer_email;

        $message = $type === 'success'
            ? "Halo {$name}, pembayaran berhasil. E-ticket Anda sudah dikirim ke email {$email}. Terima kasih telah berpartisipasi."
            : "Halo {$name}, transaksi Anda sedang menunggu pembayaran. Silakan selesaikan pembayaran Anda.";

        $wa->send($phone, $message);
    }
}