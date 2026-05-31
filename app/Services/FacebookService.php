<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FacebookService
{


    public function postToPage($pageId, $pageToken, $message)
    {
        $url = "https://graph.facebook.com/{$pageId}/feed";

        return Http::post($url, [
            'message' => $message,
            'access_token' => $pageToken,
        ])->json();
    }
}
