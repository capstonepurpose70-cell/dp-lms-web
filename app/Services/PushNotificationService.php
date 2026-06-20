<?php

namespace App\Services;

use App\Models\FcmToken;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    // Your Firebase project ID.
    private string $projectId = 'dp-lms';
    private string $fcmUrl;

    public function __construct()
    {
        $this->fcmUrl =
            "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";
    }

    /**
     * Get the service-account credentials as an array.
     * Priority: Railway env (base64) -> local file in storage.
     */
    private function credentials(): ?array
    {
        // 1) Production (Railway): base64-encoded JSON in env.
        $b64 = env('FIREBASE_CREDENTIALS_BASE64');
        if (!empty($b64)) {
            $json = base64_decode($b64, true);
            if ($json !== false) {
                $arr = json_decode($json, true);
                if (is_array($arr)) {
                    return $arr;
                }
            }
        }

        // 2) Local (XAMPP): file in storage/app/firebase/.
        $path = storage_path('app/firebase/firebase-service-account.json');
        if (file_exists($path)) {
            $arr = json_decode(file_get_contents($path), true);
            if (is_array($arr)) {
                return $arr;
            }
        }

        return null;
    }

    /**
     * Exchange the service account for a short-lived OAuth2 access token.
     */
    private function accessToken(): ?string
    {
        $creds = $this->credentials();
        if ($creds === null) {
            Log::error('FCM: no service account credentials found.');
            return null;
        }

        try {
            $sa = new ServiceAccountCredentials(
                'https://www.googleapis.com/auth/firebase.messaging',
                $creds
            );
            $token = $sa->fetchAuthToken();
            return $token['access_token'] ?? null;
        } catch (\Throwable $e) {
            Log::error('FCM access token error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Send a notification to a list of raw FCM tokens.
     */
    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): void
    {
        $tokens = array_values(array_unique(array_filter($tokens)));
        if (empty($tokens)) {
            return;
        }

        $accessToken = $this->accessToken();
        if ($accessToken === null) {
            return;
        }

        // FCM "data" values must all be strings.
        $stringData = [];
        foreach ($data as $k => $v) {
            $stringData[$k] = (string) $v;
        }

        foreach ($tokens as $token) {
            $payload = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    'data' => $stringData,
                    'android' => [
                        'priority' => 'high',
                        'notification' => [
                            'channel_id' => 'dp_lms_channel', // matches the app
                            'sound' => 'default',
                        ],
                    ],
                ],
            ];

            try {
                $res = Http::withToken($accessToken)
                    ->acceptJson()
                    ->post($this->fcmUrl, $payload);

                if ($res->failed()) {
                    $status = $res->json('error.status');
                    // Drop dead/invalid tokens so we stop retrying them.
                    if (in_array($status, ['UNREGISTERED', 'INVALID_ARGUMENT', 'NOT_FOUND'])) {
                        FcmToken::where('token', $token)->delete();
                    }
                    Log::warning('FCM send failed: ' . $res->body());
                }
            } catch (\Throwable $e) {
                Log::error('FCM send exception: ' . $e->getMessage());
            }
        }
    }

    /**
     * Send to every device token owned by the given user IDs.
     */
    public function sendToUsers(array $userIds, string $title, string $body, array $data = []): void
    {
        $userIds = array_values(array_unique(array_filter($userIds)));
        if (empty($userIds)) {
            return;
        }

        $tokens = FcmToken::whereIn('user_id', $userIds)->pluck('token')->all();
        $this->sendToTokens($tokens, $title, $body, $data);
    }
}