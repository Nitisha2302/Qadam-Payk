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

        // 🔑 Get Firebase OAuth2 token
        $client = new Client();
        $client->setAuthConfig(storage_path('app/service-account.json'));
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

            // ✅ Always send both notification + data for compatibility
            $payload = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => (string) $data['title'],
                        'body'  => (string) $data['body'],
                    ],
                    'data' => [
                        'title' => (string) $data['title'],
                        'body'  => (string) $data['body'],
                        'notification_type' => (string) $data['notification_type'],
                    ]
                ]
            ];

            Log::info("🚀 Sending FCM request", [
                'user_id' => $userId,
                'payload' => $payload
            ]);

            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ])->post(
                    'https://fcm.googleapis.com/v1/projects/' . env('FIREBASE_PROJECT_ID') . '/messages:send',
                    $payload
                );

                if ($response->successful()) {
                    Log::info("✅ FCM notification sent successfully", [
                        'user_id' => $userId,
                        'token' => $token,
                        'response' => $response->json()
                    ]);

                    // 💾 Save in DB
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

   public function sendAdminNotification($title, $description, $userIds = [], $announcementDate = null, $image = null,$type = 1)
    {
        // ✅ Map type → notification_type
       $notificationType = ($type == 2) ? 100 : 99;
        if (in_array('all', $userIds)) {
            $users = \App\Models\User::all();
        } else {
            $users = \App\Models\User::whereIn('id', $userIds)->get();
        }
        
 
        $tokens = [];
        foreach ($users as $user) {
            if (!empty($user->device_token)) { // use device_token from DB
                $tokens[] = [
                    'user_id' => $user->id,
                    'device_type' => strtolower($user->device_type ?? ''),
                    'device_token' => $user->device_token,
                    'name' => $user->name,
                ];
            }
        }
 
        if (empty($tokens)) {
            return ['status' => false, 'message' => 'No valid FCM tokens found.'];
        }
 
        $data = [
             'notification_type' => $notificationType,
            'title' => $title,
            'body' => $description,
            'announcement_date' => $announcementDate,
            'image' => $image,
             'type' => (string) $type
        ];
 
        return $this->sendAdminNotificationmain($tokens, $data);

    }
 
    public function sendAdminNotificationmain(array $tokens, array $data)
    {
        Log::info("🔔 FCMService: sendNotification called", [
            'tokens_count' => count($tokens),
            'notification_type' => $data['notification_type'],
            'title' => $data['title'],
            'body' => $data['body']
        ]);
 
        // 🔑 Get Firebase OAuth2 token
        $client = new Client();
        $client->setAuthConfig(storage_path('app/service-account.json'));
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->fetchAccessTokenWithAssertion();
        $accessToken = $client->getAccessToken()['access_token'];
 
        foreach ($tokens as $tokenInfo) {
            $token = $tokenInfo['device_token'];
            $deviceType = $tokenInfo['device_type'];
            $userId = $tokenInfo['user_id'];
            $imageUrl = $data['image']
            ? asset('assets/banner/' . $data['image'])
            : null;
 
            Log::info("📱 Preparing FCM payload", [
                'user_id' => $userId,
                'device_type' => $deviceType,
                'token' => $token,
            ]);
 
            // ✅ Always send both notification + data for compatibility
            $payload = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => (string) $data['title'],
                        'body'  => (string) $data['body'],
                       'image' => $imageUrl,
                    ],
                    'data' => [
                        'title' => (string) $data['title'],
                        'body'  => (string) $data['body'],
                        'notification_type' => (string) $data['notification_type'],
                           'announcement_date' => (string) ($data['announcement_date'] ?? ''),
                       'image' => $imageUrl ?? '',
                        'notification_type' => (string) $data['notification_type'],
                         'type' => (string) $data['type'], 
                    ]
                ]
            ];
 
            Log::info("🚀 Sending FCM request", [
                'user_id' => $userId,
                'payload' => $payload
            ]);
 
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ])->post(
                    'https://fcm.googleapis.com/v1/projects/' . env('FIREBASE_PROJECT_ID') . '/messages:send',
                    $payload
                );
 
                if ($response->successful()) {
                    Log::info("✅ FCM notification sent successfully", [
                        'user_id' => $userId,
                        'token' => $token,
                        'response' => $response->json()
                    ]);
                  
                    // 💾 Save in DB
                    $notification = Notification::create([
                        'user_id' => $userId,
                        'title' => $data['title'],
                        'description' => $data['body'],
                        'notification_type' => $data['notification_type'],
                        'notification_created_at' => now(),
                         'announcement_date' => $data['announcement_date'],
                       'image' => $data['image'],   // ✅ FIXED
                        'type' => $data['type'],
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


    public function sendCourierNotification(array $tokens, array $data)
    {
        Log::info("🔔 FCM Started", ['tokens_count' => count($tokens)]);

        try {
            $client = new \Google\Client();
            $client->setAuthConfig(storage_path('app/service-account.json'));
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->fetchAccessTokenWithAssertion();

            $accessToken = $client->getAccessToken()['access_token'];

        } catch (\Exception $e) {
            Log::error("❌ Firebase Auth Error", ['error' => $e->getMessage()]);
            return;
        }

        foreach ($tokens as $tokenInfo) {

            $payload = [
                'message' => [
                    'token' => $tokenInfo['device_token'],
                    'notification' => [
                        'title' => $data['title'],
                        'body'  => $data['body'],
                    ],
                    'data' => array_map('strval', $data)
                ]
            ];

            try {

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json',
                ])->post(
                    'https://fcm.googleapis.com/v1/projects/' . env('FIREBASE_PROJECT_ID') . '/messages:send',
                    $payload
                );

                if ($response->successful()) {

                    Log::info("✅ Sent to User ID: " . $tokenInfo['user_id']);

                    Notification::create([
                        'user_id' => $tokenInfo['user_id'],
                        'title' => $data['title'],
                        'description' => $data['body'],
                        'notification_type' => $data['notification_type'],
                        'notification_created_at' => now(),
                    ]);

                } else {

                    Log::error("❌ FCM Failed", [
                        'user_id' => $tokenInfo['user_id'],
                        'response' => $response->json()
                    ]);
                }

            } catch (\Exception $e) {

                Log::error("🔥 FCM Exception", [
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info("✅ FCM Finished");
    }


}
