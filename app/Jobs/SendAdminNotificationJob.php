<?php

namespace App\Jobs;

use App\Models\MobileUser;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SendAdminNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $type,
        public array $data,
        public ?string $barangay = null,
        public ?string $municipality = null
    ) {}

    public function handle()
    {
        // route by role type
        match ($this->type) {
            'resident' => $this->sendToResidents(),
            'mdrrmo' => $this->sendToMDRRMO(),
            default => $this->sendToAdmins(),
        };
    }

    /* =========================
        ADMINS NOTIFICATION BDRRMO
    ========================== */
    private function sendToAdmins()
    {
        $firebase = new FirebaseService();

        $admins = User::where('role', 'bdrrmo_admin')
            ->when(
                $this->barangay,
                fn($q) => $q->where('barangay', $this->barangay)
            )
            ->get();

        foreach ($admins as $admin) {

            if (!$admin->web_fcm_token) continue;

            $firebase->sendDataOnlyNotification($admin->web_fcm_token, [
                "message" => [
                    "token" => $admin->web_fcm_token,

                    "data" => [
                        "title" => $this->data['title'],
                        "body"  => $this->data['body'],
                        "url"   => $this->data['url'] ?? "/",
                        "type"  => $this->type,
                        "request_id" => (string) $this->data['request_id'],
                    ]
                ]
            ]);

            $this->sendSms($admin);
        }
    }

    /* =========================
        ADMINS NOTIFICATION MDRRMO and BDRRMO for Incidents / Emergencies
    ========================== */
    private function sendToMDRRMO()
    {
        $firebase = new FirebaseService();

        $admins = User::whereIn('role', 'mdrrmo_admin')
            ->when(
                $this->municipality,
                fn($q) => $q->where('municipality', $this->municipality)
            )
            ->get();

        foreach ($admins as $admin) {

            if (!$admin->web_fcm_token) continue;

            $firebase->sendDataOnlyNotification($admin->web_fcm_token, [
                "message" => [
                    "token" => $admin->web_fcm_token,

                    "data" => [
                        "title" => $this->data['title'],
                        "body"  => $this->data['body'],
                        "url"   => $this->data['url'] ?? "/",
                        "type"  => $this->type,
                        "request_id" => (string) $this->data['request_id'],
                    ]
                ]
            ]);

            $this->sendSms($admin);
        }
    }

    /* =========================
        RESIDENTS NOTIFICATION
    ========================== */
    private function sendToResidents()
    {
        $firebase = new FirebaseService();

        $residents = MobileUser::where('role', 'resident')
            ->when(
                $this->barangay,
                fn($q) => $q->where('barangay', $this->barangay)
            )
            ->get();

        foreach ($residents as $resident) {

            if (!$resident->fcm_token) continue;

            $firebase->sendNotification(
                $resident->fcm_token,
                $this->data['title'],
                $this->data['body'],
                [
                    "url" => $this->data['url'] ?? "/",
                    "type" => $this->type,
                    "request_id" => (string) $this->data['request_id'],
                ]
            );

            $this->sendSms($resident);
        }
    }

    /* =========================
        SMS SHARED FUNCTION
    ========================== */
    private function sendSms($user)
    {
        if (!$user->phone) return;

        Http::withHeaders([
            'X-API-KEY' => env('SMS_API_KEY')
        ])->post('https://carlesppo.com/api/send-sms-api', [
            'phone_number' => $user->phone,
            'message' => $this->data['sms'] ?? $this->data['body']
        ]);
    }
}
