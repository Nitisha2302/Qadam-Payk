<?php

namespace App\Services;

use Google\Client;
use Illuminate\Support\Facades\Http;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class FCMService
{
    public function sendNotification(array $tokens, array $data)
    {
        Log::info("🔔 FCMService: sendNotification called", [
            'tokens_count' => count($tokens),
            'notification_type' => $data['notification_type'],
            'title' => $data['title'],
            'body' => $data['body']
        ]);

        $client = new Client();
        $client->setAuthConfig(storage_path('app/firebase/service-account.json'));
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->fetchAccessTokenWithAssertion();
        $accessToken = $client->getAccessToken()['access_token'];

        foreach ($tokens as $tokenInfo) {
            $token = $tokenInfo['device_token'];
            $deviceType = $tokenInfo['device_type'];
            $userId = $tokenInfo['user_id'];

            Log::info("📱 Preparing FCM payload", [
                'user_id' => $userId,
                'device_type' => $deviceType,
                'token' => $token,
            ]);

            $payload = [
                'message' => [
                    'token' => $token,
                    'data' => [
                        'title' => $data['title'],
                        'body' => $data['body'],
                        'notification_type' => (string)$data['notification_type'],
                    ]
                ]
            ];

            if ($deviceType === 'ios') {
                $payload['message']['notification'] = [
                    'title' => $data['title'],
                    'body' => $data['body'],
                ];
            }

            Log::info("🚀 Sending FCM request", [
                'user_id' => $userId,
                'payload' => $payload
            ]);

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ])->post('https://fcm.googleapis.com/v1/projects/' . env('FIREBASE_PROJECT_ID') . '/messages:send', $payload);

                if ($response->successful()) {
                    Log::info("✅ FCM notification sent successfully", [
                        'user_id' => $userId,
                        'token' => $token,
                        'response' => $response->json()
                    ]);

                    // Save notification in DB
                    $notification = Notification::create([
                        'user_id' => $userId,
                        'title' => $data['title'],
                        'description' => $data['body'],
                        'notification_type' => $data['notification_type'],
                        'notification_created_at' => now(),
                    ]);

                    Log::info("💾 Notification saved in database", [
                        'notification_id' => $notification->id
                    ]);
                } else {
                    Log::error("❌ FCM notification failed", [
                        'user_id' => $userId,
                        'token' => $token,
                        'response' => $response->json()
                    ]);
                }

            } catch (\Exception $e) {
                Log::error("🔥 Exception while sending FCM notification", [
                    'user_id' => $userId,
                    'token' => $token,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info("✅ FCMService: sendNotification finished");
    }
}
