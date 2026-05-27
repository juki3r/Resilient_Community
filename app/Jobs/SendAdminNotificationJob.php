<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Services\FirebaseService;

class SendAdminNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $type,
        public array $data,
        public ?string $barangay = null
    ) {}

    public function handle()
    {
        $firebase = new FirebaseService();

        $admins = User::where('role', 'bdrrmo_admin')
            ->when(
                $this->barangay,
                fn($q) =>
                $q->where('barangay', $this->barangay)
            )
            ->get();

        foreach ($admins as $admin) {

            if (!$admin->web_fcm_token) continue;

            $firebase->sendDataOnlyNotification($admin->web_fcm_token, [
                "message" => [
                    "token" => $admin->web_fcm_token,

                    "notification" => [
                        "title" => $this->data['title'],
                        "body"  => $this->data['body'],
                    ],

                    "data" => [
                        "url" => $this->data['url'] ?? "/",
                        "type" => $this->type,
                        "request_id" => (string) $this->data['request_id']
                    ]
                ]
            ]);
        }

        if ($admin->phone) {
            Http::withHeaders([
                'X-API-KEY' => env('SMS_API_KEY')
            ])->post('https://carlesppo.com/api/send-sms-api', [
                'phone_number' => $admin->phone,
                'message' => $this->data['sms'] ?? $this->data['body']
            ]);
        }
    }
}
