<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    protected $apiUrl = 'https://fcm.googleapis.com/v1/projects/estancia-daan-banwa/messages:send';
    protected $credentialsPath;

    public function __construct()
    {
        $this->credentialsPath = config_path('firebase/firebase-adminsdk.json');
    }

    public function sendNotification($fcmToken, $title, $body, $data = [])
    {
        try {
            $accessToken = $this->getAccessToken();
            $client = new Client();

            $response = $client->post($this->apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'message' => [
                        'token' => $fcmToken,

                        'notification' => [
                            'title' => $title,
                            'body'  => $body,
                        ],

                        'data' => $data, // 👈 IMPORTANT
                    ],
                ],
            ]);

            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Log::error('Firebase send error: ' . $e->getMessage());
            return false;
        }
    }

    private function getAccessToken()
    {
        $credentials = json_decode(file_get_contents($this->credentialsPath), true);

        $client = new \Google\Client();
        $client->setAuthConfig($credentials);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        $token = $client->fetchAccessTokenWithAssertion();
        return $token['access_token'];
    }

    public function sendNotificationToAll($title, $body)
    {
        // 1️⃣ Get all users who have FCM tokens
        $tokens = \App\Models\User::whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->toArray();

        if (empty($tokens)) {
            \Log::info('No FCM tokens found.');
            return;
        }

        // 2️⃣ Loop through each token and send the notification
        foreach ($tokens as $token) {
            try {
                $this->sendNotification($token, $title, $body);
            } catch (\Exception $e) {
                \Log::error('FCM send failed for ' . $token . ': ' . $e->getMessage());
            }
        }
    }
}
