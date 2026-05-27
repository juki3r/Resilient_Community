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
        public string $type,        // e.g. "certificate", "blotter"
        public array $data,         // dynamic payload
        public $barangayId = null,
    ) {}

    public function handle()
    {
        $firebase = new FirebaseService();

        $admins = User::where('role', 'bdrrmo_admin');

        if ($this->barangayId) {
            $admins->where('barangay', $this->barangayId);
        }

        $admins = $admins->get();

        foreach ($admins as $admin) {

            // FIREBASE
            if ($admin->web_fcm_token) {
                $firebase->sendDataOnlyNotification(
                    $admin->web_fcm_token,
                    [
                        'title' => $this->data['title'] ?? 'Notification',
                        'body'  => $this->data['body'] ?? '',
                        'type'  => $this->type,
                        'payload' => $this->data,
                    ]
                );
            }

            // SMS
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
}
