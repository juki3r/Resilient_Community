<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FacebookService
{


    public function postToPage($pageId, $pageToken, $message)
    {
        $url = "https://graph.facebook.com/{$pageId}/feed";

        $response = Http::asForm()->post($url, [
            'message' => $message,
            'access_token' => $pageToken,
        ]);

        if (!$response->successful()) {
            throw new \Exception($response->body());
        }

        return $response->json();
    }
}
