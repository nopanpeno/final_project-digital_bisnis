<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function send(string $to, string $message): bool
    {
        $token = env('WHATSAPP_API_TOKEN');
        $baseUrl = env('WHATSAPP_API_URL');

        if (empty($token) || empty($baseUrl)) {
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])->asForm()->post($baseUrl, [
                'target' => $to,
                'message' => $message,
                'countryCode' => '62',
            ]);

            if ($response->successful()) {
                Log::info('WhatsApp sent successfully: ' . $response->body());
                return true;
            }

            Log::warning('WhatsApp send failed. Status: ' . $response->status() . ' Body: ' . $response->body());
            return false;
        } catch (\Throwable $e) {
            Log::error('WhatsApp service error: ' . $e->getMessage());
            return false;
        }
    }
}
