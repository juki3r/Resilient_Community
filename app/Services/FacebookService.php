<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FacebookService
{
    public function postToPage($message)
    {
        $pageId = env('FB_PAGE_ID');
        $token = env('FB_PAGE_TOKEN');


        $url = "https://graph.facebook.com/{$pageId}/feed";

        return Http::post($url, [
            'message' => $message,
            'access_token' => $token,
        ])->json();
    }
}
