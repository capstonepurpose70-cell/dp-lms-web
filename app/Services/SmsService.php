<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SmsService
{
    public static function send(string $number, string $message): bool
    {
        try {
            $apiKey = config('services.semaphore.key');

            // If no API key configured, just log and skip
            if (!$apiKey) {
                Log::info('SMS skipped — no API key configured', [
                    'number'  => $number,
                    'message' => $message,
                ]);
                return false;
            }

            // Clean phone number
            $number = preg_replace('/\D/', '', $number);
            if (str_starts_with($number, '639')) {
                $number = '0' . substr($number, 2);
            }

            $response = \Illuminate\Support\Facades\Http::post(
                'https://api.semaphore.co/api/v4/messages',
                [
                    'apikey'     => $apiKey,
                    'number'     => $number,
                    'message'    => $message,
                    'sendername' => config('services.semaphore.sender', 'DP-LMS'),
                ]
            );

            if ($response->successful()) {
                Log::info('SMS sent successfully', ['number' => $number]);
                return true;
            }

            Log::warning('SMS failed', [
                'number'   => $number,
                'response' => $response->body(),
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('SMS error: ' . $e->getMessage());
            return false;
        }
    }
}