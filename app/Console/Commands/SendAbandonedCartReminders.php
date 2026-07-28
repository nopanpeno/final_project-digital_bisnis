<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendAbandonedCartReminders extends Command
{
    /**
     * Nama & signature command.
     * Bisa dites manual: php artisan cart:remind-abandoned
     */
    protected $signature = 'cart:remind-abandoned
                            {--minutes=10 : Minimal umur transaksi pending (menit) sebelum dianggap "ditinggal"}
                            {--window=24 : Batas maksimal umur transaksi pending (jam) yang masih diingatkan}';

    protected $description = 'Kirim ulang link pembayaran Midtrans via WhatsApp untuk transaksi pending yang ditinggal user (abandoned cart recovery)';

    public function handle(WhatsAppService $whatsAppService): int
    {
        $minMinutes = (int) $this->option('minutes');
        $maxHours   = (int) $this->option('window');

        // Kandidat: pending, dibuat minimal $minMinutes lalu (dianggap "ditinggal"),
        // tapi tidak lebih tua dari $maxHours (di luar itu anggap sudah mati/expired, tidak usah diganggu lagi),
        // dan belum pernah dikirimi reminder sebelumnya.
        $abandoned = Transaction::where('status', 'pending')
            ->whereNull('reminder_sent_at')
            ->where('created_at', '<=', now()->subMinutes($minMinutes))
            ->where('created_at', '>=', now()->subHours($maxHours))
            ->whereNotNull('customer_phone')
            ->get();

        if ($abandoned->isEmpty()) {
            $this->info('Tidak ada transaksi pending yang perlu diingatkan.');
            return self::SUCCESS;
        }

        $sent = 0;

        foreach ($abandoned as $transaction) {
            $paymentUrl = route('checkout.payment', $transaction->order_id, true);

            $message = "Halo {$transaction->customer_name}, sepertinya transaksi tiket Anda untuk order #{$transaction->order_id} belum diselesaikan. "
                . "Yuk lanjutkan pembayaran sebelum kehabisan slot: {$paymentUrl}";

            try {
                $ok = $whatsAppService->send($transaction->customer_phone, $message);

                // Tetap tandai reminder_sent_at walau gagal kirim, supaya command ini
                // tidak terus-menerus retry ke nomor yang sama tiap kali dijalankan.
                $transaction->update(['reminder_sent_at' => now()]);

                if ($ok) {
                    $sent++;
                } else {
                    Log::warning('Abandoned cart reminder gagal terkirim', [
                        'order_id' => $transaction->order_id,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Abandoned cart reminder error: ' . $e->getMessage(), [
                    'order_id' => $transaction->order_id,
                ]);
            }
        }

        $this->info("Reminder terkirim: {$sent} dari {$abandoned->count()} transaksi pending.");

        return self::SUCCESS;
    }
}